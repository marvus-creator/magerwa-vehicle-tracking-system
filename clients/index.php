<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

$total = (int) db()->query('SELECT COUNT(*) FROM clients')->fetchColumn();
$meta = paginate($total, PER_PAGE, current_page());

$stmt = db()->prepare('SELECT * FROM clients ORDER BY id DESC LIMIT :limit OFFSET :offset');
$stmt->bindValue(':limit', PER_PAGE, PDO::PARAM_INT);
$stmt->bindValue(':offset', $meta['offset'], PDO::PARAM_INT);
$stmt->execute();
$clients = $stmt->fetchAll();

$pageTitle = 'Clients';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0">Clients</h2>
        <p class="text-muted mb-0"><?= $total ?> registered client(s)</p>
    </div>
    <a href="<?= url('clients/create.php') ?>" class="btn btn-primary"><i class="bi bi-person-plus-fill me-1"></i>Register Client</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Names</th>
                        <th>National ID</th>
                        <th>Telephone</th>
                        <th>Address</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$clients): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No clients registered yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($clients as $i => $client): ?>
                        <tr>
                            <td><?= $meta['offset'] + $i + 1 ?></td>
                            <td class="fw-semibold"><?= e($client['names']) ?></td>
                            <td><?= e($client['national_id']) ?></td>
                            <td><?= e($client['telephone']) ?></td>
                            <td><?= e($client['address']) ?></td>
                            <td class="text-end">
                                <a href="<?= url('clients/edit.php?id=' . $client['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square"></i></a>
                                <form action="<?= url('clients/delete.php') ?>" method="post" class="d-inline" onsubmit="return confirm('Delete client <?= e($client['names']) ?>? This cannot be undone.');">
                                    <input type="hidden" name="id" value="<?= e($client['id']) ?>">
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
