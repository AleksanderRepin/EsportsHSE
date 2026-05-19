<?php

declare(strict_types=1);

if (function_exists('sapi_windows_cp_set')) {
    sapi_windows_cp_set(65001);
}

$sessionPath = __DIR__ . '/tmp_sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);

require __DIR__ . '/../db.php';
require __DIR__ . '/../ui.php';
require __DIR__ . '/../auth.php';

final class TestRunner
{
    private int $passed = 0;
    private int $failed = 0;
    private array $failures = [];

    public function group(string $name): void
    {
        echo PHP_EOL . "=== {$name} ===" . PHP_EOL;
    }

    public function test(string $name, callable $callback): void
    {
        try {
            $callback();
            $this->passed++;
            echo "[OK] {$name}" . PHP_EOL;
        } catch (Throwable $e) {
            $this->failed++;
            $this->failures[] = "{$name}: " . $e->getMessage();
            echo "[FAIL] {$name}: " . $e->getMessage() . PHP_EOL;
        }
    }

    public function assertTrue(bool $condition, string $message = 'condition is false'): void
    {
        if (!$condition) {
            throw new RuntimeException($message);
        }
    }

    public function assertFalse(bool $condition, string $message = 'condition is true'): void
    {
        $this->assertTrue(!$condition, $message);
    }

    public function assertEquals(mixed $expected, mixed $actual, string $message = 'values are not equal'): void
    {
        if ($expected != $actual) {
            throw new RuntimeException($message . " expected=" . var_export($expected, true) . " actual=" . var_export($actual, true));
        }
    }

    public function assertSame(mixed $expected, mixed $actual, string $message = 'values are not identical'): void
    {
        if ($expected !== $actual) {
            throw new RuntimeException($message . " expected=" . var_export($expected, true) . " actual=" . var_export($actual, true));
        }
    }

    public function assertCount(int $expected, array $actual, string $message = 'unexpected count'): void
    {
        $this->assertSame($expected, count($actual), $message);
    }

    public function summary(): int
    {
        $total = $this->passed + $this->failed;
        echo PHP_EOL . "=== Итог ===" . PHP_EOL;
        echo "Пройдено: {$this->passed}/{$total}" . PHP_EOL;
        echo "Ошибок: {$this->failed}" . PHP_EOL;

        if ($this->failures) {
            echo PHP_EOL . "Ошибки:" . PHP_EOL;
            foreach ($this->failures as $failure) {
                echo "- {$failure}" . PHP_EOL;
            }
        }

        return $this->failed === 0 ? 0 : 1;
    }
}

function calculate_match_result(array $gameWinnerIds, int $team1Id, int $team2Id): array
{
    $team1Score = 0;
    $team2Score = 0;
    $errors = [];

    foreach ($gameWinnerIds as $index => $winnerId) {
        $gameNumber = $index + 1;
        $winnerId = (int)$winnerId;

        if ($winnerId === 0) {
            $errors[] = "В партии {$gameNumber} не выбран победитель.";
            continue;
        }

        if (!in_array($winnerId, [$team1Id, $team2Id], true)) {
            $errors[] = "В партии {$gameNumber} выбран победитель не из этого матча.";
            continue;
        }

        if ($winnerId === $team1Id) {
            $team1Score++;
        } else {
            $team2Score++;
        }
    }

    if ($errors) {
        return ['ok' => false, 'errors' => $errors, 'team1_score' => $team1Score, 'team2_score' => $team2Score, 'winner_id' => null];
    }

    if ($team1Score === $team2Score) {
        return ['ok' => false, 'errors' => ['Нельзя завершить матч: по партиям ничья.'], 'team1_score' => $team1Score, 'team2_score' => $team2Score, 'winner_id' => null];
    }

    return [
        'ok' => true,
        'errors' => [],
        'team1_score' => $team1Score,
        'team2_score' => $team2Score,
        'winner_id' => $team1Score > $team2Score ? $team1Id : $team2Id,
    ];
}

function bracket_stage_names(int $teamCount): array
{
    $stageNames = [
        2 => ['Финал'],
        4 => ['Полуфинал', 'Финал'],
        8 => ['Четвертьфинал', 'Полуфинал', 'Финал'],
        16 => ['1/8 финала', 'Четвертьфинал', 'Полуфинал', 'Финал'],
    ];

    return $stageNames[$teamCount] ?? [];
}

