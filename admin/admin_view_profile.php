<?php
session_start();
include '../sql/db_connect.php';

// Strict role-based redirect
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login/login.php");
    exit();
}
if (!isset($_SESSION['accType']) || $_SESSION['accType'] !== 'School Administrator') {
    // Redirect other user types to their dashboards
    switch ($_SESSION['accType']) {
        case 'Educator': header("Location: ../educator/welcome_educator.php"); exit();
        case 'Student': header("Location: ../student/welcome_student.php"); exit();
        default: header("Location: ../login/login.php"); exit();
    }
}

$adminAccRefNo = $_SESSION['accRefNo'];
$adminName = $_SESSION['firstName'] ?? 'Admin';

// Get admin profile info
$adminProfile = [];
$sql = "SELECT u.accRefNo, u.firstName, u.lastName, u.dob, u.emailAddress, u.contactNo, u.username,
               sa.fullName AS adminFullName, sa.adEmpIdNo
        FROM mtbmalusers u
        LEFT JOIN schooladministrator sa ON u.accRefNo = sa.accRefNo
        WHERE u.accRefNo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $adminAccRefNo);
$stmt->execute();
$result = $stmt->get_result();
$adminProfile = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Profile</title>
  <link rel="icon" type="image/png" href="../images/MTB-MAL_logo.png">
  <link rel="stylesheet" href="../style/mtbmal_accs-style.css">
  <style>
    .profile-container-main {
      margin: 0 auto;
      margin-top: 40px;
      background: #f9a6ff;
      padding: 35px 35px 25px 35px;
      border-radius: 15px;
      max-width: 520px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.10);
      text-align: left;
    }
    .profile-container-main h2 {
      text-align: center;
      margin-bottom: 18px;
      color: #620e7a;
    }
    .profile-details label {
      display: block;
      font-weight: bold;
      margin-bottom: 4px;
      color: #4a2671;
      margin-top: 15px;
    }
    .profile-details .profile-value {
      font-size: 17px;
      color: #2a2a2a;
      background: #ffe6fc;
      padding: 8px 12px;
      border-radius: 8px;
      margin-bottom: 6px;
      display: block;
      border: 1px solid #d7a6e7;
    }
    .profile-details {
      margin-bottom: 15px;
    }
    .back-btn {
      background-color: #b9cfff;
      color: #222;
      font-weight: bold;
      border: 2px solid #6f6fd7;
      border-radius: 8px;
      padding: 10px 30px;
      margin: 0 auto;
      display: block;
      cursor: pointer;
      font-size: 17px;
      transition: filter 0.2s;
    }
    .back-btn:hover {
      filter: brightness(0.95);
    }
    @media (max-width: 600px) {
      .profile-container-main { max-width: 97vw; padding: 10px; }
    }
  </style>
</head>
<body>
<!-- Sidebar -->
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
  <span>My Profile</span>
  <div class="profile-container" onclick="toggleDropdown2()">
    <div class="profile-circle"></div>
    <span style="font-weight:bold;"><?php echo htmlspecialchars($adminName); ?></span>
    <div id="dropdown-arrow2" class="dropdown-arrow2 down"></div>
    <div id="profile-dropdown-menu" class="profile-dropdown hidden">
      <a href="admin_view_profile.php"><div class="dropdown-item">View Profile</div></a>
      <a href="../login/logout.php"><div class="dropdown-item">Logout</div></a>
    </div>
  </div>
</div>

<div class="profile-container-main">
  <h2>My Profile</h2>
  <div class="profile-details">
    <label>Full Name</label>
    <span class="profile-value"><?= htmlspecialchars($adminProfile['adminFullName'] ?: $adminProfile['firstName'] . ' ' . $adminProfile['lastName']) ?></span>

    <label>Username</label>
    <span class="profile-value"><?= htmlspecialchars($adminProfile['username']) ?></span>

    <label>Email</label>
    <span class="profile-value"><?= htmlspecialchars($adminProfile['emailAddress']) ?></span>

    <label>Contact No.</label>
    <span class="profile-value"><?= htmlspecialchars($adminProfile['contactNo']) ?></span>

    <label>Employee ID</label>
    <span class="profile-value"><?= htmlspecialchars($adminProfile['adEmpIdNo']) ?></span>

    <label>Date of Birth</label>
    <span class="profile-value"><?= htmlspecialchars($adminProfile['dob']) ?></span>
  </div>
  <button class="back-btn" onclick="window.location.href='welcome_admin.php'">&larr; Back to Dashboard</button>
</div>

<footer class="footer">
  Mother Tongue-Based Multilingual Assessment and Learning System © 2025
</footer>

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
</script>
</body>
</html>
