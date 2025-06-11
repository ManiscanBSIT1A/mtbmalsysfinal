<?php
session_start();

// Prevent browser cache
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Restrict to logged-in school administrators only
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['accType'] !== 'School Administrator') {
    header("Location: ../login/login.php");
    exit();
}

// Session timeout logic (optional for better security)
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: ../login/login.php?timeout=1");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

// Get admin name
$adminName = $_SESSION['firstName'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
    
<head>
  <meta charset="UTF-8">
  <title>User Management</title>
    <link rel="icon" type="image/png" href="../images/MTB-MAL_logo.png">
    <link rel="stylesheet" href="../style/manage_options-style.css">
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

<div id="sidebar-overlay" class="sidebar-overlay" onclick="toggleSidebar()"></div> <!-- Sidebar overlay -->
<div class="main-content"> <!-- Main content wrapper -->

    <!-- Top Bar -->
    <div class="topbar"> <!-- Top bar container -->

        <div class="left"> <!-- Left section of top bar -->
            <div class="logo-topbar" onclick="toggleSidebar()"> <!-- Top bar logo -->
                <img src="../images/MTB-MAL_logo-alt.png" alt="MTB-MAL Logo">
            </div>
            <div class="system-title"> <!-- System title text -->
                Mother Tongue-Based Multilingual Assessment and Learning System
            </div>
        </div>

        <div class="right"> <!-- Right section of top bar -->
            <div class="language-selector" onclick="toggleDropdown1()"> <!-- Language selector dropdown -->
                <div class="language">🌐 English</div> 
                <div id="dropdown-arrow1" class="dropdown-arrow1 down"></div>

                <div id="lang-dropdown-menu" class="lang-dropdown hidden"> <!-- Language dropdown menu -->
                    <div class="dropdown-item">Feature Available Soon</div>
                </div>
            </div> 
        </div>
    </div>

    <!-- Second Navigation Bar -->
    <div class="second-bar"> <!-- Second bar title & profile -->
    <span>Manage MTB-MAL Accounts</span> 
        <div class="profile-container" onclick="toggleDropdown2()">
    <div class="profile-circle"></div>
    <div class="profile-name"><?php echo htmlspecialchars($adminName); ?></div>
    <div id="dropdown-arrow2" class="dropdown-arrow2 down"></div>
    <div id="profile-dropdown-menu" class="profile-dropdown hidden">
        <a href="admin_view_profile.php"><div class="dropdown-item">View Profile</div></a>
        <a href="../login/logout.php"><div class="dropdown-item">Logout</div></a>
    </div>
</div>

    </div>

<!-- Container -->
<div class="container">
    <!-- === Manage User Cards Section === -->
    <div class="manage-user-container">

        <!-- Card: Create New User Account -->
        <div class="user-card" onclick="openModal()">
            <div class="card-icon">➕</div>
            <div class="card-text">Add New User</div>
        </div>

        <!-- Card: Manage Existing Accounts -->
        <div class="user-card" onclick="location.href='mtbmal_accs.php';">
            <div class="card-icon">👥</div>
            <div class="card-text">View MTB-MAL Accounts</div>
        </div>

    </div>
</div>

    <!-- Create Account Modal -->
    <div id="createAccountModal" class="modal">
        <div class="modal-content">
            <h2>Create an account for</h2>
            <hr>
            <a class="modal-btn" href="new_schooladministrator.php">School Administrator</a>
            <a class="modal-btn" href="new_educator.php">Educator</a>
            <a class="modal-btn" href="new_student.php">Student</a>
            <button class="modal-close" onclick="closeModal()">Close</button>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer"> <!-- Footer container -->
      Mother Tongue-Based Multilingual Assessment and Learning System © 2025
    </footer>

        <script>
        // === Toggle Language Dropdown Visibility ===
        function toggleDropdown1() {
            const arrow = document.getElementById('dropdown-arrow1');
            const menu = document.getElementById('lang-dropdown-menu');
            arrow.classList.toggle('down');
            arrow.classList.toggle('up');
            menu.classList.toggle('hidden');
        }

        // === Toggle Profile Dropdown Visibility ===
        function toggleDropdown2() {
            const arrow = document.getElementById('dropdown-arrow2');
            const menu = document.getElementById('profile-dropdown-menu');
            arrow.classList.toggle('down');
            arrow.classList.toggle('up');
            menu.classList.toggle('hidden');
        }

        // === Toggle Sidebar and Overlay Visibility ===
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const logoImg = document.querySelector('.logo2 img');

            sidebar.classList.toggle('visible');
            overlay.classList.toggle('visible');

            // Reset logo image to default when toggling sidebar
            logoImg.src = '../images/MTB-MAL_logo_side.png';
        }

        // === Open Modal ===
        function openModal() {
            document.getElementById("createAccountModal").style.display = "flex";
        }

        // === Close Modal ===
        function closeModal() {
            document.getElementById("createAccountModal").style.display = "none";
        }
        </script>

</body>
</html>