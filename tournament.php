<?php
require __DIR__ . "/db.php";
include __DIR__ . "/header.php";

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT t.*, g.name AS gametype_name, g.team_size
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
    SELECT tt.*, team.name, team.country, team.rating
    FROM tournamentteam tt
    JOIN team ON team.id = tt.team_id
    WHERE tt.tournament_id = ?
    ORDER BY COALESCE(tt.seed, 9999), team.name
");
$stmt->execute([$id]);
$teams = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT * FROM tournamentstage
    WHERE tournament_id = ?
    ORDER BY stage_order
");
$stmt->execute([$id]);
$stages = $stmt->fetchAll();

$allTeams = $pdo->query("SELECT * FROM team ORDER BY name")->fetchAll();
$teamsCount = count($teams);
$bracketReady = $teamsCount >= 2 && (($teamsCount & ($teamsCount - 1)) === 0);
$prizeErrors = [];
$prizeMessage = '';
$rosterErrors = [];
$rosterMessage = '';
$selectedTeamId = (int)($_GET['team_id'] ?? $_POST['team_id'] ?? 0);
$teamIds = array_map(fn($team) => (int)$team['team_id'], $teams);
if ($selectedTeamId && !in_array($selectedTeamId, $teamIds, true)) {
    $selectedTeamId = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_prizes') {
    require_admin();
    $postedPrizes = $_POST['prizes'] ?? [];
    $postedTotal = 0.0;
    foreach ($postedPrizes as $place => $amount) {
        $place = (int)$place;
        $amount = trim((string)$amount);
        if ($place < 1 || $amount === '') {
            continue;
        }
        if (is_numeric($amount) && (float)$amount >= 0) {
            $postedTotal += (float)$amount;
        }
    }
    $prizePool = (float)($tournament['prize_pool'] ?? 0);
    if ($prizePool > 0 && $postedTotal - $prizePool > 0.01) {
        $prizeErrors[] = "Сумма по местам не должна превышать призовой фонд.";
    }

    if (!$prizeErrors) {
    try {
        $pdo->beginTransaction();
        foreach ($postedPrizes as $place => $amount) {
            $place = (int)$place;
            $amount = trim((string)$amount);

            if ($place < 1) {
                continue;
            }

            if ($amount === '') {
                $stmt = $pdo->prepare("DELETE FROM prizedistribution WHERE tournament_id = ? AND place = ?");
                $stmt->execute([$id, $place]);
                continue;
            }

            if (!is_numeric($amount) || (float)$amount < 0) {
                $prizeErrors[] = "Проверьте сумму для места $place.";
                continue;
            }

            $stmt = $pdo->prepare("
                INSERT INTO prizedistribution (tournament_id, place, prize_amount)
                VALUES (?, ?, ?)
                ON CONFLICT (tournament_id, place)
                DO UPDATE SET prize_amount = EXCLUDED.prize_amount
            ");
            $stmt->execute([$id, $place, $amount]);
        }

        if ($prizeErrors) {
            $pdo->rollBack();
        } else {
            $pdo->commit();
            $prizeMessage = "Распределение призовых сохранено.";
        }
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $prizeErrors[] = "Не удалось сохранить распределение призовых.";
    }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_roster_player') {
    require_admin();
    $teamId = (int)($_POST['team_id'] ?? 0);
    $userId = (int)($_POST['user_id'] ?? 0);
    $roleId = (int)($_POST['role_id'] ?? 0);
    $selectedTeamId = $teamId;

    if (!in_array($teamId, $teamIds, true)) {
        $rosterErrors[] = "Выберите команду этого турнира.";
    }

    $stmt = $pdo->prepare("SELECT name FROM playerrole WHERE id = ?");
    $stmt->execute([$roleId]);
    $roleName = $stmt->fetchColumn();
    if (!$userId || !$roleId || !$roleName) {
        $rosterErrors[] = "Выберите игрока и роль.";
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tournamentroster WHERE tournament_id = ? AND team_id = ?");
    $stmt->execute([$id, $teamId]);
    $currentRosterCount = (int)$stmt->fetchColumn();
    if ((int)$tournament['team_size'] > 0 && $currentRosterCount >= (int)$tournament['team_size']) {
        $rosterErrors[] = "Состав команды уже заполнен.";
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tournamentroster WHERE tournament_id = ? AND team_id = ? AND user_id = ?");
    $stmt->execute([$id, $teamId, $userId]);
    if ((int)$stmt->fetchColumn() > 0) {
        $rosterErrors[] = "Этот игрок уже есть в составе выбранной команды.";
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM teamplayer WHERE team_id = ? AND user_id = ?");
    $stmt->execute([$teamId, $userId]);
    if ((int)$stmt->fetchColumn() === 0) {
        $rosterErrors[] = "Игрока нет в общем составе выбранной команды.";
    }

    $stmt = $pdo->prepare("
        SELECT t.name
        FROM tournamentteam tt
        JOIN tournamentroster tp ON tp.tournament_id = tt.tournament_id AND tp.team_id = tt.team_id
        JOIN team t ON t.id = tt.team_id
        WHERE tt.tournament_id = ?
          AND tp.user_id = ?
          AND tt.team_id <> ?
        LIMIT 1
    ");
    $stmt->execute([$id, $userId, $teamId]);
    $conflictTeam = $stmt->fetchColumn();
    if ($conflictTeam) {
        $rosterErrors[] = "Игрок уже заявлен в этом турнире за команду $conflictTeam.";
    }

    if (!$rosterErrors) {
        $stmt = $pdo->prepare("
            INSERT INTO tournamentroster (tournament_id, team_id, user_id, role_id, joined_at)
            VALUES (?, ?, ?, ?, NOW())
            ON CONFLICT (tournament_id, user_id) DO NOTHING
        ");
        $stmt->execute([$id, $teamId, $userId, $roleId]);
        header("Location: tournament.php?id=$id&tab=teams&team_id=$teamId");
        exit;
    }
}

$stmt = $pdo->prepare("
    SELECT place, prize_amount
    FROM prizedistribution
    WHERE tournament_id = ?
    ORDER BY place
");
$stmt->execute([$id]);
$prizes = [];
foreach ($stmt->fetchAll() as $prize) {
    $prizes[(int)$prize['place']] = $prize['prize_amount'];
}
$maxPrizePlace = max(4, min(max(1, $teamsCount), 8), $prizes ? max(array_keys($prizes)) : 0);
$totalPrizeDistribution = array_sum(array_map('floatval', $prizes));

$completion = tournament_completion($pdo, $id, $teams);
$hasBracket = (bool)$stages || $completion['total_matches'] > 0;
$activeTab = $_GET['tab'] ?? 'overview';
if (!in_array($activeTab, ['overview', 'teams', 'bracket', 'results'], true)) {
    $activeTab = 'overview';
}

$stmt = $pdo->prepare("
    SELECT
        m.*,
        s.name AS stage_name,
        s.stage_order,
        t1.name AS team1_name,
        t2.name AS team2_name,
        w.name AS winner_name
    FROM match m
    JOIN tournamentstage s ON s.id = m.stage_id
    JOIN team t1 ON t1.id = m.team1_id
    JOIN team t2 ON t2.id = m.team2_id
    LEFT JOIN team w ON w.id = m.winner_team_id
    WHERE m.tournament_id = ?
    ORDER BY s.stage_order, m.match_date NULLS LAST, m.id
");
$stmt->execute([$id]);
$bracketMatches = [];
foreach ($stmt->fetchAll() as $match) {
    $bracketMatches[(int)$match['stage_order']][] = $match;
}

$stmt = $pdo->prepare("
    SELECT tt.team_id, COUNT(DISTINCT tp.user_id) AS players_count
    FROM tournamentteam tt
    LEFT JOIN tournamentroster tp ON tp.tournament_id = tt.tournament_id AND tp.team_id = tt.team_id
    WHERE tt.tournament_id = ?
    GROUP BY tt.team_id
");
$stmt->execute([$id]);
$rosterCounts = [];
foreach ($stmt->fetchAll() as $row) {
    $rosterCounts[(int)$row['team_id']] = (int)$row['players_count'];
}
$requiredPlayers = (int)($tournament['team_size'] ?? 0);
$underfilledTeams = array_filter($teams, function ($team) use ($rosterCounts, $requiredPlayers) {
    return $requiredPlayers > 0 && ($rosterCounts[(int)$team['team_id']] ?? 0) < $requiredPlayers;
});

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

$selectedTeam = null;
foreach ($teams as $team) {
    if ((int)$team['team_id'] === $selectedTeamId) {
        $selectedTeam = $team;
        break;
    }
}
$selectedRoster = [];
if ($selectedTeamId) {
    $stmt = $pdo->prepare("
        SELECT tp.id AS roster_id, u.id, u.nickname, u.country, u.rating, pr.name AS role_name
        FROM tournamentroster tp
        JOIN users u ON u.id = tp.user_id
        LEFT JOIN playerrole pr ON pr.id = tp.role_id
        WHERE tp.tournament_id = ? AND tp.team_id = ?
        ORDER BY u.nickname
    ");
    $stmt->execute([$id, $selectedTeamId]);
    $selectedRoster = $stmt->fetchAll();
}
$availablePlayers = [];
if ($selectedTeamId) {
    $stmt = $pdo->prepare("
        SELECT u.id, u.nickname, u.country, u.rating, COALESCE(pr.name, tp.role) AS role_name, tp.role_id
        FROM teamplayer tp
        JOIN users u ON u.id = tp.user_id
        LEFT JOIN playerrole pr ON pr.id = tp.role_id
        WHERE tp.team_id = ?
          AND NOT EXISTS (
              SELECT 1
              FROM tournamentroster tr
              WHERE tr.tournament_id = ?
                AND tr.user_id = u.id
          )
        ORDER BY u.nickname
    ");
    $stmt->execute([$selectedTeamId, $id]);
    $availablePlayers = $stmt->fetchAll();
}
$playerRoles = $pdo->query("SELECT id, name FROM playerrole ORDER BY name")->fetchAll();

$teamPaths = [];
$teamRecords = [];
foreach ($teams as $team) {
    $teamId = (int)$team['team_id'];
    $teamPaths[$teamId] = [];
    $teamRecords[$teamId] = ['wins' => 0, 'losses' => 0];
}
foreach ($bracketMatches as $stageMatches) {
    foreach ($stageMatches as $match) {
        if (!$match['is_finished'] || !$match['winner_team_id']) {
            continue;
        }

        $team1Id = (int)$match['team1_id'];
        $team2Id = (int)$match['team2_id'];
        $winnerId = (int)$match['winner_team_id'];
        $stageName = stage_label($match['stage_name']);
        $scoresByTeam = [
            $team1Id => (int)$match['team1_score'],
            $team2Id => (int)$match['team2_score'],
        ];

        foreach ([[$team1Id, $team2Id, $match['team2_name']], [$team2Id, $team1Id, $match['team1_name']]] as [$teamId, $opponentId, $opponentName]) {
            if (!isset($teamPaths[$teamId])) {
                continue;
            }

            $won = $teamId === $winnerId;
            $teamScore = $scoresByTeam[$teamId] ?? 0;
            $opponentScore = $scoresByTeam[$opponentId] ?? 0;
            $resultText = $won ? 'Победа над ' : 'Поражение от ';
            $teamRecords[$teamId][$won ? 'wins' : 'losses']++;
            $teamPaths[$teamId][] = [
                'stage' => $stageName,
                'label' => $resultText . $opponentName . ' (' . $teamScore . ':' . $opponentScore . ')',
                'won' => $won,
            ];
        }
    }
}

$stmt = $pdo->prepare("
    SELECT
        u.nickname,
        SUM(ps.kills) AS kills,
        SUM(ps.deaths) AS deaths,
        COUNT(DISTINCT mg.id) AS games_count
    FROM playerstats ps
    JOIN users u ON u.id = ps.user_id
    JOIN matchgame mg ON mg.id = ps.match_game_id
    JOIN match m ON m.id = mg.match_id
    WHERE m.tournament_id = ?
    GROUP BY u.id, u.nickname
    ORDER BY SUM(ps.kills) DESC, SUM(ps.kills)::numeric / GREATEST(SUM(ps.deaths), 1) DESC, u.nickname
    LIMIT 10
");
$stmt->execute([$id]);
$topPlayers = $stmt->fetchAll();
?>

<div class="page-card mb-4">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h2 class="page-title mb-2"><?= htmlspecialchars($tournament['name']) ?></h2>
            <div><b>Игра:</b> <?= htmlspecialchars($tournament['gametype_name']) ?></div>
            <div><b>Даты:</b> <?= $tournament['start_date'] ?> - <?= $tournament['end_date'] ?></div>
            <div><b>Статус:</b> <span class="badge <?= status_badge_class($tournament['status']) ?> status-badge"><?= htmlspecialchars(status_label($tournament['status'])) ?></span></div>
            <div><b>Призовой фонд:</b> <?= number_format((float)$tournament['prize_pool'], 2, '.', ' ') ?></div>
        </div>
        <div class="text-end">
            <?php if ($isAdmin && (!$bracketReady || !$hasBracket)): ?>
            <span class="badge <?= $bracketReady ? 'text-bg-success' : 'text-bg-warning' ?> fs-6">
                <?= $bracketReady ? 'Готово к сетке' : 'Нужно 2, 4, 8, 16... команд' ?>
            </span>
            <?php endif; ?>
            <div class="mt-3">
                <?php if ($isAdmin): ?>
                <a
                    href="generate_bracket.php?id=<?= $id ?>"
                    class="btn <?= $hasBracket ? 'btn-outline-danger' : 'btn-danger' ?>"
                >
                    <?= $hasBracket ? 'Пересоздать сетку' : 'Создать сетку' ?>
                </a>
                <?php endif; ?>
                <a href="matches.php?tournament_id=<?= $id ?>" class="btn btn-primary">Матчи</a>
            </div>
        </div>
    </div>
</div>

<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'overview' ? 'active' : '' ?>" href="tournament.php?id=<?= $id ?>&tab=overview">Обзор</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'teams' ? 'active' : '' ?>" href="tournament.php?id=<?= $id ?>&tab=teams">Команды</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'bracket' ? 'active' : '' ?>" href="tournament.php?id=<?= $id ?>&tab=bracket">Сетка</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'results' ? 'active' : '' ?>" href="tournament.php?id=<?= $id ?>&tab=results">Итоги</a>
    </li>
</ul>

<?php if ($activeTab === 'overview'): ?>
<div class="page-card mb-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <h3 class="page-title mb-2">Сводка турнира</h3>
            <div class="text-muted">Краткое состояние матчей и итогов турнира.</div>
        </div>
        <?php if ($completion['ready'] && $tournament['status'] === 'finished'): ?>
            <span class="badge text-bg-success fs-6">Завершен</span>
        <?php elseif ($completion['ready']): ?>
            <span class="badge text-bg-success fs-6">Данные заполнены</span>
        <?php else: ?>
            <span class="badge text-bg-warning fs-6">Есть незаполненные данные</span>
        <?php endif; ?>
    </div>

    <div class="row g-3 my-2">
        <div class="col-md-4">
            <div class="border rounded p-3 h-100">
                <div class="text-muted">Матчи</div>
                <div class="fw-bold"><?= $completion['finished_matches'] ?> из <?= $completion['total_matches'] ?> завершены</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border rounded p-3 h-100">
                <div class="text-muted">Победители матчей</div>
                <div class="fw-bold"><?= $completion['winner_matches'] ?> из <?= $completion['total_matches'] ?> выбраны</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border rounded p-3 h-100">
                <div class="text-muted">Победитель турнира</div>
                <div class="fw-bold"><?= htmlspecialchars($completion['final_match']['winner_name'] ?? 'не определен') ?></div>
            </div>
        </div>
    </div>

    <?php if ($completion['issues']): ?>
        <div class="alert alert-warning mb-0">
            <?php foreach ($completion['issues'] as $issue): ?>
                <div><?= htmlspecialchars($issue) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($tournament['status'] === 'finished' && !$completion['ready']): ?>
        <div class="alert alert-danger mt-3 mb-0">
            Турнир помечен как завершенный, но данные еще неполные.
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($activeTab === 'teams'): ?>
<div class="row g-4">
    <?php if ($isAdmin): ?>
    <div class="col-lg-5">
        <div class="page-card">
            <h3 class="page-title">Добавить команду</h3>
            <form method="post" action="add_tournament_team.php" class="row g-3">
                <input type="hidden" name="tournament_id" value="<?= $id ?>">
                <div class="col-12">
                    <label class="form-label">Команда</label>
                    <select name="team_id" class="form-select" required>
                        <?php foreach ($allTeams as $team): ?>
                            <option value="<?= $team['id'] ?>"><?= htmlspecialchars($team['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Посев</label>
                    <input type="number" name="seed" class="form-control" min="1">
                </div>
                <div class="col-12">
                    <button class="btn btn-primary">Сохранить участие</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div class="<?= $isAdmin ? 'col-lg-7' : 'col-12' ?>">
        <div class="page-card">
            <h3 class="page-title">Команды турнира</h3>
            <?php if ($isAdmin && $underfilledTeams): ?>
                <div class="alert alert-warning">
                    Не все команды укомплектованы для дисциплины <?= htmlspecialchars($tournament['gametype_name']) ?>:
                    требуется <?= $requiredPlayers ?> игроков.
                </div>
            <?php endif; ?>
            <?php if ($isAdmin && $rosterConflicts): ?>
                <div class="alert alert-danger">
                    <div class="fw-bold mb-1">Есть конфликт составов: один игрок заявлен за несколько команд турнира.</div>
                    <?php foreach ($rosterConflicts as $conflict): ?>
                        <div>
                            <?= htmlspecialchars($conflict['nickname']) ?>:
                            <?= htmlspecialchars($conflict['team_a']) ?> и <?= htmlspecialchars($conflict['team_b']) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                <tr><th>Посев</th><th>Команда</th><th>Страна</th><th>Рейтинг</th><th>Состав</th></tr>
                </thead>
                <tbody>
                <?php foreach ($teams as $team): ?>
                    <?php
                    $teamPlayersCount = $rosterCounts[(int)$team['team_id']] ?? 0;
                    $isRosterReady = $requiredPlayers <= 0 || $teamPlayersCount >= $requiredPlayers;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($team['seed'] ?? '') ?></td>
                        <td>
                            <a href="tournament.php?id=<?= $id ?>&tab=teams&team_id=<?= (int)$team['team_id'] ?>" class="fw-semibold">
                                <?= htmlspecialchars($team['name']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($team['country']) ?></td>
                        <td><?= $team['rating'] ?></td>
                        <td>
                            <span class="badge <?= $isRosterReady ? 'text-bg-success' : 'text-bg-warning' ?>">
                                <?= $teamPlayersCount ?> / <?= $requiredPlayers ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($selectedTeam): ?>
<div class="modal fade" id="teamRosterModal" tabindex="-1" aria-labelledby="teamRosterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="teamRosterModalLabel">Состав: <?= htmlspecialchars($selectedTeam['name']) ?></h5>
                    <div class="text-muted small">
                        <?= count($selectedRoster) ?> из <?= $requiredPlayers ?> игроков для дисциплины <?= htmlspecialchars($tournament['gametype_name']) ?>.
                    </div>
                </div>
                <a class="btn-close" href="tournament.php?id=<?= $id ?>&tab=teams" aria-label="Закрыть"></a>
            </div>
            <div class="modal-body">
                <?php if ($rosterErrors): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars(implode(' ', $rosterErrors)) ?></div>
                <?php endif; ?>
                <?php if ($rosterMessage): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($rosterMessage) ?></div>
                <?php endif; ?>

                <div class="row g-4">
                    <div class="<?= ($isAdmin && count($selectedRoster) < $requiredPlayers) ? 'col-lg-7' : 'col-12' ?>">
                        <?php if (!$selectedRoster): ?>
                            <div class="text-muted">В составе пока нет игроков.</div>
                        <?php else: ?>
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="table-light">
                                <tr><th>Игрок</th><th>Роль</th><th>Страна</th><th>Рейтинг</th><?php if ($isAdmin): ?><th>Действия</th><?php endif; ?></tr>
                                </thead>
                                <tbody>
                                <?php foreach ($selectedRoster as $member): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($member['nickname']) ?></td>
                                        <td><?= htmlspecialchars($member['role_name'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($member['country']) ?></td>
                                        <td><?= (int)$member['rating'] ?></td>
                                        <?php if ($isAdmin): ?>
                                        <td>
                                            <form method="post" action="tournament_roster_delete.php" onsubmit="return confirm('Удалить игрока из заявки турнира?');">
                                                <input type="hidden" name="id" value="<?= (int)$member['roster_id'] ?>">
                                                <input type="hidden" name="tournament_id" value="<?= $id ?>">
                                                <input type="hidden" name="team_id" value="<?= (int)$selectedTeam['team_id'] ?>">
                                                <button class="btn btn-sm btn-outline-danger">Удалить</button>
                                            </form>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                    <?php if ($isAdmin && count($selectedRoster) < $requiredPlayers): ?>
                    <div class="col-lg-5">
                        <?php if (!$availablePlayers): ?>
                            <div class="alert alert-warning mb-0">
                                В общем составе команды нет доступных игроков для добавления в заявку турнира.
                                Добавьте игроков в разделе «Составы», затем вернитесь сюда.
                            </div>
                        <?php else: ?>
                            <form method="post" class="border rounded p-3 bg-light">
                                <input type="hidden" name="action" value="add_roster_player">
                                <input type="hidden" name="team_id" value="<?= (int)$selectedTeam['team_id'] ?>">
                                <div class="mb-3">
                                    <label class="form-label">Игрок</label>
                                    <select name="user_id" class="form-select" required>
                                        <?php foreach ($availablePlayers as $player): ?>
                                            <option value="<?= (int)$player['id'] ?>">
                                                <?= htmlspecialchars($player['nickname']) ?>, <?= htmlspecialchars($player['country']) ?>, рейтинг <?= (int)$player['rating'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Роль</label>
                                    <select name="role_id" class="form-select" required>
                                        <?php foreach ($playerRoles as $role): ?>
                                            <option value="<?= (int)$role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button class="btn btn-primary w-100">Добавить в состав</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var rosterModal = new bootstrap.Modal(document.getElementById('teamRosterModal'));
    rosterModal.show();
});
</script>
<?php endif; ?>
<?php endif; ?>

<?php if ($activeTab === 'results'): ?>
<?php if ($isAdmin): ?>
<div class="page-card mt-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
        <div>
            <h3 class="page-title mb-1">Распределение призовых</h3>
            <div class="text-muted">Укажите суммы для мест. Пустое поле удалит сумму для этого места.</div>
        </div>
        <div class="text-end">
            <div class="text-muted small">Призовой фонд</div>
            <div class="fw-bold"><?= number_format((float)$tournament['prize_pool'], 2, '.', ' ') ?></div>
        </div>
    </div>

    <?php if ($prizeMessage): ?>
        <div class="alert alert-success"><?= htmlspecialchars($prizeMessage) ?></div>
    <?php endif; ?>
    <?php if ($prizeErrors): ?>
        <div class="alert alert-danger"><?= htmlspecialchars(implode(' ', $prizeErrors)) ?></div>
    <?php endif; ?>

    <?php if ($isAdmin): ?>
    <form method="post">
        <input type="hidden" name="action" value="save_prizes">
        <div class="row g-3">
            <?php for ($placeNumber = 1; $placeNumber <= $maxPrizePlace; $placeNumber++): ?>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label"><?= $placeNumber ?> место</label>
                    <input
                        type="number"
                        class="form-control"
                        name="prizes[<?= $placeNumber ?>]"
                        step="0.01"
                        min="0"
                        value="<?= htmlspecialchars($prizes[$placeNumber] ?? '') ?>"
                    >
                </div>
            <?php endfor; ?>
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
            <div class="text-muted">
                Распределено: <b><?= number_format($totalPrizeDistribution, 2, '.', ' ') ?></b>
                из <?= number_format((float)$tournament['prize_pool'], 2, '.', ' ') ?>
            </div>
            <button class="btn btn-primary">Сохранить призовые</button>
        </div>

        <?php if ((float)$tournament['prize_pool'] > 0 && abs($totalPrizeDistribution - (float)$tournament['prize_pool']) > 0.01): ?>
            <div class="alert alert-warning mt-3 mb-0">
                Сумма распределения не совпадает с общим призовым фондом.
            </div>
        <?php endif; ?>
    </form>
    <?php else: ?>
        <div class="text-muted">Распределение призовых доступно для просмотра. Изменение сумм выполняет администратор.</div>
    <?php endif; ?>
</div>

<?php endif; ?>
<div class="page-card mt-4">
    <h3 class="page-title">Итоги турнира</h3>
    <div class="text-muted mb-3">
        Места рассчитываются автоматически по сетке, а призовые берутся из распределения по местам в базе.
    </div>

    <?php if (!$completion['derived_places']): ?>
        <div class="text-muted">Итоги появятся после завершения матчей сетки.</div>
    <?php else: ?>
        <div class="row g-3 mb-4">
            <?php foreach ($completion['derived_places'] as $place): ?>
                <?php
                $placeTeamId = (int)$place['team_id'];
                $record = $teamRecords[$placeTeamId] ?? ['wins' => 0, 'losses' => 0];
                $path = $teamPaths[$placeTeamId] ?? [];
                ?>
                <div class="col-lg-6">
                    <div class="border rounded p-3 h-100">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <div class="text-muted small">Место <?= htmlspecialchars($place['place_label']) ?></div>
                                <div class="fw-bold fs-5"><?= htmlspecialchars($place['team_name']) ?></div>
                            </div>
                            <div class="text-end">
                                <div class="text-muted small">Призовые</div>
                                <div class="fw-bold"><?= htmlspecialchars($place['prize_label']) ?></div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <span class="badge text-bg-light text-dark border">Итог: <?= $record['wins'] ?>-<?= $record['losses'] ?></span>
                            <span class="badge text-bg-secondary"><?= count($path) ?> матч(ей)</span>
                        </div>
                        <?php if ($path): ?>
                            <div class="mt-3 border-top pt-3">
                                <div class="text-muted small mb-2">Матчи команды</div>
                                <?php foreach ($path as $pathItem): ?>
                                    <div class="d-flex justify-content-between align-items-start gap-2 small mb-2">
                                        <div>
                                            <div class="text-muted"><?= htmlspecialchars($pathItem['stage']) ?></div>
                                            <div><?= htmlspecialchars($pathItem['label']) ?></div>
                                        </div>
                                        <span class="badge <?= $pathItem['won'] ? 'text-bg-success' : 'text-bg-danger' ?>">
                                            <?= $pathItem['won'] ? 'Победа' : 'Поражение' ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-muted small mt-3">Путь по сетке появится после заполнения матчей.</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <table class="table table-bordered table-hover">
            <thead class="table-light">
            <tr><th>Место</th><th>Команда</th><th>Призовые</th><th>Основание</th></tr>
            </thead>
            <tbody>
            <?php foreach ($completion['derived_places'] as $place): ?>
                <tr>
                    <td><?= htmlspecialchars($place['place_label']) ?></td>
                    <td><?= htmlspecialchars($place['team_name']) ?></td>
                    <td><?= htmlspecialchars($place['prize_label']) ?></td>
                    <td><?= htmlspecialchars($place['source']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="page-card mt-4">
    <h3 class="page-title">Топ игроков турнира</h3>
    <?php if (!$topPlayers): ?>
        <div class="text-muted">Статистика появится после заполнения данных по партиям.</div>
    <?php else: ?>
        <table class="table table-bordered table-hover">
            <thead class="table-light">
            <tr><th>#</th><th>Игрок</th><th>Kills</th><th>Deaths</th><th>K/D</th><th>Партий</th></tr>
            </thead>
            <tbody>
            <?php foreach ($topPlayers as $index => $player): ?>
                <?php
                $kills = (int)$player['kills'];
                $deaths = (int)$player['deaths'];
                $kd = $deaths > 0 ? $kills / $deaths : $kills;
                ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($player['nickname']) ?></td>
                    <td><?= $kills ?></td>
                    <td><?= $deaths ?></td>
                    <td><?= number_format($kd, 2, '.', ' ') ?></td>
                    <td><?= (int)$player['games_count'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($activeTab === 'bracket'): ?>
<div class="page-card mt-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h3 class="page-title mb-0">Сетка</h3>
        <a href="matches.php?tournament_id=<?= $id ?>" class="btn btn-sm btn-outline-primary">Открыть матчи</a>
    </div>

    <?php if (!$bracketMatches): ?>
        <div class="text-muted">Сетка пока не создана.</div>
    <?php else: ?>
        <?php
        $maxStageMatches = max(array_map('count', $bracketMatches));
        $cardHeight = 132;
        $stageHeight = max(420, $maxStageMatches * 188);
        ?>
        <style>
            .bracket-scroll {
                overflow-x: auto;
                padding: 8px 2px 14px;
            }
            .bracket-board {
                display: grid;
                grid-auto-flow: column;
                grid-auto-columns: 304px;
                column-gap: 86px;
                min-width: <?= max(900, count($stages) * 390) ?>px;
                align-items: stretch;
                padding: 6px 18px 18px;
            }
            .bracket-stage {
                display: flex;
                flex-direction: column;
                position: relative;
                padding: 0;
            }
            .bracket-stage::before {
                content: "";
                position: absolute;
                inset: 38px -22px 0 -22px;
                border-left: 1px solid rgba(203, 213, 225, .72);
                border-right: 1px solid rgba(203, 213, 225, .36);
                background:
                    linear-gradient(90deg, rgba(248, 250, 252, .82), rgba(248, 250, 252, .34) 58%, rgba(248, 250, 252, .08)),
                    linear-gradient(180deg, rgba(241, 245, 249, .64), rgba(241, 245, 249, 0) 34%);
            }
            .bracket-stage-grid {
                position: relative;
                height: <?= $stageHeight ?>px;
            }
            .bracket-stage-title {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 100%;
                max-width: 100%;
                min-height: 30px;
                font-size: .76rem;
                font-weight: 800;
                letter-spacing: .06em;
                text-transform: uppercase;
                margin: 0 0 16px;
                color: #334155;
                position: relative;
                z-index: 2;
            }
            .bracket-stage-title::before,
            .bracket-stage-title::after {
                content: "";
                height: 1px;
                flex: 1;
                background: #dbe3ee;
            }
            .bracket-stage-title::before {
                margin-right: 12px;
            }
            .bracket-stage-title::after {
                margin-left: 12px;
            }
            .bracket-slot {
                position: absolute;
                left: 0;
                right: 0;
                z-index: 3;
            }
            .bracket-slot.has-next::after {
                content: "";
                position: absolute;
                top: <?= (int)($cardHeight / 2) ?>px;
                right: -58px;
                width: 58px;
                border-top: 1px solid #c4cedb;
            }
            .bracket-connector {
                position: absolute;
                right: -58px;
                width: 58px;
                border-right: 1px solid #c4cedb;
                pointer-events: none;
                z-index: 2;
            }
            .bracket-connector::before,
            .bracket-connector::after {
                content: "";
                position: absolute;
                right: 0;
                width: 58px;
                border-top: 1px solid #c4cedb;
            }
            .bracket-connector::before { top: 0; }
            .bracket-connector::after { bottom: 0; }
            .bracket-connector-mid {
                position: absolute;
                right: -116px;
                width: 58px;
                border-top: 1px solid #c4cedb;
                pointer-events: none;
                z-index: 2;
            }
            .bracket-match {
                display: block;
                border: 1px solid #d7e0ea;
                border-radius: 8px;
                background: #fff;
                padding: 9px;
                min-height: <?= $cardHeight ?>px;
                box-shadow: 0 8px 22px rgba(15, 23, 42, .07);
                transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
                overflow: hidden;
                position: relative;
            }
            .bracket-match.is-finished {
                border-left: 4px solid #22c55e;
            }
            .bracket-match:hover {
                border-color: #2563eb;
                box-shadow: 0 12px 24px rgba(37, 99, 235, .14);
                transform: translateY(-1px);
            }
            .bracket-team {
                display: grid;
                grid-template-columns: 1fr auto;
                gap: 10px;
                align-items: center;
                padding: 8px 10px;
                border: 1px solid transparent;
                border-radius: 7px;
                line-height: 1.25;
            }
            .bracket-team + .bracket-team {
                margin-top: 4px;
            }
            .bracket-winner {
                font-weight: 700;
                color: #14532d;
                background: #ecfdf3;
                border-color: #bbf7d0;
            }
            .bracket-score {
                font-weight: 700;
                min-width: 22px;
                text-align: right;
            }
            .bracket-meta {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 8px;
                color: #64748b;
                font-size: .78rem;
                margin-bottom: 7px;
                padding: 0 2px;
            }
            .bracket-state {
                border-radius: 999px;
                padding: 2px 7px;
                background: #f1f5f9;
                color: #475569;
                font-size: .72rem;
                font-weight: 700;
            }
            .bracket-state.done {
                background: #dcfce7;
                color: #14532d;
            }
            .bracket-empty {
                position: absolute;
                inset: 0 auto auto 0;
                width: 100%;
            }
        </style>

        <div class="bracket-scroll">
            <div class="bracket-board">
                <?php foreach ($stages as $stage): ?>
                    <?php
                    $stageOrder = (int)$stage['stage_order'];
                    $stageMatches = $bracketMatches[$stageOrder] ?? [];
                    ?>
                    <div class="bracket-stage">
                        <div class="bracket-stage-title"><?= htmlspecialchars(stage_label($stage['name'])) ?></div>
                        <div class="bracket-stage-grid">

                        <?php if (!$stageMatches): ?>
                            <div class="bracket-empty text-muted border rounded p-3">Матчи этой стадии еще не созданы.</div>
                        <?php endif; ?>

                        <?php foreach ($stageMatches as $matchIndex => $match): ?>
                            <?php
                            $stageMatchCount = max(1, count($stageMatches));
                            $slotCenter = (($matchIndex + 0.5) * $stageHeight) / $stageMatchCount;
                            $slotTop = max(0, (int)round($slotCenter - ($cardHeight / 2)));
                            $hasNextStage = $stageOrder < count($stages);
                            $slotClass = $hasNextStage ? 'has-next' : '';
                            ?>
                            <div
                                class="bracket-slot <?= $slotClass ?>"
                                style="top: <?= $slotTop ?>px;"
                            >
                                <a class="bracket-match <?= $match['is_finished'] ? 'is-finished' : '' ?> text-decoration-none text-body" href="match.php?id=<?= $match['id'] ?>">
                                    <div class="bracket-meta">
                                        <span>Матч #<?= (int)$match['id'] ?></span>
                                        <span class="bracket-state <?= $match['is_finished'] ? 'done' : '' ?>">
                                            <?= $match['is_finished'] ? 'готов' : 'план' ?>
                                        </span>
                                    </div>
                                    <div class="bracket-team <?= (int)$match['winner_team_id'] === (int)$match['team1_id'] ? 'bracket-winner' : '' ?>">
                                        <span><?= htmlspecialchars($match['team1_name']) ?></span>
                                        <span class="bracket-score"><?= (int)$match['team1_score'] ?></span>
                                    </div>
                                    <div class="bracket-team <?= (int)$match['winner_team_id'] === (int)$match['team2_id'] ? 'bracket-winner' : '' ?>">
                                        <span><?= htmlspecialchars($match['team2_name']) ?></span>
                                        <span class="bracket-score"><?= (int)$match['team2_score'] ?></span>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                        <?php if ($stageOrder < count($stages)): ?>
                            <?php for ($pairIndex = 0; $pairIndex < count($stageMatches); $pairIndex += 2): ?>
                                <?php
                                if (!isset($stageMatches[$pairIndex + 1])) {
                                    continue;
                                }
                                $stageMatchCount = max(1, count($stageMatches));
                                $center1 = (($pairIndex + 0.5) * $stageHeight) / $stageMatchCount;
                                $center2 = (($pairIndex + 1.5) * $stageHeight) / $stageMatchCount;
                                $top = (int)round(min($center1, $center2));
                                $height = (int)round(abs($center2 - $center1));
                                $mid = (int)round(($center1 + $center2) / 2);
                                ?>
                                <div class="bracket-connector" style="top: <?= $top ?>px; height: <?= $height ?>px;"></div>
                                <div class="bracket-connector-mid" style="top: <?= $mid ?>px;"></div>
                            <?php endfor; ?>
                        <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="page-card mt-4">
    <h3 class="page-title">Стадии</h3>
    <?php if (!$stages): ?>
        <div class="text-muted">Стадии пока не сформированы.</div>
    <?php else: ?>
        <table class="table table-bordered">
            <thead class="table-light"><tr><th>Порядок</th><th>Название</th></tr></thead>
            <tbody>
            <?php foreach ($stages as $stage): ?>
                <tr>
                    <td><?= $stage['stage_order'] ?></td>
                    <td><?= htmlspecialchars(stage_label($stage['name'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . "/footer.php"; ?>