function bracket_pairs(array $teamIds): array
{
    $pairs = [];
    $left = 0;
    $right = count($teamIds) - 1;

    while ($left < $right) {
        $pairs[] = [$teamIds[$left], $teamIds[$right]];
        $left++;
        $right--;
    }

    return $pairs;
}

function kd_ratio(int $kills, int $deaths): float
{
    return $deaths > 0 ? $kills / $deaths : (float)$kills;
}

function normalize_stat_value(mixed $value): int
{
    return max(0, (int)$value);
}

function is_power_of_two_team_count(int $count): bool
{
    return $count >= 2 && ($count & ($count - 1)) === 0;
}

function prize_sum_is_valid(float $pool, array $amounts): bool
{
    return array_sum($amounts) <= $pool;
}

function expect_database_exception(PDO $pdo, callable $callback): void
{
    $savepoint = 'sp_' . bin2hex(random_bytes(4));
    $pdo->exec("SAVEPOINT {$savepoint}");

    try {
        $callback();
    } catch (Throwable $e) {
        $pdo->exec("ROLLBACK TO SAVEPOINT {$savepoint}");
        $pdo->exec("RELEASE SAVEPOINT {$savepoint}");
        return;
    }

    $pdo->exec("ROLLBACK TO SAVEPOINT {$savepoint}");
    $pdo->exec("RELEASE SAVEPOINT {$savepoint}");
    throw new RuntimeException('database exception was expected');
}

function insert_returning_id(PDO $pdo, string $sql, array $params = []): int
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}

$runner = new TestRunner();

$runner->group('Модульные тесты');

$runner->test('Список стран содержит Kazakhstan', fn() => $runner->assertTrue(in_array('Kazakhstan', country_options(), true)));
$runner->test('Список стран содержит Russia', fn() => $runner->assertTrue(in_array('Russia', country_options(), true)));
$runner->test('Проверка допустимой страны', fn() => $runner->assertTrue(is_valid_country('Kazakhstan')));
$runner->test('Проверка недопустимой страны', fn() => $runner->assertFalse(is_valid_country('Atlantis')));
$runner->test('Неизвестная роль возвращается без изменения', fn() => $runner->assertSame('coach', role_label('coach')));
$runner->test('Неизвестный статус возвращается без изменения', fn() => $runner->assertSame('paused', status_label('paused')));
$runner->test('CSS-класс для завершенного турнира', fn() => $runner->assertSame('text-bg-success', status_badge_class('finished')));
$runner->test('Неизвестный этап возвращается без изменения', fn() => $runner->assertSame('Group stage', stage_label('Group stage')));
$runner->test('K/D считается как kills / deaths', fn() => $runner->assertEquals(3.0, kd_ratio(12, 4)));
$runner->test('K/D при нуле смертей равен количеству убийств', fn() => $runner->assertEquals(12.0, kd_ratio(12, 0)));
$runner->test('Отрицательная статистика приводится к нулю', fn() => $runner->assertSame(0, normalize_stat_value(-5)));
$runner->test('Статистика строкового числа приводится к целому', fn() => $runner->assertSame(17, normalize_stat_value('17')));
$runner->test('Количество команд 8 является степенью двойки', fn() => $runner->assertTrue(is_power_of_two_team_count(8)));
$runner->test('Количество команд 6 не подходит для сетки', fn() => $runner->assertFalse(is_power_of_two_team_count(6)));
$runner->test('Для 8 команд создаются три этапа', fn() => $runner->assertCount(3, bracket_stage_names(8)));
$runner->test('Для 4 команд первым этапом является полуфинал', fn() => $runner->assertSame('Полуфинал', bracket_stage_names(4)[0]));
$runner->test('Посев формирует пары 1-4 и 2-3', fn() => $runner->assertEquals([[1, 4], [2, 3]], bracket_pairs([1, 2, 3, 4])));
$runner->test('Победитель матча определяется со счетом 2:1', function () use ($runner) {
    $result = calculate_match_result([10, 20, 10], 10, 20);
    $runner->assertTrue($result['ok']);
    $runner->assertSame(10, $result['winner_id']);
    $runner->assertSame(2, $result['team1_score']);
    $runner->assertSame(1, $result['team2_score']);
});
$runner->test('Победитель второй команды определяется со счетом 0:2', function () use ($runner) {
    $result = calculate_match_result([20, 20], 10, 20);
    $runner->assertTrue($result['ok']);
    $runner->assertSame(20, $result['winner_id']);
});
$runner->test('Матч нельзя завершить при ничьей по партиям', function () use ($runner) {
    $result = calculate_match_result([10, 20], 10, 20);
    $runner->assertFalse($result['ok']);
});
$runner->test('Матч нельзя завершить без победителя партии', function () use ($runner) {
    $result = calculate_match_result([10, 0], 10, 20);
    $runner->assertFalse($result['ok']);
});
$runner->test('Матч нельзя завершить с победителем не из матча', function () use ($runner) {
    $result = calculate_match_result([10, 30], 10, 20);
    $runner->assertFalse($result['ok']);
});
$runner->test('Распределение призовых не превышает фонд', fn() => $runner->assertTrue(prize_sum_is_valid(10000, [5000, 3000, 2000])));
$runner->test('Распределение призовых не может быть больше фонда', fn() => $runner->assertFalse(prize_sum_is_valid(10000, [7000, 4000])));
$runner->test('Проверка роли admin через сессию', function () use ($runner) {
    $_SESSION['user'] = ['role' => 'admin'];
    $runner->assertTrue(is_admin());
});
$runner->test('Проверка роли player через сессию', function () use ($runner) {
    $_SESSION['user'] = ['role' => 'player'];
    $runner->assertFalse(is_admin());
});

