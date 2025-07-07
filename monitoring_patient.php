<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    // Verify request method
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Invalid request method');
    }

    // Check if patient ID is provided
    if (!isset($_GET['id'])) {
        throw new Exception('Patient ID is required');
    }

    $patientId = (int)$_GET['id'];

    // Get patient basic information
    $stmt = $pdo->prepare("
        SELECT 
            u.id,
            u.full_name,
            u.email,
            u.phone_number,
            u.date_of_birth,
            u.gender,
            u.stroke_type,
            u.stroke_severity,
            u.recovery_stage,
            TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) as age,
            u.address,
            u.emergency_contact,
            u.admission_date,
            u.last_assessment_date,
            u.physician_id,
            u.caregiver_id,
            u.profile_image,
            u.additional_notes
        FROM users u
        WHERE u.id = ? AND u.role = 'patient'
    ");
    $stmt->execute([$patientId]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        throw new Exception('Patient not found');
    }

    // Get latest vitals
    $stmt = $pdo->prepare("
        SELECT 
            v.date,
            v.mood,
            v.blood_pressure_systolic,
            v.blood_pressure_diastolic,
            CONCAT(v.blood_pressure_systolic, '/', v.blood_pressure_diastolic) as blood_pressure,
            v.heart_rate,
            v.blood_sugar,
            v.feelings,
            CASE
                WHEN v.blood_pressure_systolic > 140 OR v.blood_pressure_diastolic > 90 THEN 'High'
                WHEN v.blood_pressure_systolic < 90 OR v.blood_pressure_diastolic < 60 THEN 'Low'
                ELSE 'Normal'
            END as bp_status,
            CASE
                WHEN v.heart_rate > 100 THEN 'High'
                WHEN v.heart_rate < 60 THEN 'Low'
                ELSE 'Normal'
            END as hr_status,
            CASE
                WHEN v.blood_sugar > 140 THEN 'High'
                WHEN v.blood_sugar < 70 THEN 'Low'
                ELSE 'Normal'
            END as bs_status
        FROM daily_vitals v
        WHERE v.user_id = ?
        ORDER BY v.date DESC
        LIMIT 1
    ");
    $stmt->execute([$patientId]);
    $latestVitals = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get physician details if available
    $physician = null;
    if ($patient['physician_id']) {
        $stmt = $pdo->prepare("
            SELECT full_name, email, phone_number, specialization 
            FROM users 
            WHERE id = ?
        ");
        $stmt->execute([$patient['physician_id']]);
        $physician = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get caregiver details if available
    $caregiver = null;
    if ($patient['caregiver_id']) {
        $stmt = $pdo->prepare("
            SELECT full_name, email, phone_number, relationship 
            FROM users 
            WHERE id = ?
        ");
        $stmt->execute([$patient['caregiver_id']]);
        $caregiver = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Prepare response data with proper null handling
    $response = [
        'success' => true,
        'patient' => [
            'id' => $patient['id'],
            'full_name' => $patient['full_name'],
            'age' => $patient['age'],
            'gender' => $patient['gender'],
            'stroke_type' => $patient['stroke_type'],
            'stroke_severity' => $patient['stroke_severity'],
            'recovery_stage' => $patient['recovery_stage'],
            'profile_image' => $patient['profile_image'] ? 'uploads/' . $patient['profile_image'] : null,
            'admission_date' => $patient['admission_date'],
            'last_assessment_date' => $patient['last_assessment_date'],
            'additional_notes' => $patient['additional_notes']
        ],
        'vitals' => $latestVitals ? [
            'date' => $latestVitals['date'],
            'mood' => $latestVitals['mood'] ?? 'Not Recorded',
            'blood_pressure' => $latestVitals['blood_pressure'] ?? 'Not Recorded',
            'blood_pressure_systolic' => $latestVitals['blood_pressure_systolic'] ?? null,
            'blood_pressure_diastolic' => $latestVitals['blood_pressure_diastolic'] ?? null,
            'heart_rate' => $latestVitals['heart_rate'] ?? null,
            'blood_sugar' => $latestVitals['blood_sugar'] ?? null,
            'feelings' => $latestVitals['feelings'] ?? null,
            'bp_status' => $latestVitals['bp_status'] ?? 'Not Recorded',
            'hr_status' => $latestVitals['hr_status'] ?? 'Not Recorded',
            'bs_status' => $latestVitals['bs_status'] ?? 'Not Recorded'
        ] : null,
        'physician' => $physician,
        'caregiver' => $caregiver
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => $e->getTraceAsString()
    ]);
}