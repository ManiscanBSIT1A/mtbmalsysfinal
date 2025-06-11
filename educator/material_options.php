<?php
session_start();
include '../sql/db_connect.php';

$inactive = 1800; // seconds
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive)) {
    session_unset();
    session_destroy();
    header("Location: ../login/login.php?session_expired=1");
    exit();
}
$_SESSION['last_activity'] = time();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login/login.php");
    exit();
}
if (!isset($_SESSION['accType']) || $_SESSION['accType'] !== 'Educator') {
    // Redirect to correct dashboard
    switch ($_SESSION['accType']) {
        case 'School Administrator': header("Location: ../admin/welcome_admin.php"); exit();
        case 'Student': header("Location: ../student/welcome_student.php"); exit();
        default: header("Location: ../login/login.php"); exit();
    }
}
$educatorName = htmlspecialchars($_SESSION['firstName'] ?? 'Educator');

$subjectRefNo = isset($_GET['subjectRefNo']) ? $_GET['subjectRefNo'] : '';
if (empty($subjectRefNo)) {
    die('Invalid subject. Please select a subject from My Subjects.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Learning Material Options</title>
  <link rel="icon" type="image/png" href="../images/MTB-MAL_logo.png">
  <link rel="stylesheet" href="../style/material_options-style.css">
  <style>
    body { background-color: #ffe6d2; }
    .topbar { background-color: #eeb18d; }
    .topbar::before { opacity: 0.20; }
    .system-title { color: #5b3926; }
    .second-bar { background-color: #ffdaab; border-bottom: 4px solid #b87330; color: #703c10; }
    .nav-links a { background: #ffefad; border-color: #b87330; color: #462d15; }
    .user-card { background-color: #ffcf91; }
    .user-card:hover { background-color: #ffd4a6; }
    .footer { background-color: #eeb18d; border-top: 2px solid #deb67d; }
  </style>
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
        <a href="welcome_educator.php"><span class="icon">🏠</span> Dashboard </a>
        <a href="educator_view_subjects.php"><span class="icon">📚</span> My Subjects </a>
        <a href="templates.php"><span class="icon">📦</span> Manage Subject Learning Materials </a>
        <a href="../login/about.php"><span class="icon">📖</span> About MTB-MAL</a>
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
        <span>Learning Material</span>
        <div class="profile-container" onclick="toggleDropdown2()">
            <div class="profile-circle"></div>
            <span style="font-weight:bold; margin-left:8px; color:#574334;">
                <?= $educatorName ?>
            </span>
            <div id="dropdown-arrow2" class="dropdown-arrow2 down"></div>
            <div id="profile-dropdown-menu" class="profile-dropdown hidden">
                <a href="educator_view_profile.php"><div class="dropdown-item">View Profile</div></a>
                <a href="../login/logout.php"><div class="dropdown-item">Logout</div></a>
            </div>
        </div>
    </div>

    <!-- Cards -->
    <div class="container">
        <div class="manage-user-container">
            <div class="user-card" onclick="location.href='templates.php?subjectRefNo=<?= $subjectRefNo ?>';">
                <div class="card-icon">➕</div>
                <div class="card-text">Upload a New Learning Material</div>
            </div>

            <div class="user-card" onclick="location.href='educator_view_subjects.php';">
                <div class="card-icon">🗂️</div>
                <div class="card-text">Manage Uploaded Learning Materials</div>
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
    arrow.classList.toggle('down'); arrow.classList.toggle('up'); menu.classList.toggle('hidden');
}
function toggleDropdown2() {
    const arrow = document.getElementById('dropdown-arrow2');
    const menu = document.getElementById('profile-dropdown-menu');
    arrow.classList.toggle('down'); arrow.classList.toggle('up'); menu.classList.toggle('hidden');
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
