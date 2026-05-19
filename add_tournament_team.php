<?php
require __DIR__ . "/db.php";
require __DIR__ . "/auth.php";
require_admin();

$tournamentId = (int)($_POST['tournament_id'] ?? 0);
$teamId = (int)($_POST['team_id'] ?? 0);
$seed = $_POST['seed'] !== '' ? (int)$_POST['seed'] : null;

if (!$tournamentId || !$teamId) {
    header("Location: tournaments.php");
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO tournamentteam (tournament_id, team_id, seed, final_place)
    VALUES (?, ?, ?, NULL)
    ON CONFLICT (tournament_id, team_id)
    DO UPDATE SET seed = EXCLUDED.seed
");
$stmt->execute([$tournamentId, $teamId, $seed]);

header("Location: tournament.php?id=$tournamentId");
