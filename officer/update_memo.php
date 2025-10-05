<?php
// officer/update_memo.php
session_start();

$DEV_AUTO_LOGIN = true;
$DEBUG_ERRORS = true;

if ($DEV_AUTO_LOGIN && empty($_SESSION['user_id'])) {
  $_SESSION['user_id'] = 2;
  $_SESSION['role_id'] = 2;   // Officer
  $_SESSION['perm_id'] = 3;   // มีสิทธิ์แก้ไข
}

require_once __DIR__ . '/../functions.php';

try {
  if (empty($_SESSION['user_id'])) {
    header('Location: ../login.html?err=unauthorized', true, 302);
    exit;
  }

  $userId = (int) $_SESSION['user_id'];
  $roleId = $_SESSION['role_id'] ?? 0;

  $documentId = (int) ($_POST['document_id'] ?? 0);
  $templateId = (int) ($_POST['template_id'] ?? 1);

  if ($documentId <= 0) {
    header('Location: edit_document.php?err=nodoc', true, 302);
    exit;
  }

  $pdo = db();

  // ตรวจว่ามีเอกสาร
  $stmt = $pdo->prepare("SELECT owner_id, status FROM documents WHERE document_id = :id LIMIT 1");
  $stmt->execute([':id' => $documentId]);
  $docInfo = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$docInfo) {
    header('Location: edit_document.php?err=notfound', true, 302);
    exit;
  }

  if ($roleId != 2) { // ต้องเป็น Officer
    header('Location: edit_document.php?id='.$documentId.'&err=forbidden', true, 302);
    exit;
  }

  // รับค่าจากฟอร์ม
  $docDate     = trim($_POST['doc_date'] ?? '');
  $fullname    = trim($_POST['fullname'] ?? '');
  $position    = trim($_POST['position'] ?? '');
  $purpose     = $_POST['purpose'] ?? '';
  $eventTitle  = trim($_POST['event_title'] ?? '');
  $dateOption  = $_POST['date_option'] ?? 'range';
  $singleDate  = trim($_POST['single_date'] ?? '');
  $rangeDate   = trim($_POST['range_date'] ?? '');
  $isOnline    = isset($_POST['is_online']) ? 1 : 0;
  $place       = trim($_POST['place'] ?? '');
  $noCost      = isset($_POST['no_cost']) ? 1 : 0;

  // แปลงจำนวนเงินอย่างยืดหยุ่น
  $amountRaw = str_replace(',', '', trim($_POST['amount'] ?? ''));
  if ($amountRaw === '') $amountRaw = '0';
  $amount = (is_numeric($amountRaw) ? (float)$amountRaw : 0.00);

  $carUsed    = isset($_POST['car_used']) ? 1 : 0;
  $carPlate   = trim($_POST['car_plate'] ?? '');
  $faculty    = trim($_POST['faculty'] ?? '');
  $department = trim($_POST['department'] ?? '');
  $headerText = trim($_POST['header_text'] ?? '');
  $docNo      = trim($_POST['doc_no'] ?? '');

  // ตรวจค่าขั้นต่ำ (ผ่อนคลายให้ officer แก้ไขได้)
  $errors = [];
  if ($docDate === '') $errors['doc_date'] = 'required';
  if ($eventTitle === '') $errors['event_title'] = 'required';
  if ($dateOption === 'single' && $singleDate === '') $errors['single_date'] = 'required';
  if ($dateOption === 'range'  && $rangeDate  === '') $errors['range_date']  = 'required';
  if (!$isOnline && $place === '') $errors['place'] = 'required';
  if ($carUsed && $carPlate === '') $errors['car_plate'] = 'required';

  if (!empty($errors)) {
    header('Location: edit_document.php?id=' . $documentId . '&err=validate');
    exit;
  }

  // map purpose -> joinType (ข้อความไทย)
  $joinType = match ($purpose) {
    'academic' => 'นำเสนอผลงานทางวิชาการ',
    'training' => 'เข้ารับการฝึกอบรมหลักสูตร',
    'meeting'  => 'เข้าร่วมประชุมวิชาการในงาน',
    default    => 'อื่นๆ',
  };

  // สร้าง subject อ่านง่ายขึ้น
  $subject = trim($joinType . ' ' . $eventTitle);

  // ✅ สถานะใหม่ ต้องกำหนดก่อนใช้ใน UPDATE
  $newStatus = 'approved'; // Officer กดบันทึก = อนุมัติทันที

  $pdo->beginTransaction();

  // อัปเดตตาราง documents
  $up = $pdo->prepare("
    UPDATE documents 
    SET doc_no = :doc_no,
        doc_date = :doc_date,
        subject = :subject,
        header_text = :header_text,
        status = :status,
        approved_by = :officer,
        updated_at = NOW()
    WHERE document_id = :id
  ");
  $up->execute([
    ':doc_no'     => $docNo,
    ':doc_date'   => $docDate,
    ':subject'    => $subject,
    ':header_text'=> $headerText,
    ':status'     => $newStatus,
    ':officer'    => $userId,
    ':id'         => $documentId
  ]);

  // อัปเดตค่า field ต่าง ๆ (respect template_fields)
  $values = [
    1  => $docDate,
    2  => $fullname,
    3  => $position,
    4  => $joinType,
    5  => $eventTitle,
    6  => ($dateOption === 'single') ? $singleDate : $rangeDate,
    7  => $isOnline ? 'เข้าร่วมรูปแบบออนไลน์' : $place,
    8  => number_format($amount, 2, '.', ''),
    9  => $carUsed ? $carPlate : '',
    10 => $faculty,
    11 => $department
  ];

  $q = $pdo->prepare("SELECT field_id FROM template_fields WHERE template_id = :tid");
  $q->execute([':tid' => $templateId]);
  $allowIds = array_flip($q->fetchAll(PDO::FETCH_COLUMN));

  $ins = $pdo->prepare("
    INSERT INTO document_values (document_id, field_id, value_text)
    VALUES (:document_id, :field_id, :value_text)
    ON DUPLICATE KEY UPDATE value_text = VALUES(value_text)
  ");

  foreach ($values as $fid => $val) {
    if (!isset($allowIds[$fid])) continue;
    $ins->execute([
      ':document_id' => $documentId,
      ':field_id'    => $fid,
      ':value_text'  => $val
    ]);
  }

  error_log("Officer updated memo: doc_id={$documentId}, template={$templateId}, status={$newStatus}");

  $pdo->commit();

  header('Location: edit_document.php?id=' . $documentId . '&saved=1&autoApproved=1');
  exit;

} catch (Throwable $e) {
  if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
  if ($DEBUG_ERRORS) {
    echo 'server error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
  } else {
    header('Location: edit_document.php?id=' . ($documentId ?? 0) . '&err=server', true, 302);
  }
}