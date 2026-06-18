<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

$errors = [];
$old = ['vehicle_id' => '', 'client_id' => '', 'plate_number' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['vehicle_id'] = clean($_POST['vehicle_id'] ?? '');
    $old['client_id'] = clean($_POST['client_id'] ?? '');
    $old['plate_number'] = strtoupper(clean($_POST['plate_number'] ?? ''));

    if ($old['vehicle_id'] === '') {
        $errors['vehicle_id'] = 'Select a vehicle.';
    }
    if ($old['client_id'] === '') {
        $errors['client_id'] = 'Select a client.';
    }
    if ($old['plate_number'] === '') {
        $errors['plate_number'] = 'Plate number is required.';
    }

    if (!$errors) {
        $check = db()->prepare('SELECT id FROM vehicles WHERE plate_number = ? AND id <> ?');
        $check->execute([$old['plate_number'], $old['vehicle_id']]);
        if ($check->fetch()) {
            $errors['plate_number'] = 'This plate number is already assigned.';
        }
    }

    if (!$errors) {
        $stmt = db()->prepare('UPDATE vehicles SET client_id = ?, plate_number = ? WHERE id = ?');
        $stmt->execute([(int) $old['client_id'], $old['plate_number'], (int) $old['vehicle_id']]);
        set_flash('success', 'Vehicle linked to client successfully.');
        redirect('vehicles/linked.php');
    }
}

$vehicles = db()->query('SELECT id, chassis_number, model_name FROM vehicles WHERE client_id IS NULL ORDER BY id DESC')->fetchAll();
$clients = db()->query('SELECT id, names, national_id FROM clients ORDER BY names ASC')->fetchAll();

$pageTitle = 'Link Vehicle';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-link-45deg text-info me-2"></i>Link Vehicle to Client</h5>
            </div>
            <div class="card-body p-4">
                <?php if (!$vehicles): ?>
                <div class="alert alert-info mb-0">All vehicles are already linked. <a href="<?= url('vehicles/create.php') ?>">Register a new vehicle</a> to link it.</div>
                <?php elseif (!$clients): ?>
                <div class="alert alert-info mb-0">No clients available. <a href="<?= url('clients/create.php') ?>">Register a client</a> first.</div>
                <?php else: ?>
                <form method="post" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Vehicle (unlinked)</label>
                            <select name="vehicle_id" class="form-select <?= isset($errors['vehicle_id']) ? 'is-invalid' : '' ?>">
                                <option value="">-- Select Vehicle --</option>
                                <?php foreach ($vehicles as $v): ?>
                                <option value="<?= $v['id'] ?>" <?= $old['vehicle_id'] == $v['id'] ? 'selected' : '' ?>>
                                    <?= e($v['chassis_number']) ?> — <?= e($v['model_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"><?= e($errors['vehicle_id'] ?? '') ?></div>
                        </div>
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
                        <button type="submit" class="btn btn-info text-white"><i class="bi bi-link-45deg me-1"></i>Link Vehicle</button>
                        <a href="<?= url('vehicles/linked.php') ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
