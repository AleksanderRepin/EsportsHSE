<?php
require __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
require_admin();
include __DIR__ . "/header.php";

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$errors = [];

$stmt = $pdo->prepare("SELECT id, nickname, country, rating, role FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) {
    die("Пользователь не найден.");
}

$countries = country_options();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nickname = trim($_POST['nickname'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $rating = (int)($_POST['rating'] ?? 0);
    $role = $_POST['role'] ?? 'player';
    $password = trim($_POST['password'] ?? '');

    if ($nickname === '') $errors[] = "Введите никнейм.";
    if (!is_valid_country($country)) $errors[] = "Выберите страну из списка.";
    if ($rating < 0) $errors[] = "Рейтинг не может быть отрицательным.";
    if (!in_array($role, ['admin', 'player'], true)) $errors[] = "Некорректная роль.";

    if (!$errors) {
        if ($password !== '') {
            $stmt = $pdo->prepare("UPDATE users SET nickname = ?, country = ?, rating = ?, role = ?, password_hash = ? WHERE id = ?");
            $stmt->execute([$nickname, $country, $rating, $role, password_hash($password, PASSWORD_DEFAULT), $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET nickname = ?, country = ?, rating = ?, role = ? WHERE id = ?");
            $stmt->execute([$nickname, $country, $rating, $role, $id]);
        }
        header("Location: players.php");
        exit;
    }

    $user = ['id' => $id, 'nickname' => $nickname, 'country' => $country, 'rating' => $rating, 'role' => $role];
}
?>

<div class="page-card">
    <h2 class="page-title">Редактировать пользователя</h2>
    <?php if ($errors): ?>
        <div class="alert alert-danger"><?= htmlspecialchars(implode(' ', $errors)) ?></div>
    <?php endif; ?>
    <form method="post" class="row g-3">
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="col-md-3">
            <label class="form-label">Никнейм</label>
            <input class="form-control" name="nickname" value="<?= htmlspecialchars($user['nickname']) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Страна</label>
            <select class="form-select" name="country" required>
                <?php foreach ($countries as $countryOption): ?>
                    <option value="<?= htmlspecialchars($countryOption) ?>" <?= $user['country'] === $countryOption ? 'selected' : '' ?>>
                        <?= htmlspecialchars($countryOption) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Рейтинг</label>
            <input class="form-control" type="number" name="rating" min="0" value="<?= (int)$user['rating'] ?>" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Роль</label>
            <select class="form-select" name="role">
                <option value="player" <?= $user['role'] === 'player' ? 'selected' : '' ?>>Игрок</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Администратор</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Новый пароль</label>
            <input class="form-control" name="password" placeholder="не менять">
        </div>
        <div class="col-12">
            <button class="btn btn-primary">Сохранить</button>
            <a class="btn btn-outline-secondary" href="players.php">Отмена</a>
        </div>
    </form>
</div>

<?php include __DIR__ . "/footer.php"; ?>
