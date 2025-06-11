<?php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Role-based access: Redirect by user type
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login/login.php");
    exit();
}

if (!isset($_SESSION['accType'])) {
    header("Location: ../login/login.php");
    exit();
}

switch ($_SESSION['accType']) {
    case "School Administrator":
        // Allow access, continue
        break;
    case "Educator":
        header("Location: ../educator/welcome_educator.php");
        exit();
    case "Student":
        header("Location: ../student/welcome_student.php");
        exit();
    default:
        session_unset();
        session_destroy();
        header("Location: ../login/login.php");
        exit();
}

include '../sql/db_connect.php';

$adminName = $_SESSION['firstName'] ?? 'Admin';
$schoolIdNo = $_SESSION['schoolIdNo'] ?? -1;

$users = [];
if ($schoolIdNo != -1) {
  $sql = "SELECT u.accRefNo, u.firstName, u.lastName, u.accType, 
               u.username, u.emailAddress, u.contactNo,
               sa.fullName AS adminFullName, sa.adEmpIdNo,
               ed.fullName AS edFullName, ed.edEmpIdNo,
               st.fullName AS stFullName, st.lrn
        FROM mtbmalusers u
        LEFT JOIN schooladministrator sa ON u.accRefNo = sa.accRefNo
        LEFT JOIN educator ed ON u.accRefNo = ed.accRefNo
        LEFT JOIN student st ON u.accRefNo = st.accRefNo
        WHERE u.schoolIdNo = ?
        ORDER BY u.accRefNo ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $schoolIdNo);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage MTB-MAL Accounts</title>
  <link rel="icon" type="image/png" href="../images/MTB-MAL_logo.png">
  <link rel="stylesheet" href="../style/mtbmal_accs-style.css">
  <style>
    .action-icons a {text-decoration: none;}
    .action-icons img {
      width: 23px; vertical-align: middle; margin: 0 3px;
      cursor: pointer; transition: filter 0.2s; border: none; outline: none;
    }
    .action-icons img:hover { filter: brightness(0.7); }
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); justify-content: center; align-items: center; }
    .modal-content {
      background-color: #F9A6FF; border-radius: 15px; padding: 2rem; text-align: center; width: 370px;
      animation: zoomIn 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.25);
    }
    .modal-content h2 { margin-top: 0; font-size: 1.4rem; color: #222; }
    .modal-content hr { border: none; height: 2px; background: #2d365e; width: 80%; margin: 10px auto 20px; }
    .modal-close { background: #d07676; color: #222; border: none; border-radius: 8px; padding: 12px 24px; font-weight: bold; margin-top: 22px; font-size: 16px; cursor: pointer; }
    .modal-close:hover { filter: brightness(0.8);}
  </style>
</head>
<body>
<!-- Sidebar & Navigation -->
<button onclick="toggleSidebar()" class="toggle-btn"></button>
<div id="sidebar" class="sidebar">
  <div class="logo2" onclick="toggleSidebar()">
    <img src="../images/MTB-MAL_logo_side.png" alt="MTB-MAL Logo" />
  </div>
  <nav class="nav-links">
    <a href="welcome_admin.php"><span class="icon"> 🏠 </span> Dashboard </a>
    <a href="view_subjects.php"><span class="icon"> 📚 </span> View MTB-MLE Subjects</a>
    <a href="mtbmal_accs.php"><span class="icon"> 👥 </span> View MTB-MAL Accounts </a>
  </nav>
</div>
<div id="sidebar-overlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>

<div class="main-content">
  <div class="topbar">
    <div class="left">
      <div class="logo-topbar" onclick="toggleSidebar()">
        <img src="../images/MTB-MAL_logo-alt.png" alt="MTB-MAL Logo">
      </div>
      <div class="system-title">Mother Tongue-Based Multilingual Assessment and Learning System</div>
    </div>
    <div class="right">
      <div class="language-selector" onclick="toggleDropdown1()">
        <div class="language">🌐 English</div>
        <div id="dropdown-arrow1" class="dropdown-arrow1 down"></div>
        <div id="lang-dropdown-menu" class="lang-dropdown hidden">
          <div class="dropdown-item">Feature Available Soon</div>
        </div>
      </div>
    </div>
  </div>

  <div class="second-bar">
    <span>Manage MTB-MAL Accounts</span>
    <div class="profile-container" onclick="toggleDropdown2()">
      <div class="profile-circle"></div>
      <div class="profile-name" style="margin-left:8px;font-size:20px;font-weight:600;"><?php echo htmlspecialchars($adminName); ?></div>
      <div id="dropdown-arrow2" class="dropdown-arrow2 down"></div>
      <div id="profile-dropdown-menu" class="profile-dropdown hidden">
        <a href="admin_view_profile.php"><div class="dropdown-item">View Profile</div></a>
        <a href="../login/logout.php"><div class="dropdown-item">Logout</div></a>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="form-section">
      <div class="column">
        <div class="user-table-header">
          <div class="user-table-header button-group">
            <button class="btn selected" id="btn-all">All Users</button>
            <button class="btn" id="btn-admin">School Administrator</button>
            <button class="btn" id="btn-educator">Educator</button>
            <button class="btn" id="btn-student">Student</button>
          </div>
          <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search Registered Account">
            <img class="search-icon" src="../images/search.png" alt="Search Icon">
          </div>
        </div>

        <table class="account-table" id="accountTable">
          <thead>
            <tr>
              <th>No.</th>
              <th>Reference Number</th>
              <th>Name</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($users)): ?>
            <tr id="no-user-row">
              <td colspan="4" style="text-align:center;font-style:italic;">
                No users found for your school.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($users as $i => $user): 
              $ref = $name = "";
              if ($user['accType'] === "School Administrator") {
                  $ref = "AD-" . $user['accRefNo'];
                  $name = $user['adminFullName'] ?: "{$user['firstName']} {$user['lastName']}";
              } elseif ($user['accType'] === "Educator") {
                  $ref = "ED-" . $user['accRefNo'];
                  $name = $user['edFullName'] ?: "{$user['firstName']} {$user['lastName']}";
              } elseif ($user['accType'] === "Student") {
                  $ref = "ST-" . $user['accRefNo'];
                  $name = $user['stFullName'] ?: "{$user['firstName']} {$user['lastName']}";
              }
            ?>
            <tr data-type="<?= strtolower(str_replace(' ', '-', $user['accType'])) ?>">
              <td><strong><?= $i + 1 ?></strong></td>
              <td><?= htmlspecialchars($ref) ?></td>
              <td><?= htmlspecialchars($name) ?></td>
              <td class="action-icons">
                <a href="edit_user.php?accRefNo=<?= $user['accRefNo'] ?>" title="Edit">
                  <img src="../images/edit.png" alt="Edit">
                </a>
                <a href="#" onclick="confirmDelete(<?= $user['accRefNo'] ?>)" title="Delete">
                  <img src="../images/delete.png" alt="Delete">
                </a>
                <img src="../images/info2.png" alt="Info" style="margin-left:2px;" onclick="openModal('modal_<?= $user['accRefNo'] ?>')">
              </td>
            </tr>
            <?php endforeach; ?>
            <tr id="no-user-row" style="display:none;">
              <td colspan="4" style="text-align:center;font-style:italic;">No users found for this filter.</td>
            </tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Info Modals -->
  <?php foreach ($users as $user): ?>
  <div id="modal_<?= $user['accRefNo'] ?>" class="modal">
    <div class="modal-content">
      <h2>User Information</h2>
      <hr>
      <strong>Account Type:</strong> <?= htmlspecialchars($user['accType']) ?><br>
      <strong>Name:</strong> <?= htmlspecialchars(($user['adminFullName'] ?? $user['edFullName'] ?? $user['stFullName'] ?? "{$user['firstName']} {$user['lastName']}")) ?><br>
      <strong>Username:</strong> <?= htmlspecialchars($user['username']) ?><br>
      <strong>Email:</strong> <?= htmlspecialchars($user['emailAddress']) ?><br>
      <strong>Contact:</strong> <?= htmlspecialchars($user['contactNo']) ?><br>
      <?php if ($user['accType'] == 'School Administrator'): ?>
        <strong>Employee ID:</strong> <?= htmlspecialchars($user['adEmpIdNo']) ?><br>
      <?php elseif ($user['accType'] == 'Educator'): ?>
        <strong>Employee ID:</strong> <?= htmlspecialchars($user['edEmpIdNo']) ?><br>
      <?php elseif ($user['accType'] == 'Student'): ?>
        <strong>LRN:</strong> <?= htmlspecialchars($user['lrn']) ?><br>
      <?php endif; ?>
      <button class="modal-close" onclick="closeModal('modal_<?= $user['accRefNo'] ?>')">Close</button>
    </div>
  </div>
  <?php endforeach; ?>

  <footer class="footer">
    Mother Tongue-Based Multilingual Assessment and Learning System © 2025
  </footer>
</div>

<script>
function toggleDropdown1() {
  document.getElementById('dropdown-arrow1').classList.toggle('down');
  document.getElementById('dropdown-arrow1').classList.toggle('up');
  document.getElementById('lang-dropdown-menu').classList.toggle('hidden');
}
function toggleDropdown2() {
  document.getElementById('dropdown-arrow2').classList.toggle('down');
  document.getElementById('dropdown-arrow2').classList.toggle('up');
  document.getElementById('profile-dropdown-menu').classList.toggle('hidden');
}
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('visible');
  document.getElementById('sidebar-overlay').classList.toggle('visible');
}
function openModal(modalId) {
  document.getElementById(modalId).style.display = 'flex';
}
function closeModal(modalId) {
  document.getElementById(modalId).style.display = 'none';
}

// -- Filter + Search, both work together! --
document.addEventListener('DOMContentLoaded', function() {
  let activeType = ""; // "" for all
  let searchInput = document.getElementById('searchInput');
  let rows = document.querySelectorAll('#accountTable tbody tr[data-type]');

  // Button handlers
  document.getElementById('btn-all').onclick = function() {
    setActiveButton('btn-all'); activeType = ""; updateRows();
  };
  document.getElementById('btn-admin').onclick = function() {
    setActiveButton('btn-admin'); activeType = "school-administrator"; updateRows();
  };
  document.getElementById('btn-educator').onclick = function() {
    setActiveButton('btn-educator'); activeType = "educator"; updateRows();
  };
  document.getElementById('btn-student').onclick = function() {
    setActiveButton('btn-student'); activeType = "student"; updateRows();
  };

  searchInput.addEventListener('input', updateRows);

  function setActiveButton(activeId) {
    document.querySelectorAll('.button-group .btn').forEach(btn => {
      btn.classList.toggle('selected', btn.id === activeId);
    });
  }

  function updateNoColumn() {
    // Only count rows that are visible and have data-type (not the 'no-user-row')
    const rows = document.querySelectorAll('#accountTable tbody tr[data-type]');
    let visibleIndex = 1;
    rows.forEach(row => {
      if (row.style.display !== 'none') {
        row.querySelector('td').innerHTML = '<strong>' + visibleIndex + '</strong>';
        visibleIndex++;
      }
    });
  }

  function updateRows() {
    let search = searchInput.value.toLowerCase();
    let anyShown = false;
    rows.forEach(row => {
      let rowType = row.getAttribute('data-type');
      let rowText = row.textContent.toLowerCase();
      let matchType = !activeType || rowType === activeType;
      let matchSearch = !search || rowText.includes(search);
      let show = matchType && matchSearch;
      row.style.display = show ? '' : 'none';
      if (show) anyShown = true;
    });

    // Handle "no users" message
    let msgRow = document.getElementById('no-user-row');
    if (!anyShown) {
      if (msgRow) msgRow.style.display = '';
    } else {
      if (msgRow) msgRow.style.display = 'none';
    }

    // Always renumber "No." column for visible rows
    updateNoColumn();
  }

  // Initial display
  updateRows();
});

// Confirm delete
function confirmDelete(accRefNo) {
  if (confirm('Are you sure you want to delete this user?')) {
    window.location.href = `delete_user.php?accRefNo=${accRefNo}`;
  }
}
</script>

</body>
</html>
