<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
$old = ['names' => '', 'email' => '', 'phone' => '', 'national_id' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['names'] = clean($_POST['names'] ?? '');
    $old['email'] = clean($_POST['email'] ?? '');
    $old['phone'] = clean($_POST['phone'] ?? '');
    $old['national_id'] = clean($_POST['national_id'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');

    if ($old['names'] === '') {
        $errors['names'] = 'Names are required.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'A valid email is required.';
    }
    if ($old['phone'] === '') {
        $errors['phone'] = 'Phone number is required.';
    }
    if ($old['national_id'] === '') {
        $errors['national_id'] = 'National ID is required.';
    }
    if (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (!$errors) {
        $check = db()->prepare('SELECT id FROM admins WHERE email = ? OR national_id = ?');
        $check->execute([$old['email'], $old['national_id']]);
        if ($check->fetch()) {
            $errors['email'] = 'An admin with this email or national ID already exists.';
        }
    }

    if (!$errors) {
        $stmt = db()->prepare('INSERT INTO admins (names, email, phone, national_id, password) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $old['names'],
            $old['email'],
            $old['phone'],
            $old['national_id'],
            password_hash($password, PASSWORD_DEFAULT),
        ]);
        set_flash('success', 'Account created successfully. Please log in.');
        redirect('auth/login.php');
    }
}

$pageTitle = 'Admin Sign Up';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-7 col-md-9">
        <div class="card auth-card shadow-sm">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <i class="bi bi-truck-front-fill auth-logo"></i>
                    <h3 class="mt-2 mb-0 fw-bold">Create Admin Account</h3>
                    <p class="text-muted small">MAGERWA Vehicle Tracking System</p>
                </div>
                <form method="post" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Names</label>
                            <input type="text" name="names" class="form-control <?= isset($errors['names']) ? 'is-invalid' : '' ?>" value="<?= e($old['names']) ?>">
                            <div class="invalid-feedback"><?= e($errors['names'] ?? '') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= e($old['email']) ?>">
                            <div class="invalid-feedback"><?= e($errors['email'] ?? '') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" value="<?= e($old['phone']) ?>">
                            <div class="invalid-feedback"><?= e($errors['phone'] ?? '') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">National ID</label>
                            <input type="text" name="national_id" class="form-control <?= isset($errors['national_id']) ? 'is-invalid' : '' ?>" value="<?= e($old['national_id']) ?>">
                            <div class="invalid-feedback"><?= e($errors['national_id'] ?? '') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>">
                            <div class="invalid-feedback"><?= e($errors['password'] ?? '') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>">
                            <div class="invalid-feedback"><?= e($errors['confirm_password'] ?? '') ?></div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-4">Sign Up</button>
                </form>
                <p class="text-center mt-3 mb-0 small">Already have an account? <a href="<?= url('auth/login.php') ?>">Log in</a></p>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
