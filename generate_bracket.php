<?php
require __DIR__ . "/db.php";
require __DIR__ . "/auth.php";
require __DIR__ . "/ui.php";
require_admin();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$confirmed = ($_POST['confirm'] ?? '') === 'yes';
$confirmText = trim($_POST['confirm_text'] ?? '');

$stmt = $pdo->prepare("
    SELECT t.id, t.name, g.team_size
    FROM tournament t
    JOIN gametype g ON g.id = t.game_type_id
    WHERE t.id = ?
");
$stmt->execute([$id]);
$tournament = $stmt->fetch();

if (!$tournament) {
    die("Турнир не найден.");
}

$stmt = $pdo->prepare("
    SELECT DISTINCT
        u.nickname,
        t1.name AS team_a,
        t2.name AS team_b
    FROM tournamentteam tt1
    JOIN tournamentteam tt2
      ON tt1.tournament_id = tt2.tournament_id
     AND tt1.team_id < tt2.team_id
    JOIN tournamentroster tp1 ON tp1.tournament_id = tt1.tournament_id AND tp1.team_id = tt1.team_id
    JOIN tournamentroster tp2 ON tp2.tournament_id = tt2.tournament_id AND tp2.team_id = tt2.team_id
     AND tp1.user_id = tp2.user_id
    JOIN users u ON u.id = tp1.user_id
    JOIN team t1 ON t1.id = tt1.team_id
    JOIN team t2 ON t2.id = tt2.team_id
    WHERE tt1.tournament_id = ?
    ORDER BY u.nickname, t1.name, t2.name
");
$stmt->execute([$id]);
$rosterConflicts = $stmt->fetchAll();
if ($rosterConflicts) {
    include __DIR__ . "/header.php";
    ?>
    <div class="page-card">
        <h2 class="page-title">Сетку нельзя создать</h2>
        <div class="alert alert-danger">
            Один игрок не может участвовать в одном турнире за несколько команд.
        </div>
        <table class="table table-bordered table-hover">
            <thead class="table-light">
            <tr><th>Игрок</th><th>Команда 1</th><th>Команда 2</th></tr>
            </thead>
            <tbody>
            <?php foreach ($rosterConflicts as $conflict): ?>
                <tr>
                    <td><?= htmlspecialchars($conflict['nickname']) ?></td>
                    <td><?= htmlspecialchars($conflict['team_a']) ?></td>
                    <td><?= htmlspecialchars($conflict['team_b']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <a class="btn btn-primary" href="tournament.php?id=<?= $id ?>&tab=teams">Открыть команды турнира</a>
    </div>
    <?php
    include __DIR__ . "/footer.php";
    exit;
}

$stmt = $pdo->prepare("
    SELECT t.name, COUNT(tr.user_id) AS players_count
    FROM tournamentteam tt
    JOIN team t ON t.id = tt.team_id
    LEFT JOIN tournamentroster tr ON tr.tournament_id = tt.tournament_id AND tr.team_id = tt.team_id
    WHERE tt.tournament_id = ?
    GROUP BY t.id, t.name
    HAVING COUNT(tr.user_id) < ?
    ORDER BY t.name
");
$stmt->execute([$id, (int)$tournament['team_size']]);
$underfilledTeams = $stmt->fetchAll();
if ($underfilledTeams) {
    include __DIR__ . "/header.php";
    ?>
    <div class="page-card">
        <h2 class="page-title">Сетку нельзя создать</h2>
        <div class="alert alert-warning">
            Не все команды укомплектовали заявку на турнир. Для дисциплины требуется <?= (int)$tournament['team_size'] ?> игроков.
        </div>
        <table class="table table-bordered table-hover">
            <thead class="table-light">
            <tr><th>Команда</th><th>Игроков в заявке</th><th>Требуется</th></tr>
            </thead>
            <tbody>
            <?php foreach ($underfilledTeams as $team): ?>
                <tr>
                    <td><?= htmlspecialchars($team['name']) ?></td>
                    <td><?= (int)$team['players_count'] ?></td>
                    <td><?= (int)$tournament['team_size'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <a class="btn btn-primary" href="tournament.php?id=<?= $id ?>&tab=teams">Открыть команды турнира</a>
    </div>
    <?php
    include __DIR__ . "/footer.php";
    exit;
}

$stmt = $pdo->prepare("
    SELECT tt.team_id, COALESCE(tt.seed, 9999) AS seed
    FROM tournamentteam tt
    WHERE tt.tournament_id = ?
    ORDER BY COALESCE(tt.seed, 9999), tt.id
");
$stmt->execute([$id]);
$teams = $stmt->fetchAll();
$count = count($teams);

if ($count < 2) {
    die("Недостаточно команд для сетки.");
}

if (($count & ($count - 1)) !== 0) {
    die("Количество команд должно быть степенью двойки: 2, 4, 8, 16...");
}

$stmt = $pdo->prepare("
    SELECT COUNT(*) AS matches_count,
           COUNT(*) FILTER (WHERE is_finished OR winner_team_id IS NOT NULL) AS filled_matches
    FROM match
    WHERE tournament_id = ?
");
$stmt->execute([$id]);
$bracketStats = $stmt->fetch();
$matchesCount = (int)$bracketStats['matches_count'];
$filledMatches = (int)$bracketStats['filled_matches'];

if ($matchesCount > 0 && (!$confirmed || ($filledMatches > 0 && $confirmText !== 'ПЕРЕСОЗДАТЬ'))) {
    include __DIR__ . "/header.php";
    ?>
    <div class="page-card">
        <h2 class="page-title">Пересоздать сетку?</h2>
        <div class="alert alert-warning">
            В турнире «<?= htmlspecialchars($tournament['name']) ?>» уже есть матчи: <?= $matchesCount ?>.
            <?php if ($filledMatches > 0): ?>
                Часть матчей уже содержит результаты или победителей: <?= $filledMatches ?>.
            <?php endif; ?>
        </div>
        <p class="text-muted">
            Пересоздание удалит текущие стадии, матчи, партии и статистику, а затем создаст сетку заново по текущему посеву команд.
        </p>
        <form method="post" class="d-flex gap-2">
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="hidden" name="confirm" value="yes">
            <?php if ($filledMatches > 0): ?>
                <div class="w-100">
                    <label class="form-label">Для подтверждения введите ПЕРЕСОЗДАТЬ</label>
                    <input class="form-control mb-3" name="confirm_text" autocomplete="off">
                </div>
            <?php endif; ?>
            <div class="d-flex gap-2">
                <button class="btn btn-danger">Да, пересоздать сетку</button>
                <a class="btn btn-outline-secondary" href="tournament.php?id=<?= $id ?>">Отмена</a>
            </div>
        </form>
    </div>
    <?php
    include __DIR__ . "/footer.php";
    exit;
}

$stageNames = [
    1 => ['Финал'],
    2 => ['Полуфинал', 'Финал'],
    3 => ['Четвертьфинал', 'Полуфинал', 'Финал'],
    4 => ['1/8 финала', 'Четвертьфинал', 'Полуфинал', 'Финал'],
    5 => ['1/16 финала', '1/8 финала', 'Четвертьфинал', 'Полуфинал', 'Финал'],
];

$rounds = (int)log($count, 2);
$names = $stageNames[$rounds] ?? [];

$pdo->beginTransaction();

$pdo->prepare("DELETE FROM tournamentstage WHERE tournament_id = ?")->execute([$id]);

$stageIds = [];
foreach ($names as $index => $name) {
    $stmt = $pdo->prepare("
        INSERT INTO tournamentstage (tournament_id, name, stage_order)
        VALUES (?, ?, ?)
        RETURNING id
    ");
    $stmt->execute([$id, $name, $index + 1]);
    $stageIds[] = (int)$stmt->fetchColumn();
}

$firstStageId = $stageIds[0];
$pairs = [];
$left = 0;
$right = $count - 1;
while ($left < $right) {
    $pairs[] = [$teams[$left]['team_id'], $teams[$right]['team_id']];
    $left++;
    $right--;
}

$matchDate = date('Y-m-d H:i:s');
$firstMatchId = 0;
foreach ($pairs as $pair) {
    $stmt = $pdo->prepare("
        INSERT INTO match (
            tournament_id, stage_id, team1_id, team2_id,
            team1_score, team2_score, winner_team_id,
            match_date, end_time, is_finished
        )
        VALUES (?, ?, ?, ?, 0, 0, NULL, ?, NULL, false)
        RETURNING id
    ");
    $stmt->execute([$id, $firstStageId, $pair[0], $pair[1], $matchDate]);
    if (!$firstMatchId) {
        $firstMatchId = (int)$stmt->fetchColumn();
    }
}

$pdo->commit();

header("Location: match.php?id=$firstMatchId&flow=bracket");
