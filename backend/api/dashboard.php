<?php

require_once '../config.php';
requireAuth();

$userId = $_SESSION['user_id'];
$latestStmt = $pdo->prepare("
    SELECT 
        lt.name AS test_name,
        lt.unit,
        lr.value,
        lr.date_recorded,
        i.status,
        i.status_label
    FROM lab_results lr
    JOIN lab_tests lt ON lr.test_id = lt.id
    LEFT JOIN interpretations i ON lr.id = i.result_id
    WHERE lr.user_id = ?
      AND lr.date_recorded = (
          SELECT MAX(date_recorded) 
          FROM lab_results lr2 
          WHERE lr2.user_id = lr.user_id AND lr2.test_id = lr.test_id
      )
    ORDER BY lt.name
");
$latestStmt->execute([$userId]);
$latest = $latestStmt->fetchAll();
$trendStmt = $pdo->prepare("
    SELECT 
        lt.name AS test_name,
        lt.unit,
        lr.value,
        lr.date_recorded
    FROM lab_results lr
    JOIN lab_tests lt ON lr.test_id = lt.id
    WHERE lr.user_id = ?
    ORDER BY lt.name, lr.date_recorded ASC
");
$trendStmt->execute([$userId]);
$allResults = $trendStmt->fetchAll();
$trends = [];
foreach ($allResults as $row) {
    $name = $row['test_name'];
    if (!isset($trends[$name])) {
        $trends[$name] = [
            'unit' => $row['unit'],
            'data' => []
        ];
    }
    $trends[$name]['data'][] = [
        'date' => $row['date_recorded'],
        'value' => (float)$row['value']
    ];
}
$statsStmt = $pdo->prepare("
    SELECT 
        COUNT(*) AS total_records,
        COUNT(DISTINCT test_id) AS tests_tracked,
        MIN(date_recorded) AS first_record,
        MAX(date_recorded) AS last_record
    FROM lab_results
    WHERE user_id = ?
");
$statsStmt->execute([$userId]);
$stats = $statsStmt->fetch();
$statusStmt = $pdo->prepare("
    SELECT i.status, COUNT(*) AS count
    FROM lab_results lr
    JOIN interpretations i ON lr.id = i.result_id
    WHERE lr.user_id = ?
    GROUP BY i.status
");
$statusStmt->execute([$userId]);
$statusBreakdown = $statusStmt->fetchAll();
$historyStmt = $pdo->prepare("
    SELECT 
        lr.id,
        lr.value,
        lr.date_recorded,
        lt.name AS test_name,
        lt.unit,
        i.status,
        i.status_label
    FROM lab_results lr
    JOIN lab_tests lt ON lr.test_id = lt.id
    LEFT JOIN interpretations i ON lr.id = i.result_id
    WHERE lr.user_id = ?
    ORDER BY lr.date_recorded DESC, lr.id DESC
    LIMIT 10
");
$historyStmt->execute([$userId]);
$recentHistory = $historyStmt->fetchAll();

sendResponse(true, [
    'latest_values'     => $latest,
    'trends'            => $trends,
    'statistics'        => $stats,
    'status_breakdown'  => $statusBreakdown,
    'recent_history'    => $recentHistory
]);