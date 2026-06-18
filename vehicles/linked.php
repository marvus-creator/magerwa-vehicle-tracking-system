<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

$total = (int) db()->query('SELECT COUNT(*) FROM vehicles WHERE client_id IS NOT NULL')->fetchColumn();
$meta = paginate($total, PER_PAGE, current_page());

$stmt = db()->prepare(
    'SELECT v.*, c.names AS client_name, c.national_id AS client_nid, c.telephone AS client_phone
     FROM vehicles v
     INNER JOIN clients c ON v.client_id = c.id
     ORDER BY v.id DESC LIMIT :limit OFFSET :offset'
);
$stmt->bindValue(':limit', PER_PAGE, PDO::PARAM_INT);
$stmt->bindValue(':offset', $meta['offset'], PDO::PARAM_INT);
$stmt->execute();
$records = $stmt->fetchAll();

$pageTitle = 'Linked Records';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0">Linked Records</h2>
        <p class="text-muted mb-0"><?= $total ?> vehicle(s) linked to clients</p>
    </div>
    <a href="<?= url('vehicles/link.php') ?>" class="btn btn-info text-white"><i class="bi bi-link-45deg me-1"></i>Link Vehicle</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Plate</th>
                        <th>Vehicle</th>
                        <th>Chassis No.</th>
                        <th>Client</th>
                        <th>Client NID</th>
                        <th>Client Phone</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$records): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No linked records yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($records as $i => $r): ?>
                        <tr>
                            <td><?= $meta['offset'] + $i + 1 ?></td>
                            <td><span class="badge text-bg-dark"><?= e($r['plate_number']) ?></span></td>
                            <td class="fw-semibold"><?= e($r['manufacture_company']) ?> <?= e($r['model_name']) ?> (<?= e($r['manufacture_year']) ?>)</td>
                            <td><?= e($r['chassis_number']) ?></td>
                            <td><?= e($r['client_name']) ?></td>
                            <td><?= e($r['client_nid']) ?></td>
                            <td><?= e($r['client_phone']) ?></td>
                            <td class="text-end">
                                <a href="<?= url('vehicles/edit_link.php?id=' . $r['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square"></i></a>
                                <form action="<?= url('vehicles/unlink.php') ?>" method="post" class="d-inline" onsubmit="return confirm('Unlink vehicle <?= e($r['plate_number']) ?> from <?= e($r['client_name']) ?>?');">
                                    <input type="hidden" name="id" value="<?= e($r['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-link-45deg"></i></button>
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
