<?php // user/edit_document.php 
session_start(); /** DEV: auto login ระหว่างพัฒนา */
$DEV_AUTO_LOGIN = true;
if ($DEV_AUTO_LOGIN && empty($_SESSION['user_id'])) {
  $_SESSION['user_id'] = 1;
}
require_once __DIR__
  . '/../functions.php';
if (empty($_SESSION['user_id'])) {
  http_response_code(401);
  echo 'Unauthorized';
  exit;
}
$userId = (int) $_SESSION['user_id'];
function h($s)
{
  return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}
function thai_date($ymd)
{
  if (!$ymd || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $ymd))
    return '';

  [$y, $m, $d] = explode('-', $ymd);   // ✅ แก้แล้ว
  $m = (int) $m;
  $d = (int) $d;
  $y = (int) $y + 543;

  $thMonths = [
    1 => 'มกราคม',
    'กุมภาพันธ์',
    'มีนาคม',
    'เมษายน',
    'พฤษภาคม',
    'มิถุนายน',
    'กรกฎาคม',
    'สิงหาคม',
    'กันยายน',
    'ตุลาคม',
    'พฤศจิกายน',
    'ธันวาคม'
  ];

  return $d . ' ' . $thMonths[$m] . ' ' . $y;
}


$pdo = db();

