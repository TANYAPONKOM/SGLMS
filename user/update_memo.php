<?php
// user/update_memo.php
session_start();

/** DEV */
$DEV_AUTO_LOGIN = true;
$DEBUG_ERRORS = true;
if ($DEV_AUTO_LOGIN && empty($_SESSION['user_id'])) {
  $_SESSION['user_id'] = 1;
}

require_once __DIR__ . '/../functions.php';

try {
  if (empty($_SESSION['user_id'])) {
    header('Location: /form_Memo.html?err=unauthorized', true, 302);
    exit;
  }
  $userId = (int) $_SESSION['user_id'];

  $documentId = (int) ($_POST['document_id'] ?? 0);
  $templateId = (int) ($_POST['template_id'] ?? 1);

  if ($documentId <= 0) {
    header('Location: /form_Memo.html?err=nodoc', true, 302);
    exit;
  }

  // ตรวจสิทธิ์เป็นเจ้าของเอกสาร
  $pdo = db();
  $chk = $pdo->prepare("SELECT owner_id FROM documents WHERE document_id = :id LIMIT 1");
  $chk->execute([':id' => $documentId]);
  $owner = (int) $chk->fetchColumn();
  if ($owner !== $userId) {
    header('Location: /form_Memo.html?err=forbidden', true, 302);
    exit;
  }

  // รับค่า
  $docDate    = trim($_POST['doc_date'] ?? '');
  $fullname   = trim($_POST['fullname'] ?? '');
  $position   = trim($_POST['position'] ?? '');
  $purpose    = $_POST['purpose'] ?? '';
  $eventTitle = trim($_POST['event_title'] ?? '');

  $dateOption = $_POST['date_option'] ?? '';
  $singleDate = trim($_POST['single_date'] ?? '');
  $rangeDate  = trim($_POST['range_date'] ?? '');

  $isOnline   = isset($_POST['is_online']) ? 1 : 0;
  $place      = trim($_POST['place'] ?? '');

  $noCost     = isset($_POST['no_cost']) ? 1 : 0;
  $amountRaw  = str_replace(',', '', trim($_POST['amount'] ?? '0'));
  $amount     = $noCost ? 0.00 : (is_numeric($amountRaw) ? (float)$amountRaw : 0.00);

  $carUsed    = isset($_POST['car_used']) ? 1 : 0;
  $carPlate   = trim($_POST['car_plate'] ?? '');

  $faculty    = trim($_POST['faculty'] ?? '');
  $department = trim($_POST['department'] ?? '');
  $headerText = trim($_POST['header_text'] ?? ''); // ✅ ใช้ค่าที่กรอกมาโดยตรง
$docNo       = trim($_POST['doc_no'] ?? '');
$docDateDisp = trim($_POST['doc_date_display'] ?? ''); 

  // ตรวจขั้นต่ำ
  $errors = [];
  if ($docDate === '') $errors['doc_date'] = 'required';
  if ($purpose === '') $errors['purpose'] = 'required';
  if ($eventTitle === '') $errors['event_title'] = 'required';
  if ($dateOption === 'single' && $singleDate === '') $errors['single_date'] = 'required';
  if ($dateOption === 'range' && $rangeDate === '') $errors['range_date'] = 'required';
  if (!$isOnline && $place === '') $errors['place'] = 'required';
  if (!$noCost && !is_numeric($amountRaw)) $errors['amount'] = 'number';
  if ($carUsed && $carPlate === '') $errors['car_plate'] = 'required';

  if (!empty($errors)) {
    header('Location: edit_document.php?id=' . $documentId . '&err=validate');
    exit;
  }

  $pdo->beginTransaction();

  // อัปเดตหัวเอกสาร
  $joinType = match ($purpose) {
      'academic' => 'นำเสนอผลงานทางวิชาการ',
      'training' => 'เข้ารับการฝึกอบรมหลักสูตร',
      'meeting'  => 'เข้าร่วมประชุมวิชาการในงาน',
      default    => 'อื่นๆ',
  };
  $subject = trim($joinType . $eventTitle);

 $up = $pdo->prepare("
    UPDATE documents 
    SET doc_no = :doc_no,
        doc_date = :doc_date,
        subject = :subject,
        header_text = :header_text,
        updated_at = NOW() 
    WHERE document_id = :id
");
$up->execute([
    ':doc_no'     => $docNo,
    ':doc_date'   => $docDate,
    ':subject'    => $subject,
    ':header_text'=> $headerText,
    ':id'         => $documentId
]);

  // ค่า field อื่น ๆ
  $values = [ 
    1 => $docDate,
    2 => $fullname,
    3 => $position,
    4 => $joinType,
    5 => $eventTitle,
    6 => ($dateOption === 'single') ? $singleDate : $rangeDate,
    7 => $isOnline ? 'เข้าร่วมรูปแบบออนไลน์' : $place,
    8 => number_format($amount, 2, '.', ''),
    9 => $carUsed ? $carPlate : '',
    10 => $faculty,
    11 => $department
    // ❌ ไม่ต้องมี phone แยกแล้ว
  ];

  $q = $pdo->prepare("SELECT field_id FROM template_fields WHERE template_id = :tid");
  $q->execute([':tid' => $templateId]);
  $allowIds = array_flip($q->fetchAll(PDO::FETCH_COLUMN));

  $ins = $pdo->prepare("
        INSERT INTO document_values (document_id, field_id, value_text)
        VALUES (:document_id, :field_id, :value_text)
        ON DUPLICATE KEY UPDATE value_text = VALUES(value_text)
  ");
  foreach ($values as $fieldId => $val) {
    if (!isset($allowIds[$fieldId])) continue;
    $ins->execute([
      ':document_id' => $documentId,
      ':field_id'    => $fieldId,
      ':value_text'  => $val
    ]);
  }

  $pdo->commit();

  header('Location: edit_document.php?id=' . $documentId . '&saved=1&from=update', true, 302);
exit;


} catch (Throwable $e) {
  if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
  if ($DEBUG_ERRORS) {
    echo 'server error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
  } else {
    header('Location: edit_document.php?id=' . $documentId .  '&err=server', true, 302);
  }
}