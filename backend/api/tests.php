<?php

require_once '../config.php';

$testId = $_GET['id'] ?? null;
$categoryId = $_GET['category'] ?? null;

if ($testId) {
    $stmt = $pdo->prepare("
        SELECT 
            lt.id, lt.name, lt.unit, lt.who_min, lt.who_max, lt.who_optimal,
            lt.description, lt.warning_low, lt.warning_high,
            tc.name AS category_name, tc.icon
        FROM lab_tests lt
        LEFT JOIN test_categories tc ON lt.category_id = tc.id
        WHERE lt.id = ?
    ");
    $stmt->execute([$testId]);
    $test = $stmt->fetch();

    if (!$test) {
        sendResponse(false, 'Test not found.', 404);
    }
    sendResponse(true, $test);

} else {
    $sql = "
        SELECT 
            lt.id, lt.name, lt.unit, lt.who_min, lt.who_max, lt.who_optimal,
            lt.description, lt.warning_low, lt.warning_high,
            tc.name AS category_name, tc.icon
        FROM lab_tests lt
        LEFT JOIN test_categories tc ON lt.category_id = tc.id
    ";
    $params = [];

    if ($categoryId) {
        $sql .= " WHERE lt.category_id = ?";
        $params[] = (int)$categoryId;
    }

    $sql .= " ORDER BY tc.name, lt.name";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tests = $stmt->fetchAll();

    sendResponse(true, $tests);
}