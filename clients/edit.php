<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = db()->prepare('SELECT * FROM clients WHERE id = ?');
$stmt->execute([$id]);
$client = $stmt->fetch();

if (!$client) {
    set_flash('danger', 'Client not found.');
    redirect('clients/index.php');
}

$errors = [];
$old = [
    'names' => $client['names'],
    'national_id' => $client['national_id'],
    'telephone' => $client['telephone'],
    'address' => $client['address'],
];

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
        $check = db()->prepare('SELECT id FROM clients WHERE national_id = ? AND id <> ?');
        $check->execute([$old['national_id'], $id]);
        if ($check->fetch()) {
            $errors['national_id'] = 'Another client with this national ID already exists.';
        }
    }

    if (!$errors) {
        $stmt = db()->prepare('UPDATE clients SET names = ?, national_id = ?, telephone = ?, address = ? WHERE id = ?');
        $stmt->execute([$old['names'], $old['national_id'], $old['telephone'], $old['address'], $id]);
        set_flash('success', 'Client updated successfully.');
        redirect('clients/index.php');
    }
}

$pageTitle = 'Edit Client';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Client</h5>
            </div>
            <div class="card-body p-4">
                <form method="post" novalidate>
                    <input type="hidden" name="id" value="<?= e($client['id']) ?>">
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
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Update Client</button>
                        <a href="<?= url('clients/index.php') ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
