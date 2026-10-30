<?php

require_once '../config.php';
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        $stmt = $pdo->prepare("
            SELECT age, gender, height_cm, weight_kg, updated_at
            FROM user_profiles
            WHERE user_id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $profile = $stmt->fetch();

        if (!$profile) {
            sendResponse(false, 'Profile not found.', 404);
        }
        if ($profile['height_cm'] && $profile['weight_kg']) {
            $heightM = $profile['height_cm'] / 100;
            $profile['bmi'] = round($profile['weight_kg'] / ($heightM * $heightM), 1);
        }

        sendResponse(true, $profile);
        break;

    case 'PUT':
        $body = getJsonBody();

        $fields = [];
        $params = [];

        if (isset($body['age'])) {
            $fields[] = 'age = ?';
            $params[] = (int)$body['age'];
        }
        if (isset($body['gender'])) {
            $fields[] = 'gender = ?';
            $params[] = $body['gender'];
        }
        if (isset($body['height_cm'])) {
            $fields[] = 'height_cm = ?';
            $params[] = (float)$body['height_cm'];
        }
        if (isset($body['weight_kg'])) {
            $fields[] = 'weight_kg = ?';
            $params[] = (float)$body['weight_kg'];
        }

        if (empty($fields)) {
            sendResponse(false, 'No fields to update.', 400);
        }

        $params[] = $_SESSION['user_id'];
        $sql = "UPDATE user_profiles SET " . implode(', ', $fields) . " WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        sendResponse(true, 'Profile updated successfully.');
        break;

    default:
        sendResponse(false, 'Method not allowed.', 405);
}