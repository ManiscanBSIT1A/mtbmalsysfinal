<?php
session_start();

$timeout_duration = 1800;

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: ../login/login.php?timeout=1");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

require_once '../sql/db_connect.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../login/login.php');
    exit();
}

if ($_SESSION['accType'] !== 'Educator') {
    switch ($_SESSION['accType']) {
        case 'School Administrator': header('Location: ../admin/welcome_admin.php'); break;
        case 'Student': header('Location: ../student/welcome_student.php'); break;
        default: header('Location: ../login/login.php');
    }
    exit();
}

$educatorName = isset($_SESSION['firstName']) ? htmlspecialchars($_SESSION['firstName']) : 'Educator';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <link rel="icon" type="image/png" href="../images/MTB-MAL_logo.png">
  <link rel="stylesheet" href="../style/welcome_educator-style.css">
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
        <a href="welcome_educator.php"><span class="icon">🏠</span> Dashboard</a>
        <a href="educator_subject-view.php"><span class="icon">📚</span> My Subjects</a>
        <a href="educator_manage_student_records.php"><span class="icon">👥</span> My Students</a>
        <a href="../login/about.php"><span class="icon">📖</span> About MTB-MAL</a>
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

    <!-- Second Navigation Bar -->
    <div class="second-bar">
        <span>Dashboard</span>
       <div class="profile-container" onclick="toggleDropdown2()">
    <div class="profile-circle"></div>
    <span style="font-size:18px; font-weight:bold; margin-left:10px;"><?php echo $educatorName; ?></span>
    <div id="dropdown-arrow2" class="dropdown-arrow2 down"></div>
    <div id="profile-dropdown-menu" class="profile-dropdown hidden">
        <a href="educator_view_profile.php"><div class="dropdown-item">View Profile</div></a>
        <a href="../login/logout.php"><div class="dropdown-item">Logout</div></a>
        </div>
    </div>
        </div>
    </div>

    <!-- Container -->
    <div class="container">
        <!-- Dashboard Cards -->
        <div class="dashboard-container">
            <a href="educator_view_subjects.php" class="dashboard-card-link">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2>Manage MTB-MLE Subjects</h2>
                        <span class="icon">📚</span>
                    </div>
                    <div class="card-body">
                        <p><strong>Manage the Assigned MTB-MLE Subjects</strong></p>
                        <p>View the Details of the Assigned MTB-MLE Subject and access tools for managing students and learning materials.</p>
                    </div>
                </div>
            </a>
            <a href="educator_manage_student_records.php" class="dashboard-card-link">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2>Manage Enrolled Students</h2>
                        <span class="icon">👤</span>
                    </div>
                    <div class="card-body">
                        <p><strong>Access and update student enrollment and performance data.</strong></p>
                        <p>View and manage student records, including enrollment info and academic performance for each subject you handle.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
      Mother Tongue-Based Multilingual Assessment and Learning System © 2025
    </footer>

    <script>
        // Toggle language dropdown visibility
        function toggleDropdown1() {
            const arrow = document.getElementById('dropdown-arrow1');
            const menu = document.getElementById('lang-dropdown-menu');
            arrow.classList.toggle('down');
            arrow.classList.toggle('up');
            menu.classList.toggle('hidden');
        }
        // Toggle profile dropdown visibility
        function toggleDropdown2() {
            const arrow = document.getElementById('dropdown-arrow2');
            const menu = document.getElementById('profile-dropdown-menu');
            arrow.classList.toggle('down');
            arrow.classList.toggle('up');
            menu.classList.toggle('hidden');
        }
        // Toggle sidebar and overlay visibility
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const logoImg = document.querySelector('.logo2 img');
            sidebar.classList.toggle('visible');
            overlay.classList.toggle('visible');
            // Reset logo image when sidebar is toggled
            logoImg.src = '../images/MTB-MAL_logo_side.png';
        }
    </script>
</body>
</html>
