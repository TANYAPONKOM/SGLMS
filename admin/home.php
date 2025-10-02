<?php
session_start();
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header('Location: ../login.html');
    exit;
}

require_once __DIR__ . '/../functions.php'; 
$pdo = getPDO();
$users = getActiveUsers();

// ✅ ฟิกข้อมูลตัวอย่างเอกสาร
$documents = [
    [
        "fullname" => "ดร.พิทย์พิมล ชูรอด",
        "title"    => "โครงการวิจัยด้านการศึกษา",
        "date"     => "2025-09-28",
        "status"   => "pending",
        "action"   => "เปิดเอกสาร"
    ],
    [
        "fullname" => "ดร.พิทย์พิมล ชูรอด",
        "title"    => "บันทึกขออนุมัติใช้งบประมาณ",
        "date"     => "2025-09-27",
        "status"   => "approved",
        "action"   => "แจ้งทางเมล"
    ],
    [
        "fullname" => "ดร.พิทย์พิมล ชูรอด",
        "title"    => "รายงานผลการดำเนินงาน",
        "date"     => "2025-09-25",
        "status"   => "rejected",
        "action"   => "แจ้งทางเมล"
    ],
];

// ✅ อ่านสถานะที่เลือกจาก query string (ค่าเริ่มต้น = all)
$activeTab = $_GET['tab'] ?? 'all';

// ✅ ฟิลเตอร์ข้อมูลตาม tab
$filteredDocs = ($activeTab === 'all')
    ? $documents
    : array_filter($documents, fn($d) => $d['status'] === $activeTab);