$runner->group('Интеграционные тесты PostgreSQL');

$pdo->beginTransaction();

try {
    $prefix = 'ut_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3));

    $runner->test('Подключение к базе данных PostgreSQL', function () use ($runner, $pdo) {
        $runner->assertSame(1, (int)$pdo->query('SELECT 1')->fetchColumn());
    });

    $gameTypeId = insert_returning_id($pdo, "INSERT INTO gametype (name, team_size) VALUES (?, ?) RETURNING id", ["{$prefix}_discipline", 2]);
    $runner->test('Создание игровой дисциплины', fn() => $runner->assertTrue($gameTypeId > 0));
    $runner->test('Запрет размера команды меньше единицы', fn() => expect_database_exception($pdo, fn() => insert_returning_id($pdo, "INSERT INTO gametype (name, team_size) VALUES (?, ?) RETURNING id", ["{$prefix}_bad_size", 0])));

    $mapId = insert_returning_id($pdo, "INSERT INTO gamemap (game_type_id, name) VALUES (?, ?) RETURNING id", [$gameTypeId, "{$prefix}_map"]);
    $runner->test('Создание карты дисциплины', fn() => $runner->assertTrue($mapId > 0));
    $runner->test('Запрет повторяющейся карты в одной дисциплине', fn() => expect_database_exception($pdo, fn() => insert_returning_id($pdo, "INSERT INTO gamemap (game_type_id, name) VALUES (?, ?) RETURNING id", [$gameTypeId, "{$prefix}_map"])));

    $roleId = insert_returning_id($pdo, "INSERT INTO playerrole (name) VALUES (?) RETURNING id", ["{$prefix}_role"]);
    $runner->test('Создание игровой роли', fn() => $runner->assertTrue($roleId > 0));
    $runner->test('Запрет повторяющейся игровой роли', fn() => expect_database_exception($pdo, fn() => insert_returning_id($pdo, "INSERT INTO playerrole (name) VALUES (?) RETURNING id", ["{$prefix}_role"])));

    $passwordHash = password_hash('admin', PASSWORD_DEFAULT);
    $user1Id = insert_returning_id($pdo, "INSERT INTO users (nickname, country, rating, role, password_hash) VALUES (?, ?, ?, ?, ?) RETURNING id", ["{$prefix}_player1", 'Kazakhstan', 1000, 'player', $passwordHash]);
    $user2Id = insert_returning_id($pdo, "INSERT INTO users (nickname, country, rating, role, password_hash) VALUES (?, ?, ?, ?, ?) RETURNING id", ["{$prefix}_player2", 'Russia', 900, 'player', $passwordHash]);
    $user3Id = insert_returning_id($pdo, "INSERT INTO users (nickname, country, rating, role, password_hash) VALUES (?, ?, ?, ?, ?) RETURNING id", ["{$prefix}_player3", 'USA', 800, 'player', $passwordHash]);
    $user4Id = insert_returning_id($pdo, "INSERT INTO users (nickname, country, rating, role, password_hash) VALUES (?, ?, ?, ?, ?) RETURNING id", ["{$prefix}_player4", 'Germany', 700, 'player', $passwordHash]);
    $runner->test('Создание тестовых пользователей', fn() => $runner->assertTrue($user1Id > 0 && $user4Id > 0));
    $runner->test('Проверка хэшированного пароля', fn() => $runner->assertTrue(password_verify('admin', $passwordHash)));
    $runner->test('Неверный пароль не проходит проверку', fn() => $runner->assertFalse(password_verify('wrong', $passwordHash)));
    $runner->test('Запрет повторяющегося nickname', fn() => expect_database_exception($pdo, fn() => insert_returning_id($pdo, "INSERT INTO users (nickname, country, rating, role, password_hash) VALUES (?, ?, ?, ?, ?) RETURNING id", ["{$prefix}_player1", 'Kazakhstan', 1000, 'player', $passwordHash])));
    $runner->test('Запрет отрицательного рейтинга пользователя', fn() => expect_database_exception($pdo, fn() => insert_returning_id($pdo, "INSERT INTO users (nickname, country, rating, role, password_hash) VALUES (?, ?, ?, ?, ?) RETURNING id", ["{$prefix}_bad_rating", 'Kazakhstan', -1, 'player', $passwordHash])));
    $runner->test('Запрет некорректной системной роли', fn() => expect_database_exception($pdo, fn() => insert_returning_id($pdo, "INSERT INTO users (nickname, country, rating, role, password_hash) VALUES (?, ?, ?, ?, ?) RETURNING id", ["{$prefix}_bad_role", 'Kazakhstan', 0, 'coach', $passwordHash])));

    $team1Id = insert_returning_id($pdo, "INSERT INTO team (name, country, rating) VALUES (?, ?, ?) RETURNING id", ["{$prefix}_team1", 'Kazakhstan', 1500]);
    $team2Id = insert_returning_id($pdo, "INSERT INTO team (name, country, rating) VALUES (?, ?, ?) RETURNING id", ["{$prefix}_team2", 'Russia', 1400]);
    $team3Id = insert_returning_id($pdo, "INSERT INTO team (name, country, rating) VALUES (?, ?, ?) RETURNING id", ["{$prefix}_team3", 'USA', 1300]);
    $runner->test('Создание тестовых команд', fn() => $runner->assertTrue($team1Id > 0 && $team3Id > 0));
    $runner->test('Запрет повторяющегося названия команды', fn() => expect_database_exception($pdo, fn() => insert_returning_id($pdo, "INSERT INTO team (name, country, rating) VALUES (?, ?, ?) RETURNING id", ["{$prefix}_team1", 'Kazakhstan', 1500])));
    $runner->test('Запрет отрицательного рейтинга команды', fn() => expect_database_exception($pdo, fn() => insert_returning_id($pdo, "INSERT INTO team (name, country, rating) VALUES (?, ?, ?) RETURNING id", ["{$prefix}_bad_team", 'Kazakhstan', -1])));

    insert_returning_id($pdo, "INSERT INTO teamplayer (user_id, team_id, role_id) VALUES (?, ?, ?) RETURNING id", [$user1Id, $team1Id, $roleId]);
    insert_returning_id($pdo, "INSERT INTO teamplayer (user_id, team_id, role_id) VALUES (?, ?, ?) RETURNING id", [$user2Id, $team1Id, $roleId]);
    insert_returning_id($pdo, "INSERT INTO teamplayer (user_id, team_id, role_id) VALUES (?, ?, ?) RETURNING id", [$user3Id, $team2Id, $roleId]);
    insert_returning_id($pdo, "INSERT INTO teamplayer (user_id, team_id, role_id) VALUES (?, ?, ?) RETURNING id", [$user1Id, $team2Id, $roleId]);
    $runner->test('Добавление игроков в общий состав команд', function () use ($runner, $pdo, $team1Id) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM teamplayer WHERE team_id = ?");
        $stmt->execute([$team1Id]);
        $runner->assertSame(2, (int)$stmt->fetchColumn());
    });
    $runner->test('Запрет повторного добавления игрока в общий состав команды', fn() => expect_database_exception($pdo, fn() => insert_returning_id($pdo, "INSERT INTO teamplayer (user_id, team_id, role_id) VALUES (?, ?, ?) RETURNING id", [$user1Id, $team1Id, $roleId])));

    $tournamentId = insert_returning_id($pdo, "INSERT INTO tournament (name, game_type_id, start_date, end_date, prize_pool, status) VALUES (?, ?, ?, ?, ?, ?) RETURNING id", ["{$prefix}_tournament", $gameTypeId, '2026-01-01', '2026-01-10', 10000, 'planned']);
    $runner->test('Создание турнира', fn() => $runner->assertTrue($tournamentId > 0));
    $runner->test('Запрет отрицательного призового фонда', fn() => expect_database_exception($pdo, fn() => insert_returning_id($pdo, "INSERT INTO tournament (name, game_type_id, start_date, prize_pool, status) VALUES (?, ?, ?, ?, ?) RETURNING id", ["{$prefix}_bad_pool", $gameTypeId, '2026-01-01', -1, 'planned'])));
    $runner->test('Запрет некорректного статуса турнира', fn() => expect_database_exception($pdo, fn() => insert_returning_id($pdo, "INSERT INTO tournament (name, game_type_id, start_date, prize_pool, status) VALUES (?, ?, ?, ?, ?) RETURNING id", ["{$prefix}_bad_status", $gameTypeId, '2026-01-01', 0, 'paused'])));
    $runner->test('Запрет даты окончания раньше даты начала', fn() => expect_database_exception($pdo, fn() => insert_returning_id($pdo, "INSERT INTO tournament (name, game_type_id, start_date, end_date, prize_pool, status) VALUES (?, ?, ?, ?, ?, ?) RETURNING id", ["{$prefix}_bad_dates", $gameTypeId, '2026-01-10', '2026-01-01', 0, 'planned'])));

    insert_returning_id($pdo, "INSERT INTO tournamentteam (tournament_id, team_id, seed) VALUES (?, ?, ?) RETURNING id", [$tournamentId, $team1Id, 1]);
    insert_returning_id($pdo, "INSERT INTO tournamentteam (tournament_id, team_id, seed) VALUES (?, ?, ?) RETURNING id", [$tournamentId, $team2Id, 2]);
    $runner->test('Добавление команд в турнир', function () use ($runner, $pdo, $tournamentId) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tournamentteam WHERE tournament_id = ?");
        $stmt->execute([$tournamentId]);
        $runner->assertSame(2, (int)$stmt->fetchColumn());
    });
    $runner->test('Запрет повторного добавления команды в турнир', fn() => expect_database_exception($pdo, fn() => insert_returning_id($pdo, "INSERT INTO tournamentteam (tournament_id, team_id, seed) VALUES (?, ?, ?) RETURNING id", [$tournamentId, $team1Id, 3])));

    insert_returning_id($pdo, "INSERT INTO tournamentroster (tournament_id, team_id, user_id, role_id) VALUES (?, ?, ?, ?) RETURNING id", [$tournamentId, $team1Id, $user1Id, $roleId]);
    insert_returning_id($pdo, "INSERT INTO tournamentroster (tournament_id, team_id, user_id, role_id) VALUES (?, ?, ?, ?) RETURNING id", [$tournamentId, $team1Id, $user2Id, $roleId]);
    insert_returning_id($pdo, "INSERT INTO tournamentroster (tournament_id, team_id, user_id, role_id) VALUES (?, ?, ?, ?) RETURNING id", [$tournamentId, $team2Id, $user3Id, $roleId]);
    $runner->test('Добавление игрока из общего состава в заявку турнира', function () use ($runner, $pdo, $tournamentId, $team1Id) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tournamentroster WHERE tournament_id = ? AND team_id = ?");
        $stmt->execute([$tournamentId, $team1Id]);
        $runner->assertSame(2, (int)$stmt->fetchColumn());
    });
    $runner->test('Запрет заявки игрока не из общего состава команды', fn() => expect_database_exception($pdo, fn() => insert_returning_id($pdo, "INSERT INTO tournamentroster (tournament_id, team_id, user_id, role_id) VALUES (?, ?, ?, ?) RETURNING id", [$tournamentId, $team2Id, $user4Id, $roleId])));
    $runner->test('Запрет участия одного игрока за две команды в турнире', fn() => expect_database_exception($pdo, fn() => insert_returning_id($pdo, "INSERT INTO tournamentroster (tournament_id, team_id, user_id, role_id) VALUES (?, ?, ?, ?) RETURNING id", [$tournamentId, $team2Id, $user1Id, $roleId])));

    $stageId = insert_returning_id($pdo, "INSERT INTO tournamentstage (tournament_id, name, stage_order) VALUES (?, ?, ?) RETURNING id", [$tournamentId, 'Финал', 1]);
    $runner->test('Создание этапа турнира', fn() => $runner->assertTrue($stageId > 0));
    $runner->test('Запрет повторного порядка этапа в одном турнире', fn() => expect_database_exception($pdo, fn() => insert_returning_id($pdo, "INSERT INTO tournamentstage (tournament_id, name, stage_order) VALUES (?, ?, ?) RETURNING id", [$tournamentId, 'Дубль', 1])));

    $matchId = insert_returning_id($pdo, "INSERT INTO match (tournament_id, stage_id, team1_id, team2_id, team1_score, team2_score, match_date, is_finished) VALUES (?, ?, ?, ?, 0, 0, NOW(), false) RETURNING id", [$tournamentId, $stageId, $team1Id, $team2Id]);
    $runner->test('Создание матча', fn() => $runner->assertTrue($matchId > 0));
    $runner->test('Запрет матча команды самой с собой', fn() => expect_database_exception($pdo, fn() => insert_returning_id($pdo, "INSERT INTO match (tournament_id, stage_id, team1_id, team2_id, team1_score, team2_score, is_finished) VALUES (?, ?, ?, ?, 0, 0, false) RETURNING id", [$tournamentId, $stageId, $team1Id, $team1Id])));
    $runner->test('Запрет отрицательного счета матча', fn() => expect_database_exception($pdo, fn() => insert_returning_id($pdo, "INSERT INTO match (tournament_id, stage_id, team1_id, team2_id, team1_score, team2_score, is_finished) VALUES (?, ?, ?, ?, -1, 0, false) RETURNING id", [$tournamentId, $stageId, $team1Id, $team2Id])));

    $game1Id = insert_returning_id($pdo, "INSERT INTO matchgame (match_id, map_id, map_name, game_number, winner_team_id) VALUES (?, ?, ?, ?, ?) RETURNING id", [$matchId, $mapId, "{$prefix}_map", 1, $team1Id]);
    $game2Id = insert_returning_id($pdo, "INSERT INTO matchgame (match_id, map_id, map_name, game_number, winner_team_id) VALUES (?, ?, ?, ?, ?) RETURNING id", [$matchId, $mapId, "{$prefix}_map", 2, $team2Id]);
    $game3Id = insert_returning_id($pdo, "INSERT INTO matchgame (match_id, map_id, map_name, game_number, winner_team_id) VALUES (?, ?, ?, ?, ?) RETURNING id", [$matchId, $mapId, "{$prefix}_map", 3, $team1Id]);
    $runner->test('Добавление партий матча', fn() => $runner->assertTrue($game1Id > 0 && $game3Id > 0));
    $runner->test('Запрет повторного номера партии в матче', fn() => expect_database_exception($pdo, fn() => insert_returning_id($pdo, "INSERT INTO matchgame (match_id, map_id, map_name, game_number, winner_team_id) VALUES (?, ?, ?, ?, ?) RETURNING id", [$matchId, $mapId, "{$prefix}_map", 1, $team1Id])));
    $runner->test('Запрет нулевого номера партии', fn() => expect_database_exception($pdo, fn() => insert_returning_id($pdo, "INSERT INTO matchgame (match_id, map_id, map_name, game_number, winner_team_id) VALUES (?, ?, ?, ?, ?) RETURNING id", [$matchId, $mapId, "{$prefix}_map", 0, $team1Id])));

    $upsertStats = $pdo->prepare("
        INSERT INTO playerstats (match_game_id, user_id, kills, deaths)
        VALUES (?, ?, ?, ?)
        ON CONFLICT (match_game_id, user_id)
        DO UPDATE SET kills = EXCLUDED.kills, deaths = EXCLUDED.deaths
    ");
    $upsertStats->execute([$game1Id, $user1Id, 10, 2]);
    $upsertStats->execute([$game1Id, $user2Id, 5, 4]);
    $runner->test('Сохранение статистики игроков по партии', function () use ($runner, $pdo, $game1Id) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM playerstats WHERE match_game_id = ?");
        $stmt->execute([$game1Id]);
        $runner->assertSame(2, (int)$stmt->fetchColumn());
    });
    $upsertStats->execute([$game1Id, $user1Id, 12, 3]);
    $runner->test('Обновление статистики без создания дубля', function () use ($runner, $pdo, $game1Id, $user1Id) {
        $stmt = $pdo->prepare("SELECT COUNT(*), MAX(kills), MAX(deaths) FROM playerstats WHERE match_game_id = ? AND user_id = ?");
        $stmt->execute([$game1Id, $user1Id]);
        $row = $stmt->fetch(PDO::FETCH_NUM);
        $runner->assertSame(1, (int)$row[0]);
        $runner->assertSame(12, (int)$row[1]);
        $runner->assertSame(3, (int)$row[2]);
    });
    $runner->test('Запрет отрицательных убийств в статистике', fn() => expect_database_exception($pdo, fn() => insert_returning_id($pdo, "INSERT INTO playerstats (match_game_id, user_id, kills, deaths) VALUES (?, ?, ?, ?) RETURNING id", [$game2Id, $user1Id, -1, 0])));
    $runner->test('Запрет отрицательных смертей в статистике', fn() => expect_database_exception($pdo, fn() => insert_returning_id($pdo, "INSERT INTO playerstats (match_game_id, user_id, kills, deaths) VALUES (?, ?, ?, ?) RETURNING id", [$game2Id, $user1Id, 0, -1])));

    $pdo->prepare("UPDATE match SET team1_score = 2, team2_score = 1, winner_team_id = ?, is_finished = true, end_time = NOW() WHERE id = ?")->execute([$team1Id, $matchId]);
    $runner->test('Завершение матча и сохранение победителя', function () use ($runner, $pdo, $matchId, $team1Id) {
        $stmt = $pdo->prepare("SELECT team1_score, team2_score, winner_team_id, is_finished FROM match WHERE id = ?");
        $stmt->execute([$matchId]);
        $match = $stmt->fetch();
        $runner->assertSame(2, (int)$match['team1_score']);
        $runner->assertSame(1, (int)$match['team2_score']);
        $runner->assertSame($team1Id, (int)$match['winner_team_id']);
        $runner->assertTrue((bool)$match['is_finished']);
    });

    insert_returning_id($pdo, "INSERT INTO prizedistribution (tournament_id, place, prize_amount) VALUES (?, ?, ?) RETURNING id", [$tournamentId, 1, 7000]);
    insert_returning_id($pdo, "INSERT INTO prizedistribution (tournament_id, place, prize_amount) VALUES (?, ?, ?) RETURNING id", [$tournamentId, 2, 3000]);
    $runner->test('Сохранение распределения призовых', function () use ($runner, $pdo, $tournamentId) {
        $stmt = $pdo->prepare("SELECT SUM(prize_amount) FROM prizedistribution WHERE tournament_id = ?");
        $stmt->execute([$tournamentId]);
        $runner->assertEquals(10000.0, (float)$stmt->fetchColumn());
    });
    $runner->test('Запрет повторного призового места', fn() => expect_database_exception($pdo, fn() => insert_returning_id($pdo, "INSERT INTO prizedistribution (tournament_id, place, prize_amount) VALUES (?, ?, ?) RETURNING id", [$tournamentId, 1, 1])));
    $runner->test('Запрет отрицательной суммы призовых', fn() => expect_database_exception($pdo, fn() => insert_returning_id($pdo, "INSERT INTO prizedistribution (tournament_id, place, prize_amount) VALUES (?, ?, ?) RETURNING id", [$tournamentId, 3, -1])));

    $runner->test('Получение статистики игрока по турниру', function () use ($runner, $pdo, $tournamentId, $user1Id) {
        $stmt = $pdo->prepare("
            SELECT SUM(ps.kills) AS kills, SUM(ps.deaths) AS deaths
            FROM playerstats ps
            JOIN matchgame mg ON mg.id = ps.match_game_id
            JOIN match m ON m.id = mg.match_id
            WHERE m.tournament_id = ? AND ps.user_id = ?
        ");
        $stmt->execute([$tournamentId, $user1Id]);
        $stats = $stmt->fetch();
        $runner->assertSame(12, (int)$stats['kills']);
        $runner->assertSame(3, (int)$stats['deaths']);
    });

    $pdo->rollBack();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $runner->test('Аварийное завершение интеграционных тестов', fn() => throw $e);
}

exit($runner->summary());
