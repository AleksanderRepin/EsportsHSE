<?php
require __DIR__ . "/db.php";
include __DIR__ . "/header.php";

$errors = [];
$selectedTournamentId = (int)($_GET['tournament_id'] ?? $_POST['tournament_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin();
    $stageId = (int)($_POST['stage_id'] ?? 0);
    $team1Id = (int)($_POST['team1_id'] ?? 0);
    $team2Id = (int)($_POST['team2_id'] ?? 0);
    $matchDate = $_POST['match_date'] ?: null;

    if (!$selectedTournamentId || !$stageId || !$team1Id || !$team2Id) {
        $errors[] = "Выберите турнир, стадию и обе команды.";
    } elseif ($team1Id === $team2Id) {
        $errors[] = "Команда не может играть сама с собой.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO match (
                tournament_id, stage_id, team1_id, team2_id,
                team1_score, team2_score, match_date, is_finished
            )
            VALUES (?, ?, ?, ?, 0, 0, ?, false)
        ");
        $stmt->execute([$selectedTournamentId, $stageId, $team1Id, $team2Id, $matchDate]);
        header("Location: matches.php?tournament_id=$selectedTournamentId");
        exit;
    }
}

$tournaments = $pdo->query("SELECT id, name FROM tournament ORDER BY start_date DESC")->fetchAll();
if (!$selectedTournamentId && $tournaments) {
    $selectedTournamentId = (int)$tournaments[0]['id'];
}

$stages = [];
$tournamentTeams = [];
$matches = [];

if ($selectedTournamentId) {
    $stmt = $pdo->prepare("SELECT * FROM tournamentstage WHERE tournament_id = ? ORDER BY stage_order");
    $stmt->execute([$selectedTournamentId]);
    $stages = $stmt->fetchAll();

    $stmt = $pdo->prepare("
        SELECT team.id, team.name
        FROM tournamentteam tt
        JOIN team ON team.id = tt.team_id
        WHERE tt.tournament_id = ?
        ORDER BY COALESCE(tt.seed, 9999), team.name
    ");
    $stmt->execute([$selectedTournamentId]);
    $tournamentTeams = $stmt->fetchAll();

    $stmt = $pdo->prepare("
        SELECT
            m.*,
            s.name AS stage_name,
            t1.name AS team1_name,
            t2.name AS team2_name,
            w.name AS winner_name,
            COUNT(mg.id) AS games_count
        FROM match m
        JOIN tournamentstage s ON s.id = m.stage_id
        JOIN team t1 ON t1.id = m.team1_id
        JOIN team t2 ON t2.id = m.team2_id
        LEFT JOIN team w ON w.id = m.winner_team_id
        LEFT JOIN matchgame mg ON mg.match_id = m.id
        WHERE m.tournament_id = ?
        GROUP BY m.id, s.name, s.stage_order, t1.name, t2.name, w.name
        ORDER BY s.stage_order, m.match_date, m.id
    ");
    $stmt->execute([$selectedTournamentId]);
    $matches = $stmt->fetchAll();
}
?>

<div class="page-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <h2 class="page-title mb-0">Матчи</h2>
        <form method="get" class="d-flex gap-2">
            <select name="tournament_id" class="form-select" onchange="this.form.submit()">
                <?php foreach ($tournaments as $tournament): ?>
                    <option value="<?= $tournament['id'] ?>" <?= $selectedTournamentId === (int)$tournament['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($tournament['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <a class="btn btn-outline-primary" href="tournament.php?id=<?= $selectedTournamentId ?>">Турнир</a>
        </form>
    </div>
</div>

<?php if ($errors): ?>
    <div class="alert alert-danger"><?= htmlspecialchars(implode(' ', $errors)) ?></div>
<?php endif; ?>

<?php if ($isAdmin): ?>
<div class="page-card mb-4">
    <h3 class="page-title">Новый матч</h3>
    <form method="post" class="row g-3">
        <input type="hidden" name="tournament_id" value="<?= $selectedTournamentId ?>">
        <div class="col-md-2">
            <label class="form-label">Стадия</label>
            <select name="stage_id" class="form-select" required>
                <?php foreach ($stages as $stage): ?>
                    <option value="<?= $stage['id'] ?>"><?= htmlspecialchars(stage_label($stage['name'])) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Первая команда</label>
            <select name="team1_id" class="form-select" required>
                <?php foreach ($tournamentTeams as $team): ?>
                    <option value="<?= $team['id'] ?>"><?= htmlspecialchars($team['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Вторая команда</label>
            <select name="team2_id" class="form-select" required>
                <?php foreach ($tournamentTeams as $team): ?>
                    <option value="<?= $team['id'] ?>"><?= htmlspecialchars($team['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Дата и время</label>
            <input type="datetime-local" name="match_date" class="form-control">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100">Создать</button>
        </div>
    </form>
</div>
<?php endif; ?>

<div class="page-card">
    <h3 class="page-title">Список матчей</h3>

    <?php if (!$matches): ?>
        <div class="text-muted">Матчей пока нет.</div>
    <?php else: ?>
        <table class="table table-hover table-bordered">
            <thead class="table-light">
            <tr>
                <th>Стадия</th>
                <th>Матч</th>
                <th>Счет</th>
                <th>Победитель</th>
                <th>Дата</th>
                <th>Статус</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($matches as $match): ?>
                <tr>
                    <td><?= htmlspecialchars(stage_label($match['stage_name'])) ?></td>
                    <td><?= htmlspecialchars($match['team1_name']) ?> против <?= htmlspecialchars($match['team2_name']) ?></td>
                    <td class="fw-bold"><?= (int)$match['team1_score'] ?> : <?= (int)$match['team2_score'] ?></td>
                    <td><?= htmlspecialchars($match['winner_name'] ?? 'не выбран') ?></td>
                    <td><?= htmlspecialchars($match['match_date'] ?? '') ?></td>
                    <td>
                        <span class="badge <?= $match['is_finished'] ? 'text-bg-success' : 'text-bg-secondary' ?>">
                            <?= $match['is_finished'] ? 'завершен' : 'запланирован' ?>
                        </span>
                    </td>
                    <td>
                        <a class="btn btn-sm btn-outline-primary" href="match.php?id=<?= $match['id'] ?>">Открыть</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . "/footer.php"; ?>
