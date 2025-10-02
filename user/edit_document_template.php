<?php
$document     = $document     ?? [];
$thaiDocDate  = $thaiDocDate  ?? '';
$ownerName    = $ownerName    ?? '';
$position     = $position     ?? '';
$joinType     = $joinType     ?? '';
$courseName   = $courseName   ?? '';
$joinDates    = $joinDates    ?? '';
$location     = $location     ?? '';
$prettyAmount = $prettyAmount ?? '';
$thaiYear     = $thaiYear     ?? '';
$faculty      = $faculty      ?? '';
$department   = $department   ?? '';
$hdr_to       = $hdr_to       ?? 'คณบดี'.($faculty ?: 'คณะ..................................');
?>
<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8" />
  <style>
  @page {
    margin: 3cm 2cm 3cm 3cm;
  }

  @font-face {
    font-family: 'THSarabunPSK';
    src: url('<?= __DIR__ . "/../fonts/THSarabun.ttf" ?>') format('truetype');
    font-weight: normal;
    font-style: normal;
  }

  @font-face {
    font-family: 'THSarabunPSK';
    src: url('<?= __DIR__ . "/../fonts/THSarabun Bold.ttf" ?>') format('truetype');
    font-weight: bold;
    font-style: normal;
  }

  body {
    font-family: 'THSarabunPSK', sans-serif;
    font-size: 16pt;
    line-height: 1.2;
    margin: 0;
  }

  .doc-table {
    width: 100%;
    border-collapse: collapse;
  }

  h1 {
    margin: 0;
  }

  /* เส้นจุดต่อเนื่อง */
  .dotted {
    border-bottom: 1px dotted #000;
    width: 100%;
    display: inline-block;
    white-space: nowrap;
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
  </style>
</head>

<body>

  <!-- ครุฑ + หัวเรื่อง -->
  <table class="doc-table">
    <tr>
      <td style="width:2.5cm;">
        <img src="https://i.pinimg.com/474x/bd/55/cc/bd55ccc4416012910a723da8f810658b.jpg" style="height:1.5cm;" />
      </td>
      <td style="text-align:center;">
        <h1>บันทึกข้อความ</h1>
      </td>
      <td style="width:2.5cm;"></td>
    </tr>
  </table>

  <!-- ส่วนหัว -->
  <div class="doc-header" style="margin-top:5px;">
    <!-- ส่วนราชการ -->
    <div class="doc-row">
      <div class="doc-label" style="font-size:20pt; font-family:'TH SarabunPSK'; font-weight:bold;">
        ส่วนราชการ
      </div>
      <div class="dot-line">
        <input type="text" class="dot-input" style="font-size:16pt; font-family:'TH SarabunPSK';"
          value="คณะเทคโนโลยีและการจัดการอุตสาหกรรม ภาควิชาเทคโนโลยีสารสนเทศ โทร. 7064" />
      </div>
    </div>

    <!-- ที่ + วันที่ -->
    <div class="doc-row">
      <div class="doc-label" style="font-size:20pt; font-family:'TH SarabunPSK'; font-weight:bold;">ที่</div>
      <div class="dot-line">
        <input type="text" class="dot-input box" value="ทส. พิเศษ.486/2568" style="width:240px;" />
      </div>

      <div class="doc-label" style="font-size:20pt; font-family:'TH SarabunPSK'; font-weight:bold;">วันที่</div>
      <div class="dot-line">
        <input type="text" class="dot-input box" value="<?= h($thaiDocDate ?: '') ?>" style="width:200px;" />
      </div>
    </div>



    <!-- เรื่อง -->
    <div class="doc-row">
      <div class="doc-label" style="font-size:20pt; font-family:'TH SarabunPSK'; font-weight:bold;">
        เรื่อง
      </div>
      <div class="dot-line">
        <input type="text" class="dot-input box" style="font-size:16pt; font-family:'TH SarabunPSK';"
          value="เข้ารับการฝึกอบรมหลักสูตร" />
      </div>
    </div>
  </div>
  <main>
    <!-- ย่อหน้า "เรียน" -->
    <div class="content-block single">
      เรียน คณบดีคณะเทคโนโลยีและการจัดการอุตสาหกรรม
    </div>

    <div class="content-block paragraph">
      ตามที่ สมาคมสหกิจศึกษาไทย กำหนดจัดอบรมหลักสูตร
      <span class="chip" contenteditable="true"><?= h($courseName ?: 'ชื่อหลักสูตร') ?></span>
      ระหว่างวันที่ <span class="chip" contenteditable="true"><?= h($joinDates ?: '...') ?></span>
      ณ <span class="chip" contenteditable="true"><?= h($location ?: '...') ?></span> นั้น
      ซึ่งหลักสูตรดังกล่าวเป็นประโยชน์ต่อการพัฒนาทั้งกระบวนการจัดการเรียนการสอนในรูปแบบสหกิจศึกษา
    </div>

    <!-- ย่อหน้าที่สอง -->
    <div class="content-block paragraph">
      การนี้ ข้าพเจ้า
      <span class="chip"
        contenteditable="true"><?= h(($ownerName ?: 'ชื่อ-นามสกุล') . ' ' . ($position ?: '')) ?></span>
      สังกัดภาควิชา <span class="chip" contenteditable="true"><?= h($department ?: '................') ?></span>
      คณะ <span class="chip" contenteditable="true"><?= h($faculty ?: '................') ?></span>
      มหาวิทยาลัยเทคโนโลยีพระจอมเกล้าพระนครเหนือ
      วิทยาเขตปราจีนบุรี จึงมีความประสงค์ที่จะขออนุมัติตัวบุคคลเข้ารับการอบรมหลักสูตร
      <span class="chip" contenteditable="true"><?= h($courseName ?: 'ชื่อหลักสูตร') ?></span>
      ระหว่างวันที่ <span class="chip" contenteditable="true"><?= h($joinDates ?: '') ?></span>
      ณ <span class="chip" contenteditable="true"><?= h($location ?: '') ?></span>
      วงเงินทั้งสิ้น <span class="chip" contenteditable="true"><?= h($prettyAmount ?: '') ?></span> บาท
      โดยขอใช้แหล่งเงินจัดสรรให้หน่วยงาน ประจำปีงบประมาณ
      <span class="chip" contenteditable="true"><?= h($thaiYear ? 'พ.ศ. ' . $thaiYear : 'พ.ศ. ....') ?></span>
      ในส่วนของภาควิชา<span><?= h($department ?: '................') ?></span>
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



    <div style="font-family:'TH SarabunPSK'; font-size:16pt; line-height:1.2;">
      เรียน <?= h($hdr_to) ?>
    </div>

    <div class="content-block single align-to-dean">
      เพื่อโปรดพิจารณาอนุมัติ
    </div>
    <div class="content-block single align-to-dean" style="margin-top:50px;;">
      (ผู้ช่วยศาสตราจารย์ ดร. ขนิษฐา นามี)<br />
      หัวหน้าภาควิชาเทคโนโลยีสารสนเทศ
    </div>


</body>

</html>