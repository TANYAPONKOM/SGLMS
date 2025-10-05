<?php
session_start();
require_once __DIR__ . '/../functions.php';
$pdo = getPDO();

$userId = $_SESSION['user_id'] ?? 0;
$roleId = $_SESSION['role_id'] ?? 0;

// Officer เห็นทุกเอกสาร
if ($roleId == 2) {
  $sql = "
    SELECT 
      d.document_id,
      d.doc_date,
      d.status,
      MAX(CASE WHEN f.field_key = 'join_type' THEN v.value_text END) AS join_type,
      MAX(CASE WHEN f.field_key = 'course_name' THEN v.value_text END) AS course_name
    FROM documents d
    LEFT JOIN document_values v ON d.document_id = v.document_id
    LEFT JOIN template_fields f ON v.field_id = f.field_id
    WHERE d.status IN ('submitted', 'approved', 'rejected')
    GROUP BY d.document_id, d.doc_date, d.status
    ORDER BY d.created_at DESC
  ";
  $stmt = $pdo->query($sql);

} else { // User เห็นเฉพาะของตัวเอง
  $sql = "
    SELECT 
      d.document_id,
      d.doc_date,
      d.status,
      MAX(CASE WHEN f.field_key = 'join_type' THEN v.value_text END) AS join_type,
      MAX(CASE WHEN f.field_key = 'course_name' THEN v.value_text END) AS course_name
    FROM documents d
    LEFT JOIN document_values v ON d.document_id = v.document_id
    LEFT JOIN template_fields f ON v.field_id = f.field_id
    WHERE d.owner_id = :u
      AND d.status IN ('submitted', 'approved', 'rejected')
    GROUP BY d.document_id, d.doc_date, d.status
    ORDER BY d.created_at DESC
  ";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([':u' => $userId]);
}

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
header('Content-Type: application/json; charset=utf-8');
echo json_encode($rows, JSON_UNESCAPED_UNICODE);