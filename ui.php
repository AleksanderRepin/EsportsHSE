<?php

function status_label(string $status): string
{
    return [
        'planned' => 'Запланирован',
        'active' => 'Идет',
        'finished' => 'Завершен',
    ][$status] ?? $status;
}

function status_badge_class(string $status): string
{
    return [
        'planned' => 'text-bg-secondary',
        'active' => 'text-bg-primary',
        'finished' => 'text-bg-success',
    ][$status] ?? 'text-bg-secondary';
}

function role_label(string $role): string
{
    return [
        'admin' => 'Администратор',
        'player' => 'Игрок',
    ][$role] ?? $role;
}

function country_options(): array
{
    return [
        'Argentina',
        'Armenia',
        'Azerbaijan',
        'Belarus',
        'Brazil',
        'Canada',
        'China',
        'Czech Republic',
        'Denmark',
        'Finland',
        'France',
        'Georgia',
        'Germany',
        'India',
        'Italy',
        'Japan',
        'Kazakhstan',
        'Kyrgyzstan',
        'Moldova',
        'Netherlands',
        'Norway',
        'Peru',
        'Poland',
        'Romania',
        'Russia',
        'Serbia',
        'South Korea',
        'Spain',
        'Sweden',
        'Tajikistan',
        'Turkey',
        'UAE',
        'UK',
        'USA',
        'Ukraine',
        'United Kingdom',
        'Uzbekistan',
    ];
}

function is_valid_country(string $country): bool
{
    return in_array($country, country_options(), true);
}

function stage_label(string $stage): string
{
    return [
        'Final' => 'Финал',
        'Semifinal' => 'Полуфинал',
        'Quarterfinal' => 'Четвертьфинал',
        'Round of 16' => '1/8 финала',
        'Round of 32' => '1/16 финала',
    ][$stage] ?? $stage;
}