/** รับ id เอกสาร; ถ้าไม่ส่งมาให้หยิบของล่าสุดของ user */
$docId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($docId <= 0) {
  $q = $pdo->prepare("SELECT document_id FROM documents WHERE owner_id = :u ORDER BY document_id DESC
    LIMIT 1");
  $q->execute([':u' => $userId]);
  $docId = (int) ($q->fetchColumn() ?: 0);
  if ($docId <= 0) {
    echo 'ยังไม่มีเอกสารของคุณ';
    exit;
  }
} /** ดึงหัวเอกสาร */
$doc = $pdo->prepare("SELECT
      document_id, template_id, owner_id, department_id, doc_no, doc_date, subject, status
      FROM documents
      WHERE document_id = :id AND owner_id = :u
      LIMIT 1");
$doc->execute([':id' => $docId, ':u' => $userId]);
$document = $doc->fetch(PDO::FETCH_ASSOC);

if (!$document) {
  echo 'ไม่พบเอกสาร หรือไม่มีสิทธิ์เข้าถึง';
  exit;
}

/** ดึงค่ารายช่อง */
$vals = $pdo->prepare("SELECT field_id, value_text FROM document_values WHERE document_id = :id");
$vals->execute([':id' => $docId]);
$valueMap = [];
foreach ($vals->fetchAll(PDO::FETCH_ASSOC) as $r) {
  $valueMap[(int) $r['field_id']] = (string) $r['value_text'];
}

/** map ค่า */
$docDate = $valueMap[1] ?? ($document['doc_date'] ?? '');
$ownerName = $valueMap[2] ?? '';
$position = $valueMap[3] ?? '';
$joinType = $valueMap[4] ?? ''; // เรื่อง
$courseName = $valueMap[5] ?? '';
$joinDates = $valueMap[6] ?? '';
$location = $valueMap[7] ?? '';
$amountStr = $valueMap[8] ?? '';
$vehicle = $valueMap[9] ?? '';
$faculty = $valueMap[10] ?? '';
$department = $valueMap[11] ?? '';

$thaiDocDate = thai_date($docDate);
$prettyAmount = $amountStr !== '' ? number_format((float) $amountStr, 2) : '';

/** header ช่วยประกอบบรรทัด */
$hdr_agency = trim(($faculty ? $faculty : 'คณะ..................................') . ' ' . ($department ?
  'ภาควิชา' . $department : 'ภาควิชา........................'));
$hdr_subject = $joinType ?: 'เข้ารับการฝึกอบรมหลักสูตร';
$hdr_to = 'คณบดี' . ($faculty ? $faculty : 'คณะ..................................');

/** ปีงบ/พ.ศ.แบบคร่าว ๆ จากวันที่เอกสาร */
$thaiYear = '';
if ($docDate && preg_match('/^\d{4}/', $docDate)) {
  $thaiYear = ((int) substr($docDate, 0, 4) + 543);
}
$subject = $document['subject'] ?? '';
$len = mb_strlen($subject, 'UTF-8');  // นับตัวอักษรแบบ UTF-8
$len = max(20, $len);

?>
<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>บันทึกข้อความ #<?= h($document['document_id']) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @import url("https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap");

    html,
    body {
      margin: 0;
      background: #f3f4f6;
      font-family: "TH SarabunPSK", sans-serif;
    }

    .page {
      width: 794px;
      min-height: 1123px;
      margin: 40px auto;
      padding: 60px 70px 50px 100px;
      background: #fff;
      box-shadow: 0 0 5px rgba(0, 0, 0, .1);
      position: relative;
      border: 2px solid #fff;
    }

    h1 {
      font-family: "TH SarabunPSK";
      font-size: 29pt;
      font-weight: bold;
      text-align: center;
      line-height: 1.2;
      margin-bottom: 1.5em;
    }

    .doc-title {
      margin-left: -30px;
    }

    .doc-row {
      display: flex;
      align-items: center;
      margin-bottom: 6px;
      flex-wrap: nowrap;
    }

    .doc-label {
      margin-right: 2px;
    }

    /* ✅ เส้นจุดจริง */
    .dot-line {
      flex: 1;
      display: flex;
      align-items: flex-end;
      height: 22px;
      margin: 0;
      position: relative;
    }

    .dot-line::after {
      content: "";
      position: absolute;
      left: 0;
      right: 0;
      bottom: 2px;
      height: 2px;
      background-image: radial-gradient(circle, #000 1px, transparent 1px);
      background-size: 6px 2px;
      background-repeat: repeat-x;
    }

    .dot-input {
      border: none;
      background: transparent;
      font-family: "TH SarabunPSK";
      font-size: 16pt;
      line-height: 1.0;
      padding: 0 1px;
      margin: 0;
      min-width: 30px;
      max-width: 100%;
      box-sizing: border-box;
      position: relative;
      z-index: 1;
      /* ให้ข้อความอยู่บนเส้น */
    }

    .dot-input.box {
      border: 1px solid #000;
      background: #fff;
      padding: 0 4px;
      height: 24px;
      margin: 0;
    }

    .dot-input.box.full {
      width: 100%;
      box-sizing: border-box;
    }

    .content-block {
      font-family: "TH SarabunPSK";
      font-size: 16pt;
      line-height: 1.0;
      margin: 0;
      text-align: justify;
      text-justify: inter-word;
    }

    .content-block.paragraph {
      text-indent: 2.5cm;
      margin-top: 0.5em;
      line-height: 1.3;
    }

    .content-block.single {
      line-height: 1.0;
    }

    .content-block.indent-first {
      text-indent: 2.5cm;
      display: block;
    }

    .indent-block {
      margin-left: 2.5cm;
      text-align: left;
      font-family: 'TH SarabunPSK';
      font-size: 16pt;
      line-height: 1.2;
    }

    .chip {
      display: inline;
      padding: 0 1px;
      margin: 0;
      border: 1px solid #000;
      background: #fff;
      font-family: "TH SarabunPSK";
      font-size: 16pt;
      line-height: 1em;
      white-space: nowrap;
      vertical-align: baseline;
    }

    .keep {
      white-space: nowrap;
    }

    .signature-wrapper {
      display: flex;
      justify-content: center;
      margin-top: 2em;
    }

    .signature-block {
      margin-top: 50px;
      margin-left: 187px;
      text-align: center;
      font-family: 'TH SarabunPSK';
      font-size: 16pt;
      line-height: 1.2;
    }

    .sig-name {
      display: block;
      white-space: nowrap;
    }

    .sig-position {
      display: block;
      white-space: nowrap;
    }

    .footer-actions {
      margin-top: 24px;
      padding-top: 16px;
      display: flex;
      justify-content: flex-end;
      gap: 12px;
      border-top: 1px solid #e5e7eb;
    }

    @media print {

      header,
      .footer-actions {
        display: none !important;
      }

      body {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }

      .page {
        margin: 0;
        box-shadow: none;
        /* กำหนดขอบแต่ละด้าน: บน 2cm, ขวา 2cm, ล่าง 2cm, ซ้าย 2.5cm */
        padding: 3cm 2cm 3cm 3cm;
        width: 21cm;
        min-height: 29.7cm;
        border: 2px solid #fff !important;
      }

      .dot-line::after {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        bottom: 2px;
        height: 2px;
        background-image: radial-gradient(circle, #000 0.6px, transparent 0.6px);
        background-size: 4px 2px;


        background-repeat: repeat-x;
      }

      .dot-input {
        border: none !important;
        background: transparent !important;
        outline: none !important;
        font-size: 16pt !important;
        line-height: 1.2 !important;
        padding: 0 !important;
        margin: 0 !important;
        height: auto !important;
        position: relative;
        top: 3px !important;
      }

      .chip {
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
      }
    }


    /* ฟอนต์ Sarabun */
    @font-face {
      font-family: 'TH SarabunPSK';
      src: url('/fonts/THSarabunPSK.ttf') format('truetype');
      font-weight: normal;
      font-style: normal;
    }

    @font-face {
      font-family: 'TH SarabunPSK';
      src: url('/fonts/THSarabunPSK-Bold.ttf') format('truetype');
      font-weight: bold;
      font-style: normal;
    }

    @font-face {
      font-family: 'TH SarabunPSK';
      src: url('fonts/THSarabunPSK.ttf') format('truetype');
    }

    body {
      font-family: 'TH SarabunPSK', sans-serif;
    }
  </style>
</head>

<body>
  <header class="bg-teal-500 text-white p-4 flex justify-between items-center shadow-md">
    <div class="flex items-center space-x-3">
      <div class="w-[56px] h-[56px] flex items-center justify-center relative overflow-visible">
        <svg xmlns="http://www.w3.org/2000/svg" class="absolute scale-[1.4] text-white" style="width:60px;height:60px"
          fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m0 0a2 2 0 00-2-2H5a2 2 0 00-2 2m18 0v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8" />
        </svg>
      </div>
      <div class="leading-tight">
        <div class="text-[16px] font-bold">Smart</div>
        <div class="text-[16px] font-bold -mt-[2px]">Government</div>
        <div class="text-[13px] mt-0">Letter Management System</div>
      </div>
    </div>
    <div class="flex items-center space-x-4">
      <a href="../home.html">
        <div class="px-4 py-2 rounded-[11px] font-bold transition bg-white text-teal-500 shadow">หน้าหลัก</div>
      </a>
      <a href="form_Memo.html">
        <div class="px-4 py-2 rounded-[11px] font-bold transition text-white">แบบฟอร์มบันทึกข้อความ</div>
      </a>
      <div class="bg-white text-teal-500 px-4 py-2 rounded-[11px] shadow flex items-center space-x-2">
        <div class="text-right leading-tight">
          <div class="font-bold text-[14px]"><?= h($ownerName ?: 'ผู้ใช้งาน') ?></div>
          <div class="text-[12px]"><?= h($position ?: '') ?></div>
        </div>
        <div class="w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-bold">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M5.121 17.804A13.937 13.937 0 0112 15c2.33 0 4.487.577 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </div>
      </div>
    </div>
  </header>

  <main class="page">
    <form id="updateForm" action="update_memo.php" method="post">
      <!-- hidden input ครบทุก field_id -->
      <input type="hidden" name="document_id" value="<?= h($document['document_id']) ?>">
      <input type="hidden" name="doc_date" id="hidden_doc_date">
      <input type="hidden" name="ownerName" id="hidden_ownerName">
      <input type="hidden" name="position" id="hidden_position">
      <input type="hidden" name="joinType" id="hidden_joinType">
      <input type="hidden" name="courseName" id="hidden_courseName">
      <input type="hidden" name="joinDates" id="hidden_joinDates">
      <input type="hidden" name="location" id="hidden_location">
      <input type="hidden" name="amountStr" id="hidden_amountStr">
      <input type="hidden" name="vehicle" id="hidden_vehicle">
      <input type="hidden" name="faculty" id="hidden_faculty">
      <input type="hidden" name="department" id="hidden_department">

      <!-- หัวบันทึก -->
      <div style="display:flex; align-items:flex-end; justify-content:flex-start; gap:20px; margin-bottom:0.5em;">
        <img src="https://i.pinimg.com/474x/bd/55/cc/bd55ccc4416012910a723da8f810658b.jpg"
          style="height:1.5cm; width:auto;" />
        <h1 class="doc-title"
          style="font-size:29pt;font-weight:bold;font-family:'TH SarabunPSK';line-height:1.0;text-align:center;flex:1;">
          บันทึกข้อความ
        </h1>
      </div>

      <!-- ส่วนหัว -->
      <div class="doc-header" style="margin-top:5px;">
        <div class="doc-row">
          <div class="doc-label" style="font-size:20pt; font-family:'TH SarabunPSK'; font-weight:bold;">ส่วนราชการ</div>
          <div class="dot-line">
            <input type="text" class="dot-input box full"
              value="<?= h($faculty . ' ' . $department ?: 'คณะ... ภาควิชา...') ?>" />
          </div>
        </div>

        <div class="doc-row">
          <div class="doc-label" style="font-size:20pt; font-family:'TH SarabunPSK'; font-weight:bold;">ที่</div>
          <div class="dot-line">
            <input type="text" class="dot-input box" value="<?= h($document['doc_no'] ?: 'ทส. พิเศษ.486/2568') ?>"
              style="width:240px;" />
          </div>
          <div class="doc-label" style="font-size:20pt; font-family:'TH SarabunPSK'; font-weight:bold;">วันที่</div>
          <div class="dot-line">
            <input type="text" class="dot-input box" value="<?= h($thaiDocDate ?: '') ?>" style="width:200px;" />
          </div>
        </div>

        <div class="doc-row">
          <div class="doc-label" style="font-size:20pt; font-family:'TH SarabunPSK'; font-weight:bold;">เรื่อง</div>
          <div class="dot-line">
            <input type="text" class="dot-input box"
              style="font-size:16pt; font-family:'TH SarabunPSK'; width: <?= $len ?>ch;" value="<?= h($subject) ?>" />
          </div>
        </div>
      </div>

      <!-- เนื้อหา -->
      <div class="content-block single">
        เรียน คณบดีคณะเทคโนโลยีและการจัดการอุตสาหกรรม
      </div>

      <div class="content-block paragraph">
        ตามที่ สมาคมสหกิจศึกษาไทย กำหนดจัดอบรมหลักสูตร
        <span class="chip" contenteditable="true"
          data-target="courseName"><?= h($courseName ?: 'ชื่อหลักสูตร') ?></span>
        ระหว่างวันที่ <span class="chip" contenteditable="true"
          data-target="joinDates"><?= h($joinDates ?: '...') ?></span>
        ณ <span class="chip" contenteditable="true" data-target="location"><?= h($location ?: '...') ?></span> นั้น
        ซึ่งหลักสูตรดังกล่าวเป็นประโยชน์ต่อการพัฒนาทั้งกระบวนการจัดการเรียนการสอนในรูปแบบสหกิจศึกษา
      </div>

      <div class="content-block paragraph">
        การนี้ ข้าพเจ้า
        <span class="chip" contenteditable="true" data-target="ownerName"><?= h($ownerName ?: 'ชื่อ-นามสกุล') ?></span>
        <span class="chip" contenteditable="true" data-target="position"><?= h($position ?: '') ?></span>
        สังกัดภาควิชา <span class="chip" contenteditable="true"
          data-target="department"><?= h($department ?: '...') ?></span>
        คณะ <span class="chip" contenteditable="true" data-target="faculty"><?= h($faculty ?: '...') ?></span>
        มหาวิทยาลัยเทคโนโลยีพระจอมเกล้าพระนครเหนือ วิทยาเขตปราจีนบุรี
        จึงมีความประสงค์ที่จะขออนุมัติ เข้ารับการอบรมหลักสูตร
        <span class="chip" contenteditable="true"
          data-target="courseName"><?= h($courseName ?: 'ชื่อหลักสูตร') ?></span>
        ระหว่างวันที่ <span class="chip" contenteditable="true"
          data-target="joinDates"><?= h($joinDates ?: '') ?></span>
        ณ <span class="chip" contenteditable="true" data-target="location"><?= h($location ?: '') ?></span>
        วงเงินทั้งสิ้น <span class="chip" contenteditable="true"
          data-target="amountStr"><?= h($prettyAmount ?: '') ?></span> บาท
        โดยขอใช้แหล่งเงินจัดสรรให้หน่วยงาน ประจำปีงบประมาณ
        <span class="chip" contenteditable="true"
          data-target="doc_date"><?= h($thaiYear ? 'พ.ศ. ' . $thaiYear : 'พ.ศ. ....') ?></span>
        ในส่วนของภาควิชา<span><?= h($department ?: '...') ?></span>
        แผนงานจัดการศึกษาระดับอุดมศึกษา กองทุนพัฒนาบุคลากร หมวดค่าใช้สอย (รายละเอียดตามเอกสารแนบ)
      </div>

      <div class="content-block paragraph">
        จึงเรียนมาเพื่อโปรดพิจารณาอนุมัติ
      </div>

      <div class="signature-wrapper">
        <div class="signature-block" id="signatureBlock">
          <div class="sig-name">(<?= h($ownerName ?: '') ?>)</div>
          <div class="sig-position"><?= h($position ?: '') ?></div>
        </div>
      </div>

      <div style="font-family:'TH SarabunPSK'; font-size:16pt; line-height:1.2;"> เรียน <?= h($hdr_to) ?> </div>
      <div class="content-block single align-to-dean"> เพื่อโปรดพิจารณาอนุมัติ </div>
      <div class="content-block single align-to-dean" style="margin-top:50px;;"> (ผู้ช่วยศาสตราจารย์ ดร. ขนิษฐา
        นามี)<br /> หัวหน้าภาควิชาเทคโนโลยีสารสนเทศ </div>
      <div class="footer-actions">
        <button type="button" onclick="window.print()"
          class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md text-xl font-bold">
          พิมพ์/ตัวอย่าง
        </button>

        <button type="submit" class="bg-teal-500 hover:bg-teal-600 text-white px-6 py-2 rounded-md text-xl font-bold">
          ยืนยัน
        </button>

      </div>
    </form>
  </main>

  <script>
    document.getElementById("updateForm").addEventListener("submit", function () {
      document.querySelectorAll("[contenteditable][data-target]").forEach(el => {
        const target = el.dataset.target;
        const hidden = document.getElementById("hidden_" + target);
        if (hidden) hidden.value = el.innerText.trim();
      });
    });
    document.querySelectorAll('.editable[contenteditable], .chip[contenteditable]').forEach(el => {
      el.addEventListener('keydown', e => {
        if (e.key === 'Enter') e.preventDefault();
      });
      el.addEventListener('paste', e => {
        e.preventDefault();
        const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\r?\n/g, ' ');
        document.execCommand('insertText', false, text);
      });
    });
    (function () {
      const box = document.getElementById('signatureBlock');
      if (!box) return;
      const nameEl = box.querySelector('.sig-name');
      // กำหนดความกว้างกล่อง = ความกว้างบรรทัดชื่อ -> ตำแหน่งจะกึ่งกลางใต้ชื่อพอดี
      box.style.width = nameEl.offsetWidth + 'px';
    })();
  </script>
</body>


</html>