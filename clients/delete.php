<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('clients/index.php');
}

$id = (int) ($_POST['id'] ?? 0);

$stmt = db()->prepare('DELETE FROM clients WHERE id = ?');
$stmt->execute([$id]);

if ($stmt->rowCount() > 0) {
    set_flash('success', 'Client deleted successfully. Any linked vehicles were unlinked.');
} else {
    set_flash('danger', 'Client not found.');
}
redirect('clients/index.php');
