<?php
require_once __DIR__ . '/../config/db.php';

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
    
    // 4. Construct Prompt
    $prompt = "
    You are a Senior Real Estate Investment Analyst specializing exclusively in the Gurugram (Gurgaon) real estate market. 
    Your job is to provide a critical, data-driven 'Investment Memo' for a potential buyer. 
    You are objective, blunt, and financially savvy.

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
      <p>Estimate the monthly rental potential for a $type in this specific sector. Give a realistic range (e.g., ₹45k - ₹55k) based on current Gurugram trends.</p>

      <h4>4. ⚖️ The Reality Check (Pros & Cons)</h4>
      <ul>
        <li><strong>✅ The Upside:</strong> (e.g., Upcoming infrastructure, low density, etc.)</li>
        <li><strong>⚠️ The Risk:</strong> (e.g., High traffic, waterlogging, noise, etc.)</li>
      </ul>

      <div class='verdict' style='background:#eef; padding:10px; margin-top:10px; border-radius:5px;'>
        <strong>🏁 Final Verdict:</strong> [One sentence summary]
      </div>

    </div>
    ";

    // 5. Call Gemini API
    $apiKey = 'AIzaSyANM2QdaNw_WTJHEwqkkcQow2iLWpKnmIM';
    // Using gemini-2.5-flash as per available models
    $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;

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

    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        throw new Exception('Curl error: ' . curl_error($ch));
    }
    
    curl_close($ch);

    $responseData = json_decode($response, true);

    if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
        $generatedHtml = $responseData['candidates'][0]['content']['parts'][0]['text'];
        
        // Clean up any potential markdown code blocks if the model ignores instructions
        $generatedHtml = str_replace('```html', '', $generatedHtml);
        $generatedHtml = str_replace('```', '', $generatedHtml);
        
        echo json_encode(['html' => $generatedHtml]);
    } else {
        // Fallback or Error from API
        // error_log(print_r($responseData, true)); // Debug
        throw new Exception("Failed to generate report from AI provider.");
    }

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
