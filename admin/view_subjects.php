<?php
session_start();
include '../sql/db_connect.php';

// SESSION TIMEOUT (optional)
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: ../login/login.php?timeout=1");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

// === ACCESS CONTROL FOR ROLES ===
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login/login.php");
    exit();
}
if (!isset($_SESSION['accType'])) {
    header("Location: ../login/login.php");
    exit();
}
switch ($_SESSION['accType']) {
    case 'School Administrator':
        break; // Allowed
    case 'Educator':
        header("Location: ../educator/welcome_educator.php");
        exit();
    case 'Student':
        header("Location: ../student/welcome_student.php");
        exit();
    default:
        header("Location: ../login/login.php");
        exit();
}

$adminFirstName = $_SESSION['firstName'];
$adminSchoolIdNo = $_SESSION['schoolIdNo'];
$adminAccRefNo = $_SESSION['accRefNo'];

// FETCH SUBJECTS FOR THIS SCHOOL
$subjects = [];
$sql = "SELECT * FROM subject WHERE schoolIdNo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $adminSchoolIdNo);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $subjects[] = $row;
}
$stmt->close();

function getEducatorName($conn, $educAccRefNo) {
    if (!$educAccRefNo) return '';
    $sql = "SELECT firstName, lastName FROM mtbmalusers WHERE accRefNo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $educAccRefNo);
    $stmt->execute();
    $stmt->bind_result($fname, $lname);
    $stmt->fetch();
    $stmt->close();
    return $fname && $lname ? "$fname $lname" : "";
}
function getEnrolledCount($conn, $subjectRefNo) {
    $sql = "SELECT COUNT(*) FROM enrolled_students WHERE subjectRefNo = ? AND status = 'Enrolled'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $subjectRefNo);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count;
}
function formatSubjectIdNo($subjectIdNo) {
    return $subjectIdNo;}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Subjects</title>
  <link rel="icon" type="image/png" href="../images/MTB-MAL_logo.png">
  <link rel="stylesheet" href="../style/view_subjects-style.css">
  <style>
    .delete-btn {
      background: none; border: none; outline: none;
      padding: 0; margin: 0; cursor: pointer; box-shadow: none; display: inline-flex; align-items: center;
    }
    .delete-btn img { width: 24px; height: 22px; }
    .delete-btn:hover img { filter: brightness(0.75); }
    .delete-btn:focus { outline: none; }
    /* Sidebar is hidden by default, only .visible makes it show */
    .sidebar, .sidebar-overlay { display: none; }
    .sidebar.visible, .sidebar-overlay.visible { display: block; }
    body.sidebar-open { overflow: hidden; }
  </style>
</head>
<body>
<!-- Sidebar (hidden until toggled) -->
<div id="sidebar" class="sidebar">
    <div class="logo2">
        <img src="../images/MTB-MAL_logo_side.png" alt="MTB-MAL Logo" />
    </div>
  <nav class="nav-links">
      <nav class="nav-links">
        <a href="welcome_admin.php"><span class="icon"> 🏠 </span> Dashboard </a>
        <a href="view_subjects.php"><span class="icon"> 📚 </span> View MTB-MLE Subjects</a>
        <a href="mtbmal_accs.php"><span class="icon"> 👥 </span> View MTB-MAL Accounts </a>
      </nav>
</div>
<div id="sidebar-overlay" class="sidebar-overlay"></div>

