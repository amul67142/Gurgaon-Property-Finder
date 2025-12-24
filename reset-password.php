<?php
require_once __DIR__ . '/includes/header.php';

$error = '';
$success = '';

$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';

if (empty($token) || empty($email)) {
    redirect('login.php');
}

// Verify token
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND reset_token = ? AND reset_expiry > NOW()");
$stmt->execute([$email, $token]);
$user = $stmt->fetch();

if (!$user) {
    $error = 'Invalid or expired reset link. Please request a new one.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
        $stmt->execute([$hashed, $user['id']]);
        
        $success = 'Password reset successfully! You can now <a href="login.php" class="underline font-bold">login</a>.';
    }
}
?>

<div class="flex items-center justify-center py-24 px-4">
    <div class="bg-white rounded-2xl shadow-xl p-8 md:p-12 w-full max-w-md relative overflow-hidden" data-aos="zoom-in">
        <div class="absolute top-0 left-0 w-full h-2 bg-secondary"></div>
        
        <div class="text-center mb-10">
            <h2 class="text-3xl font-bold text-slate-800 font-display">New Password</h2>
            <p class="text-slate-500 mt-2 text-sm">Create a secure new password for your account.</p>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 text-sm flex items-center gap-2 border border-red-100">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
            </div>
            <?php if (!$user): ?>
                <a href="forgot-password.php" class="block text-center text-secondary font-bold hover:underline">Request New Link</a>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-50 text-green-600 p-4 rounded-xl mb-6 text-sm border border-green-100 flex items-center gap-2">
                <i class="fa-solid fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php elseif ($user): ?>
            <form method="POST" action="" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">New Password</label>
                    <input type="password" name="password" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-secondary/50 transition" required minlength="6">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Confirm Password</label>
                    <input type="password" name="confirm_password" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-secondary/50 transition" required>
                </div>
                
                <button type="submit" class="w-full bg-secondary text-white font-bold py-3.5 rounded-xl hover:bg-blue-600 transition shadow-lg shadow-blue-500/25">
                    Update Password
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
