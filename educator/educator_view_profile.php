<?php
session_start();

$timeout_duration = 1800; // 1800 seconds = 30 minutes

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: ../login/login.php?timeout=1");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

require_once '../sql/db_connect.php';

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
$accRefNo = isset($_SESSION['accRefNo']) ? intval($_SESSION['accRefNo']) : 0;

// Default values
$profile = [
    'fullName' => '',
    'accRefNo' => '',
    'dob' => '',
    'emailAddress' => '',
    'contactNo' => '',
    'accType' => 'Educator',
    'edEmpIdNo' => '',
    'schoolIdNo' => '',
];

if ($accRefNo) {
    // Join mtbmalusers and educator tables
    $sql = "SELECT 
                e.fullName,
                u.accRefNo,
                u.dob,
                u.emailAddress,
                u.contactNo,
                u.accType,
                e.edEmpIdNo,
                e.schoolIdNo
            FROM mtbmalusers u
            INNER JOIN educator e ON u.accRefNo = e.accRefNo
            WHERE u.accRefNo = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $accRefNo);
        $stmt->execute();
        $stmt->bind_result(
            $profile['fullName'],
            $profile['accRefNo'],
            $profile['dob'],
            $profile['emailAddress'],
            $profile['contactNo'],
            $profile['accType'],
            $profile['edEmpIdNo'],
            $profile['schoolIdNo']
        );
        $stmt->fetch();
        $stmt->close();
    }
}

// Fallback for the profile name near the profile icon
$educatorName = $profile['fullName'] ? htmlspecialchars(explode(' ', $profile['fullName'])[0]) : 'Educator';

function safe($v) {
    return htmlspecialchars($v ?? '');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Educator Profile</title>
    <link rel="icon" type="image/png" href="../images/MTB-MAL_logo.png">
    <link rel="stylesheet" href="../style/welcome_educator-style.css">
    <style>
        .profile-summary-card {
            background: #fff6e6;
            border-radius: 18px;
            box-shadow: 0 4px 15px rgba(230, 165, 108, 0.19);
            max-width: 500px;
            margin: 48px auto 0 auto;
            padding: 35px 38px 32px 38px;
            text-align: left;
            font-size: 18px;
        }
        .profile-header-row {
            display: flex;
            align-items: center;
            margin-bottom: 18px;
        }
        .profile-avatar2 {
            background: #E6A56C;
            border-radius: 50%;
            width: 84px;
            height: 84px;
            display: flex; align-items: center; justify-content: center;
            font-size: 45px;
            color: #fff;
            margin-right: 25px;
        }
        .profile-summary-title {
            font-size: 2.1rem;
            font-weight: bold;
            color: #c27204;
            letter-spacing: 0.5px;
        }
        .profile-info-table {
            width: 100%;
            margin-top: 25px;
        }
        .profile-info-table tr {
            border-bottom: 1px solid #f0c9a7;
        }
        .profile-label {
            font-weight: bold;
            color: #b47123;
            width: 170px;
            padding: 10px 0 10px 2px;
            font-size: 1.05em;
        }
        .profile-value {
            color: #333;
            padding: 10px 0 10px 2px;
        }
        .back-btn {
            display: inline-block;
            margin-top: 32px;
            padding: 10px 36px;
            background: #ffd49b;
            color: #3c2001;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 2px 6px rgba(0,0,0,0.09);
        }
        .back-btn:hover {
            background: #e6a56c;
        }
        @media (max-width: 600px) {
            .profile-summary-card {
                max-width: 99vw;
                padding: 18px 5vw 25px 5vw;
                font-size: 16px;
            }
            .profile-header-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .profile-avatar2 {
                margin-right: 0;
                margin-bottom: 9px;
            }
            .profile-summary-title {
                font-size: 1.3rem;
            }
        }
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
        <span>My Profile</span>
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

    <!-- Profile Summary Card -->
    <div class="container">
        <div class="profile-summary-card">
            <div class="profile-header-row">
                <div class="profile-avatar2"><span>👤</span></div>
                <div>
                    <div class="profile-summary-title"><?php echo safe($profile['fullName']); ?></div>
                    <div style="color:#a88462;font-size:1.1em;margin-top:6px;">
                        Educator &mdash; MTB-MAL
                    </div>
                </div>
            </div>
            <table class="profile-info-table">
                <tr>
                    <td class="profile-label">Reference No.</td>
                    <td class="profile-value"><?php echo safe($profile['accRefNo']); ?></td>
                </tr>
                <tr>
                    <td class="profile-label">Employee No.</td>
                    <td class="profile-value"><?php echo safe($profile['edEmpIdNo']); ?></td>
                </tr>
                <tr>
                    <td class="profile-label">Account Type</td>
                    <td class="profile-value"><?php echo safe($profile['accType']); ?></td>
                </tr>
                <tr>
                    <td class="profile-label">School ID No.</td>
                    <td class="profile-value"><?php echo safe($profile['schoolIdNo']); ?></td>
                </tr>
                <tr>
                    <td class="profile-label">Date of Birth</td>
                    <td class="profile-value"><?php echo safe($profile['dob']); ?></td>
                </tr>
                <tr>
                    <td class="profile-label">Email</td>
                    <td class="profile-value"><?php echo safe($profile['emailAddress']); ?></td>
                </tr>
                <tr>
                    <td class="profile-label">Contact Number</td>
                    <td class="profile-value"><?php echo safe($profile['contactNo']); ?></td>
                </tr>
            </table>
            <a href="welcome_educator.php" class="back-btn">Back to Dashboard</a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        Mother Tongue-Based Multilingual Assessment and Learning System © 2025
    </footer>
</div>

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
        logoImg.src = '../images/MTB-MAL_logo_side.png';
    }
</script>
</body>
</html>