$totalDocs     = count($documents);
$approvedDocs  = count(array_filter($documents, fn($d) => $d['status'] === 'approved'));
$pendingDocs   = count(array_filter($documents, fn($d) => $d['status'] === 'pending'));
$rejectedDocs  = count(array_filter($documents, fn($d) => $d['status'] === 'rejected'));    
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ประวัติการใช้งานเอกสาร</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-teal-500 text-white p-4 flex justify-between items-center shadow-md">
        <div class="flex items-center space-x-3">
            <div class="w-[56px] h-[56px] flex items-center justify-center relative">
                <svg xmlns="http://www.w3.org/2000/svg" class="absolute scale-[1.4] text-white"
                    style="width: 60px; height: 60px" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m0 0a2 2 0 00-2-2H5a2 2 0 00-2 2m18 0v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8" />
                </svg>
            </div>
            <div class="leading-tight">
                <div class="text-[16px] font-bold">Smart</div>
                <div class="text-[16px] font-bold -mt-[2px]">Government</div>
                <div class="text-[13px] mt-[0px]">Letter Management System</div>
            </div>
        </div>
        <div class="flex items-center space-x-4">
            <a href="home.php">
                <div class="px-4 py-2 rounded-[11px] font-bold transition bg-white text-teal-500 shadow">หน้าหลัก</div>
            </a>
            <a href="user_Managerment.php" id="tab-users">
                <div
                    class="px-4 py-2 rounded-[11px] font-bold transition text-white hover:bg-white hover:text-teal-500">
                    กำหนดสิทธิ์
                </div>
            </a>
            <!-- Dropdown จัดการเทมเพลต -->
            <div class="relative">
                <button id="templateBtn"
                    class="px-4 py-2 rounded-[11px] font-bold transition text-white hover:bg-white hover:text-teal-500 flex items-center space-x-1">
                    <span>จัดการเทมเพลต</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- เมนูย่อย -->
                <div id="templateMenu"
                    class="hidden absolute bg-white text-gray-700 mt-1 rounded-lg shadow-lg w-48 z-50">
                    <a href="form_Templates.php" class="block px-4 py-2 hover:bg-teal-100">การจัดการเทมเพลต</a>
                    <a href="department_Managerment.php" class="block px-4 py-2 hover:bg-teal-100">การจัดการภาควิชา</a>
                </div>
            </div>

            <div class="relative">
                <button id="profileBtn"
                    class="bg-white text-teal-500 px-4 py-2 rounded-[11px] shadow flex items-center space-x-2 hover:bg-gray-100">
                    <div class="text-right leading-tight">
                        <div class="font-bold text-[14px]"><?= htmlspecialchars($_SESSION['fullname']) ?></div>
                        <div class="text-[12px]"><?= htmlspecialchars($_SESSION['role_name']) ?></div>
                    </div>
                    <div
                        class="w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-bold">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M5.121 17.804A13.937 13.937 0 0112 15c2.33 0 4.487.577 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </button>
                <!-- เมนู Dropdown -->
                <div id="profileMenu"
                    class="hidden absolute right-0 mt-2 w-40 bg-white border rounded-lg shadow-lg z-50">
                    <a href="../logout.php"
                        class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">ออกจากระบบ</a>
                    <button onclick="closeMenu()"
                        class="w-full text-left px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">
                        อยู่ต่อ
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main -->
    <main class="max-w-7xl w-full px-8 mx-auto bg-white mt-4 mb-12 p-6 rounded shadow min-h-[70vh]">

        <!-- Operation History -->
        <!-- Header ของตาราง + การ์ดผู้ใช้ -->
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold">ประวัติการใช้งานเอกสาร</h2>

            <!-- Card Users -->
            <div id="card-users" class="bg-white px-4 py-2 rounded-lg shadow flex items-center space-x-3">
                <!-- Circle Icon -->
                <div class="w-12 h-12 rounded-full bg-teal-500 flex items-center justify-center text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 7a3 3 0 11-6 0 3 3 0 016 0zM4 14s1-1 6-1 6 1 6 1v1H4v-1z" />
                    </svg>
                </div>

                <!-- Text Info (inline) -->
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500">จำนวนผู้ใช้ : </span>
                    <span class="text-lg font-bold text-gray-800"><?= number_format(count($users)) ?></span>
                </div>
            </div>

        </div>

        <!-- Tabs + ฟอร์มเลือกช่วงวัน -->
        <div class="flex items-center justify-between border-b mb-4">
            <!-- Tabs -->
            <div class="flex space-x-6">
                <a href="?tab=all"
                    class="px-4 py-2 rounded-t-md font-semibold <?= $activeTab==='all' ? 'bg-teal-500 text-white' : 'text-gray-500' ?>">
                    เอกสารทั้งหมด
                </a>
                <a href="?tab=pending"
                    class="px-4 py-2 rounded-t-md font-semibold <?= $activeTab==='pending' ? 'bg-teal-500 text-white' : 'text-gray-500' ?>">
                    รอตรวจสอบ
                </a>
                <a href="?tab=approved"
                    class="px-4 py-2 rounded-t-md font-semibold <?= $activeTab==='approved' ? 'bg-teal-500 text-white' : 'text-gray-500' ?>">
                    อนุมัติแล้ว
                </a>
                <a href="?tab=rejected"
                    class="px-4 py-2 rounded-t-md font-semibold <?= $activeTab==='rejected' ? 'bg-teal-500 text-white' : 'text-gray-500' ?>">
                    ถูกตีกลับ
                </a>
            </div>

            <!-- ฟอร์มเลือกช่วงวัน -->
            <form method="get" class="flex space-x-2 items-center">
                <label class="text-sm text-gray-600">ช่วงวันที่:</label>
                <input type="date" name="date_from" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>"
                    class="border rounded px-2 py-1 text-sm">
                <input type="date" name="date_to" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>"
                    class="border rounded px-2 py-1 text-sm">
                <button type="submit"
                    class="bg-teal-500 text-white px-3 py-1 rounded text-sm hover:bg-teal-600 transition">ค้นหา</button>
            </form>
        </div>

        <!-- Document Table -->
        <div class="overflow-x-auto rounded-lg shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100 text-gray-700 text-sm">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">ผู้ส่ง</th>
                        <th class="px-4 py-3 text-left font-semibold">เรื่องเอกสาร</th>
                        <th class="px-4 py-3 text-left font-semibold">วันที่</th>
                        <th class="px-4 py-3 text-left font-semibold">สถานะ</th>
                        <th class="px-4 py-3 text-center font-semibold">การดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm">
                    <?php foreach ($filteredDocs as $doc): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <!-- Fullname -->
                        <td class="px-4 py-3 flex items-center space-x-3">
                            <div
                                class="w-8 h-8 rounded-full bg-teal-400 flex items-center justify-center font-bold text-white">
                                <?= mb_substr($doc['fullname'],0,1) ?>
                            </div>
                            <span class="font-medium text-gray-800"><?= htmlspecialchars($doc['fullname']) ?></span>
                        </td>
                        <!-- Title -->
                        <td class="px-4 py-3 text-gray-700">
                            <?= htmlspecialchars($doc['title']) ?>
                        </td>
                        <!-- Date -->
                        <td class="px-4 py-3 text-gray-600">
                            <?= date("d M Y", strtotime($doc['date'])) ?>
                        </td>
                        <!-- Status -->
                        <td class="px-4 py-3">
                            <?php if ($doc['status'] === 'pending'): ?>
                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-600">รอตรวจสอบ</span>
                            <?php elseif ($doc['status'] === 'approved'): ?>
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-600">อนุมัติ</span>
                            <?php elseif ($doc['status'] === 'rejected'): ?>
                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-600">ถูกตีกลับ</span>
                            <?php endif; ?>
                        </td>
                        <!-- Action -->
                        <td class="px-4 py-3 text-center align-middle">
                            <a href="#" class="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-r from-sky-400 to-sky-500 
              hover:from-sky-500 hover:to-sky-600 text-white shadow-md transition duration-200 ease-in-out mx-auto"
                                title="เปิดเอกสาร">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2l4-4m-6 6h6m2 5H7a2 2 0 01-2-2V5a2 2 0 
             012-2h5.586A2 2 0 0114 4.414L19.586 
             10A2 2 0 0120 11.586V19a2 2 0 
             01-2 2z" />
                                </svg>

                            </a>
                        </td>

                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
    const profileBtn = document.getElementById("profileBtn");
    const profileMenu = document.getElementById("profileMenu");
    if (profileBtn && profileMenu) {
        profileBtn.addEventListener("click", () => {
            profileMenu.classList.toggle("hidden");
        });

        function closeMenu() {
            profileMenu.classList.add("hidden");
        }
        window.addEventListener("click", (e) => {
            if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
                profileMenu.classList.add("hidden");
            }
        });
    }

    function deleteLog(id) {
        if (confirm("คุณแน่ใจหรือไม่ว่าต้องการลบประวัติ?")) {
            window.location.href = "delete_log.php?id=" + id;
        }
    }
    </script>

    <script>
    const templateBtn = document.getElementById("templateBtn");
    const templateMenu = document.getElementById("templateMenu");

    templateBtn.addEventListener("click", () => {
        templateMenu.classList.toggle("hidden");
    });

    // ปิด dropdown ถ้าคลิกนอกเมนู
    document.addEventListener("click", (e) => {
        if (!templateBtn.contains(e.target) && !templateMenu.contains(e.target)) {
            templateMenu.classList.add("hidden");
        }
    });
    </script>

</body>

</html>