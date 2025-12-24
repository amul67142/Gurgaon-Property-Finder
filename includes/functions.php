<?php
// Core Helper Functions

function clean_input($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = clean_input($value);
        }
        return $data;
    }
    return htmlspecialchars(stripslashes(trim($data)));
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isBroker() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'broker';
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect(BASE_URL . '/login.php');
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        redirect(BASE_URL . '/index.php');
    }
}

function requireBroker() {
    if (!isBroker()) {
        redirect(BASE_URL . '/index.php');
    }
}

/**
 * Send Mail Helper
 * Logs to assets/mail_logs.txt for local testing if mail() fails or environment is local
 */
function sendMail($to, $subject, $message) {
    $headers = "From: Gurgaon Property Finder <noreply@gurgaonproperty.in>\r\n";
    $headers .= "Reply-To: noreply@gurgaonproperty.in\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    // Log for local development
    $logDir = __DIR__ . '/../logs/';
    if (!is_dir($logDir)) mkdir($logDir, 0777, true);
    
    $logContent = "--- [" . date('Y-m-d H:i:s') . "] ---\nTO: $to\nSUB: $subject\nMSG: $message\n------------------------\n\n";
    file_put_contents($logDir . 'mail_logs.txt', $logContent, FILE_APPEND);
    
    // Attempt actual mail
    try {
        return @mail($to, $subject, $message, $headers);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * File Upload Helper
 */
function uploadFile($fileInputName, $targetDir) {
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
        if ($_FILES[$fileInputName]['size'] > 2 * 1024 * 1024) {
            throw new Exception("File " . $_FILES[$fileInputName]['name'] . " exceeds 2MB limit.");
        }
        $tmpName = $_FILES[$fileInputName]['tmp_name'];
        $name = time() . '_' . basename($_FILES[$fileInputName]['name']);
        $destination = $targetDir . $name;
        if (move_uploaded_file($tmpName, $destination)) {
            return 'assets/uploads/' . $name;
        }
    }
    return null;
}

/**
 * Get Seller Logo URL
 */
function get_seller_logo($prop) {
    if (!defined('BASE_URL')) define('BASE_URL', 'http://localhost/ggn'); // Fallback

    $sImg = '';
    
    // Priority 1: ad_broker_image set specifically for this property
    if (!empty($prop['ad_broker_image'])) {
        $imgPath = $prop['ad_broker_image'];
        if (strpos($imgPath, 'http') !== false) return $imgPath;
        return BASE_URL . '/' . ltrim($imgPath, '/');
    }
    
    // Priority 2: profile_image from the broker
    if (!empty($prop['profile_image'])) {
        $imgPath = $prop['profile_image'];
        if (strpos($imgPath, 'http') !== false) return $imgPath;
        // Profile images are usually in assets/images/users/
        return BASE_URL . '/assets/images/users/' . basename($imgPath);
    }
    
    return null; // Fallback handled by UI (e.g., FontAwesome icon)
}

/**
 * Get Property Cover Image URL
 */
function get_property_cover($propertyId, $pdo) {
    $stmt = $pdo->prepare("SELECT image_path FROM property_images WHERE property_id = ? AND is_cover = 1 LIMIT 1");
    $stmt->execute([$propertyId]);
    $img = $stmt->fetchColumn();
    
    if (!$img) {
        $stmt = $pdo->prepare("SELECT image_path FROM property_images WHERE property_id = ? LIMIT 1");
        $stmt->execute([$propertyId]);
        $img = $stmt->fetchColumn();
    }
    
    if ($img) {
        if (strpos($img, 'http') !== false) return $img;
        return BASE_URL . '/' . ltrim($img, '/');
    }
    
    return 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'; // Fallback
}
?>
