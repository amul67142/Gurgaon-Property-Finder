<?php
@session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        redirect(BASE_URL . '/admin/dashboard.php');
    } elseif (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'broker') {
        redirect(BASE_URL . '/broker/dashboard.php');
    } else {
        redirect(BASE_URL . '/index.php');
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean_input($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_email'] = $user['email'];

                if ($user['role'] === 'admin') {
                    redirect(BASE_URL . '/admin/dashboard.php');
                } elseif ($user['role'] === 'broker') {
                    redirect(BASE_URL . '/broker/dashboard.php');
                } else {
                    redirect(BASE_URL . '/index.php');
                }
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $error = "Database Error: Table 'users' may not exist. Please import schema.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="flex items-center justify-center py-20 px-4">
    <div class="bg-white rounded-2xl shadow-xl p-8 md:p-12 w-full max-w-md relative overflow-hidden" data-aos="zoom-in">
        <!-- Top accent -->
        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-secondary to-blue-400"></div>
        
        <h2 class="text-3xl font-bold text-center text-slate-800 mb-8 font-display">Welcome Back</h2>
        
        <?php if ($error): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 text-sm flex items-center gap-3 border border-red-100">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'device_unauthorized'): ?>
            <div class="bg-amber-50 text-amber-600 p-4 rounded-xl mb-6 text-sm flex items-center gap-3 border border-amber-100">
                <i class="fa-solid fa-shield-halved"></i>
                <div>
                    <p class="font-bold">Device Not Authorized</p>
                    <p>Admin access requires an authorized device. Use your secret link to authorize this browser.</p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'device_authorized'): ?>
            <div class="bg-green-50 text-green-600 p-4 rounded-xl mb-6 text-sm flex items-center gap-3 border border-green-100">
                <i class="fa-solid fa-circle-check"></i>
                <div>
                    <p class="font-bold">Device Authorized!</p>
                    <p>This browser is now authorized for admin access. You can now login with your password.</p>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Email Address</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><i class="fa-solid fa-envelope"></i></span>
                    <input type="email" name="email" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-12 pr-4 focus:outline-none focus:ring-2 focus:ring-secondary/50 focus:border-secondary transition" placeholder="you@example.com" required>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" name="password" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-12 pr-4 focus:outline-none focus:ring-2 focus:ring-secondary/50 focus:border-secondary transition" placeholder="••••••••" required>
                </div>
                <div class="text-right mt-2">
                    <a href="forgot-password.php" class="text-xs text-slate-500 hover:text-secondary transition font-medium">Forgot Password?</a>
                </div>
            </div>
            
            <button type="submit" class="w-full bg-gradient-to-r from-secondary to-blue-600 text-white font-bold py-3.5 rounded-xl hover:shadow-lg hover:shadow-blue-500/30 transition transform hover:-translate-y-0.5">
                Sign In
            </button>
        </form>
        
        <div class="mt-8 text-center text-sm text-slate-500">
            Don't have an account? 
            <a href="register.php" class="text-secondary font-bold hover:underline">Create Account</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