function tournament_completion(PDO $pdo, int $tournamentId, array $teams): array
{
    $issues = [];
    $derivedPlaces = tournament_derived_places($pdo, $tournamentId);

    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total,
               COUNT(*) FILTER (WHERE is_finished) AS finished,
               COUNT(winner_team_id) AS winners
        FROM match
        WHERE tournament_id = ?
    ");
    $stmt->execute([$tournamentId]);
    $matchStats = $stmt->fetch();

    $totalMatches = (int)($matchStats['total'] ?? 0);
    $finishedMatches = (int)($matchStats['finished'] ?? 0);
    $winnerMatches = (int)($matchStats['winners'] ?? 0);

    if ($totalMatches === 0) {
        $issues[] = "Сетка матчей еще не создана.";
    } else {
        if ($finishedMatches < $totalMatches) {
            $issues[] = "Завершены не все матчи: $finishedMatches из $totalMatches.";
        }
        if ($winnerMatches < $totalMatches) {
            $issues[] = "Не у всех матчей выбран победитель: $winnerMatches из $totalMatches.";
        }
    }

    $stmt = $pdo->prepare("
        SELECT m.*, t1.name AS team1_name, t2.name AS team2_name, w.name AS winner_name
        FROM match m
        JOIN tournamentstage s ON s.id = m.stage_id
        JOIN team t1 ON t1.id = m.team1_id
        JOIN team t2 ON t2.id = m.team2_id
        LEFT JOIN team w ON w.id = m.winner_team_id
        WHERE m.tournament_id = ?
        ORDER BY s.stage_order DESC, m.id DESC
        LIMIT 1
    ");
    $stmt->execute([$tournamentId]);
    $finalMatch = $stmt->fetch();

    if (!$finalMatch) {
        $issues[] = "Финальный матч еще не создан.";
    } elseif (!$finalMatch['is_finished'] || !$finalMatch['winner_team_id']) {
        $issues[] = "Финальный матч не завершен или в нем не выбран победитель.";
    }

    if (!array_filter($derivedPlaces, fn($place) => $place['place'] === 1)) {
        $issues[] = "Первое место еще не определяется по матчам.";
    }
    if (!array_filter($derivedPlaces, fn($place) => $place['place'] === 2)) {
        $issues[] = "Второе место еще не определяется по матчам.";
    }

    return [
        'ready' => !$issues,
        'issues' => $issues,
        'total_matches' => $totalMatches,
        'finished_matches' => $finishedMatches,
        'winner_matches' => $winnerMatches,
        'final_match' => $finalMatch,
        'derived_places' => $derivedPlaces,
    ];
}

function tournament_derived_places(PDO $pdo, int $tournamentId): array
{
    $stmt = $pdo->prepare("
        SELECT place, prize_amount
        FROM prizedistribution
        WHERE tournament_id = ?
        ORDER BY place
    ");
    $stmt->execute([$tournamentId]);
    $prizesByPlace = [];
    foreach ($stmt->fetchAll() as $prize) {
        $prizesByPlace[(int)$prize['place']] = (float)$prize['prize_amount'];
    }

    $stmt = $pdo->prepare("
        SELECT
            m.id, m.team1_id, m.team2_id, m.winner_team_id, m.is_finished,
            s.stage_order, s.name AS stage_name,
            t1.name AS team1_name, t2.name AS team2_name, w.name AS winner_name
        FROM match m
        JOIN tournamentstage s ON s.id = m.stage_id
        JOIN team t1 ON t1.id = m.team1_id
        JOIN team t2 ON t2.id = m.team2_id
        LEFT JOIN team w ON w.id = m.winner_team_id
        WHERE m.tournament_id = ?
        ORDER BY s.stage_order DESC, m.id
    ");
    $stmt->execute([$tournamentId]);
    $matches = $stmt->fetchAll();

    if (!$matches) {
        return [];
    }

    $maxStageOrder = max(array_map(fn($match) => (int)$match['stage_order'], $matches));
    $places = [];

    foreach ($matches as $match) {
        if (!$match['is_finished'] || !$match['winner_team_id']) {
            continue;
        }

        $winnerId = (int)$match['winner_team_id'];
        $team1Id = (int)$match['team1_id'];
        $team2Id = (int)$match['team2_id'];
        $loserId = $winnerId === $team1Id ? $team2Id : $team1Id;
        $loserName = $winnerId === $team1Id ? $match['team2_name'] : $match['team1_name'];
        $distanceFromFinal = $maxStageOrder - (int)$match['stage_order'];

        if ($distanceFromFinal === 0) {
            $places[$winnerId] = [
                'team_id' => $winnerId,
                'team_name' => $match['winner_name'],
                'place' => 1,
                'place_end' => 1,
                'place_label' => '1',
                'prize_label' => tournament_prize_label($prizesByPlace, 1, 1),
                'source' => 'Победитель финала',
            ];
            $places[$loserId] = [
                'team_id' => $loserId,
                'team_name' => $loserName,
                'place' => 2,
                'place_end' => 2,
                'place_label' => '2',
                'prize_label' => tournament_prize_label($prizesByPlace, 2, 2),
                'source' => 'Финалист',
            ];
            continue;
        }

        $placeStart = (2 ** $distanceFromFinal) + 1;
        $placeEnd = 2 ** ($distanceFromFinal + 1);
        $places[$loserId] = [
            'team_id' => $loserId,
            'team_name' => $loserName,
            'place' => $placeStart,
            'place_end' => $placeEnd,
            'place_label' => "$placeStart-$placeEnd",
            'prize_label' => tournament_prize_label($prizesByPlace, $placeStart, $placeEnd),
            'source' => 'Выбыла на стадии: ' . stage_label($match['stage_name']),
        ];
    }

    uasort($places, fn($a, $b) => [$a['place'], $a['team_name']] <=> [$b['place'], $b['team_name']]);
    return $places;
}

function tournament_prize_label(array $prizesByPlace, int $placeStart, int $placeEnd): string
{
    $amounts = [];
    for ($place = $placeStart; $place <= $placeEnd; $place++) {
        if (array_key_exists($place, $prizesByPlace)) {
            $amounts[] = $prizesByPlace[$place];
        }
    }

    if (!$amounts) {
        return 'Не указано';
    }

    $min = min($amounts);
    $max = max($amounts);
    if ($min === $max) {
        return number_format($min, 2, '.', ' ');
    }

    return number_format($min, 2, '.', ' ') . ' - ' . number_format($max, 2, '.', ' ');
}
