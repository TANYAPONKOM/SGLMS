<?php
session_start();
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
  header('Location: ../login.html');
  exit;
}

require_once __DIR__ . '/../functions.php';
$pdo = getPDO();

// ดึงข้อมูลจาก template_fields
$sql = "SELECT field_id, template_id, field_key, field_label, field_type, is_required, sort_order
        FROM template_fields 
        ORDER BY sort_order ASC";
$stmt = $pdo->query($sql);
$fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>การจัดการสิทธิ์ของผู้ใช้</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
  <!-- Header -->
  <header class="bg-teal-500 text-white p-4 flex justify-between items-center shadow-md">
    <div class="flex items-center space-x-3">
      <div class="w-[56px] h-[56px] flex items-center justify-center relative overflow-visible">
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
      <!-- เมนู หน้าหลัก -->
      <a href="home.php">
        <div
          class="px-4 py-2 rounded-[11px] font-bold transition 
      <?= $current === 'home.php' ? 'bg-white text-teal-500 shadow' : 'text-white hover:bg-white hover:text-teal-500' ?>">
          หน้าหลัก
        </div>
      </a>

      <!-- เมนู กำหนดสิทธิ์ -->
      <a href="user_Managerment.php" id="tab-users">
        <div
          class="px-4 py-2 rounded-[11px] font-bold transition 
      <?= $current === 'user_Managerment.php' ? 'bg-white text-teal-500 shadow' : 'text-white hover:bg-white hover:text-teal-500' ?>">
          กำหนดสิทธิ์
        </div>
      </a>

      <!-- Dropdown จัดการเทมเพลต -->
      <div class="relative">
        <button id="templateBtn" class="px-4 py-2 rounded-[11px] font-bold transition flex items-center space-x-1
      <?= ($current === 'form_Templates.php' || $current === 'department_Management.php') 
          ? 'bg-white text-teal-500 shadow' 
          : 'text-white hover:bg-white hover:text-teal-500' ?>">
          <span>จัดการเทมเพลต</span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>

        <!-- เมนูย่อย -->
        <div id="templateMenu" class="hidden absolute bg-white text-gray-700 mt-1 rounded-lg shadow-lg w-48 z-50">
          <a href="form_Templates.php"
            class="block px-4 py-2 hover:bg-teal-100 <?= $current === 'form_Templates.php' ? 'bg-teal-100 font-bold' : '' ?>">
            การจัดการเทมเพลต
          </a>
          <a href="department_Managerment.php"
            class="block px-4 py-2 hover:bg-teal-100 <?= $current === 'department_Managerment.php' ? 'bg-teal-100 font-bold' : '' ?>">
            การจัดการภาควิชา
          </a>
        </div>
      </div>





      <div class="relative">
        <button id="profileBtn"
          class="bg-white text-teal-500 px-4 py-2 rounded-[11px] shadow flex items-center space-x-2 hover:bg-gray-100">
          <div class="text-right leading-tight">
            <div class="font-bold text-[14px]"><?= htmlspecialchars($_SESSION['fullname']) ?></div>
            <div class="text-[12px]"><?= htmlspecialchars($_SESSION['role_name']) ?></div>
          </div>
          <div class="w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-bold">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M5.121 17.804A13.937 13.937 0 0112 15c2.33 0 4.487.577 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
          </div>
        </button>
        <!-- เมนู Dropdown -->
        <div id="profileMenu" class="hidden absolute right-0 mt-2 w-40 bg-white border rounded-lg shadow-lg z-50">
          <a href="../logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">ออกจากระบบ</a>
          <button onclick="closeMenu()" class="w-full text-left px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">
            อยู่ต่อ
          </button>
        </div>
      </div>
    </div>
    </button>
    </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="max-w-5xl w-full px-8 mx-auto bg-white mt-6 mb-12 p-6 rounded shadow min-h-[85vh]">
    <div class="flex justify-between items-center mb-4 border-b pb-2">
      <h2 class="text-lg font-bold">การจัดการเทมเพลต</h2>
      <button onclick="confirmUserAction('add')" class="px-3 py-1 bg-teal-500 text-white rounded">+ เพิ่ม</button>
    </div>
    <table class="w-full text-sm text-left border-separate border-spacing-y-2">
      <thead class="text-gray-600 bg-gray-100">
        <tr>
          <th class="px-4 py-2">Field Key</th>
          <th class="px-4 py-2">Field Label</th>
          <th class="px-4 py-2">Field Type</th>
          <th class="px-4 py-2">Required</th>

          <th class="px-4 py-2 text-center">การจัดการ</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($fields): ?>
        <?php foreach ($fields as $row): ?>
        <tr class="bg-white shadow-sm rounded-lg">
          <!-- Field Key (แทน Avatar ด้วยตัวอักษรแรก) -->
          <td class="px-4 py-3 flex items-center space-x-3">
            <div class="w-8 h-8 rounded-full bg-teal-500 text-white flex items-center justify-center font-bold">
              <?= strtoupper(mb_substr($row['field_key'], 0, 1)) ?>
            </div>
            <span class="font-medium text-gray-800"><?= htmlspecialchars($row['field_key']) ?></span>
          </td>

          <!-- Field Label -->
          <td class="px-4 py-3 text-gray-700"><?= htmlspecialchars($row['field_label']) ?></td>

          <!-- Field Type -->
          <td class="px-4 py-3 text-gray-700"><?= htmlspecialchars($row['field_type']) ?></td>

          <!-- Required -->
          <td class="px-4 py-3">
            <?php if ($row['is_required']): ?>
            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-600">NOT NULL</span>
            <?php else: ?>
            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-600">NULL</span>
            <?php endif; ?>
          </td>

          <!-- Sort Order -->

          <!-- ปุ่มจัดการ -->
          <td class="px-4 py-3 text-center">
            <div class="flex justify-center space-x-2">
              <!-- ปุ่มแก้ไข -->
              <button onclick="confirmUserAction('edit', <?= $row['field_id'] ?>)"
                class="w-10 h-10 flex items-center justify-center rounded-full bg-purple-100 hover:bg-purple-200 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 20h9M16.5 3.5a2.121 2.121 0 113 3L7 19l-4 1 1-4 12.5-12.5z" />
                </svg>
              </button>

              <!-- ปุ่มลบ -->
              <button onclick="confirmUserAction('delete', <?= $row['field_id'] ?>)"
                class="w-10 h-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-200 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-7 0h8" />
                </svg>
              </button>
            </div>
          </td>

        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr>
          <td colspan="6" class="text-center py-4 text-gray-500">ไม่พบข้อมูล Template Fields</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>


  </main>

  <script>
  const profileBtn = document.getElementById("profileBtn");
  const profileMenu = document.getElementById("profileMenu");
  if (profileBtn) {
    profileBtn.addEventListener("click", () => {
      profileMenu.classList.toggle("hidden");
    });
  }
  </script>

  <script>
  function confirmUserAction(action, id = null) {
    if (action === "add") {
      window.location.href = "template_Add.php";
    } else if (action === "edit") {
      window.location.href = "template_Edit.php?id=" + id;
    } else if (action === "delete") {
      if (confirm("คุณแน่ใจว่าต้องการลบ Field นี้หรือไม่?")) {
        window.location.href = "template_Delete.php?id=" + id;
      }
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