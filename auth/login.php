<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $errors['general'] = 'Email and password are required.';
    } else {
        $stmt = db()->prepare('SELECT * FROM admins WHERE email = ?');
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            set_flash('success', 'Welcome back, ' . $admin['names'] . '.');
            redirect('dashboard.php');
        } else {
            $errors['general'] = 'Invalid email or password.';
        }
    }
}

$pageTitle = 'Admin Login';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-5 col-md-7">
        <div class="card auth-card shadow-sm">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <i class="bi bi-truck-front-fill auth-logo"></i>
                    <h3 class="mt-2 mb-0 fw-bold">Admin Login</h3>
                    <p class="text-muted small">MAGERWA Vehicle Tracking System</p>
                </div>
                <?php if (isset($errors['general'])): ?>
                <div class="alert alert-danger py-2"><?= e($errors['general']) ?></div>
                <?php endif; ?>
                <form method="post" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= e($email) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-2">Log In</button>
                </form>
                <p class="text-center mt-3 mb-0 small">No account yet? <a href="<?= url('auth/signup.php') ?>">Sign up</a></p>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
