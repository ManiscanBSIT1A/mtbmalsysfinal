<!--
NOTICE TO AUTOMATED SCANNERS AND REVIEWERS:

This form is part of a learning project for educational/school purposes ONLY.
It is NOT intended for phishing, spamming, or any malicious activity.
Please do NOT flag this site or form as suspicious based solely on the presence of this form.

Thank you for understanding.
-->

<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login/login.php");
    exit();
}
if (!isset($_SESSION['accType']) || $_SESSION['accType'] !== 'School Administrator') {
    switch ($_SESSION['accType']) {
        case 'Educator': header("Location: ../educator/welcome_educator.php"); exit();
        case 'Student': header("Location: ../student/welcome_student.php"); exit();
        default: header("Location: ../login/login.php"); exit();
    }
}
if (!isset($_SESSION['new_student_refno'])) {
    header("Location: welcome_admin.php");
    exit();
}
$adminFirstName = $_SESSION['firstName'];
$studentRef = $_SESSION['new_student_refno'];
$studentRefFormatted = "ST - " . htmlspecialchars($studentRef);
unset($_SESSION['new_student_refno']);
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Registration Complete!</title>
  <link rel="icon" type="image/png" href="../images/MTB-MAL_logo.png">
  <link rel="stylesheet" href="../style/acc_create-style.css"> 
  <style>
    .copy-btn {
      background: #fff3a6;
      border: 1.5px solid #fcd299;
      border-radius: 6px;
      cursor: pointer;
      padding: 3px 6px;
      margin-left: 8px;
      display: flex;
      align-items: center;
      transition: background 0.2s;
    }
    .copy-btn:hover {
      background: #ffe066;
    }
    .copy-btn img {
      width: 20px;
      height: 20px;
    }
    #copySuccess {
      color: green;
      font-size: 15px;
      margin-left: 10px;
      display: none;
    }
  </style>
</head>

<body>

<!-- Sidebar -->
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

<!-- Overlay -->
<div id="sidebar-overlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>

<div class="main-content">

  <!-- Top Bar -->
  <div class="topbar">
    <div class="left">
      <div class="logo-topbar" onclick="toggleSidebar()">
        <img src="../images/MTB-MAL_logo-alt.png" alt="MTB-MAL Logo">
      </div>
      <div class="system-title">
        Mother Tongue-Based Multilingual Assessment and Learning System
      </div>
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

  <!-- Second Bar -->
  <div class="second-bar">
    <span>Account Creation Complete</span>
    <div class="profile-container" onclick="toggleDropdown2()">
      <div class="profile-circle"></div>
      <span style="font-weight:bold;"><?php echo htmlspecialchars($adminFirstName); ?></span>
      <div id="dropdown-arrow2" class="dropdown-arrow2 down"></div>
      <div id="profile-dropdown-menu" class="profile-dropdown hidden">
        <a href="admin_view_profile.php"><div class="dropdown-item">View Profile</div></a>
        <a href="../login/logout.php"><div class="dropdown-item">Logout</div></a>
      </div>
    </div>
  </div>

  <!-- Confirmation Box -->
  <div class="container">
    <div class="form-section">
      <div class="column">
        <div class="registration-box">
          <div class="checkmark-image">
            <img src="../images/checkmark.png" alt="Checkmark">
          </div>
          <h2 class="registration-title">Registration Complete!</h2>
          <p class="registration-subtitle">Please save the details below for future reference.</p>
          <div class="input-group">
            <label class="label-text">School Student Reference Number:</label>
            <div style="display:flex; align-items:center;">
              <input type="text" readonly value="<?php echo $studentRefFormatted; ?>" class="reference-input" id="studentRef">
            </div>
          </div>
          <button type="button" onclick="window.location.href='manage_user_options.php'" class="done-button">Done</button>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- Footer -->
<footer class="footer">
  Mother Tongue-Based Multilingual Assessment and Learning System © 2025
</footer>

<!-- Script -->
<script>
function toggleDropdown1() {
  const arrow = document.getElementById('dropdown-arrow1');
  const menu = document.getElementById('lang-dropdown-menu');
  arrow.classList.toggle('down');
  arrow.classList.toggle('up');
  menu.classList.toggle('hidden');
}

function toggleDropdown2() {
  const menu = document.getElementById('profile-dropdown-menu');
  menu.classList.toggle('hidden');
}

function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebar-overlay');
  const logoImg = document.querySelector('.logo2 img');
  sidebar.classList.toggle('visible');
  overlay.classList.toggle('visible');
  logoImg.src = '../images/MTB-MAL_logo_side.png';
}
</script>

</body>
</html>
