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
    You are an Enthusiastic Real Estate Investment Advisor specializing in the Gurugram (Gurgaon) real estate market. 
    Your job is to provide an ENCOURAGING, POSITIVE 'Investment Opportunity Report' for a potential buyer. 
    You are optimistic, supportive, and focus on GROWTH POTENTIAL and OPPORTUNITIES.
    ALWAYS highlight the POSITIVE aspects and present challenges as 'things to be aware of' rather than risks.

    ### THE PROPERTY DETAILS
    - **Property Type:** $type
    - **Location/Sector:** $location
    - **Asking Price:** ₹ $priceStr
    - **Size:** $size
    - **Key Amenities:** $amenitiesStr

    ### YOUR TASK
    Generate a POSITIVE, ENCOURAGING Investment Report in clean HTML format (no markdown backticks, no ```html wrappers). 
    Use specific knowledge about Gurugram sectors to HIGHLIGHT OPPORTUNITIES and GROWTH POTENTIAL.
    ALWAYS frame everything positively - this is an EXCELLENT investment opportunity!

    ### REQUIRED OUTPUT SECTIONS (HTML Format with Beautiful Styling)

    <div class='investment-report' style='font-family: Inter, sans-serif; color: #1e293b;'>

      <div style='background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); padding: 20px; border-radius: 16px; margin-bottom: 20px; border-left: 4px solid #22c55e;'>
        <h4 style='color: #166534; margin: 0 0 12px 0; font-size: 18px; display: flex; align-items: center; gap: 8px;'>
          <span style='font-size: 24px;'>📍</span> Prime Location Advantage
        </h4>
        <p style='margin: 0; color: #166534; line-height: 1.6;'>
          Highlight EXCELLENT connectivity of $location to key Gurugram employment hubs (Cyber City/Udyog Vihar). 
          Mention upcoming metro lines, highways, and infrastructure projects that make this location HIGHLY DESIRABLE.
          Frame it as a STRATEGIC LOCATION with STRONG APPRECIATION POTENTIAL.
        </p>
      </div>

      <div style='background: linear-gradient(135deg, #fefce8 0%, #fef9c3 100%); padding: 20px; border-radius: 16px; margin-bottom: 20px; border-left: 4px solid #eab308;'>
        <h4 style='color: #854d0e; margin: 0 0 12px 0; font-size: 18px; display: flex; align-items: center; gap: 8px;'>
          <span style='font-size: 24px;'>💰</span> Excellent Value Proposition
        </h4>
        <p style='margin: 0; color: #854d0e; line-height: 1.6;'>
          Based on current market trends for $location, ₹ $priceStr represents <strong>EXCELLENT VALUE</strong> for this area. 
          Explain why this is a SMART INVESTMENT at this price point. Mention how prices in this sector have been APPRECIATING STEADILY.
          Always present the price as FAIR or BELOW MARKET VALUE - a GREAT OPPORTUNITY!
        </p>
      </div>

      <div style='background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); padding: 20px; border-radius: 16px; margin-bottom: 20px; border-left: 4px solid #3b82f6;'>
        <h4 style='color: #1e40af; margin: 0 0 12px 0; font-size: 18px; display: flex; align-items: center; gap: 8px;'>
          <span style='font-size: 24px;'>📈</span> Strong Rental Income Potential
        </h4>
        <p style='margin: 0; color: #1e40af; line-height: 1.6;'>
          Estimate ATTRACTIVE monthly rental potential for a $type in this premium sector. 
          Give a realistic but OPTIMISTIC range (e.g., ₹45k - ₹60k) based on current Gurugram trends.
          Highlight this as PASSIVE INCOME OPPORTUNITY with STEADY RETURNS.
        </p>
      </div>

      <div style='background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); padding: 20px; border-radius: 16px; margin-bottom: 20px; border-left: 4px solid #a855f7;'>
        <h4 style='color: #6b21a8; margin: 0 0 12px 0; font-size: 18px; display: flex; align-items: center; gap: 8px;'>
          <span style='font-size: 24px;'>✨</span> Key Investment Highlights
        </h4>
        <ul style='margin: 8px 0; padding-left: 20px; color: #6b21a8; line-height: 1.8;'>
          <li><strong>🚀 Growth Catalysts:</strong> List POSITIVE aspects like upcoming infrastructure, premium amenities, reputed developer, low density, green surroundings, etc.</li>
          <li><strong>💎 Premium Features:</strong> Highlight the amenities ($amenitiesStr) as WORLD-CLASS and EXCLUSIVE</li>
          <li><strong>📊 Market Momentum:</strong> Mention how this sector is EXPERIENCING STRONG DEMAND and STEADY APPRECIATION</li>
        </ul>
      </div>

      <div style='background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); padding: 20px; border-radius: 16px; margin-bottom: 20px; border-left: 4px solid #f97316; opacity: 0.8;'>
        <h4 style='color: #9a3412; margin: 0 0 12px 0; font-size: 16px; display: flex; align-items: center; gap: 8px;'>
          <span style='font-size: 20px;'>💡</span> Points to Note
        </h4>
        <p style='margin: 0; color: #9a3412; line-height: 1.6; font-size: 14px;'>
          Mention any minor considerations GENTLY (e.g., 'During peak hours, traffic can be moderate' instead of 'High traffic congestion').
          Frame these as THINGS TO BE AWARE OF rather than RISKS. Keep this section BRIEF and LIGHT.
        </p>
      </div>

      <div style='background: linear-gradient(135deg, #d4af37 0%, #f59e0b 100%); padding: 24px; border-radius: 16px; box-shadow: 0 10px 25px rgba(212, 175, 55, 0.3);'>
        <div style='display: flex; align-items: center; gap: 12px; margin-bottom: 12px;'>
          <span style='font-size: 32px;'>🏆</span>
          <strong style='color: white; font-size: 20px;'>Investment Recommendation</strong>
        </div>
        <p style='margin: 0; color: white; line-height: 1.6; font-size: 16px;'>
          ALWAYS give a POSITIVE, ENCOURAGING verdict like: 
          'This property presents an EXCELLENT INVESTMENT OPPORTUNITY with STRONG APPRECIATION POTENTIAL. HIGHLY RECOMMENDED for both end-use and investment purposes!'
          Make it sound EXCITING and COMPELLING!
        </p>
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
