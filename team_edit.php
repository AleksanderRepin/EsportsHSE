<?php
require __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
require_admin();
include __DIR__ . "/header.php";

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$errors = [];

$stmt = $pdo->prepare("SELECT id, name, country, rating FROM team WHERE id = ?");
$stmt->execute([$id]);
$team = $stmt->fetch();
if (!$team) {
    die("Команда не найдена.");
}

$countries = country_options();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $rating = (int)($_POST['rating'] ?? 0);

    if ($name === '') $errors[] = "Введите название команды.";
    if (!is_valid_country($country)) $errors[] = "Выберите страну из списка.";
    if ($rating < 0 || $rating > 10000) $errors[] = "Рейтинг должен быть от 0 до 10000.";

    if (!$errors) {
        $stmt = $pdo->prepare("UPDATE team SET name = ?, country = ?, rating = ? WHERE id = ?");
        $stmt->execute([$name, $country, $rating, $id]);
        header("Location: teams.php");
        exit;
    }

    $team = ['id' => $id, 'name' => $name, 'country' => $country, 'rating' => $rating];
}
?>

<div class="page-card">
    <h2 class="page-title">Редактировать команду</h2>
    <?php if ($errors): ?>
        <div class="alert alert-danger"><?= htmlspecialchars(implode(' ', $errors)) ?></div>
    <?php endif; ?>
    <form method="post" class="row g-3">
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="col-md-4">
            <label class="form-label">Название</label>
            <input class="form-control" name="name" value="<?= htmlspecialchars($team['name']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Страна</label>
            <select class="form-select" name="country" required>
                <?php foreach ($countries as $countryOption): ?>
                    <option value="<?= htmlspecialchars($countryOption) ?>" <?= $team['country'] === $countryOption ? 'selected' : '' ?>>
                        <?= htmlspecialchars($countryOption) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Рейтинг</label>
            <input class="form-control" type="number" name="rating" min="0" max="10000" value="<?= (int)$team['rating'] ?>" required>
        </div>
        <div class="col-12">
            <button class="btn btn-primary">Сохранить</button>
            <a class="btn btn-outline-secondary" href="teams.php">Отмена</a>
        </div>
    </form>
</div>

<?php include __DIR__ . "/footer.php"; ?>
