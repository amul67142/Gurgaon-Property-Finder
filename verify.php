<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$title = "Email Verification";
require_once __DIR__ . '/includes/header.php';

$message = "";
$type = "error"; // error, success
$token = isset($_GET['token']) ? $_GET['token'] : '';

if (empty($token)) {
    $message = "Invalid or missing verification token.";
} else {
    try {
        // Find user with this token
        $stmt = $pdo->prepare("SELECT id, name FROM users WHERE verification_token = ? AND is_verified = 0 LIMIT 1");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            // Update user to verified
            $update = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
            if ($update->execute([$user['id']])) {
                $message = "Congratulations, <strong>" . htmlspecialchars($user['name']) . "</strong>! Your email has been successfully verified.";
                $type = "success";
            } else {
                $message = "An error occurred while verifying your email. Please contact support.";
            }
        } else {
            $message = "The verification link is invalid or has already been used.";
        }
    } catch (PDOException $e) {
        $message = "Database Error: " . $e->getMessage();
    }
}
?>

<div class="flex items-center justify-center py-32 px-4 shadow-sm">
    <div class="bg-white rounded-2xl p-10 max-w-lg w-full text-center border border-slate-100 shadow-xl" data-aos="fade-up">
        <div class="mb-8">
            <?php if ($type === 'success'): ?>
                <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto text-4xl mb-6 shadow-lg shadow-green-100">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            <?php else: ?>
                <div class="w-20 h-20 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto text-4xl mb-6 shadow-lg shadow-red-100">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>
            <?php endif; ?>

            <h1 class="text-3xl font-bold text-slate-800 mb-4 font-display">
                <?php echo ($type === 'success') ? "Verification Successful" : "Verification Failed"; ?>
            </h1>
            <p class="text-slate-500 text-lg leading-relaxed">
                <?php echo $message; ?>
            </p>
        </div>

        <?php if ($type === 'success'): ?>
            <a href="login.php" class="inline-block bg-secondary text-white font-bold py-4 px-10 rounded-xl hover:bg-blue-600 transition shadow-lg shadow-blue-500/25">
                Login to Your Account
            </a>
        <?php else: ?>
            <a href="index.php" class="inline-block bg-slate-100 text-slate-600 font-bold py-4 px-10 rounded-xl hover:bg-slate-200 transition">
                Return to Home
            </a>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
