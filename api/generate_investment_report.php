<?php
require_once __DIR__ . '/../config/db.php';
if (file_exists(__DIR__ . '/../config/secrets.php')) {
    require_once __DIR__ . '/../config/secrets.php';
}

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

$property_id = isset($_POST['property_id']) ? intval($_POST['property_id']) : 0;

if ($property_id <= 0) {
    echo json_encode(['error' => 'Invalid Property ID']);
    exit;
}

try {
    // 1. Fetch Property Details
    $stmt = $pdo->prepare("SELECT p.*, u.name as broker_name FROM properties p LEFT JOIN users u ON p.broker_id = u.id WHERE p.id = ?");
    $stmt->execute([$property_id]);
    $property = $stmt->fetch();

    if (!$property) {
        throw new Exception("Property not found");
    }

    // 2. Fetch Amenities
    $amenitiesStmt = $pdo->prepare("SELECT a.name FROM amenities a JOIN property_amenities pa ON a.id = pa.amenity_id WHERE pa.property_id = ?");
    $amenitiesStmt->execute([$property_id]);
    $amenitiesList = $amenitiesStmt->fetchAll(PDO::FETCH_COLUMN);
    $amenitiesStr = implode(", ", $amenitiesList);

    // 3. Prepare Data for Prompt
    $type = $property['type'] ?? 'Property';
    $location = $property['location'] ?? 'Gurugram';
    $price = $property['price'] ?? 0;
    
    // Format Price
    if ($price >= 10000000) {
        $priceStr = number_format($price / 10000000, 2) . ' Cr';
    } else {
        $priceStr = number_format($price / 100000, 2) . ' Lac';
    }

    $size = $property['size_range'] ?? 'N/A';
    $isFeatured = ($property['is_featured'] == 1);
    
    // 4. Construct Prompt
    $persona = $isFeatured 
        ? "You are a Senior Investment Strategist and Premium Asset Advisor specializing in high-value Gurugram real estate." 
        : "You are a Senior Real Estate Investment Analyst specializing exclusively in the Gurugram (Gurgaon) real estate market.";

    $toneGuidance = $isFeatured 
        ? "This is a FEATURED PREMIUM property. Your tone should be highly positive, emphasizing the exceptional value, luxury, and long-term appreciation potential. Focus on why this is a 'Must-Invest' opportunity."
        : "You are objective, blunt, and financially savvy.";

    $prompt = "
    $persona
    $toneGuidance

    ### THE PROPERTY DETAILS
    - **Property Type:** $type
    - **Location/Sector:** $location
    - **Asking Price:** ₹ $priceStr
    - **Size:** $size
    - **Key Amenities:** $amenitiesStr

    ### YOUR TASK
    Generate a structured Investment Report in clean HTML format (no markdown backticks, no ```html wrappers). 
    Use specific knowledge about Gurugram sectors to add value.

    ### REQUIRED OUTPUT SECTIONS (HTML Format)

    <div class='investment-report'>

      <h4>1. 📍 Location Intelligence</h4>
      <p>Analyze the connectivity of $location relative to key Gurugram employment hubs (Cyber City/Udyog Vihar). Mention the nearest major road.</p>

      <h4>2. 💰 Price & Value Check</h4>
      <p>Based on current market trends for $location, is ₹ $priceStr considered: <strong>Fair Market Value</strong>, <strong>Overpriced</strong>, or a <strong>Distress Deal</strong>? Explain why briefly.</p>

      <h4>3. 📈 Rental Yield Forecast</h4>
      <p>Estimate the monthly rental potential for a $type in this specific sector. Give a realistic range (e.g., ₹45,000 - ₹55,000) based on current Gurugram trends.</p>

      <h4>4. ⚖️ The Reality Check (Pros & Cons)</h4>
      <ul>
        <li><strong>✅ The Upside:</strong> (e.g., Upcoming infrastructure, low density, etc.)</li>
        <li><strong>⚠️ The Risk:</strong> (e.g., High traffic, waterlogging, noise, etc.)</li>
      </ul>

      <div class='verdict' style='background:" . ($isFeatured ? '#f0f9ff' : '#eef') . "; padding:10px; margin-top:10px; border-radius:5px; border: " . ($isFeatured ? '1px solid #bae6fd' : 'none') . "'>
        <strong>🏁 Final Verdict:</strong> " . ($isFeatured ? "[Highly Recommended - Explain why this featured property is a top-tier choice]" : "[One sentence summary]") . "
      </div>

    </div>
    ";

    // 5. Call Gemini API
    $apiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : 'AIzaSyANM2QdaNw_WTJHEwqkkcQow2iLWpKnmIM';
    // Priority models based on key authorization
    $models = ['gemini-flash-latest', 'gemini-1.5-flash', 'gemini-pro-latest'];
    $response = null;
    $lastError = '';

    foreach ($models as $model) {
        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=" . $apiKey;

    $data = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $prompt]
                ]
            ]
        ]
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $actualResponse = curl_exec($ch);
        
        if (curl_errno($ch)) {
            $lastError = 'Curl error: ' . curl_error($ch);
            curl_close($ch);
            continue;
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $response = $actualResponse;
            break;
        } else {
            $responseData = json_decode($actualResponse, true);
            $msg = $responseData['error']['message'] ?? 'Unknown API error';
            $lastError = "API Error ($model): $msg";
            error_log("Gemini API Error ($model): " . $actualResponse);
        }
    }

    if (!$response) {
        throw new Exception($lastError ?: "Failed to generate report from AI provider.");
    }

    $responseData = json_decode($response, true);

    if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
        $generatedHtml = $responseData['candidates'][0]['content']['parts'][0]['text'];
        
        // Clean up any potential markdown code blocks if the model ignores instructions
        $generatedHtml = str_replace('```html', '', $generatedHtml);
        $generatedHtml = str_replace('```', '', $generatedHtml);
        
        echo json_encode(['html' => $generatedHtml]);
    } else {
        // Fallback or Error from API
        error_log("Gemini API Error Response: " . $response); // Capture the raw error for logs
        throw new Exception("Failed to generate report from AI provider.");
    }

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
