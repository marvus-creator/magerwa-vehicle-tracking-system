<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

$errors = [];
$old = ['chassis_number' => '', 'manufacture_company' => '', 'manufacture_year' => '', 'price' => '', 'model_name' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['chassis_number'] = clean($_POST['chassis_number'] ?? '');
    $old['manufacture_company'] = clean($_POST['manufacture_company'] ?? '');
    $old['manufacture_year'] = clean($_POST['manufacture_year'] ?? '');
    $old['price'] = clean($_POST['price'] ?? '');
    $old['model_name'] = clean($_POST['model_name'] ?? '');

    if ($old['chassis_number'] === '') {
        $errors['chassis_number'] = 'Chassis number is required.';
    }
    if ($old['manufacture_company'] === '') {
        $errors['manufacture_company'] = 'Manufacture company is required.';
    }
    if (!ctype_digit($old['manufacture_year']) || (int) $old['manufacture_year'] < 1900 || (int) $old['manufacture_year'] > (int) date('Y') + 1) {
        $errors['manufacture_year'] = 'Enter a valid manufacture year.';
    }
    if (!is_numeric($old['price']) || (float) $old['price'] < 0) {
        $errors['price'] = 'Enter a valid price.';
    }
    if ($old['model_name'] === '') {
        $errors['model_name'] = 'Model name is required.';
    }

    if (!$errors) {
        $check = db()->prepare('SELECT id FROM vehicles WHERE chassis_number = ?');
        $check->execute([$old['chassis_number']]);
        if ($check->fetch()) {
            $errors['chassis_number'] = 'A vehicle with this chassis number already exists.';
        }
    }

    if (!$errors) {
        $stmt = db()->prepare('INSERT INTO vehicles (chassis_number, manufacture_company, manufacture_year, price, model_name) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $old['chassis_number'],
            $old['manufacture_company'],
            (int) $old['manufacture_year'],
            (float) $old['price'],
            $old['model_name'],
        ]);
        set_flash('success', 'Vehicle registered successfully.');
        redirect('vehicles/index.php');
    }
}

$pageTitle = 'Register Vehicle';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-car-front-fill text-success me-2"></i>Register Vehicle</h5>
            </div>
            <div class="card-body p-4">
                <form method="post" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Chassis Number</label>
                            <input type="text" name="chassis_number" class="form-control <?= isset($errors['chassis_number']) ? 'is-invalid' : '' ?>" value="<?= e($old['chassis_number']) ?>">
                            <div class="invalid-feedback"><?= e($errors['chassis_number'] ?? '') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Manufacture Company</label>
                            <input type="text" name="manufacture_company" class="form-control <?= isset($errors['manufacture_company']) ? 'is-invalid' : '' ?>" value="<?= e($old['manufacture_company']) ?>">
                            <div class="invalid-feedback"><?= e($errors['manufacture_company'] ?? '') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Manufacture Year</label>
                            <input type="number" name="manufacture_year" class="form-control <?= isset($errors['manufacture_year']) ? 'is-invalid' : '' ?>" value="<?= e($old['manufacture_year']) ?>">
                            <div class="invalid-feedback"><?= e($errors['manufacture_year'] ?? '') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Price (RWF)</label>
                            <input type="number" step="0.01" name="price" class="form-control <?= isset($errors['price']) ? 'is-invalid' : '' ?>" value="<?= e($old['price']) ?>">
                            <div class="invalid-feedback"><?= e($errors['price'] ?? '') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Model Name</label>
                            <input type="text" name="model_name" class="form-control <?= isset($errors['model_name']) ? 'is-invalid' : '' ?>" value="<?= e($old['model_name']) ?>">
                            <div class="invalid-feedback"><?= e($errors['model_name'] ?? '') ?></div>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i>Save Vehicle</button>
                        <a href="<?= url('vehicles/index.php') ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