<div class="main-content">
    <!-- Top Bar -->
    <div class="topbar">
        <div class="left">
            <div class="logo-topbar" id="sidebarToggler" style="cursor:pointer;">
                <img src="../images/MTB-MAL_logo-alt.png" alt="MTB-MAL Logo">
            </div>
            <div class="system-title">
                Mother Tongue-Based Multilingual Assessment and Learning System
            </div>
        </div>
        <div class="right">
            <div class="language-selector" id="langSel">
                <div class="language">🌐 English</div>
                <div id="dropdown-arrow1" class="dropdown-arrow1 down"></div>
                <div id="lang-dropdown-menu" class="lang-dropdown hidden">
                    <div class="dropdown-item">Feature Available Soon</div>
                </div>
            </div>
        </div>
    </div>
    <!-- Second Navigation Bar -->
    <div class="second-bar">
        <span>View Subjects</span>
        <div class="profile-container" id="profileSel">
            <div class="profile-circle"></div>
            <span style="font-weight:bold;font-size:20px;margin-left:12px;"><?php echo htmlspecialchars($adminFirstName); ?></span>
            <div id="dropdown-arrow2" class="dropdown-arrow2 down"></div>
            <div id="profile-dropdown-menu" class="profile-dropdown hidden">
                <a href="admin_view_profile.php"><div class="dropdown-item">View Profile</div></a>
                <a href="../login/logout.php"><div class="dropdown-item">Logout</div></a>
            </div>
        </div>
    </div>
    <!-- Main Section -->
    <div class="container">
      <div class="form-section">
        <div class="column">
          <div class="user-table-header">
            <div class="user-table-header button-group">
              <button class="btn selected" id="btn-subject">Subjects</button>
            </div>
            <div class="search-container">
              <input type="text" id="subjectSearch" placeholder="Search MTB-MLE Subject" onkeyup="filterSubjects()">
              <img class="search-icon" src="../images/search.png" alt="Search Icon">
            </div>
          </div>
          <table class="account-table" id="subjectsTable">
            <thead>
              <tr>
                <th>No.</th>
                <th>Subject Identification Number</th>
                <th>Subject Title</th>
                <th>Assigned Educator</th>
                <th>Total Number of Students</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
            <?php if (empty($subjects)): ?>
              <tr><td colspan="6" style="text-align:center;font-style:italic;">No subjects found for your school.</td></tr>
            <?php else: ?>
              <?php foreach ($subjects as $i => $subj): ?>
                <tr>
                  <td><strong><?= $i + 1 ?></strong></td>
                  <td><?= htmlspecialchars($subj['subjectIdNo']) ?></td>
                  <td><?= htmlspecialchars($subj['subjTitle']) ?></td>
                  <td>
                    <?php
                      $educatorName = getEducatorName($conn, $subj['assignedEducator']);
                      echo htmlspecialchars($educatorName ?: '-');
                    ?>
                  </td>
                  <td>
                    <?php
                      $totalStudents = getEnrolledCount($conn, $subj['subjectRefNo']);
                      echo $totalStudents;
                    ?>
                  </td>
                    <td>
                      <span class="action-icons">
                        <!-- EDIT ICON -->
                        <a href="edit_subject.php?subjectRefNo=<?= $subj['subjectRefNo'] ?>" title="Edit Subject">
                          <img src="../images/edit.png" alt="Edit">
                        </a>
                        <!-- DELETE ICON -->
                        <form method="POST" action="delete_subject.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this subject?');">
                          <input type="hidden" name="subjectRefNo" value="<?= $subj['subjectRefNo'] ?>">
                          <button type="submit" class="delete-btn" title="Delete Subject" tabindex="0">
                            <img src="../images/delete.png" alt="Delete">
                          </button>
                        </form>
                        <!-- INFO ICON -->
                        <img src="../images/info.png" alt="Info Icon" style="cursor:pointer;" tabindex="0" onclick="openModal('modal_<?= $subj['subjectRefNo'] ?>')">
                      </span>
                    </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modals for Subject Info -->
    <?php foreach ($subjects as $subj): ?>
    <div id="modal_<?= $subj['subjectRefNo'] ?>" class="modal">
      <div class="modal-content2">
        <h1>Subject Info</h1>
        <hr class="hr-main-title">
        <table class="info-bar-table" style="position: relative; left:80px;">
          <tr>
            <th>Subject Title</th>
            <th>Language</th>
            <th>Subject Identification Number</th>
          </tr>
          <tr>
            <td><?= htmlspecialchars($subj['subjTitle']) ?></td>
            <td><?= htmlspecialchars($subj['mtLanguage']) ?></td>
            <td><?= formatSubjectIdNo($subj['subjectIdNo']) ?></td>
          </tr>
        </table>
        <hr class="hr-section-divider">
        <table class="info-bar-table">
          <tr>
            <th>Assigned Educator</th>
            <th>Educator Reference Number</th>
            <th>School Identification Number</th>
          </tr>
          <tr>
            <td><?= htmlspecialchars(getEducatorName($conn, $subj['assignedEducator'])) ?></td>
            <td><?= $subj['assignedEducator'] ? "ED-" . $subj['assignedEducator'] : '-' ?></td>
            <td><?= $subj['schoolIdNo'] ?></td>
          </tr>
        </table>
        <hr class="hr-section-divider">
        <table class="info-bar-table">
          <tr>
            <th>Subject Creator (Admin)</th>
            <th>Admin Reference Number</th>
          </tr>
          <tr>
            <td><?= htmlspecialchars($adminFirstName) ?></td>
            <td><?= "AD-" . $adminAccRefNo ?></td>
          </tr>
        </table>
        <hr class="hr-section-divider">
        <div style="background-color: #C7FF80; border-radius: 10px; padding: 20px; margin: 20px auto; width: 95%; font-family: sans-serif; line-height:2em;">
          <h3 style="margin-top: 0; font-size: 25px;">Subject Description</h3>
          <p style="margin-top: 0; padding-top:0; text-align:justify;">
            <?= htmlspecialchars($subj['subjDescription']) ?>
          </p>
        </div>
        <div class="modal-buttons-row">
          <button class="modal-close2" onclick="closeModal('modal_<?= $subj['subjectRefNo'] ?>')">Close</button>
        </div>
      </div>
    </div>
    <?php endforeach; ?>

    <footer class="footer">
        Mother Tongue-Based Multilingual Assessment and Learning System © 2025
    </footer>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    sidebar.classList.toggle('visible');
    overlay.classList.toggle('visible');
    document.body.classList.toggle('sidebar-open');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('visible');
    document.getElementById('sidebar-overlay').classList.remove('visible');
    document.body.classList.remove('sidebar-open');
}
function toggleDropdown1() {
    const arrow = document.getElementById('dropdown-arrow1');
    const menu = document.getElementById('lang-dropdown-menu');
    arrow.classList.toggle('down');
    arrow.classList.toggle('up');
    menu.classList.toggle('hidden');
}
function openModal(modalId) {
    document.getElementById(modalId).style.display = "flex";
}
function closeModal(modalId) {
    document.getElementById(modalId).style.display = "none";
}
function filterSubjects() {
    let input = document.getElementById("subjectSearch").value.toLowerCase();
    let table = document.getElementById("subjectsTable").getElementsByTagName('tbody')[0];
    let rows = table.getElementsByTagName('tr');
    for (let i = 0; i < rows.length; i++) {
        let cells = rows[i].getElementsByTagName('td');
        if (cells.length > 2) {
            let subjectTitle = cells[2].innerText.toLowerCase();
            rows[i].style.display = subjectTitle.indexOf(input) > -1 ? "" : "none";
        }
    }
}
function toggleDropdown2() {
    const menu = document.getElementById('profile-dropdown-menu');
    menu.classList.toggle('hidden');
}
window.onclick = function(e) {
    if (!e.target.closest('#profileSel')) {
      document.getElementById('profile-dropdown-menu').classList.add('hidden');
    }
    if (!e.target.closest('#langSel')) {
      document.getElementById('lang-dropdown-menu').classList.add('hidden');
    }
    if (!e.target.closest('#sidebar') && !e.target.closest('#sidebarToggler')) {
      closeSidebar();
    }
};
document.getElementById('sidebarToggler').onclick = function(e) {
    toggleSidebar();
    e.stopPropagation();
};
document.getElementById('sidebar-overlay').onclick = function(e) {
    closeSidebar();
};
document.getElementById('langSel').onclick = function(e) {
    toggleDropdown1();
    e.stopPropagation();
};
document.getElementById('profileSel').onclick = function(e) {
    toggleDropdown2();
    e.stopPropagation();
};
</script>
</body>
</html>
