<?php

require_once '../config.php';
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$resultId = $_GET['id'] ?? null;

switch ($method) {
    case 'GET':
        if ($resultId) {
            $stmt = $pdo->prepare("
                SELECT 
                    lr.id, lr.value, lr.date_recorded, lr.notes, lr.created_at,
                    lt.name AS test_name, lt.unit, lt.who_min, lt.who_max,
                    i.status, i.status_label, i.message, i.recommendation
                FROM lab_results lr
                JOIN lab_tests lt ON lr.test_id = lt.id
                LEFT JOIN interpretations i ON lr.id = i.result_id
                WHERE lr.id = ? AND lr.user_id = ?
            ");
            $stmt->execute([$resultId, $_SESSION['user_id']]);
            $result = $stmt->fetch();

            if (!$result) {
                sendResponse(false, 'Result not found.', 404);
            }
            sendResponse(true, $result);

        } else {
            $stmt = $pdo->prepare("
                SELECT 
                    lr.id, lr.value, lr.date_recorded, lr.notes,
                    lt.name AS test_name, lt.unit,
                    i.status, i.status_label
                FROM lab_results lr
                JOIN lab_tests lt ON lr.test_id = lt.id
                LEFT JOIN interpretations i ON lr.id = i.result_id
                WHERE lr.user_id = ?
                ORDER BY lr.date_recorded DESC, lr.id DESC
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $results = $stmt->fetchAll();

            sendResponse(true, $results);
        }
        break;
    case 'POST':
        $body = getJsonBody();
        if (empty($body['test_id']) || !isset($body['value']) || empty($body['date_recorded'])) {
            sendResponse(false, 'test_id, value, and date_recorded are required.', 400);
        }

        $testId = (int)$body['test_id'];
        $value = (float)$body['value'];
        $date = $body['date_recorded'];
        $notes = $body['notes'] ?? '';
        $testStmt = $pdo->prepare("SELECT * FROM lab_tests WHERE id = ?");
        $testStmt->execute([$testId]);
        $test = $testStmt->fetch();

        if (!$test) {
            sendResponse(false, 'Invalid test ID.', 400);
        }
        $stmt = $pdo->prepare("
            INSERT INTO lab_results (user_id, test_id, value, date_recorded, notes)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$_SESSION['user_id'], $testId, $value, $date, $notes]);
        $newResultId = $pdo->lastInsertId();
        $interpretation = generateInterpretation($value, $test);

        $interpStmt = $pdo->prepare("
            INSERT INTO interpretations (result_id, status, status_label, message, recommendation)
            VALUES (?, ?, ?, ?, ?)
        ");
        $interpStmt->execute([
            $newResultId,
            $interpretation['status'],
            $interpretation['status_label'],
            $interpretation['message'],
            $interpretation['recommendation']
        ]);

        sendResponse(true, [
            'result_id'       => $newResultId,
            'interpretation'  => $interpretation,
            'message'         => 'Lab result saved and decoded successfully!'
        ], 201);
        break;
    case 'PUT':
        if (!$resultId) {
            sendResponse(false, 'Result ID required for update.', 400);
        }

        $body = getJsonBody();
        $check = $pdo->prepare("SELECT id, test_id FROM lab_results WHERE id = ? AND user_id = ?");
        $check->execute([$resultId, $_SESSION['user_id']]);
        $existing = $check->fetch();

        if (!$existing) {
            sendResponse(false, 'Result not found or access denied.', 404);
        }
        $fields = [];
        $params = [];

        if (isset($body['value'])) {
            $fields[] = 'value = ?';
            $params[] = (float)$body['value'];
        }
        if (!empty($body['date_recorded'])) {
            $fields[] = 'date_recorded = ?';
            $params[] = $body['date_recorded'];
        }
        if (isset($body['notes'])) {
            $fields[] = 'notes = ?';
            $params[] = $body['notes'];
        }

        if (empty($fields)) {
            sendResponse(false, 'No fields to update.', 400);
        }

        $params[] = $resultId;
        $sql = "UPDATE lab_results SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        if (isset($body['value'])) {
            $testStmt = $pdo->prepare("SELECT * FROM lab_tests WHERE id = ?");
            $testStmt->execute([$existing['test_id']]);
            $test = $testStmt->fetch();

            $interpretation = generateInterpretation((float)$body['value'], $test);

            $interpStmt = $pdo->prepare("
                UPDATE interpretations 
                SET status = ?, status_label = ?, message = ?, recommendation = ?
                WHERE result_id = ?
            ");
            $interpStmt->execute([
                $interpretation['status'],
                $interpretation['status_label'],
                $interpretation['message'],
                $interpretation['recommendation'],
                $resultId
            ]);
        }

        sendResponse(true, 'Result updated successfully.');
        break;
    case 'DELETE':
        if (!$resultId) {
            sendResponse(false, 'Result ID required for deletion.', 400);
        }
        $check = $pdo->prepare("SELECT id FROM lab_results WHERE id = ? AND user_id = ?");
        $check->execute([$resultId, $_SESSION['user_id']]);

        if (!$check->fetch()) {
            sendResponse(false, 'Result not found or access denied.', 404);
        }
        $stmt = $pdo->prepare("DELETE FROM lab_results WHERE id = ?");
        $stmt->execute([$resultId]);

        sendResponse(true, 'Result deleted successfully.');
        break;

    default:
        sendResponse(false, 'Method not allowed.', 405);
}
function generateInterpretation(float $value, array $test): array {
    $name = $test['name'];
    $unit = $test['unit'];
    $min = (float)$test['who_min'];
    $max = (float)$test['who_max'];
    $warnLow = $test['warning_low'] ? (float)$test['warning_low'] : null;
    $warnHigh = $test['warning_high'] ? (float)$test['warning_high'] : null;
    if ($warnLow !== null && $value < $warnLow) {
        $status = 'critical';
        $statusLabel = 'Critically Low';
    } elseif ($warnHigh !== null && $value > $warnHigh) {
        $status = 'critical';
        $statusLabel = 'Critically High';
    } elseif ($value < $min) {
        $status = 'low';
        $statusLabel = 'Below Normal';
    } elseif ($value > $max) {
        $status = 'high';
        $statusLabel = 'Above Normal';
    } elseif ($value >= $min && $value <= $max) {
        $range = $max - $min;
        $buffer = $range * 0.1;
        if ($value <= $min + $buffer || $value >= $max - $buffer) {
            $status = 'borderline';
            $statusLabel = 'Borderline';
        } else {
            $status = 'normal';
            $statusLabel = 'Normal';
        }
    } else {
        $status = 'normal';
        $statusLabel = 'Normal';
    }
    $message = generateMessage($name, $value, $unit, $status, $min, $max);
    $recommendation = generateRecommendation($name, $status);

    return [
        'status'         => $status,
        'status_label'   => $statusLabel,
        'message'        => $message,
        'recommendation' => $recommendation
    ];
}

function generateMessage(string $name, float $value, string $unit, string $status, float $min, float $max): string {
    $rangeText = "The WHO normal range is {$min}–{$max} {$unit}.";

    switch ($status) {
        case 'normal':
            return "Your {$name} of {$value} {$unit} is within the healthy range. {$rangeText} Keep up your current lifestyle habits.";
        case 'borderline':
            return "Your {$name} of {$value} {$unit} is at the edge of the normal range. {$rangeText} Small lifestyle adjustments may help bring it to optimal levels.";
        case 'low':
            return "Your {$name} of {$value} {$unit} is below the recommended range. {$rangeText} This may indicate a deficiency or underlying condition that warrants attention.";
        case 'high':
            return "Your {$name} of {$value} {$unit} is above the healthy range. {$rangeText} Elevated levels increase your risk for related health complications if left unaddressed.";
        case 'critical':
            return "Your {$name} of {$value} {$unit} is critically outside the safe range. {$rangeText} This requires immediate medical attention. Please consult a healthcare professional as soon as possible.";
        default:
            return "Your {$name} is {$value} {$unit}. {$rangeText}";
    }
}

function generateRecommendation(string $name, string $status): string {
    $recommendations = [
        'Fasting Blood Glucose' => [
            'normal'     => 'Maintain a balanced diet with moderate carbohydrate intake. Continue regular physical activity. Re-test annually.',
            'borderline' => 'Reduce refined sugars and processed carbohydrates. Increase fiber intake. Consider 30 minutes of daily walking. Re-test in 3 months.',
            'low'        => 'Eat small, frequent meals. Include complex carbs and avoid skipping meals. Consult a doctor to rule out hypoglycemia.',
            'high'       => 'Adopt a low-glycemic diet. Aim for 150+ minutes of exercise weekly. Monitor blood sugar regularly. Consult a doctor about pre-diabetes screening.',
            'critical'   => 'Seek immediate medical attention. Uncontrolled blood glucose can lead to diabetic ketoacidosis or hyperosmolar states.'
        ],
        'Systolic Blood Pressure' => [
            'normal'     => 'Maintain heart-healthy habits: regular exercise, low sodium diet, and stress management.',
            'borderline' => 'Reduce sodium intake to under 2g/day. Limit caffeine and alcohol. Practice relaxation techniques. Monitor weekly.',
            'low'        => 'Stay hydrated. Rise slowly from sitting/lying positions. Increase salt intake slightly if advised by a doctor.',
            'high'       => 'Reduce salt, limit alcohol, and exercise regularly. Manage stress through meditation or yoga. Consult a doctor if readings persist.',
            'critical'   => 'This is a hypertensive crisis. Seek emergency medical care immediately. Do not wait.'
        ],
        'Diastolic Blood Pressure' => [
            'normal'     => 'Maintain heart-healthy habits: regular exercise, low sodium diet, and stress management.',
            'borderline' => 'Reduce sodium intake to under 2g/day. Limit caffeine and alcohol. Practice relaxation techniques. Monitor weekly.',
            'low'        => 'Stay hydrated. Rise slowly from sitting/lying positions. Increase salt intake slightly if advised by a doctor.',
            'high'       => 'Reduce salt, limit alcohol, and exercise regularly. Manage stress through meditation or yoga. Consult a doctor if readings persist.',
            'critical'   => 'This is a hypertensive crisis. Seek emergency medical care immediately. Do not wait.'
        ],
        'Total Cholesterol' => [
            'normal'     => 'Continue eating healthy fats, fiber-rich foods, and avoiding trans fats. Re-test annually.',
            'borderline' => 'Reduce saturated fats (red meat, full-fat dairy). Increase soluble fiber (oats, beans). Consider plant sterols.',
            'low'        => 'Ensure adequate healthy fat intake (avocado, nuts, olive oil). Very low cholesterol may indicate other issues — consult a doctor.',
            'high'       => 'Adopt a Mediterranean-style diet. Increase physical activity. Limit saturated and trans fats. Consult a doctor about statin therapy.',
            'critical'   => 'Very high cholesterol significantly increases heart attack and stroke risk. See a doctor urgently for medication and lifestyle plan.'
        ],
        'LDL Cholesterol' => [
            'normal'     => 'Continue heart-healthy habits. LDL (bad cholesterol) is well controlled.',
            'borderline' => 'Reduce saturated fat intake. Add foods rich in soluble fiber. Consider omega-3 supplements.',
            'low'        => 'Low LDL is generally protective. Ensure you still get enough healthy fats for hormone production.',
            'high'       => 'Strictly limit saturated fats and trans fats. Increase exercise. Consider medication if lifestyle changes are insufficient.',
            'critical'   => 'Very high LDL poses severe cardiovascular risk. Immediate medical intervention and likely medication required.'
        ],
        'HDL Cholesterol' => [
            'normal'     => 'Your HDL (good cholesterol) is in a healthy range. Continue regular exercise and moderate alcohol if applicable.',
            'borderline' => 'Increase aerobic exercise. Quit smoking if applicable. Include healthy fats like olive oil and fatty fish.',
            'low'        => 'Low HDL increases heart disease risk. Exercise regularly, quit smoking, and consume healthy fats. Consult a doctor.',
            'high'       => 'High HDL is protective. Continue your current lifestyle — this is excellent for heart health.',
            'critical'   => 'Extremely high HDL is rare but may indicate other conditions. Consult a doctor for evaluation.'
        ],
        'Body Mass Index' => [
            'normal'     => 'Your BMI is in the healthy range. Maintain balanced nutrition and regular physical activity.',
            'borderline' => 'Monitor portion sizes and increase daily activity. Small changes prevent progression to overweight.',
            'low'        => 'You may be underweight. Focus on nutrient-dense foods and strength training. Consult a nutritionist.',
            'high'       => 'Aim for gradual weight loss (0.5–1 kg/week) through calorie deficit and 150+ minutes of exercise weekly.',
            'critical'   => 'Severe obesity significantly impacts health. Consult a doctor for a comprehensive weight management plan.'
        ],
        'HbA1c' => [
            'normal'     => 'Your long-term glucose control is excellent. Continue current habits.',
            'borderline' => 'Pre-diabetes range. Reduce sugar and refined carbs. Increase exercise. Re-test in 3 months.',
            'low'        => 'Low HbA1c may indicate frequent hypoglycemia. Discuss with your doctor.',
            'high'       => 'Diabetes range. Strict glucose monitoring, dietary changes, and medical consultation essential.',
            'critical'   => 'Severely elevated HbA1c indicates poor glucose control. Immediate medical intervention required.'
        ]
    ];
    $defaultRecs = [
        'normal'     => 'Continue your current healthy lifestyle. Re-test as recommended by your doctor.',
        'borderline' => 'Make small lifestyle improvements and re-test in 3 months.',
        'low'        => 'Consult a healthcare professional for personalized guidance.',
        'high'       => 'Lifestyle changes are recommended. Consult a doctor for a personalized plan.',
        'critical'   => 'Seek immediate medical attention.'
    ];

    if (isset($recommendations[$name][$status])) {
        return $recommendations[$name][$status];
    }
    return $defaultRecs[$status] ?? $defaultRecs['normal'];
}