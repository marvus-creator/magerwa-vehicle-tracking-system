<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = db()->prepare('SELECT id, names, email, phone, national_id FROM admins WHERE id = ?');
$stmt->execute([$id]);
$admin = $stmt->fetch();

if (!$admin) {
    set_flash('danger', 'Staff member not found.');
    redirect('admins/index.php');
}

$errors = [];
$old = [
    'names' => $admin['names'],
    'email' => $admin['email'],
    'phone' => $admin['phone'],
    'national_id' => $admin['national_id'],
];

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
    if ($password !== '' && strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters.';
    }
    if ($password !== '' && $password !== $confirm) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (!$errors) {
        $check = db()->prepare('SELECT id FROM admins WHERE (email = ? OR national_id = ?) AND id <> ?');
        $check->execute([$old['email'], $old['national_id'], $id]);
        if ($check->fetch()) {
            $errors['email'] = 'Another admin with this email or national ID already exists.';
        }
    }

    if (!$errors) {
        if ($password !== '') {
            $stmt = db()->prepare('UPDATE admins SET names = ?, email = ?, phone = ?, national_id = ?, password = ? WHERE id = ?');
            $stmt->execute([$old['names'], $old['email'], $old['phone'], $old['national_id'], password_hash($password, PASSWORD_DEFAULT), $id]);
        } else {
            $stmt = db()->prepare('UPDATE admins SET names = ?, email = ?, phone = ?, national_id = ? WHERE id = ?');
            $stmt->execute([$old['names'], $old['email'], $old['phone'], $old['national_id'], $id]);
        }
        set_flash('success', 'Staff member updated successfully.');
        redirect('admins/index.php');
    }
}

$pageTitle = 'Edit Staff';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Staff</h5>
            </div>
            <div class="card-body p-4">
                <form method="post" novalidate>
                    <input type="hidden" name="id" value="<?= e($admin['id']) ?>">
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
                            <label class="form-label">New Password <span class="text-muted small">(leave blank to keep)</span></label>
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
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Update Staff</button>
                        <a href="<?= url('admins/index.php') ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
