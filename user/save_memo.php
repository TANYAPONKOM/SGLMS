<?php
// user/save_memo.php
session_start();

/** ==== DEV FLAGS (ปรับได้) ==== */
$DEV_AUTO_LOGIN = true;  // true = ให้ผ่านแม้ยังไม่ล็อกอิน (ตั้ง user_id=1)
$DEBUG_ERRORS   = true;  // true = ส่งข้อความ error ออกมาใน response (ห้ามเปิดใน prod)

if ($DEV_AUTO_LOGIN && empty($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // ไอดีผู้ใช้ทดสอบในตาราง users
}

require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // ต้องมี user_id ใน session (ถ้า DEV_AUTO_LOGIN=false จะต้องล็อกอินจริง)
    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'unauthorized']);
        exit;
    }
    $userId = (int)$_SESSION['user_id'];

    /** ===== รับค่า POST ===== */
    $templateId   = (int)($_POST['template_id']   ?? 1);
    $departmentId = (int)($_POST['department_id'] ?? 1); // ใช้ผูก FK กับ documents.department_id ถ้ามี

    $docDate    = trim($_POST['doc_date']    ?? '');   // YYYY-MM-DD
    $fullname   = trim($_POST['fullname']    ?? '');
    $position   = trim($_POST['position']    ?? '');
    $purpose    = $_POST['purpose']          ?? '';    // academic|training|meeting|other
    $eventTitle = trim($_POST['event_title'] ?? '');

    $dateOption = $_POST['date_option']      ?? '';    // single|range
    $singleDate = trim($_POST['single_date'] ?? '');
    $rangeDate  = trim($_POST['range_date']  ?? '');

    $isOnline   = isset($_POST['is_online']) ? 1 : 0;
    $place      = trim($_POST['place']       ?? '');

    $noCost     = isset($_POST['no_cost']) ? 1 : 0;
    $amountRaw  = str_replace(',', '', trim($_POST['amount'] ?? '0'));
    $amount     = $noCost ? 0.00 : (is_numeric($amountRaw) ? (float)$amountRaw : 0.00);

    $carUsed    = isset($_POST['car_used']) ? 1 : 0;
    $carPlate   = trim($_POST['car_plate'] ?? '');

    // ใหม่: เก็บข้อความของคณะ/ภาควิชา ไว้ใน document_values (field_id 10,11)
    $faculty    = trim($_POST['faculty']    ?? '');
    $department = trim($_POST['department'] ?? '');

    /** ===== ตรวจขั้นต่ำฝั่งเซิร์ฟเวอร์ ให้สอดคล้องกับหน้าเว็บ ===== */
    $errors = [];
    if ($docDate === '')                                $errors['doc_date']    = 'required';
    if ($purpose === '')                                $errors['purpose']     = 'required';
    if ($eventTitle === '')                             $errors['event_title'] = 'required';
    if ($dateOption === 'single' && $singleDate === '') $errors['single_date'] = 'required';
    if ($dateOption === 'range'  && $rangeDate  === '') $errors['range_date']  = 'required';
    if (!$isOnline && $place === '')                    $errors['place']       = 'required';
    if (!$noCost && !is_numeric($amountRaw))            $errors['amount']      = 'number';
    if ($carUsed && $carPlate === '')                   $errors['car_plate']   = 'required';

    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'errors' => $errors]);
        exit;
    }

    /** ===== เขียนฐานข้อมูล ===== */
    $pdo = db();
    $pdo->beginTransaction();

    // 1) สร้างเอกสารใน documents
    $stmt = $pdo->prepare("
        INSERT INTO documents (template_id, owner_id, department_id, doc_no, doc_date, status, remark)
        VALUES (:template_id, :owner_id, :department_id, NULL, :doc_date, 'submitted', NULL)
    ");
    $stmt->execute([
        ':template_id'   => $templateId,
        ':owner_id'      => $userId,
        ':department_id' => $departmentId,
        ':doc_date'      => $docDate,
    ]);
    $documentId = (int)$pdo->lastInsertId();

    // 2) เตรียม map ค่าฟอร์ม -> document_values
    $joinType = match ($purpose) {
        'academic' => 'นำเสนอผลงานทางวิชาการ',
        'training' => 'เข้ารับการฝึกอบรมหลักสูตร',
        'meeting'  => 'เข้าร่วมประชุมวิชาการในงาน',
        default    => 'อื่นๆ',
    };

    $values = [
        1  => $docDate,                                              // doc_date
        2  => $fullname,                                             // owner_name
        3  => $position,                                             // position
        4  => $joinType,                                             // join_type
        5  => $eventTitle,                                           // course_name
        6  => ($dateOption === 'single') ? $singleDate : $rangeDate, // join_date_range
        7  => $isOnline ? 'เข้าร่วมรูปแบบออนไลน์' : $place,         // location
        8  => number_format($amount, 2, '.', ''),                    // total_cost (string)
        9  => $carUsed ? $carPlate : '',                             // vehicle
        10 => $faculty,                                              // faculty (text)
        11 => $department,                                           // department (text)
    ];

    // ✅ แก้ตรงนี้: ตาราง template_fields ใช้คอลัมน์ field_id (ไม่ใช่ id)
    $q = $pdo->prepare("SELECT field_id FROM template_fields WHERE template_id = :tid");
    $q->execute([':tid' => $templateId]);
    $allowIds = array_flip($q->fetchAll(PDO::FETCH_COLUMN)); // คีย์คือ field_id ที่มีจริง

    $ins = $pdo->prepare("
        INSERT INTO document_values (document_id, field_id, value_text)
        VALUES (:document_id, :field_id, :value_text)
        ON DUPLICATE KEY UPDATE value_text = VALUES(value_text)
    ");

    foreach ($values as $fieldId => $val) {
        if (!isset($allowIds[$fieldId])) continue; // ข้าม field ที่ยังไม่ได้ประกาศใน template_fields
        $ins->execute([
            ':document_id' => $documentId,
            ':field_id'    => $fieldId,
            ':value_text'  => $val
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'ok' => true,
        'document_id' => $documentId
    ]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    $res = ['ok' => false, 'error' => 'server'];
    if ($DEBUG_ERRORS) { $res['message'] = $e->getMessage(); }
    echo json_encode($res);
}