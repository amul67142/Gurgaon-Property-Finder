<?php
require_once __DIR__ . '/includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean_input($_POST['name']);
    $email = clean_input($_POST['email']);
    $password = $_POST['password'];
    $phone = clean_input($_POST['phone']);
    $role = clean_input($_POST['role']);

    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error = 'Please fill in all required fields.';
    } elseif (!in_array($role, ['user', 'broker'])) {
        $error = 'Invalid role selected.';
    } else {
        try {
            // Ensure database has verification_token and UNIQUE constraints (Dynamic Migration)
            try {
                $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS verification_token VARCHAR(255) NULL AFTER is_verified");
                
                // Add Unique Constraints
                // Note: We use a try-catch for these specific lines in case they already exist
                try { $pdo->exec("ALTER TABLE users ADD UNIQUE (email)"); } catch (Exception $e) {}
                try { $pdo->exec("ALTER TABLE users ADD UNIQUE (phone)"); } catch (Exception $e) {}
            } catch (PDOException $e) { /* DB structure might have issues, but continue */ }

            // Check if email or phone exists (Case-insensitive for email)
            $stmt = $pdo->prepare("SELECT id, email, phone FROM users WHERE LOWER(email) = LOWER(?) OR phone = ?");
            $stmt->execute([$email, $phone]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                if ($existing['email'] === $email) {
                    $error = 'Email already exists.';
                } else {
                    $error = 'Phone number already exists.';
                }
            } else {
                // File Upload Handling
                $profile_image = null;
                $seller_type = null;

                if ($role === 'broker') {
                    $seller_type = $_POST['seller_type'] ?? 'Broker';
                    
                    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = 'assets/images/users/';
                        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                        
                        $fileExt = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
                        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                        
                        if (in_array($fileExt, $allowed)) {
                            if ($_FILES['profile_image']['size'] > 2 * 1024 * 1024) {
                                $error = 'Profile image must be less than 2MB.';
                            } else {
                                $fileName = uniqid('user_') . '.' . $fileExt;
                                $targetPath = $uploadDir . $fileName;
                                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
                                    $profile_image = $targetPath;
                                }
                            }
                        } else {
                            $error = 'Invalid file type selected for profile image.';
                        }
                    }
                }

                if (!$error) {
                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Generate Verification Token
                    $verification_token = bin2hex(random_bytes(32));

                    // Insert user (is_verified = 0 for new signups)
                    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, role, seller_type, profile_image, is_verified, verification_token) VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?)");
                    $stmt->execute([$name, $email, $hashed_password, $phone, $role, $seller_type, $profile_image, $verification_token]);
                    
                    // Send Verification Email
                    $verifyLink = BASE_URL . "/verify.php?token=" . $verification_token;
                    $subject = "Verify Your Gurgaon Property Finder Account";
                    $message = "
                        <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                            <h2 style='color: #333;'>Welcome to Gurgaon Property Finder!</h2>
                            <p>Hi $name,</p>
                            <p>Thank you for registering. Please click the button below to verify your email address and activate your account:</p>
                            <a href='$verifyLink' style='display: inline-block; padding: 12px 24px; background-color: #3b82f6; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0;'>Verify Email Address</a>
                            <p>Or copy and paste this link in your browser:</p>
                            <p style='font-size: 12px; color: #666;'>$verifyLink</p>
                            <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
                            <p style='font-size: 11px; color: #999;'>If you didn't create an account, you can safely ignore this email.</p>
                        </div>
                    ";
                    
                    sendMail($email, $subject, $message);

                    $success = 'Registration successful! <strong>Please check your email to verify your account</strong> before logging in.';
                }
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'Email or phone number already exists in our system.';
            } else {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="flex items-center justify-center py-20 px-4">
    <div class="bg-white rounded-2xl shadow-xl p-8 md:p-12 w-full max-w-lg relative overflow-hidden" data-aos="zoom-in" data-aos-delay="100">
        <!-- Top accent -->
        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-secondary to-blue-400"></div>
        
        <h2 class="text-3xl font-bold text-center text-slate-800 mb-8 font-display">Create Account</h2>
        
        <?php if ($error): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 text-sm border border-red-100"><i class="fa-solid fa-circle-exclamation mr-2"></i><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="bg-green-50 text-green-600 p-4 rounded-xl mb-6 text-sm border border-green-100"><i class="fa-solid fa-check-circle mr-2"></i><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-5" enctype="multipart/form-data">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                <input type="text" name="name" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-secondary/50 transition" required>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                <input type="email" name="email" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-secondary/50 transition" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
                <input type="text" name="phone" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-secondary/50 transition">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <input type="password" name="password" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-secondary/50 transition" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">I am a</label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="cursor-pointer">
                        <input type="radio" name="role" value="user" class="peer sr-only" checked onchange="toggleSellerFields()">
                        <div class="text-center py-3 border border-slate-200 rounded-xl peer-checked:bg-secondary peer-checked:text-white peer-checked:border-secondary transition">
                            <i class="fa-solid fa-user mb-1 block"></i> Buyer
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="role" value="broker" class="peer sr-only" onchange="toggleSellerFields()">
                        <div class="text-center py-3 border border-slate-200 rounded-xl peer-checked:bg-secondary peer-checked:text-white peer-checked:border-secondary transition">
                            <i class="fa-solid fa-briefcase mb-1 block"></i> Seller
                        </div>
                    </label>
                </div>
            </div>

            <div id="seller-fields" class="hidden space-y-5 border-t border-slate-100 pt-5">
                <div>
                   <label class="block text-sm font-medium text-slate-700 mb-1">Seller Type</label>
                   <select name="seller_type" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-secondary/50 transition module-input">
                       <option value="Broker">Broker</option>
                       <option value="Developer">Developer</option>
                       <option value="Owner">Owner</option>
                   </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Profile Image / Logo (Optional)</label>
                    <input type="file" name="profile_image" accept="image/*" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-4 text-sm focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-secondary/10 file:text-secondary hover:file:bg-secondary/20">
                    <p class="text-xs text-slate-400 mt-1">Displayed on your property listings.</p>
                </div>
            </div>

            <script>
            function toggleSellerFields() {
                const role = document.querySelector('input[name="role"]:checked').value;
                const fields = document.getElementById('seller-fields');
                if (role === 'broker') {
                    fields.classList.remove('hidden');
                } else {
                    fields.classList.add('hidden');
                }
            }
            </script>
            
            <button type="submit" class="w-full bg-secondary text-white font-bold py-3.5 rounded-xl hover:bg-blue-600 transition shadow-lg shadow-blue-500/25 mt-4">
                Register Now
            </button>
        </form>
        
        <div class="mt-6 text-center text-sm text-slate-500">
            Already have an account? 
            <a href="login.php" class="text-secondary font-bold hover:underline">Login here</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
