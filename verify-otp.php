<?php
require_once __DIR__ . '/includes/header.php';

$error = '';
$success = '';

// Check if email set in session
$email = $_SESSION['verify_email'] ?? '';

if (empty($email)) {
    redirect('register.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = clean_input($_POST['otp']);

    if (empty($otp)) {
        $error = 'Please enter the OTP.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND otp_code = ? AND otp_expiry > NOW()");
        $stmt->execute([$email, $otp]);
        $user = $stmt->fetch();

        if ($user) {
            // Verify User
            $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, otp_code = NULL, otp_expiry = NULL WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            unset($_SESSION['verify_email']);
            $success = 'Email verified successfully! You can now <a href="login.php" class="underline font-bold">login</a>.';
        } else {
            $error = 'Invalid or expired OTP.';
        }
    }
}

// Handle Resend
if (isset($_GET['resend'])) {
    $newOtp = rand(100000, 999999);
    $newExpiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));
    
    $stmt = $pdo->prepare("UPDATE users SET otp_code = ?, otp_expiry = ? WHERE email = ?");
    $stmt->execute([$newOtp, $newExpiry, $email]);
    
    $subject = "Your New OTP - Gurgaon Property Finder";
    $message = "<h2>Gurgaon Property Finder</h2>
                <p>Your new OTP is: <b style='font-size: 24px; color: #f59e0b;'>$newOtp</b></p>
                <p>Valid for 30 minutes.</p>";
    sendMail($email, $subject, $message);
    $success = 'A new OTP has been sent to your email.';
}
?>

<div class="flex items-center justify-center py-24 px-4">
    <div class="bg-white rounded-2xl shadow-xl p-8 md:p-12 w-full max-w-md relative overflow-hidden" data-aos="zoom-in">
        <div class="absolute top-0 left-0 w-full h-2 bg-secondary"></div>
        
        <div class="text-center mb-10">
            <div class="w-16 h-16 bg-blue-50 text-secondary rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fa-solid fa-envelope-circle-check"></i>
            </div>
            <h2 class="text-3xl font-bold text-slate-800 font-display">Verify Email</h2>
            <p class="text-slate-500 mt-2 text-sm">We've sent a 6-digit code to <br><span class="font-bold text-slate-700"><?php echo htmlspecialchars($email); ?></span></p>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 text-sm border border-red-100 flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success || isset($_SESSION['success_msg'])): ?>
            <?php 
                $msg = $success ?: $_SESSION['success_msg'];
                unset($_SESSION['success_msg']);
            ?>
            <div class="bg-green-50 text-green-600 p-4 rounded-xl mb-6 text-sm border border-green-100 flex items-center gap-2">
                <i class="fa-solid fa-check-circle"></i> <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-6">
            <div>
                <input type="text" name="otp" maxlength="6" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-4 px-4 text-center text-3xl font-bold tracking-[0.5em] focus:outline-none focus:ring-2 focus:ring-secondary/50 transition" placeholder="000000" required autofocus>
            </div>
            
            <button type="submit" class="w-full bg-secondary text-white font-bold py-4 rounded-2xl hover:bg-blue-600 transition shadow-lg shadow-blue-500/25">
                Verify OTP
            </button>
        </form>
        
        <div class="mt-8 text-center text-sm">
            <p class="text-slate-500">Didn't receive the code?</p>
            <a href="?resend=1" class="text-secondary font-bold hover:underline">Resend OTP</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
