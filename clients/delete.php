<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('clients/index.php');
}

$id = (int) ($_POST['id'] ?? 0);

// Clear the plate on any vehicles linked to this client so they are fully
// unlinked. The FK nulls client_id on delete, but plate_number would linger.
$clear = db()->prepare('UPDATE vehicles SET plate_number = NULL WHERE client_id = ?');
$clear->execute([$id]);

$stmt = db()->prepare('DELETE FROM clients WHERE id = ?');
$stmt->execute([$id]);

if ($stmt->rowCount() > 0) {
    set_flash('success', 'Client deleted successfully. Any linked vehicles were unlinked.');
} else {
    set_flash('danger', 'Client not found.');
}
redirect('clients/index.php');
