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
    $hasRole = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    if (!$hasRole) return false;
    
    // Check for Device Token
    return validateAdminToken();
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
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            // Logged in as admin but device not authorized
            redirect(BASE_URL . '/login.php?error=device_unauthorized');
        }
        redirect(BASE_URL . '/index.php');
    }
}

/**
 * Validate Admin Device Token
 */
function validateAdminToken() {
    if (!isset($_COOKIE['admin_device_token'])) return false;
    
    $secret = defined('ADMIN_SECRET_KEY') ? ADMIN_SECRET_KEY : 'default_fallback_change_me';
    $token = $_COOKIE['admin_device_token'];
    
    // The token is expected to be a hash of the secret
    return hash_equals(hash('sha256', $secret), $token);
}

function requireBroker() {
    if (!isBroker()) {
        redirect(BASE_URL . '/index.php');
    }
}

/**
 * Send Mail Helper
 * On Live: Uses standard PHP mail()
 * On Local/Fallback: Logs to logs/mail_logs.txt
 */
function sendMail($to, $subject, $message) {
    $from = 'support@gurgaonpropertyfinder.com';
    $headers = "From: Gurgaon Property Finder <$from>\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    // 1. Log locally first for debugging/development
    $logDir = __DIR__ . '/../logs/';
    if (!is_dir($logDir)) mkdir($logDir, 0777, true);
    
    $logContent = "--- [" . date('Y-m-d H:i:s') . "] ---\nTO: $to\nSUB: $subject\nMSG: $message\n------------------------\n\n";
    file_put_contents($logDir . 'mail_logs.txt', $logContent, FILE_APPEND);
    
    // 2. Attempt actual mail sending on live server
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

/**
 * Delete all images associated with a property
 * Called before deleting a property from the database
 */
function deletePropertyImages($propertyId, $pdo) {
    try {
        $deletedFiles = [];
        $errors = [];
        
        // 1. Get all gallery images from property_images table
        $stmt = $pdo->prepare("SELECT image_path FROM property_images WHERE property_id = ?");
        $stmt->execute([$propertyId]);
        $galleryImages = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // 2. Get property-specific images (highlights, location, ad_broker)
        $stmt = $pdo->prepare("SELECT highlights_image, location_advantages_image, ad_broker_image FROM properties WHERE id = ?");
        $stmt->execute([$propertyId]);
        $propertyImages = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 3. Get floor plan images
        $stmt = $pdo->prepare("SELECT image_path FROM property_floor_plans WHERE property_id = ?");
        $stmt->execute([$propertyId]);
        $floorPlanImages = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Collect all image paths
        $allImages = array_merge(
            $galleryImages,
            array_filter([
                $propertyImages['highlights_image'] ?? null,
                $propertyImages['location_advantages_image'] ?? null,
                $propertyImages['ad_broker_image'] ?? null
            ]),
            $floorPlanImages
        );
        
        // Delete each file
        foreach ($allImages as $imagePath) {
            if (empty($imagePath)) continue;
            
            // Skip external URLs
            if (strpos($imagePath, 'http') !== false) continue;
            
            // Construct full file path
            $fullPath = __DIR__ . '/../' . ltrim($imagePath, '/');
            
            // Security: Ensure the path is within uploads directory
            $realPath = realpath($fullPath);
            $uploadsDir = realpath(__DIR__ . '/../assets/uploads/');
            
            if ($realPath && $uploadsDir && strpos($realPath, $uploadsDir) === 0) {
                // File is within uploads directory, safe to delete
                if (file_exists($realPath)) {
                    if (unlink($realPath)) {
                        $deletedFiles[] = basename($realPath);
                    } else {
                        $errors[] = "Failed to delete: " . basename($realPath);
                    }
                }
            }
        }
        
        // Log results (optional)
        if (!empty($deletedFiles) || !empty($errors)) {
            $logDir = __DIR__ . '/../logs/';
            if (!is_dir($logDir)) mkdir($logDir, 0777, true);
            
            $logMessage = "[" . date('Y-m-d H:i:s') . "] Property ID: $propertyId\n";
            if (!empty($deletedFiles)) {
                $logMessage .= "Deleted files: " . implode(', ', $deletedFiles) . "\n";
            }
            if (!empty($errors)) {
                $logMessage .= "Errors: " . implode(', ', $errors) . "\n";
            }
            $logMessage .= "------------------------\n\n";
            
            file_put_contents($logDir . 'image_deletion.log', $logMessage, FILE_APPEND);
        }
        
        return [
            'success' => true,
            'deleted' => count($deletedFiles),
            'errors' => $errors
        ];
        
    } catch (Exception $e) {
        // Log error but don't prevent property deletion
        error_log("Error deleting property images: " . $e->getMessage());
        return [
            'success' => false,
            'deleted' => 0,
            'errors' => [$e->getMessage()]
        ];
    }
}
/**
 * Format Price in Rupees with Cr/Lac suffixes
 */
function formatPrice($amount) {
    if (!is_numeric($amount)) return '₹' . $amount;
    
    if ($amount >= 10000000) {
        return '₹' . number_format($amount / 10000000, 2) . ' Cr';
    } elseif ($amount >= 100000) {
        return '₹' . number_format($amount / 100000, 2) . ' Lac';
    }
    return '₹' . number_format($amount);
}

?>
