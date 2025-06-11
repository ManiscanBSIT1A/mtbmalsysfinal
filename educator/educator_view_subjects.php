<?php
session_start();
require_once '../sql/db_connect.php';

$timeout_duration = 1800; // 30 mins

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: ../login/login.php?timeout=1");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

// Strict Educator access control
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../login/login.php');
    exit();
}
if (!isset($_SESSION['accType']) || $_SESSION['accType'] !== 'Educator') {
    switch ($_SESSION['accType']) {
        case 'School Administrator': header('Location: ../admin/welcome_admin.php'); exit();
        case 'Student': header('Location: ../student/welcome_student.php'); exit();
        default: header('Location: ../login/login.php'); exit();
    }
}
$educatorRefNo = $_SESSION['accRefNo']; // educator's accRefNo

// Fetch assigned subjects
$subjects = [];
$sql = "SELECT subjectRefNo, subjectIdNo, subjTitle, subjDescription, mtLanguage 
        FROM subject 
        WHERE assignedEducator = ? 
        ORDER BY subjectIdNo ASC";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $educatorRefNo);
    $stmt->execute();
    $stmt->bind_result($subjectRefNo, $subjectIdNo, $subjTitle, $subjDescription, $mtLanguage);
    while ($stmt->fetch()) {
        $subjects[] = [
            'subjectRefNo'   => $subjectRefNo,
            'subjectIdNo'    => $subjectIdNo,
            'subjTitle'      => $subjTitle,
            'subjDescription'=> $subjDescription,
            'mtLanguage'     => $mtLanguage,
        ];
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Subjects</title>
    <link rel="icon" type="image/png" href="../images/MTB-MAL_logo.png">
    <link rel="stylesheet" href="../style/welcome_educator-style.css">
    <style>
        .subjects-table-container {
            max-width: 1350px;
            margin: 40px auto 0 auto;
            background: #fff6e6;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(230, 165, 108, 0.10);
            padding: 32px 24px 28px 24px;
        }
        .subjects-table-container h2 { margin-top: -8px }
        .subjects-table { width: 100%; border-collapse: collapse; background: none; }
        .subjects-table th, .subjects-table td { padding: 12px 10px; text-align: center; }
        .subjects-table th { background: #E6A56C; color: #543309; font-size: 1.05em; }
        .subjects-table tr { border-bottom: 1px solid #ffe2c0; }
        .subjects-table td { background: #ffebdd; font-size: 1.01em; }
        .action-btn {
            margin: 4px 6px; padding: 7px 18px; border-radius: 6px; font-size: 15px;
            font-weight: bold; background: #FFD2B3; color: #2c1500; border: 2px solid #e0c2a0;
            cursor: pointer; transition: background 0.19s; text-decoration: none; display: inline-block;
        }
        .action-btn:hover { background: #e6a56c; color: #fff; }
        .no-subjects { color: #bb741a; text-align: center; font-size: 1.1em; padding: 50px 0; }
        @media (max-width: 800px) {
            .subjects-table-container { padding: 14px 2vw; }
            .subjects-table th, .subjects-table td { font-size: 14px; padding: 8px 5px; }
        }
    </style>
</head>
<body>
<!-- Sidebar, Top Bar, Second Bar (use same as dashboard) -->
<button onclick="toggleSidebar()" class="toggle-btn" style="cursor: pointer;"></button>

<div id="sidebar" class="sidebar">
    <div class="logo2" onclick="toggleSidebar()">
        <img src="../images/MTB-MAL_logo_side.png" alt="MTB-MAL Logo" />
    </div>
    <nav class="nav-links">
        <a href="welcome_educator.php"><span class="icon">🏠</span> Dashboard</a>
        <a href="educator_view_subjects.php"><span class="icon">📚</span> My Subjects</a>
        <a href="educator_manage_student_records.php"><span class="icon">👥</span> My Students</a>
        <a href="../login/about.php"><span class="icon">📖</span> About MTB-MAL</a>
    </nav>
</div>
<div id="sidebar-overlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>

<div class="main-content">
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
    <div class="second-bar">
        <span>My Subjects</span>
        <div class="profile-container" onclick="toggleDropdown2()">
            <div class="profile-circle"></div>
            <span style="font-size:18px; font-weight:bold; margin-left:10px;">
                <?php echo isset($_SESSION['firstName']) ? htmlspecialchars($_SESSION['firstName']) : "Educator"; ?>
            </span>
            <div id="dropdown-arrow2" class="dropdown-arrow2 down"></div>
            <div id="profile-dropdown-menu" class="profile-dropdown hidden">
                <a href="educator_view_profile.php"><div class="dropdown-item">View Profile</div></a>
                <a href="../login/logout.php"><div class="dropdown-item">Logout</div></a>
            </div>
        </div>
    </div>

    <!-- Subjects Table -->
    <div class="subjects-table-container">
        <h2 style="text-align:center; color:#c27204;">Subjects Assigned to Me</h2>
        <?php if (count($subjects) > 0): ?>
        <table class="subjects-table">
            <thead>
                <tr>
                    <th>Subject ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Language</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($subjects as $subj): ?>
                <tr>
                    <td><?php echo htmlspecialchars($subj['subjectIdNo']); ?></td>
                    <td><?php echo htmlspecialchars($subj['subjTitle']); ?></td>
                    <td><?php echo htmlspecialchars($subj['subjDescription']); ?></td>
                    <td><?php echo htmlspecialchars($subj['mtLanguage']); ?></td>
                    <td>
                        <a href="material_options.php?subjectRefNo=<?php echo urlencode($subj['subjectRefNo']); ?>" class="action-btn">Manage Learning Materials</a>
                        <a href="educator_manage_student_records.php?subjectRefNo=<?php echo urlencode($subj['subjectRefNo']); ?>" class="action-btn">Manage Enrolled Students</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="no-subjects">No subjects assigned yet.</div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="footer">
        Mother Tongue-Based Multilingual Assessment and Learning System © 2025
    </footer>
</div>

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
