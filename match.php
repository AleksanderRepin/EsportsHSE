<?php
require __DIR__ . "/db.php";
include __DIR__ . "/header.php";

$id = (int)($_GET['id'] ?? $_POST['match_id'] ?? 0);
$selectedGameId = (int)($_GET['game_id'] ?? $_POST['match_game_id'] ?? 0);
$selectedTeamId = (int)($_GET['team_id'] ?? $_POST['team_id'] ?? 0);
$flow = ($_GET['flow'] ?? $_POST['flow'] ?? '') === 'bracket' ? 'bracket' : '';
$errors = [];

function redirect_match(int $id, int $gameId = 0, string $flow = ''): void
{
    $url = "match.php?id=$id";
    if ($gameId > 0) {
        $url .= "&game_id=$gameId";
    }
    if ($flow === 'bracket') {
        $url .= "&flow=bracket";
    }
    header("Location: $url");
    exit;
}

function next_unfinished_match(PDO $pdo, int $tournamentId): ?int
{
    $stmt = $pdo->prepare("
        SELECT m.id
        FROM match m
        JOIN tournamentstage s ON s.id = m.stage_id
        WHERE m.tournament_id = ?
          AND (m.is_finished = false OR m.winner_team_id IS NULL)
        ORDER BY s.stage_order, m.match_date NULLS LAST, m.id
        LIMIT 1
    ");
    $stmt->execute([$tournamentId]);
    $nextId = $stmt->fetchColumn();
    return $nextId ? (int)$nextId : null;
}

function redirect_next_or_results(PDO $pdo, int $tournamentId): void
{
    $nextId = next_unfinished_match($pdo, $tournamentId);
    if ($nextId) {
        header("Location: match.php?id=$nextId&flow=bracket");
        exit;
    }

    header("Location: tournament.php?id=$tournamentId&tab=results");
    exit;
}

function match_position(PDO $pdo, int $matchId, int $stageId): int
{
    $stmt = $pdo->prepare("
        SELECT id
        FROM match
        WHERE stage_id = ?
        ORDER BY match_date NULLS LAST, id
    ");
    $stmt->execute([$stageId]);

    foreach ($stmt->fetchAll() as $index => $row) {
        if ((int)$row['id'] === $matchId) {
            return $index + 1;
        }
    }

    return 0;
}

function advance_winner(PDO $pdo, int $matchId): void
{
    $stmt = $pdo->prepare("
        SELECT m.*, s.stage_order
        FROM match m
        JOIN tournamentstage s ON s.id = m.stage_id
        WHERE m.id = ?
    ");
    $stmt->execute([$matchId]);
    $match = $stmt->fetch();

    if (!$match || !$match['is_finished'] || !$match['winner_team_id']) {
        return;
    }

    $stmt = $pdo->prepare("
        SELECT id
        FROM tournamentstage
        WHERE tournament_id = ? AND stage_order = ?
    ");
    $stmt->execute([(int)$match['tournament_id'], (int)$match['stage_order'] + 1]);
    $nextStageId = (int)$stmt->fetchColumn();
    if (!$nextStageId) {
        return;
    }

    $position = match_position($pdo, $matchId, (int)$match['stage_id']);
    if (!$position) {
        return;
    }

    $siblingPosition = $position % 2 === 1 ? $position + 1 : $position - 1;
    $stmt = $pdo->prepare("
        SELECT id, winner_team_id, is_finished
        FROM match
        WHERE stage_id = ?
        ORDER BY match_date NULLS LAST, id
        LIMIT 1 OFFSET " . ($siblingPosition - 1)
    );
    $stmt->execute([(int)$match['stage_id']]);
    $sibling = $stmt->fetch();
    if (!$sibling || !$sibling['is_finished'] || !$sibling['winner_team_id']) {
        return;
    }

    $team1Id = $position % 2 === 1 ? (int)$match['winner_team_id'] : (int)$sibling['winner_team_id'];
    $team2Id = $position % 2 === 1 ? (int)$sibling['winner_team_id'] : (int)$match['winner_team_id'];
    $nextSlot = (int)ceil($position / 2);

    $stmt = $pdo->prepare("
        SELECT id, team1_id, team2_id
        FROM match
        WHERE stage_id = ?
        ORDER BY match_date NULLS LAST, id
        LIMIT 1 OFFSET " . ($nextSlot - 1)
    );
    $stmt->execute([$nextStageId]);
    $nextMatch = $stmt->fetch();

    if ($nextMatch) {
        $changed = (int)$nextMatch['team1_id'] !== $team1Id || (int)$nextMatch['team2_id'] !== $team2Id;
        if ($changed) {
            $stmt = $pdo->prepare("
                UPDATE match
                SET team1_id = ?, team2_id = ?,
                    team1_score = 0, team2_score = 0,
                    winner_team_id = NULL, is_finished = false, end_time = NULL
                WHERE id = ?
            ");
            $stmt->execute([$team1Id, $team2Id, (int)$nextMatch['id']]);
        }
        return;
    }

    $stmt = $pdo->prepare("
        INSERT INTO match (
            tournament_id, stage_id, team1_id, team2_id,
            team1_score, team2_score, winner_team_id,
            match_date, end_time, is_finished
        )
        VALUES (?, ?, ?, ?, 0, 0, NULL, NULL, NULL, false)
    ");
    $stmt->execute([(int)$match['tournament_id'], $nextStageId, $team1Id, $team2Id]);
}

function finish_match_from_games(PDO $pdo, int $matchId): array
{
    $stmt = $pdo->prepare("SELECT tournament_id, team1_id, team2_id FROM match WHERE id = ?");
    $stmt->execute([$matchId]);
    $match = $stmt->fetch();
    if (!$match) {
        return ["Матч не найден."];
    }

    $team1Id = (int)$match['team1_id'];
    $team2Id = (int)$match['team2_id'];

    $stmt = $pdo->prepare("
        SELECT id, game_number, winner_team_id
        FROM matchgame
        WHERE match_id = ?
        ORDER BY game_number
    ");
    $stmt->execute([$matchId]);
    $games = $stmt->fetchAll();
    if (!$games) {
        return ["Сначала добавьте хотя бы одну партию."];
    }

    $errors = [];
    $team1Score = 0;
    $team2Score = 0;
    foreach ($games as $game) {
        $winnerId = (int)($game['winner_team_id'] ?? 0);
        if (!$winnerId) {
            $errors[] = "В партии " . (int)$game['game_number'] . " не выбран победитель.";
            continue;
        }
        if (!in_array($winnerId, [$team1Id, $team2Id], true)) {
            $errors[] = "В партии " . (int)$game['game_number'] . " выбран победитель не из этого матча.";
            continue;
        }
        if ($winnerId === $team1Id) {
            $team1Score++;
        } else {
            $team2Score++;
        }
    }

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM tournamentroster
        WHERE tournament_id = ? AND team_id IN (?, ?)
    ");
    $stmt->execute([(int)$match['tournament_id'], $team1Id, $team2Id]);
    $expectedStatsPerGame = (int)$stmt->fetchColumn();
    if ($expectedStatsPerGame > 0) {
        $stmt = $pdo->prepare("
            SELECT mg.game_number, COUNT(DISTINCT tr.user_id) AS stats_count
            FROM matchgame mg
            LEFT JOIN playerstats ps ON ps.match_game_id = mg.id
            LEFT JOIN tournamentroster tr
              ON tr.tournament_id = ?
             AND tr.user_id = ps.user_id
             AND tr.team_id IN (?, ?)
            WHERE mg.match_id = ?
            GROUP BY mg.id, mg.game_number
            HAVING COUNT(DISTINCT tr.user_id) < ?
            ORDER BY mg.game_number
        ");
        $stmt->execute([(int)$match['tournament_id'], $team1Id, $team2Id, $matchId, $expectedStatsPerGame]);
        foreach ($stmt->fetchAll() as $row) {
            $errors[] = "В партии " . (int)$row['game_number'] . " заполнена не вся статистика игроков.";
        }
    }

    if ($errors) {
        return $errors;
    }
    if ($team1Score === $team2Score) {
        return ["Нельзя завершить матч: по партиям ничья $team1Score:$team2Score. Добавьте еще одну партию или исправьте победителя партии."];
    }

    $winnerId = $team1Score > $team2Score ? $team1Id : $team2Id;
    $stmt = $pdo->prepare("
        UPDATE match
        SET team1_score = ?, team2_score = ?, winner_team_id = ?,
            is_finished = true, end_time = COALESCE(end_time, NOW())
        WHERE id = ?
    ");
    $stmt->execute([$team1Score, $team2Score, $winnerId, $matchId]);
    advance_winner($pdo, $matchId);

    return [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin();
    $action = $_POST['action'] ?? '';

    if ($action === 'finish_games') {
        $errors = finish_match_from_games($pdo, $id);
        if (!$errors) {
            if ($flow === 'bracket' && ($_POST['continue'] ?? '') === 'yes') {
                $stmt = $pdo->prepare("SELECT tournament_id FROM match WHERE id = ?");
                $stmt->execute([$id]);
                redirect_next_or_results($pdo, (int)$stmt->fetchColumn());
            }
            redirect_match($id, 0, $flow);
        }
    }

    if (false && $action === 'save_result') {
        $team1Score = max(0, (int)($_POST['team1_score'] ?? 0));
        $team2Score = max(0, (int)($_POST['team2_score'] ?? 0));
        $winnerId = (int)($_POST['winner_team_id'] ?? 0);
        $finished = isset($_POST['is_finished']);

        $stmt = $pdo->prepare("SELECT team1_id, team2_id FROM match WHERE id = ?");
        $stmt->execute([$id]);
        $savedMatch = $stmt->fetch();

        if ($finished && !$winnerId && $team1Score !== $team2Score) {
            $winnerId = $team1Score > $team2Score ? (int)$savedMatch['team1_id'] : (int)$savedMatch['team2_id'];
        }

        if ($finished && !$winnerId) {
            $errors[] = "Чтобы завершить матч, выберите победителя.";
        } elseif ($finished && $team1Score === $team2Score) {
            $errors[] = "Завершенный матч не может закончиться ничьей.";
        } elseif ($winnerId && !in_array($winnerId, [(int)$savedMatch['team1_id'], (int)$savedMatch['team2_id']], true)) {
            $errors[] = "Победитель должен быть одной из команд матча.";
        } elseif ($winnerId === (int)$savedMatch['team1_id'] && $team1Score < $team2Score) {
            $errors[] = "Победитель не совпадает со счетом.";
        } elseif ($winnerId === (int)$savedMatch['team2_id'] && $team2Score < $team1Score) {
            $errors[] = "Победитель не совпадает со счетом.";
        }

        if (!$errors) {
            $stmt = $pdo->prepare("
                UPDATE match
                SET team1_score = ?, team2_score = ?, winner_team_id = NULLIF(?, 0),
                    is_finished = ?, end_time = CASE WHEN ? THEN COALESCE(end_time, NOW()) ELSE NULL END
                WHERE id = ?
            ");
            $stmt->execute([$team1Score, $team2Score, $winnerId, $finished, $finished, $id]);
            advance_winner($pdo, $id);
            if ($flow === 'bracket' && ($_POST['continue'] ?? '') === 'yes') {
                $stmt = $pdo->prepare("SELECT tournament_id FROM match WHERE id = ?");
                $stmt->execute([$id]);
                redirect_next_or_results($pdo, (int)$stmt->fetchColumn());
            }
            redirect_match($id, 0, $flow);
        }
    }

    if ($action === 'save_game') {
        $mapId = (int)($_POST['map_id'] ?? 0);
        $gameNumber = max(1, (int)($_POST['game_number'] ?? 1));
        $winnerId = (int)($_POST['winner_team_id'] ?? 0);

        $stmt = $pdo->prepare("SELECT name FROM gamemap WHERE id = ?");
        $stmt->execute([$mapId]);
        $mapName = $stmt->fetchColumn();

        if (!$mapId || !$mapName) {
            $errors[] = "Выберите игровую карту.";
        } elseif (!$winnerId) {
            $errors[] = "Выберите победителя партии.";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM matchgame WHERE match_id = ? AND game_number = ?");
            $stmt->execute([$id, $gameNumber]);
            if ($stmt->fetchColumn()) {
                $errors[] = "Партия с таким номером уже есть. Добавьте следующую партию.";
            }
        }

        if (!$errors) {
            $stmt = $pdo->prepare("
                INSERT INTO matchgame (match_id, map_name, map_id, game_number, winner_team_id)
                VALUES (?, ?, ?, ?, ?)
                RETURNING id
            ");
            $stmt->execute([$id, $mapName, $mapId, $gameNumber, $winnerId]);
            redirect_match($id, (int)$stmt->fetchColumn(), $flow);
        }
    }

    if ($action === 'save_team_stats') {
        $matchGameId = (int)($_POST['match_game_id'] ?? 0);
        $teamId = (int)($_POST['team_id'] ?? 0);
        $killsByUser = $_POST['kills'] ?? [];
        $deathsByUser = $_POST['deaths'] ?? [];

        if (!$matchGameId || !$teamId) {
            $errors[] = "Выберите партию и команду.";
        } else {
            $stmt = $pdo->prepare("SELECT tournament_id, team1_id, team2_id FROM match WHERE id = ?");
            $stmt->execute([$id]);
            $savedMatch = $stmt->fetch();

            if (!$savedMatch || !in_array($teamId, [(int)$savedMatch['team1_id'], (int)$savedMatch['team2_id']], true)) {
                $errors[] = "Выбранная команда не участвует в этом матче.";
            }
        }

        if (!$errors) {
            $stmt = $pdo->prepare("
                SELECT u.id
                FROM tournamentroster tp
                JOIN users u ON u.id = tp.user_id
                WHERE tp.tournament_id = ? AND tp.team_id = ?
            ");
            $stmt->execute([(int)$savedMatch['tournament_id'], $teamId]);
            $allowedUserIds = array_map('intval', array_column($stmt->fetchAll(), 'id'));

            $stmt = $pdo->prepare("
                INSERT INTO playerstats (match_game_id, user_id, kills, deaths)
                VALUES (?, ?, ?, ?)
                ON CONFLICT (match_game_id, user_id)
                DO UPDATE SET kills = EXCLUDED.kills, deaths = EXCLUDED.deaths
            ");

            foreach ($allowedUserIds as $userId) {
                $kills = max(0, (int)($killsByUser[$userId] ?? 0));
                $deaths = max(0, (int)($deathsByUser[$userId] ?? 0));
                $stmt->execute([$matchGameId, $userId, $kills, $deaths]);
            }

            $url = "match.php?id=$id&game_id=$matchGameId&team_id=$teamId";
            if ($flow === 'bracket') $url .= "&flow=bracket";
            header("Location: $url");
            exit;
        }
    }
}

$stmt = $pdo->prepare("
    SELECT
        m.*,
        tr.name AS tournament_name,
        tr.game_type_id,
        s.name AS stage_name,
        t1.name AS team1_name,
        t2.name AS team2_name,
        w.name AS winner_name
    FROM match m
    JOIN tournament tr ON tr.id = m.tournament_id
    JOIN tournamentstage s ON s.id = m.stage_id
    JOIN team t1 ON t1.id = m.team1_id
    JOIN team t2 ON t2.id = m.team2_id
    LEFT JOIN team w ON w.id = m.winner_team_id
    WHERE m.id = ?
");
$stmt->execute([$id]);
$match = $stmt->fetch();

if (!$match) {
    die("Матч не найден.");
}

$stmt = $pdo->prepare("SELECT id, name FROM gamemap WHERE game_type_id = ? ORDER BY name");
$stmt->execute([$match['game_type_id']]);
$maps = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT mg.*, w.name AS winner_name
    FROM matchgame mg
    LEFT JOIN team w ON w.id = mg.winner_team_id
    WHERE mg.match_id = ?
    ORDER BY mg.game_number
");
$stmt->execute([$id]);
$games = $stmt->fetchAll();
$gameIds = array_map('intval', array_column($games, 'id'));
if (!$selectedGameId && $gameIds) {
    $selectedGameId = $gameIds[0];
}
if ($selectedGameId && !in_array($selectedGameId, $gameIds, true)) {
    $selectedGameId = $gameIds[0] ?? 0;
}

$matchTeams = [
    ['id' => (int)$match['team1_id'], 'name' => $match['team1_name']],
    ['id' => (int)$match['team2_id'], 'name' => $match['team2_name']],
];
if (!$selectedTeamId) {
    $selectedTeamId = $matchTeams[0]['id'];
}
if (!in_array($selectedTeamId, array_column($matchTeams, 'id'), true)) {
    $selectedTeamId = $matchTeams[0]['id'];
}
$selectedTeamName = $matchTeams[0]['id'] === $selectedTeamId ? $matchTeams[0]['name'] : $matchTeams[1]['name'];

$stmt = $pdo->prepare("
    SELECT DISTINCT tp.team_id, u.id, u.nickname
    FROM tournamentroster tp
    JOIN users u ON u.id = tp.user_id
    WHERE tp.tournament_id = ? AND tp.team_id IN (?, ?)
    ORDER BY tp.team_id, u.nickname
");
$stmt->execute([$match['tournament_id'], $match['team1_id'], $match['team2_id']]);
$playersByTeam = [];
foreach ($stmt->fetchAll() as $player) {
    $playersByTeam[(int)$player['team_id']][] = $player;
}
$selectedTeamPlayers = $playersByTeam[$selectedTeamId] ?? [];

$stats = [];
$statsByUser = [];
if ($selectedGameId) {
    $stmt = $pdo->prepare("
        SELECT ps.*, u.nickname, mg.game_number, mg.map_name
        FROM playerstats ps
        JOIN users u ON u.id = ps.user_id
        JOIN matchgame mg ON mg.id = ps.match_game_id
        JOIN tournamentroster tp ON tp.user_id = u.id
        WHERE ps.match_game_id = ? AND tp.tournament_id = ? AND tp.team_id = ?
        ORDER BY ps.kills DESC, u.nickname
    ");
    $stmt->execute([$selectedGameId, $match['tournament_id'], $selectedTeamId]);
    $stats = $stmt->fetchAll();
    foreach ($stats as $row) {
        $statsByUser[(int)$row['user_id']] = $row;
    }
}

$resultReady = (bool)$match['is_finished'] && (bool)$match['winner_team_id'];
$gamesReady = count($games) > 0;
$statsReady = count($stats) > 0;
$selectedGame = null;
foreach ($games as $game) {
    if ($selectedGameId === (int)$game['id']) {
        $selectedGame = $game;
        break;
    }
}
$gameWins = [
    (int)$match['team1_id'] => 0,
    (int)$match['team2_id'] => 0,
];
$gamesWithoutWinner = 0;
foreach ($games as $game) {
    $winnerId = (int)($game['winner_team_id'] ?? 0);
    if (!$winnerId) {
        $gamesWithoutWinner++;
    } elseif (array_key_exists($winnerId, $gameWins)) {
        $gameWins[$winnerId]++;
    }
}
$calculatedWinnerId = 0;
if ($games && !$gamesWithoutWinner && $gameWins[(int)$match['team1_id']] !== $gameWins[(int)$match['team2_id']]) {
    $calculatedWinnerId = $gameWins[(int)$match['team1_id']] > $gameWins[(int)$match['team2_id']]
        ? (int)$match['team1_id']
        : (int)$match['team2_id'];
}

$flowProgress = null;
if ($flow === 'bracket') {
    $stmt = $pdo->prepare("
        SELECT m.id, s.stage_order, s.name AS stage_name
        FROM match m
        JOIN tournamentstage s ON s.id = m.stage_id
        WHERE m.tournament_id = ?
        ORDER BY s.stage_order, m.match_date NULLS LAST, m.id
    ");
    $stmt->execute([(int)$match['tournament_id']]);
    $flowMatches = $stmt->fetchAll();
    $stageOrders = [];
    $currentIndex = 0;
    $stageMatchIndex = 0;
    $stageMatchCount = 0;
    foreach ($flowMatches as $index => $flowMatch) {
        $stageOrders[(int)$flowMatch['stage_order']] = true;
        if ((int)$flowMatch['id'] === $id) {
            $currentIndex = $index + 1;
            $currentStageOrder = (int)$flowMatch['stage_order'];
            $stageName = $flowMatch['stage_name'];
        }
    }
    foreach ($flowMatches as $flowMatch) {
        if ((int)$flowMatch['stage_order'] === ($currentStageOrder ?? 0)) {
            $stageMatchCount++;
            if ((int)$flowMatch['id'] === $id) {
                $stageMatchIndex = $stageMatchCount;
            }
        }
    }
    $flowProgress = [
        'current' => $currentIndex,
        'total' => count($flowMatches),
        'stage_index' => array_search($currentStageOrder ?? 0, array_keys($stageOrders), true) + 1,
        'stage_total' => count($stageOrders),
        'stage_name' => $stageName ?? '',
        'stage_match_index' => $stageMatchIndex,
        'stage_match_count' => $stageMatchCount,
    ];
}
?>

<div class="page-card mb-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <h2 class="page-title mb-2"><?= htmlspecialchars($match['team1_name']) ?> против <?= htmlspecialchars($match['team2_name']) ?></h2>
            <div><b>Турнир:</b> <?= htmlspecialchars($match['tournament_name']) ?></div>
            <div><b>Стадия:</b> <?= htmlspecialchars(stage_label($match['stage_name'])) ?></div>
            <div><b>Дата:</b> <?= htmlspecialchars($match['match_date'] ?? '') ?></div>
        </div>
        <div class="text-end">
            <span class="badge <?= $match['is_finished'] ? 'text-bg-success' : 'text-bg-secondary' ?> fs-6">
                <?= $match['is_finished'] ? 'завершен' : 'запланирован' ?>
            </span>
            <div class="mt-3">
                <a class="btn btn-outline-secondary" href="matches.php?tournament_id=<?= $match['tournament_id'] ?>">К списку матчей</a>
                <a class="btn btn-outline-primary" href="tournament.php?id=<?= $match['tournament_id'] ?>&tab=bracket">К сетке</a>
            </div>
        </div>
    </div>
</div>

<?php if ($errors): ?>
    <div class="alert alert-danger"><?= htmlspecialchars(implode(' ', $errors)) ?></div>
<?php endif; ?>

<?php if ($flowProgress): ?>
    <div class="alert alert-info">
        <b>Пошаговое заполнение сетки.</b>
        Матч <?= $flowProgress['current'] ?> из <?= $flowProgress['total'] ?>,
        стадия <?= htmlspecialchars(stage_label($flowProgress['stage_name'])) ?>
        (<?= $flowProgress['stage_match_index'] ?> из <?= $flowProgress['stage_match_count'] ?>).
    </div>
<?php endif; ?>

<?php if ($isAdmin): ?>
<div class="page-card mb-4">
    <h3 class="page-title">Заполнение матча</h3>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="border rounded p-3 h-100">
                <span class="badge <?= $resultReady ? 'text-bg-success' : 'text-bg-warning' ?> mb-2">
                    <?= $resultReady ? 'готово' : 'нужно заполнить' ?>
                </span>
                <div class="fw-bold">1. Результат</div>
                <div class="text-muted">Счет, победитель и статус завершения матча.</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border rounded p-3 h-100">
                <span class="badge <?= $gamesReady ? 'text-bg-success' : 'text-bg-secondary' ?> mb-2">
                    <?= $gamesReady ? 'есть партии' : 'пока пусто' ?>
                </span>
                <div class="fw-bold">2. Партии</div>
                <div class="text-muted">Отдельные игры внутри матча: номер, карта и победитель.</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border rounded p-3 h-100">
                <span class="badge <?= $statsReady ? 'text-bg-success' : 'text-bg-secondary' ?> mb-2">
                    <?= $statsReady ? 'заполнено' : 'по выбранной партии пусто' ?>
                </span>
                <div class="fw-bold">3. Статистика</div>
                <div class="text-muted">Показывается только для выбранной партии.</div>
            </div>
        </div>
    </div>
</div>

<div class="page-card mb-4">
    <h3 class="page-title">Итог по партиям</h3>
    <div class="text-muted mb-3">Итог матча считается автоматически по победителям партий. Когда все партии добавлены и статистика заполнена, сохраните этот блок.</div>
    <form method="post" class="row g-3">
        <input type="hidden" name="action" value="finish_games">
        <input type="hidden" name="match_id" value="<?= $id ?>">
        <?php if ($flow === 'bracket'): ?>
            <input type="hidden" name="flow" value="bracket">
        <?php endif; ?>
        <div class="col-md-2">
            <label class="form-label"><?= htmlspecialchars($match['team1_name']) ?></label>
            <input class="form-control" type="number" min="0" value="<?= $gameWins[(int)$match['team1_id']] ?>" readonly>
        </div>
        <div class="col-md-2">
            <label class="form-label"><?= htmlspecialchars($match['team2_name']) ?></label>
            <input class="form-control" type="number" min="0" value="<?= $gameWins[(int)$match['team2_id']] ?>" readonly>
        </div>
        <div class="col-md-4">
            <label class="form-label">Победитель</label>
            <select name="winner_team_id" class="form-select" disabled>
                <option value="0">Победитель не выбран</option>
                <option value="<?= $match['team1_id'] ?>" <?= ($calculatedWinnerId ?: (int)$match['winner_team_id']) == $match['team1_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($match['team1_name']) ?>
                </option>
                <option value="<?= $match['team2_id'] ?>" <?= ($calculatedWinnerId ?: (int)$match['winner_team_id']) == $match['team2_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($match['team2_name']) ?>
                </option>
            </select>
        </div>
        <div class="col-md-2 form-check d-flex align-items-end pb-2 ps-4">
            <input class="form-check-input me-2" type="checkbox" id="is_finished" <?= $match['is_finished'] ? 'checked' : '' ?> disabled>
            <label class="form-check-label" for="is_finished">Матч завершен</label>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <div class="d-grid gap-2 w-100">
                <button class="btn btn-primary">Это все партии</button>
                <?php if ($flow === 'bracket'): ?>
                    <button class="btn btn-success" name="continue" value="yes">Завершить и продолжить</button>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>
<?php endif; ?>

<div class="page-card mb-4">
    <h3 class="page-title">Партии матча</h3>
    <?php if ($isAdmin): ?>
    <form method="post" class="row g-3 mb-4">
        <input type="hidden" name="action" value="save_game">
        <input type="hidden" name="match_id" value="<?= $id ?>">
        <div class="col-md-2">
            <label class="form-label">Номер партии</label>
            <input class="form-control" type="number" min="1" name="game_number" value="<?= count($games) + 1 ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Игровая карта</label>
            <select name="map_id" class="form-select" required>
                <?php foreach ($maps as $map): ?>
                    <option value="<?= $map['id'] ?>"><?= htmlspecialchars($map['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Победитель партии</label>
            <select name="winner_team_id" class="form-select">
                <option value="0">Победитель не выбран</option>
                <option value="<?= $match['team1_id'] ?>"><?= htmlspecialchars($match['team1_name']) ?></option>
                <option value="<?= $match['team2_id'] ?>"><?= htmlspecialchars($match['team2_name']) ?></option>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-outline-primary w-100">Сохранить</button>
        </div>
    </form>
    <?php endif; ?>

    <?php if (!$games): ?>
        <div class="text-muted">Партии еще не добавлены.</div>
    <?php else: ?>
        <div class="d-flex flex-wrap gap-2 mb-4">
            <?php foreach ($games as $game): ?>
                <a
                    class="btn <?= $selectedGameId === (int)$game['id'] ? 'btn-primary' : 'btn-outline-primary' ?>"
                    href="match.php?id=<?= $id ?>&game_id=<?= $game['id'] ?>&team_id=<?= $selectedTeamId ?>"
                >
                    Партия <?= (int)$game['game_number'] ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($selectedGame): ?>
            <div class="border rounded p-3 mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-muted">Игровая карта</div>
                        <div class="fw-bold"><?= htmlspecialchars($selectedGame['map_name']) ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted">Победитель партии</div>
                        <div class="fw-bold"><?= htmlspecialchars($selectedGame['winner_name'] ?? 'не выбран') ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted">Статистика</div>
                        <div class="fw-bold"><?= $statsReady ? 'заполнена для выбранной команды' : 'пока не заполнена' ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <h4 class="mb-3">Статистика выбранной партии</h4>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">Команда</label>
                <select
                    class="form-select"
                    required
                    onchange="window.location.href='match.php?id=<?= $id ?>&game_id=<?= $selectedGameId ?>&team_id=' + this.value"
                >
                    <?php foreach ($matchTeams as $team): ?>
                        <option value="<?= $team['id'] ?>" <?= $selectedTeamId === (int)$team['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($team['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-8 d-flex align-items-end justify-content-md-end">
                <?php if ($isAdmin): ?>
                <button
                    type="button"
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#teamStatsModal"
                    <?= !$selectedTeamPlayers ? 'disabled' : '' ?>
                >
                    Заполнить статистику команды
                </button>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!$selectedTeamPlayers): ?>
            <div class="text-muted">У выбранной команды нет игроков в составе.</div>
        <?php elseif (!$stats): ?>
            <div class="text-muted">Для выбранной партии и команды статистика еще не заполнена.</div>
        <?php else: ?>
            <table class="table table-hover table-bordered">
                <thead class="table-light">
                <tr><th>Игрок</th><th>Убийства</th><th>Смерти</th></tr>
                </thead>
                <tbody>
                <?php foreach ($stats as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nickname']) ?></td>
                        <td><?= (int)$row['kills'] ?></td>
                        <td><?= (int)$row['deaths'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php if ($isAdmin && $games && $selectedTeamPlayers): ?>
    <div class="modal fade" id="teamStatsModal" tabindex="-1" aria-labelledby="teamStatsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="post">
                    <input type="hidden" name="action" value="save_team_stats">
                    <input type="hidden" name="match_id" value="<?= $id ?>">
                    <input type="hidden" name="match_game_id" value="<?= $selectedGameId ?>">
                    <input type="hidden" name="team_id" value="<?= $selectedTeamId ?>">

                    <div class="modal-header">
                        <h5 class="modal-title" id="teamStatsModalLabel">Статистика команды</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3 text-muted">
                            <?= htmlspecialchars($selectedTeamName) ?>,
                            <?php foreach ($games as $game): ?>
                                <?php if ($selectedGameId === (int)$game['id']): ?>
                                    партия <?= (int)$game['game_number'] ?>: <?= htmlspecialchars($game['map_name']) ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>

                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                            <tr><th>Игрок</th><th>Убийства</th><th>Смерти</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($selectedTeamPlayers as $player): ?>
                                <?php $saved = $statsByUser[(int)$player['id']] ?? null; ?>
                                <tr>
                                    <td><?= htmlspecialchars($player['nickname']) ?></td>
                                    <td>
                                        <input
                                            class="form-control"
                                            type="number"
                                            min="0"
                                            name="kills[<?= (int)$player['id'] ?>]"
                                            value="<?= (int)($saved['kills'] ?? 0) ?>"
                                        >
                                    </td>
                                    <td>
                                        <input
                                            class="form-control"
                                            type="number"
                                            min="0"
                                            name="deaths[<?= (int)$player['id'] ?>]"
                                            value="<?= (int)($saved['deaths'] ?? 0) ?>"
                                        >
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button class="btn btn-primary">Сохранить статистику</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . "/footer.php"; ?>
