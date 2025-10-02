<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); 
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>รายการส่งคำขอ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
    html,
    body {
        height: 100vh;
        overflow: hidden;
        margin: 0;
    }

    body {
        display: flex;
        flex-direction: column;
    }

    main {
        flex: 1;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    #requestListContainer {
        flex: 1;
        overflow-y: auto;
    }
    </style>
</head>

<body class="bg-gray-100 ">
    <!-- Header -->
    <header class="bg-teal-500 text-white p-4 flex justify-between items-center shadow-md"
        style="font-family: Arial, Helvetica, sans-serif;">
        <div class="flex items-center space-x-3">
            <div class="w-[56px] h-[56px] flex items-center justify-center relative overflow-visible">
                <svg xmlns="http://www.w3.org/2000/svg" class="absolute scale-[1.4] text-white"
                    style="width: 60px; height: 60px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
            <a href="home.html">
                <div class="px-4 py-2 rounded-[11px] font-bold transition bg-white text-teal-500 shadow">หน้าหลัก</div>
            </a>
            <a href="form_Memo.html">
                <div class="px-4 py-2 rounded-[11px] font-bold transition text-white">แบบฟอร์มบันทึกข้อความ</div>
            </a>
            <div class="relative">
                <!-- ปุ่ม Profile -->
                <button id="profileBtn"
                    class="bg-white text-teal-500 px-4 py-2 rounded-[11px] shadow flex items-center space-x-2 hover:bg-gray-100">
                    <div class="text-right leading-tight">
                        <div class="font-bold text-[14px]">
                            <?= htmlspecialchars($_SESSION['fullname'] ?? 'Guest') ?>
                        </div>
                        <div class="text-[12px]">
                            <?= htmlspecialchars($_SESSION['role_name'] ?? '') ?>
                        </div>

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
                        class="w-full text-left px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">อยู่ต่อ</button>
                </div>
            </div>

        </div>
    </header>


    <!-- Content -->
    <main class="max-w-7xl w-full px-8 mx-auto bg-white mt-4 mb-12 p-6 rounded shadow min-h-[85vh]">
        <h2 class="text-xl font-bold mb-4">รายการส่งคำขอ</h2>

        <!-- Tabs -->
        <div class="flex space-x-6 border-b mb-4">
            <button id="tab-pending" class="bg-teal-500 text-white px-4 py-2 rounded-t-md font-semibold">
                ส่งคำขอ
            </button>
            <button id="tab-done" class="text-gray-500 px-4 py-2 rounded-t-md font-semibold">
                เสร็จสิ้น
            </button>
        </div>

        <!-- Filter + Sort -->
        <div class="flex justify-between items-center mb-2">
            <label class="text-sm text-gray-700">
                แสดง:
                <select id="itemsPerPage" class="border rounded px-2 py-1 text-sm">
                    <option value="5" selected>5</option>
                    <option value="10">10</option>
                </select>
                รายการ/หน้า
            </label>
            <button id="sortBtn" class="flex items-center text-sm text-teal-600">
                วันที่
                <svg id="sortIcon" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                </svg>
            </button>
        </div>

        <!-- List Container -->
        <div id="requestListContainer">
            <div id="requestList" class="space-y-4"></div>
        </div>
        <div id="pagination" class="flex justify-center mt-6 space-x-2"></div>
    </main>

    <script>
    const detail =
        "“คณาจารย์นิเทศ CWIE สำหรับผู้ที่ ไม่เคยอบรม (ฉบับปรับปรุง พ.ศ. 2566)” รุ่นที่ 1";
    const dataAll = [{
            title: "ขออนุมัติไปเข้ารับการฝึกอบรมหลักสูตร",
            date: "2025-07-01",
            status: "pending",
        },
        {
            title: "ขออนุมัติค่าใช้จ่ายในการอบรม",
            date: "2025-07-25",
            status: "pending",
        },
        {
            title: "ขออนุมัติใช้รถยนต์ส่วนบุคคล",
            date: "2025-07-03",
            status: "pending",
        },
        {
            title: "ประมาณการค่าใช้จ่าย",
            date: "2025-07-17",
            status: "pending"
        },
        {
            title: "ขออนุมัติเข้าร่วมสัมมนาวิชาการ",
            date: "2025-07-16",
            status: "done",
        },
        {
            title: "ขออนุมัติใช้สถานที่ราชการ",
            date: "2025-06-29",
            status: "done",
        },
        {
            title: "ขออนุมัติจัดโครงการอบรม",
            date: "2025-07-05",
            status: "done",
        },
        {
            title: "แจ้งผลการดำเนินโครงการ",
            date: "2025-06-30",
            status: "done"
        },
        {
            title: "เสนอรายงานค่าใช้จ่ายเพิ่มเติม",
            date: "2025-07-21",
            status: "pending",
        },
        {
            title: "ขออนุมัติเบิกเงินสำรองจ่าย",
            date: "2025-06-27",
            status: "pending",
        },
    ];

    let currentPage = 1;
    let itemsPerPage = 10;
    let sortAsc = false;
    let activeTab = "pending";

    const requestList = document.getElementById("requestList");
    const pagination = document.getElementById("pagination");
    const itemsPerPageEl = document.getElementById("itemsPerPage");
    const sortBtn = document.getElementById("sortBtn");
    const sortIcon = document.getElementById("sortIcon");
    const tabPending = document.getElementById("tab-pending");
    const tabDone = document.getElementById("tab-done");

    function formatDate(iso) {
        const date = new Date(iso);
        return date.toLocaleDateString("th-TH", {
            year: "numeric",
            month: "long",
            day: "numeric",
        });
    }

    function renderList() {
        const dataFiltered = dataAll.filter((d) => d.status === activeTab);
        const sorted = dataFiltered.sort((a, b) =>
            sortAsc ?
            new Date(a.date) - new Date(b.date) :
            new Date(b.date) - new Date(a.date)
        );

        const start = (currentPage - 1) * itemsPerPage;
        const shown = sorted.slice(start, start + itemsPerPage);

        requestList.innerHTML = shown
            .map(
                (req) => `
        <div class="bg-gray-50 p-4 rounded-xl shadow flex justify-between items-start">
          <div>
            <div class="font-semibold text-gray-800">${req.title}</div>
            <div class="text-sm text-gray-500 mt-1">${detail}</div>
          </div>
          <div class="text-right text-sm text-gray-600">
            <div>${formatDate(req.date)}</div>
            <div class="mt-2 flex justify-end space-x-2">
             <a href="#" class="text-blue-500 flex items-center space-x-1"><img src="https://cdn-icons-png.flaticon.com/16/281/281760.png" alt="Word"> <span>Word</span></a>
            <a href="#" class="text-red-500 flex items-center space-x-1"><img src="https://cdn-icons-png.flaticon.com/16/337/337946.png" alt="PDF"> <span>PDF</span></a>
          </div>
            </div>
          </div>
        </div>`
            )
            .join("");

        const totalPages = Math.ceil(dataFiltered.length / itemsPerPage);
        pagination.innerHTML = Array.from({
                    length: totalPages
                },
                (_, i) => i + 1
            )
            .map(
                (i) => `
        <button onclick="goToPage(${i})" class="px-3 py-1 rounded border ${
              i === currentPage
                ? "bg-teal-500 text-white"
                : "text-teal-500 border-teal-500"
            }">${i}</button>
      `
            )
            .join("");
    }

    function goToPage(page) {
        currentPage = page;
        renderList();
    }

    sortBtn.onclick = () => {
        sortAsc = !sortAsc;
        sortIcon.innerHTML = sortAsc ?
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />' :
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />';
        renderList();
    };

    itemsPerPageEl.onchange = () => {
        itemsPerPage = parseInt(itemsPerPageEl.value);
        currentPage = 1;
        renderList();
    };

    tabPending.onclick = () => {
        activeTab = "pending";
        tabPending.classList.add("bg-teal-500", "text-white");
        tabDone.classList.remove("bg-teal-500", "text-white");
        currentPage = 1;
        renderList();
    };

    tabDone.onclick = () => {
        activeTab = "done";
        tabDone.classList.add("bg-teal-500", "text-white");
        tabPending.classList.remove("bg-teal-500", "text-white");
        currentPage = 1;
        renderList();
    };

    renderList();

    const profileBtn = document.getElementById("profileBtn");
    const profileMenu = document.getElementById("profileMenu");

    profileBtn.addEventListener("click", () => {
        profileMenu.classList.toggle("hidden");
    });

    function closeMenu() {
        profileMenu.classList.add("hidden");
    }

    // กดนอกเมนูให้ปิด
    window.addEventListener("click", (e) => {
        if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
            profileMenu.classList.add("hidden");
        }
    });
    </script>
</body>

</html>