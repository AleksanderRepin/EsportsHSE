<?php
require __DIR__ . "/db.php";
include __DIR__ . "/header.php";

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin();
    $nickname = trim($_POST['nickname'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $rating = (int)($_POST['rating'] ?? 0);
    $role = $_POST['role'] ?? 'player';
    $password = $_POST['password'] !== '' ? $_POST['password'] : 'password';

    if ($nickname === '') $errors[] = "Введите никнейм.";
    if (!is_valid_country($country)) $errors[] = "Выберите страну из списка.";
    if ($rating < 0) $errors[] = "Рейтинг не может быть отрицательным.";
    if (!in_array($role, ['admin', 'player'], true)) $errors[] = "Некорректная роль пользователя.";

    if (!$errors) {
        $stmt = $pdo->prepare("
            INSERT INTO users (nickname, country, rating, role, password_hash)
            VALUES (:nickname, :country, :rating, :role, :password_hash)
        ");
        $stmt->execute([
            ':nickname' => $nickname,
            ':country' => $country,
            ':rating' => $rating,
            ':role' => $role,
            ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        header('Location: players.php');
        exit;
    }
}

$countries = country_options();
$editUserId = (int)($_GET['edit'] ?? 0);
$selectedUserId = (int)($_GET['user_id'] ?? 0);
$editUser = null;
if ($editUserId) {
    $stmt = $pdo->prepare("SELECT id, nickname, country, rating, role FROM users WHERE id = ?");
    $stmt->execute([$editUserId]);
    $editUser = $stmt->fetch();
}

$selectedUser = null;
$selectedUserTournaments = [];
$selectedUserGameStats = [];
if ($selectedUserId) {
    $stmt = $pdo->prepare("SELECT id, nickname, country, rating, role FROM users WHERE id = ?");
    $stmt->execute([$selectedUserId]);
    $selectedUser = $stmt->fetch();

    if ($selectedUser) {
        $stmt = $pdo->prepare("
            SELECT
                tr.tournament_id,
                tr.team_id,
                tournament.name AS tournament_name,
                tournament.status,
                tournament.start_date,
                tournament.end_date,
                gametype.name AS game_name,
                team.name AS team_name,
                COALESCE(playerrole.name, 'Не указана') AS role_name
            FROM tournamentroster tr
            JOIN tournament ON tournament.id = tr.tournament_id
            JOIN gametype ON gametype.id = tournament.game_type_id
            JOIN team ON team.id = tr.team_id
            LEFT JOIN playerrole ON playerrole.id = tr.role_id
            WHERE tr.user_id = ?
            ORDER BY tournament.start_date DESC, tournament.id DESC
        ");
        $stmt->execute([$selectedUserId]);
        $selectedUserTournaments = $stmt->fetchAll();

        $matchCountStmt = $pdo->prepare("
            SELECT COUNT(*) FROM match
            WHERE tournament_id = ?
              AND is_finished
              AND (team1_id = ? OR team2_id = ?)
        ");

        foreach ($selectedUserTournaments as &$participation) {
            $places = tournament_derived_places($pdo, (int)$participation['tournament_id']);
            $place = null;
            foreach ($places as $placeRow) {
                if ((int)$placeRow['team_id'] === (int)$participation['team_id']) {
                    $place = $placeRow;
                    break;
                }
            }

            $matchCountStmt->execute([
                (int)$participation['tournament_id'],
                (int)$participation['team_id'],
                (int)$participation['team_id'],
            ]);

            $participation['place_label'] = $place['place_label'] ?? 'Не определено';
            $participation['prize_label'] = $place['prize_label'] ?? 'Не указано';
            $participation['place_source'] = $place['source'] ?? 'Турнир еще не завершен';
            $participation['matches_count'] = (int)$matchCountStmt->fetchColumn();
        }
        unset($participation);

        $stmt = $pdo->prepare("
            SELECT
                tournament.id AS tournament_id,
                tournament.name AS tournament_name,
                gametype.name AS game_name,
                m.id AS match_id,
                s.name AS stage_name,
                s.stage_order,
                m.team1_id,
                m.team2_id,
                m.team1_score,
                m.team2_score,
                t1.name AS team1_name,
                t2.name AS team2_name,
                mg.id AS match_game_id,
                mg.game_number,
                COALESCE(gamemap.name, mg.map_name) AS map_name,
                mg.winner_team_id AS game_winner_team_id,
                tr.team_id AS player_team_id,
                player_team.name AS player_team_name,
                ps.kills,
                ps.deaths
            FROM playerstats ps
            JOIN matchgame mg ON mg.id = ps.match_game_id
            JOIN match m ON m.id = mg.match_id
            JOIN tournamentstage s ON s.id = m.stage_id
            JOIN tournament ON tournament.id = m.tournament_id
            JOIN gametype ON gametype.id = tournament.game_type_id
            JOIN team t1 ON t1.id = m.team1_id
            JOIN team t2 ON t2.id = m.team2_id
            JOIN tournamentroster tr
              ON tr.tournament_id = tournament.id
             AND tr.user_id = ps.user_id
            JOIN team player_team ON player_team.id = tr.team_id
            LEFT JOIN gamemap ON gamemap.id = mg.map_id
            WHERE ps.user_id = ?
            ORDER BY tournament.start_date DESC, tournament.id DESC, s.stage_order, m.id, mg.game_number
        ");
        $stmt->execute([$selectedUserId]);
        $selectedUserGameStats = $stmt->fetchAll();
    }
}

$selectedUserTournamentProfile = [];
foreach ($selectedUserTournaments as $participation) {
    $selectedUserTournamentProfile[(int)$participation['tournament_id']] = [
        'participation' => $participation,
        'rows' => [],
    ];
}
foreach ($selectedUserGameStats as $stat) {
    $tournamentId = (int)$stat['tournament_id'];
    if (!isset($selectedUserTournamentProfile[$tournamentId])) {
        $selectedUserTournamentProfile[$tournamentId] = [
            'participation' => [
                'tournament_id' => $tournamentId,
                'tournament_name' => $stat['tournament_name'],
                'game_name' => $stat['game_name'],
                'team_name' => $stat['player_team_name'],
                'role_name' => 'Не указана',
                'place_label' => 'Не определено',
                'prize_label' => 'Не указано',
                'place_source' => 'Турнир еще не завершен',
                'matches_count' => 0,
                'status' => 'planned',
                'start_date' => null,
                'end_date' => null,
            ],
            'rows' => [],
        ];
    }
    $selectedUserTournamentProfile[$tournamentId]['rows'][] = $stat;
}
foreach ($selectedUserTournamentProfile as &$profile) {
    $profile['stages'] = [];
    foreach ($profile['rows'] as $row) {
        $stageKey = (int)$row['stage_order'] . '_' . $row['stage_name'];
        if (!isset($profile['stages'][$stageKey])) {
            $profile['stages'][$stageKey] = [
                'name' => stage_label($row['stage_name']),
                'rows' => [],
            ];
        }
        $profile['stages'][$stageKey]['rows'][] = $row;
    }
}
unset($profile);

$filters = [
    'q' => trim($_GET['q'] ?? ''),
    'country' => is_valid_country(trim($_GET['country'] ?? '')) ? trim($_GET['country'] ?? '') : '',
    'role' => $_GET['role'] ?? '',
    'rating_min' => trim($_GET['rating_min'] ?? ''),
    'rating_max' => trim($_GET['rating_max'] ?? ''),
    'sort' => $_GET['sort'] ?? 'rating_desc',
];

$where = [];
$params = [];

if ($filters['q'] !== '') {
    $where[] = "nickname ILIKE ?";
    $params[] = '%' . $filters['q'] . '%';
}

if ($filters['country'] !== '') {
    $where[] = "country = ?";
    $params[] = $filters['country'];
}

if (in_array($filters['role'], ['admin', 'player'], true)) {
    $where[] = "role = ?";
    $params[] = $filters['role'];
}

if ($filters['rating_min'] !== '' && is_numeric($filters['rating_min'])) {
    $where[] = "rating >= ?";
    $params[] = (int)$filters['rating_min'];
}

if ($filters['rating_max'] !== '' && is_numeric($filters['rating_max'])) {
    $where[] = "rating <= ?";
    $params[] = (int)$filters['rating_max'];
}

$sorts = [
    'rating_desc' => 'rating DESC, nickname ASC',
    'rating_asc' => 'rating ASC, nickname ASC',
    'nickname_asc' => 'nickname ASC',
    'nickname_desc' => 'nickname DESC',
    'country_asc' => 'country ASC, nickname ASC',
    'role_asc' => 'role ASC, rating DESC, nickname ASC',
];
$orderBy = $sorts[$filters['sort']] ?? $sorts['rating_desc'];

$sql = "SELECT id, nickname, country, rating, role FROM users";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY $orderBy";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$players = $stmt->fetchAll();

$activeFilters = [];
if ($filters['q'] !== '') $activeFilters[] = 'Ник: ' . $filters['q'];
if ($filters['country'] !== '') $activeFilters[] = 'Страна: ' . $filters['country'];
if (in_array($filters['role'], ['admin', 'player'], true)) $activeFilters[] = 'Роль: ' . role_label($filters['role']);
if ($filters['rating_min'] !== '') $activeFilters[] = 'Рейтинг от ' . $filters['rating_min'];
if ($filters['rating_max'] !== '') $activeFilters[] = 'Рейтинг до ' . $filters['rating_max'];
?>

<style>
    .users-toolbar {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 16px;
    }
    .users-filter-grid {
        display: grid;
        grid-template-columns: minmax(220px, 1.4fr) minmax(160px, 1fr) minmax(150px, .8fr) repeat(2, minmax(110px, .6fr)) minmax(180px, 1fr) auto;
        gap: 12px;
        align-items: end;
    }
    .filter-label {
        font-size: .78rem;
        color: #64748b;
        font-weight: 700;
        margin-bottom: 6px;
    }
    .filter-chip {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 4px 10px;
        background: #e0f2fe;
        color: #075985;
        font-size: .82rem;
        font-weight: 600;
        margin: 8px 6px 0 0;
    }
    .role-pill {
        display: inline-flex;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: .82rem;
        font-weight: 700;
    }
    .role-admin { background: #fee2e2; color: #991b1b; }
    .role-player { background: #dcfce7; color: #166534; }
    .rating-pill {
        display: inline-flex;
        min-width: 58px;
        justify-content: center;
        border-radius: 6px;
        padding: 4px 8px;
        background: #f1f5f9;
        font-weight: 700;
    }
    .users-table tbody tr:hover { background: #f8fafc; }
    @media (max-width: 1200px) {
        .users-filter-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 768px) {
        .users-filter-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="page-card">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h2 class="page-title mb-1">Пользователи</h2>
            <div class="text-muted">Фильтруйте список по нескольким условиям одновременно.</div>
        </div>
        <div class="text-end">
            <div class="fs-4 fw-bold"><?= count($players) ?></div>
            <div class="text-muted">найдено</div>
        </div>
    </div>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['delete_error'])): ?>
        <div class="alert alert-warning">Пользователя нельзя удалить: у него есть связанные данные.</div>
    <?php endif; ?>

    <div class="users-toolbar mb-4">
        <form method="get" class="users-filter-grid">
            <div>
                <div class="filter-label">Поиск по нику</div>
                <input type="text" name="q" class="form-control" value="<?= htmlspecialchars($filters['q']) ?>">
            </div>
            <div>
                <div class="filter-label">Страна</div>
                <select name="country" class="form-select">
                    <option value="">Все страны</option>
                    <?php foreach ($countries as $country): ?>
                        <option value="<?= htmlspecialchars($country) ?>" <?= $filters['country'] === $country ? 'selected' : '' ?>>
                            <?= htmlspecialchars($country) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <div class="filter-label">Роль</div>
                <select name="role" class="form-select">
                    <option value="">Все роли</option>
                    <option value="player" <?= $filters['role'] === 'player' ? 'selected' : '' ?>>Игрок</option>
                    <option value="admin" <?= $filters['role'] === 'admin' ? 'selected' : '' ?>>Администратор</option>
                </select>
            </div>
            <div>
                <div class="filter-label">Рейтинг от</div>
                <input type="number" name="rating_min" class="form-control" value="<?= htmlspecialchars($filters['rating_min']) ?>" min="0">
            </div>
            <div>
                <div class="filter-label">До</div>
                <input type="number" name="rating_max" class="form-control" value="<?= htmlspecialchars($filters['rating_max']) ?>" min="0">
            </div>
            <div>
                <div class="filter-label">Сортировка</div>
                <select name="sort" class="form-select">
                    <option value="rating_desc" <?= $filters['sort'] === 'rating_desc' ? 'selected' : '' ?>>Рейтинг: высокий</option>
                    <option value="rating_asc" <?= $filters['sort'] === 'rating_asc' ? 'selected' : '' ?>>Рейтинг: низкий</option>
                    <option value="nickname_asc" <?= $filters['sort'] === 'nickname_asc' ? 'selected' : '' ?>>Ник: А-Я</option>
                    <option value="nickname_desc" <?= $filters['sort'] === 'nickname_desc' ? 'selected' : '' ?>>Ник: Я-А</option>
                    <option value="country_asc" <?= $filters['sort'] === 'country_asc' ? 'selected' : '' ?>>Страна</option>
                    <option value="role_asc" <?= $filters['sort'] === 'role_asc' ? 'selected' : '' ?>>Роль</option>
                </select>
            </div>
            <div class="d-grid gap-2">
                <button class="btn btn-primary">Найти</button>
                <a class="btn btn-outline-secondary" href="players.php">Сброс</a>
            </div>
        </form>

        <?php if ($activeFilters): ?>
            <div class="mt-2">
                <?php foreach ($activeFilters as $filter): ?>
                    <span class="filter-chip"><?= htmlspecialchars($filter) ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($isAdmin): ?>
    <div class="mb-4">
        <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#addUserForm" aria-expanded="false" aria-controls="addUserForm">
            Добавить пользователя
        </button>
        <div class="collapse mt-3" id="addUserForm">
            <form method="post" class="row g-3 border rounded p-3">
                <div class="col-md-2">
                    <label class="form-label">Никнейм</label>
                    <input type="text" name="nickname" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Страна</label>
                    <select name="country" class="form-select" required>
                        <option value="">Выберите страну</option>
                        <?php foreach ($countries as $country): ?>
                            <option value="<?= htmlspecialchars($country) ?>"><?= htmlspecialchars($country) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Рейтинг</label>
                    <input type="number" name="rating" class="form-control" value="0" min="0" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Роль в системе</label>
                    <select name="role" class="form-select">
                        <option value="player">Игрок</option>
                        <option value="admin">Администратор</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Пароль</label>
                    <input type="text" name="password" class="form-control" placeholder="по умолчанию password">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <table class="table table-hover table-bordered users-table">
        <thead class="table-light">
        <tr>
            <th>ID</th>
            <th>Никнейм</th>
            <th>Страна</th>
            <th>Рейтинг</th>
            <th>Роль в системе</th>
            <?php if ($isAdmin): ?><th>Действия</th><?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($players as $player): ?>
            <tr>
                <td><?= $player['id'] ?></td>
                <td>
                    <a class="fw-semibold text-decoration-none" href="players.php?user_id=<?= (int)$player['id'] ?>">
                        <?= htmlspecialchars($player['nickname']) ?>
                    </a>
                </td>
                <td><?= htmlspecialchars($player['country']) ?></td>
                <td><span class="rating-pill"><?= $player['rating'] ?></span></td>
                <td>
                    <span class="role-pill role-<?= htmlspecialchars($player['role']) ?>">
                        <?= htmlspecialchars(role_label($player['role'])) ?>
                    </span>
                </td>
                <?php if ($isAdmin): ?>
                <td>
                    <div class="d-flex gap-2">
                        <a class="btn btn-sm btn-outline-primary" href="players.php?edit=<?= (int)$player['id'] ?>">Редактировать</a>
                        <form method="post" action="user_delete.php" onsubmit="return confirm('Удалить пользователя?');">
                            <input type="hidden" name="id" value="<?= (int)$player['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger">Удалить</button>
                        </form>
                    </div>
                </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($isAdmin && $editUser): ?>
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="user_edit.php">
                <input type="hidden" name="id" value="<?= (int)$editUser['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Редактировать пользователя</h5>
                    <a class="btn-close" href="players.php" aria-label="Закрыть"></a>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Никнейм</label>
                            <input class="form-control" name="nickname" value="<?= htmlspecialchars($editUser['nickname']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Страна</label>
                            <select class="form-select" name="country" required>
                                <?php foreach ($countries as $country): ?>
                                    <option value="<?= htmlspecialchars($country) ?>" <?= $editUser['country'] === $country ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($country) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Рейтинг</label>
                            <input class="form-control" type="number" name="rating" min="0" value="<?= (int)$editUser['rating'] ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Роль</label>
                            <select class="form-select" name="role">
                                <option value="player" <?= $editUser['role'] === 'player' ? 'selected' : '' ?>>Игрок</option>
                                <option value="admin" <?= $editUser['role'] === 'admin' ? 'selected' : '' ?>>Администратор</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Новый пароль</label>
                            <input class="form-control" name="password" placeholder="не менять">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a class="btn btn-outline-secondary" href="players.php">Отмена</a>
                    <button class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
});
</script>
<?php endif; ?>

<?php if ($selectedUser): ?>
<div class="modal fade" id="userProfileModal" tabindex="-1" aria-labelledby="userProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="userProfileModalLabel"><?= htmlspecialchars($selectedUser['nickname']) ?></h5>
                    <div class="text-muted small">
                        <?= htmlspecialchars($selectedUser['country']) ?> · рейтинг <?= (int)$selectedUser['rating'] ?> · <?= htmlspecialchars(role_label($selectedUser['role'])) ?>
                    </div>
                </div>
                <a class="btn-close" href="players.php" aria-label="Закрыть"></a>
            </div>
            <div class="modal-body">
                <h6 class="fw-bold mb-3">Турниры и партии игрока</h6>
                <?php if (!$selectedUserTournaments): ?>
                    <div class="text-muted">Игрок пока не добавлен в составы турниров.</div>
                <?php else: ?>
                    <div class="accordion" id="playerTournamentProfile">
                        <?php foreach ($selectedUserTournamentProfile as $tournamentId => $group): ?>
                            <?php
                            $participation = $group['participation'];
                            $rows = $group['rows'];
                            $collapseId = 'playerTournament' . $tournamentId;
                            ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>" aria-expanded="false" aria-controls="<?= $collapseId ?>">
                                        <span class="fw-semibold me-2"><?= htmlspecialchars($participation['tournament_name']) ?></span>
                                        <span class="text-muted me-3"><?= htmlspecialchars($participation['team_name']) ?></span>
                                        <span class="badge text-bg-secondary me-2">место: <?= htmlspecialchars($participation['place_label']) ?></span>
                                        <span class="badge text-bg-light text-dark border me-2"><?= count($rows) ?> партий</span>
                                    </button>
                                </h2>
                                <div id="<?= $collapseId ?>" class="accordion-collapse collapse" data-bs-parent="#playerTournamentProfile">
                                    <div class="accordion-body">
                                        <div class="d-flex justify-content-end mb-3">
                                            <a class="btn btn-sm btn-outline-primary" href="tournament.php?id=<?= (int)$participation['tournament_id'] ?>&tab=results">Открыть турнир</a>
                                        </div>
                                        <?php if (!$rows): ?>
                                            <div class="text-muted">Для этого турнира пока нет заполненной статистики по партиям.</div>
                                        <?php else: ?>
                                        <?php foreach ($group['stages'] as $stageGroup): ?>
                                            <?php
                                            $stageRows = $stageGroup['rows'];
                                            $stageKills = array_sum(array_map(fn($row) => (int)$row['kills'], $stageRows));
                                            $stageDeaths = array_sum(array_map(fn($row) => (int)$row['deaths'], $stageRows));
                                            $stageKd = $stageDeaths > 0 ? $stageKills / $stageDeaths : $stageKills;
                                            ?>
                                            <div class="border rounded mb-3">
                                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-3 py-2 bg-light border-bottom">
                                                    <div class="fw-semibold"><?= htmlspecialchars($stageGroup['name']) ?></div>
                                                    <div class="d-flex gap-2">
                                                        <span class="badge text-bg-light text-dark border"><?= count($stageRows) ?> партий</span>
                                                        <span class="badge text-bg-secondary">K/D <?= number_format($stageKd, 2, '.', ' ') ?></span>
                                                    </div>
                                                </div>
                                                <div class="table-responsive">
                                                    <table class="table table-hover table-bordered mb-0">
                                                        <thead class="table-light">
                                                        <tr>
                                                            <th>Матч</th>
                                                            <th>Партия</th>
                                                            <th>Карта</th>
                                                            <th>Kills</th>
                                                            <th>Deaths</th>
                                                            <th>K/D</th>
                                                            <th>Итог партии</th>
                                                            <th></th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php foreach ($stageRows as $stat): ?>
                                                            <?php
                                                            $playerTeamId = (int)$stat['player_team_id'];
                                                            $team1Id = (int)$stat['team1_id'];
                                                            $team2Id = (int)$stat['team2_id'];
                                                            $opponentName = $playerTeamId === $team1Id ? $stat['team2_name'] : $stat['team1_name'];
                                                            $playerScore = $playerTeamId === $team1Id ? (int)$stat['team1_score'] : (int)$stat['team2_score'];
                                                            $opponentScore = $playerTeamId === $team1Id ? (int)$stat['team2_score'] : (int)$stat['team1_score'];
                                                            $kills = (int)$stat['kills'];
                                                            $deaths = (int)$stat['deaths'];
                                                            $kd = $deaths > 0 ? $kills / $deaths : $kills;
                                                            $gameWinnerId = (int)($stat['game_winner_team_id'] ?? 0);
                                                            $gameResult = !$gameWinnerId ? 'Не указан' : ($gameWinnerId === $playerTeamId ? 'Победа' : 'Поражение');
                                                            ?>
                                                            <tr>
                                                                <td>
                                                                    <div><?= htmlspecialchars($stat['player_team_name']) ?> против <?= htmlspecialchars($opponentName) ?></div>
                                                                    <div class="text-muted small"><?= $playerScore ?>:<?= $opponentScore ?></div>
                                                                </td>
                                                                <td>Партия <?= (int)$stat['game_number'] ?></td>
                                                                <td><?= htmlspecialchars($stat['map_name'] ?? 'Не указана') ?></td>
                                                                <td><?= $kills ?></td>
                                                                <td><?= $deaths ?></td>
                                                                <td><?= number_format($kd, 2, '.', ' ') ?></td>
                                                                <td>
                                                                    <span class="badge <?= $gameResult === 'Победа' ? 'text-bg-success' : ($gameResult === 'Поражение' ? 'text-bg-danger' : 'text-bg-secondary') ?>">
                                                                        <?= htmlspecialchars($gameResult) ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <a class="btn btn-sm btn-outline-primary" href="match.php?id=<?= (int)$stat['match_id'] ?>&game_id=<?= (int)$stat['match_game_id'] ?>&team_id=<?= $playerTeamId ?>">
                                                                        Открыть
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <a class="btn btn-outline-secondary" href="players.php">Закрыть</a>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    new bootstrap.Modal(document.getElementById('userProfileModal')).show();
});
</script>
<?php endif; ?>

<?php include __DIR__ . "/footer.php"; ?>
