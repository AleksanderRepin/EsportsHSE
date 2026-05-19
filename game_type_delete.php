<?php
require __DIR__ . "/db.php";
require __DIR__ . "/auth.php";
require_admin();

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM gametype WHERE id = ?");
        $stmt->execute([$id]);
    } catch (Throwable $e) {
        header("Location: game_types.php?type_id=$id&delete_error=1");
        exit;
    }
}

header("Location: game_types.php");
exit;
