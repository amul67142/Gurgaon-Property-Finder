<?php
require_once __DIR__ . '/includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean_input($_POST['email']);

    if (empty($email)) {
        $error = 'Please enter your email address.';
    } else {
        $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE id = ?");
            $stmt->execute([$token, $expiry, $user['id']]);

            $resetLink = BASE_URL . "/reset-password.php?token=$token&email=$email";
            
            $subject = "Reset Your Password - Gurgaon Property Finder";
            $message = "<h2>Password Reset Request</h2>
                        <p>Hi " . htmlspecialchars($user['name']) . ",</p>
                        <p>Click the link below to reset your password. This link is valid for 1 hour.</p>
                        <p><a href='$resetLink' style='display:inline-block; padding:12px 24px; background:#f59e0b; color:#fff; text-decoration:none; border-radius:8px; font-weight:bold;'>Reset Password</a></p>
                        <p>If you didn't request this, please ignore this email.</p>";
            
            sendMail($email, $subject, $message);
        }
        
        // Always show success to prevent email enumeration
        $success = "If this email is registered, you will receive a reset link shortly. Please check your inbox (and spam folder).";
    }
}
?>

<div class="flex items-center justify-center py-24 px-4">
    <div class="bg-white rounded-2xl shadow-xl p-8 md:p-12 w-full max-w-md relative overflow-hidden" data-aos="zoom-in">
        <div class="absolute top-0 left-0 w-full h-2 bg-secondary"></div>
        
        <div class="text-center mb-10">
            <h2 class="text-3xl font-bold text-slate-800 font-display">Reset Password</h2>
            <p class="text-slate-500 mt-2 text-sm">Enter your email and we'll send you a reset link.</p>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-50 text-green-600 p-4 rounded-xl mb-6 text-sm border border-green-100 flex items-center gap-2">
                <i class="fa-solid fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php else: ?>
            <form method="POST" action="" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Email Address</label>
                    <input type="email" name="email" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-secondary/50 transition" placeholder="you@example.com" required>
                </div>
                
                <button type="submit" class="w-full bg-secondary text-white font-bold py-3.5 rounded-xl hover:bg-blue-600 transition shadow-lg shadow-blue-500/25">
                    Send Reset Link
                </button>
            </form>
        <?php endif; ?>
        
        <div class="mt-8 text-center text-sm">
            <a href="login.php" class="text-slate-500 hover:text-secondary font-medium transition"><i class="fa-solid fa-arrow-left mr-1"></i> Back to Login</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
