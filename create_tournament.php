<?php
require __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
require_admin();
include __DIR__ . "/header.php";

$errors = [];
$gameTypes = $pdo->query("SELECT * FROM gametype ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $gameTypeId = (int)($_POST['game_type_id'] ?? 0);
    $start = $_POST['start_date'] ?? '';
    $end = $_POST['end_date'] ?? '';
    $prize = $_POST['prize_pool'] !== '' ? $_POST['prize_pool'] : 0;
    $status = $_POST['status'] ?? 'planned';

    if ($name === '') $errors[] = "Введите название турнира.";
    if (!$gameTypeId) $errors[] = "Выберите игровую дисциплину.";
    if (!$start || !$end) $errors[] = "Укажите даты турнира.";
    if ($start && $end && $end < $start) $errors[] = "Дата окончания не может быть раньше даты начала.";
    if (!is_numeric($prize) || $prize < 0) $errors[] = "Призовой фонд должен быть не меньше 0.";
    if (!in_array($status, ['planned', 'active'], true)) $errors[] = "Некорректный статус.";

    if (!$errors) {
        $stmt = $pdo->prepare("
            INSERT INTO tournament (name, game_type_id, start_date, end_date, prize_pool, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $gameTypeId, $start, $end, $prize, $status]);
        header("Location: tournaments.php");
        exit;
    }
}
?>

<div class="page-card">
    <h2 class="page-title">Создать турнир</h2>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Название</label>
            <input class="form-control" name="name" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Игровая дисциплина</label>
            <select class="form-select" name="game_type_id" required>
                <option value="">Выберите дисциплину</option>
                <?php foreach ($gameTypes as $g): ?>
                    <option value="<?= $g['id'] ?>">
                        <?= htmlspecialchars($g['name']) ?>, <?= $g['team_size'] ?> игроков
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Дата начала</label>
            <input class="form-control" type="date" name="start_date" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Дата окончания</label>
            <input class="form-control" type="date" name="end_date" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Призовой фонд</label>
            <input class="form-control" type="number" step="0.01" min="0" name="prize_pool" value="0">
        </div>
        <div class="col-md-3">
            <label class="form-label">Статус</label>
            <select class="form-select" name="status">
                <option value="planned">Запланирован</option>
                <option value="active">Идет</option>
            </select>
            <div class="form-text">Завершение доступно только после заполнения матчей и итогов.</div>
        </div>
        <div class="col-12">
            <button class="btn btn-primary">Создать</button>
            <a href="tournaments.php" class="btn btn-outline-secondary">Назад</a>
        </div>
    </form>
</div>

<?php include __DIR__ . "/footer.php"; ?>
