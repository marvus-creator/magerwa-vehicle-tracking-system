<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

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
        set_flash('success', 'Staff member added successfully.');
        redirect('admins/index.php');
    }
}

$pageTitle = 'Add Staff';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-person-plus-fill text-primary me-2"></i>Add Staff</h5>
            </div>
            <div class="card-body p-4">
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
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Save Staff</button>
                        <a href="<?= url('admins/index.php') ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
