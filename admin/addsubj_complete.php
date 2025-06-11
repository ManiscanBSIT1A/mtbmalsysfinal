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

// Get admin's first name for display
$adminFirstName = $_SESSION['firstName'] ?? 'Admin';

// Get the new subject's refno from URL
$subjectRefNo = '';
if (isset($_GET['refno']) && is_numeric($_GET['refno'])) {
    require_once("../sql/db_connect.php");
    $refno = intval($_GET['refno']);
    $stmt = $conn->prepare("SELECT subjectRefNo FROM subject WHERE subjectRefNo = ?");
    $stmt->bind_param("i", $refno);
    $stmt->execute();
    $stmt->bind_result($foundRefNo);
    if ($stmt->fetch()) {
        $subjectRefNo = $foundRefNo;
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Subject Creation Complete!</title>
  <link rel="icon" type="image/png" href="../images/MTB-MAL_logo.png">
  <link rel="stylesheet" href="../style/add_mtbsubject-style.css">
</head>
<body>

<!-- Sidebar Toggle Button -->
<button onclick="toggleSidebar()" class="toggle-btn" style="cursor: pointer;"></button>

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

    <!-- Second Navigation Bar -->
    <div class="second-bar">
        <span>Subject Creation</span>
        <div class="profile-container" onclick="toggleDropdown2()">
            <div class="profile-circle"></div>
            <div class="profile-name"><?php echo htmlspecialchars($adminFirstName); ?></div>
            <div id="dropdown-arrow2" class="dropdown-arrow2 down"></div>
            <div id="profile-dropdown-menu" class="profile-dropdown hidden">
                <a href="admin_view_profile.php"><div class="dropdown-item">View Profile</div></a>
                <a href="../login/logout.php"><div class="dropdown-item">Logout</div></a>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <div class="container">
      <div class="form-section">
        <div class="column">
          <div class="registration-box">
            <div class="checkmark-image">
              <img src="../images/checkmark.png" alt="Checkmark">
            </div>
            <h2 class="registration-title">Subject Creation Complete!</h2>
            <p class="registration-subtitle">Please save the details below for future reference.</p>

            <!-- Subject Reference Display -->
            <div class="input-group">
              <label class="label-text">Subject Reference Number:</label>
              <input type="text" readonly value="<?php echo $subjectRefNo ? 'SB - ' . $subjectRefNo : 'Not Available'; ?>" class="reference-input" />
            </div>
            <button type="button" onclick="window.location.href='manage_subject_options.php'" class="done-button">
              Done
            </button>
          </div>
        </div>
      </div>
    </div>
</div>

<footer class="footer">
    Mother Tongue-Based Multilingual Assessment and Learning System © 2025
</footer>

<script>
    function toggleDropdown1() {
        const arrow = document.getElementById('dropdown-arrow1');
        const menu = document.getElementById('lang-dropdown-menu');
        arrow.classList.toggle('down');
        arrow.classList.toggle('up');
        menu.classList.toggle('hidden');
    }
    function toggleDropdown2() {
        const arrow = document.getElementById('dropdown-arrow2');
        const menu = document.getElementById('profile-dropdown-menu');
        arrow.classList.toggle('down');
        arrow.classList.toggle('up');
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
