<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

$id = (int) ($_GET['id'] ?? $_POST['vehicle_id'] ?? 0);

$stmt = db()->prepare('SELECT * FROM vehicles WHERE id = ? AND client_id IS NOT NULL');
$stmt->execute([$id]);
$vehicle = $stmt->fetch();

if (!$vehicle) {
    set_flash('danger', 'Linked record not found.');
    redirect('vehicles/linked.php');
}

$errors = [];
$old = [
    'client_id' => $vehicle['client_id'],
    'plate_number' => $vehicle['plate_number'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['client_id'] = clean($_POST['client_id'] ?? '');
    $old['plate_number'] = strtoupper(clean($_POST['plate_number'] ?? ''));

    if ($old['client_id'] === '') {
        $errors['client_id'] = 'Select a client.';
    }
    if ($old['plate_number'] === '') {
        $errors['plate_number'] = 'Plate number is required.';
    }

    if (!$errors) {
        $check = db()->prepare('SELECT id FROM vehicles WHERE plate_number = ? AND id <> ?');
        $check->execute([$old['plate_number'], $id]);
        if ($check->fetch()) {
            $errors['plate_number'] = 'This plate number is already assigned.';
        }
    }

    if (!$errors) {
        $stmt = db()->prepare('UPDATE vehicles SET client_id = ?, plate_number = ? WHERE id = ?');
        $stmt->execute([(int) $old['client_id'], $old['plate_number'], $id]);
        set_flash('success', 'Linked record updated successfully.');
        redirect('vehicles/linked.php');
    }
}

$clients = db()->query('SELECT id, names, national_id FROM clients ORDER BY names ASC')->fetchAll();

$pageTitle = 'Edit Linked Record';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square text-info me-2"></i>Edit Linked Record</h5>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-light border mb-4">
                    <i class="bi bi-car-front me-1"></i>
                    <strong><?= e($vehicle['manufacture_company']) ?> <?= e($vehicle['model_name']) ?></strong>
                    (<?= e($vehicle['manufacture_year']) ?>) — Chassis <?= e($vehicle['chassis_number']) ?>
                </div>
                <form method="post" novalidate>
                    <input type="hidden" name="vehicle_id" value="<?= e($vehicle['id']) ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Client</label>
                            <select name="client_id" class="form-select <?= isset($errors['client_id']) ? 'is-invalid' : '' ?>">
                                <option value="">-- Select Client --</option>
                                <?php foreach ($clients as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $old['client_id'] == $c['id'] ? 'selected' : '' ?>>
                                    <?= e($c['names']) ?> (<?= e($c['national_id']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"><?= e($errors['client_id'] ?? '') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Plate Number</label>
                            <input type="text" name="plate_number" class="form-control text-uppercase <?= isset($errors['plate_number']) ? 'is-invalid' : '' ?>" value="<?= e($old['plate_number']) ?>" placeholder="e.g. RAD 123 A">
                            <div class="invalid-feedback"><?= e($errors['plate_number'] ?? '') ?></div>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-info text-white"><i class="bi bi-save me-1"></i>Update Record</button>
                        <a href="<?= url('vehicles/linked.php') ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
