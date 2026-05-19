<?php
require __DIR__ . "/db.php";
require __DIR__ . "/auth.php";
require_admin();

$id = (int)($_POST['id'] ?? 0);
$typeId = (int)($_POST['type_id'] ?? 0);
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM gamemap WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: game_types.php?type_id=$typeId");
exit;
