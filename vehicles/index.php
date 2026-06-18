<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

$total = (int) db()->query('SELECT COUNT(*) FROM vehicles')->fetchColumn();
$meta = paginate($total, PER_PAGE, current_page());

$stmt = db()->prepare(
    'SELECT v.*, c.names AS client_name FROM vehicles v
     LEFT JOIN clients c ON v.client_id = c.id
     ORDER BY v.id DESC LIMIT :limit OFFSET :offset'
);
$stmt->bindValue(':limit', PER_PAGE, PDO::PARAM_INT);
$stmt->bindValue(':offset', $meta['offset'], PDO::PARAM_INT);
$stmt->execute();
$vehicles = $stmt->fetchAll();

$pageTitle = 'Vehicles';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0">Vehicles</h2>
        <p class="text-muted mb-0"><?= $total ?> registered vehicle(s)</p>
    </div>
    <a href="<?= url('vehicles/create.php') ?>" class="btn btn-success"><i class="bi bi-plus-square-fill me-1"></i>Register Vehicle</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Chassis No.</th>
                        <th>Company</th>
                        <th>Model</th>
                        <th>Year</th>
                        <th>Price (RWF)</th>
                        <th>Plate</th>
                        <th>Client</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$vehicles): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">No vehicles registered yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($vehicles as $i => $v): ?>
                        <tr>
                            <td><?= $meta['offset'] + $i + 1 ?></td>
                            <td class="fw-semibold"><?= e($v['chassis_number']) ?></td>
                            <td><?= e($v['manufacture_company']) ?></td>
                            <td><?= e($v['model_name']) ?></td>
                            <td><?= e($v['manufacture_year']) ?></td>
                            <td><?= number_format((float) $v['price'], 2) ?></td>
                            <td>
                                <?php if ($v['plate_number']): ?>
                                    <span class="badge text-bg-dark"><?= e($v['plate_number']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($v['client_name']): ?>
                                    <?= e($v['client_name']) ?>
                                <?php else: ?>
                                    <span class="badge text-bg-warning">Unlinked</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= url('vehicles/edit.php?id=' . $v['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square"></i></a>
                                <form action="<?= url('vehicles/delete.php') ?>" method="post" class="d-inline" onsubmit="return confirm('Delete vehicle <?= e($v['chassis_number']) ?>? This cannot be undone.');">
                                    <input type="hidden" name="id" value="<?= e($v['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php require __DIR__ . '/../includes/pagination.php'; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
