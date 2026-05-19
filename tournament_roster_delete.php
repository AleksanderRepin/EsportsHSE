<?php
require __DIR__ . "/db.php";
require __DIR__ . "/auth.php";
require_admin();

$id = (int)($_POST['id'] ?? 0);
$tournamentId = (int)($_POST['tournament_id'] ?? 0);
$teamId = (int)($_POST['team_id'] ?? 0);

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM tournamentroster WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: tournament.php?id=$tournamentId&tab=teams&team_id=$teamId");
exit;
