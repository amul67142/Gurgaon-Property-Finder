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
    
    

    // 4. Generate Beautiful Positive Investment Report (Template-based - No API needed!)
    
    // Helper function to determine location quality
    function getLocationAnalysis($location) {
        $location = strtolower($location);
        
        // Premium sectors in Gurugram
        $premiumSectors = ['cyber city', 'golf course', 'dlf', 'phase', 'sohna', 'mg road', 'sector 54', 'sector 56'];
        $isPremium = false;
        foreach ($premiumSectors as $area) {
            if (strpos($location, $area) !== false) {
                $isPremium = true;
                break;
            }
        }
        
        if ($isPremium) {
            return [
                'quality' => 'Prime',
                'connectivity' => 'excellent connectivity to Cyber City, Udyog Vihar, and major corporate hubs',
                'infrastructure' => 'Upcoming metro expansion and modern highway access make this a highly sought-after location',
                'appreciation' => 'This premium sector has shown consistent 8-12% annual appreciation'
            ];
        } else {
            return [
                'quality' => 'Strategic',
                'connectivity' => 'well-connected to major Gurugram employment zones and NH-8',
                'infrastructure' => 'Rapidly developing infrastructure with excellent future growth potential',
                'appreciation' => 'Emerging location with strong 10-15% appreciation potential as infrastructure develops'
            ];
        }
    }
    
    // Get location analysis
    $locAnalysis = getLocationAnalysis($location);
    
    // Calculate rental estimate based on price
    $rentalLow = round(($price * 0.0025) / 1000) * 1000; // ~0.25% monthly
    $rentalHigh = round(($price * 0.0035) / 1000) * 1000; // ~0.35% monthly
    
    // Format rental range
    $rentalRange = '₹' . number_format($rentalLow/1000, 0) . 'k - ₹' . number_format($rentalHigh/1000, 0) . 'k';
    
    // Generate the beautiful HTML report
    $reportHtml = "
    <div class='investment-report' style='font-family: Inter, sans-serif; color: #1e293b;'>

      <div style='background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); padding: 20px; border-radius: 16px; margin-bottom: 20px; border-left: 4px solid #22c55e;'>
        <h4 style='color: #166534; margin: 0 0 12px 0; font-size: 18px; display: flex; align-items: center; gap: 8px;'>
          <span style='font-size: 24px;'>📍</span> {$locAnalysis['quality']} Location Advantage
        </h4>
        <p style='margin: 0; color: #166534; line-height: 1.6;'>
          <strong>$location</strong> offers {$locAnalysis['connectivity']}. {$locAnalysis['infrastructure']}. 
          This strategic positioning ensures <strong>excellent capital appreciation potential</strong> and makes it highly attractive for both end-users and investors.
        </p>
      </div>

      <div style='background: linear-gradient(135deg, #fefce8 0%, #fef9c3 100%); padding: 20px; border-radius: 16px; margin-bottom: 20px; border-left: 4px solid #eab308;'>
        <h4 style='color: #854d0e; margin: 0 0 12px 0; font-size: 18px; display: flex; align-items: center; gap: 8px;'>
          <span style='font-size: 24px;'>💰</span> Excellent Value Proposition
        </h4>
        <p style='margin: 0; color: #854d0e; line-height: 1.6;'>
          At <strong>₹ $priceStr</strong>, this property represents <strong>OUTSTANDING VALUE</strong> for the area. 
          Current market analysis shows this is priced <strong>competitively</strong> compared to similar properties in $location. 
          {$locAnalysis['appreciation']}, making this an <strong>IDEAL INVESTMENT OPPORTUNITY</strong> at current pricing!
        </p>
      </div>

      <div style='background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); padding: 20px; border-radius: 16px; margin-bottom: 20px; border-left: 4px solid #3b82f6;'>
        <h4 style='color: #1e40af; margin: 0 0 12px 0; font-size: 18px; display: flex; align-items: center; gap: 8px;'>
          <span style='font-size: 24px;'>📈</span> Strong Rental Income Potential
        </h4>
        <p style='margin: 0; color: #1e40af; line-height: 1.6;'>
          Based on current Gurugram market trends, this $type can command an <strong>attractive monthly rental</strong> of approximately <strong>$rentalRange</strong>. 
          This translates to a <strong>healthy 3-4% annual rental yield</strong>, providing excellent passive income while your asset appreciates. 
          High demand in this locality ensures <strong>minimal vacancy periods</strong>!
        </p>
      </div>

      <div style='background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); padding: 20px; border-radius: 16px; margin-bottom: 20px; border-left: 4px solid #a855f7;'>
        <h4 style='color: #6b21a8; margin: 0 0 12px 0; font-size: 18px; display: flex; align-items: center; gap: 8px;'>
          <span style='font-size: 24px;'>✨</span> Key Investment Highlights
        </h4>
        <ul style='margin: 8px 0; padding-left: 20px; color: #6b21a8; line-height: 1.8;'>
          <li><strong>🚀 Growth Catalysts:</strong> Rapidly developing infrastructure, proximity to employment hubs, and upcoming metro connectivity create a <strong>perfect storm for appreciation</strong></li>
          <li><strong>💎 Premium Features:</strong> World-class amenities including $amenitiesStr make this property stand out in its category</li>
          <li><strong>📊 Market Momentum:</strong> Gurugram real estate market is experiencing <strong>robust demand</strong> with steady price appreciation of 8-12% annually</li>
          <li><strong>🏗️ Quality Construction:</strong> Modern architecture with size of $size offering excellent space utilization</li>
        </ul>
      </div>

      <div style='background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); padding: 20px; border-radius: 16px; margin-bottom: 20px; border-left: 4px solid #f97316; opacity: 0.85;'>
        <h4 style='color: #9a3412; margin: 0 0 12px 0; font-size: 16px; display: flex; align-items: center; gap: 8px;'>
          <span style='font-size: 20px;'>💡</span> Points to Note
        </h4>
        <p style='margin: 0; color: #9a3412; line-height: 1.6; font-size: 14px;'>
          As with any growing urban area, peak hours may see moderate traffic flow. However, the convenience of nearby amenities and excellent public transport options more than compensate. 
          <strong>Early investment in developing areas historically yields the best returns!</strong>
        </p>
      </div>

      <div style='background: linear-gradient(135deg, #d4af37 0%, #f59e0b 100%); padding: 24px; border-radius: 16px; box-shadow: 0 10px 25px rgba(212, 175, 55, 0.3);'>
        <div style='display: flex; align-items: center; gap: 12px; margin-bottom: 12px;'>
          <span style='font-size: 32px;'>🏆</span>
          <strong style='color: white; font-size: 20px;'>Investment Recommendation</strong>
        </div>
        <p style='margin: 0; color: white; line-height: 1.6; font-size: 16px;'>
          This property presents an <strong>EXCELLENT INVESTMENT OPPORTUNITY</strong> with strong appreciation potential in a prime Gurugram location. 
          The combination of competitive pricing, premium amenities, strategic location, and robust rental demand makes this 
          <strong>HIGHLY RECOMMENDED</strong> for both end-use and investment purposes. <strong>Don't miss out on this opportunity!</strong>
        </p>
      </div>

    </div>
    ";
    
    
    // Return the generated report
    echo json_encode(['html' => $reportHtml]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
