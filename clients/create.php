<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

$errors = [];
$old = ['names' => '', 'national_id' => '', 'telephone' => '', 'address' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['names'] = clean($_POST['names'] ?? '');
    $old['national_id'] = clean($_POST['national_id'] ?? '');
    $old['telephone'] = clean($_POST['telephone'] ?? '');
    $old['address'] = clean($_POST['address'] ?? '');

    if ($old['names'] === '') {
        $errors['names'] = 'Names are required.';
    }
    if ($old['national_id'] === '') {
        $errors['national_id'] = 'National ID is required.';
    }
    if ($old['telephone'] === '') {
        $errors['telephone'] = 'Telephone is required.';
    }
    if ($old['address'] === '') {
        $errors['address'] = 'Address is required.';
    }

    if (!$errors) {
        $check = db()->prepare('SELECT id FROM clients WHERE national_id = ?');
        $check->execute([$old['national_id']]);
        if ($check->fetch()) {
            $errors['national_id'] = 'A client with this national ID already exists.';
        }
    }

    if (!$errors) {
        $stmt = db()->prepare('INSERT INTO clients (names, national_id, telephone, address) VALUES (?, ?, ?, ?)');
        $stmt->execute([$old['names'], $old['national_id'], $old['telephone'], $old['address']]);
        set_flash('success', 'Client registered successfully.');
        redirect('clients/index.php');
    }
}

$pageTitle = 'Register Client';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-person-plus-fill text-primary me-2"></i>Register Client</h5>
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
                            <label class="form-label">National ID</label>
                            <input type="text" name="national_id" class="form-control <?= isset($errors['national_id']) ? 'is-invalid' : '' ?>" value="<?= e($old['national_id']) ?>">
                            <div class="invalid-feedback"><?= e($errors['national_id'] ?? '') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telephone</label>
                            <input type="text" name="telephone" class="form-control <?= isset($errors['telephone']) ? 'is-invalid' : '' ?>" value="<?= e($old['telephone']) ?>">
                            <div class="invalid-feedback"><?= e($errors['telephone'] ?? '') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>" value="<?= e($old['address']) ?>">
                            <div class="invalid-feedback"><?= e($errors['address'] ?? '') ?></div>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Save Client</button>
                        <a href="<?= url('clients/index.php') ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
