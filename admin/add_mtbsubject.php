<!--
NOTICE TO AUTOMATED SCANNERS AND REVIEWERS:

This form is part of a learning project for educational/school purposes ONLY.
It is NOT intended for phishing, spamming, or any malicious activity.
Please do NOT flag this site or form as suspicious based solely on the presence of this form.

Thank you for understanding.
-->
<?php
session_start();
// Strict School Admin access
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

// Get session data for auto-fill
$adminFirstName = $_SESSION['firstName'] ?? 'Admin';
$adminCreator = $_SESSION['accRefNo'] ?? '';
$schoolIdNo = $_SESSION['schoolIdNo'] ?? '';

// Handle form POST
require_once("../sql/db_connect.php");
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subjTitle = trim($_POST['subjTitle'] ?? '');
    $subjDescription = trim($_POST['subjDescription'] ?? '');
    $mtLanguage = trim($_POST['mtLanguage'] ?? '');
    $schoolIdNo = intval($_POST['schoolIdNo'] ?? 0);
    $subjectIdNo = intval($_POST['subjectId'] ?? 0);
    $educAccRefNo = intval($_POST['educAccRefNo'] ?? 0);
    $adminCreator = intval($_POST['adminCreator'] ?? 0);

    // Check required fields
    if (
        empty($subjTitle) ||
        empty($subjDescription) ||
        empty($mtLanguage) ||
        empty($schoolIdNo) ||
        empty($subjectIdNo) ||
        empty($educAccRefNo) ||
        empty($adminCreator)
    ) {
        $error = "All fields are required!";
    } else {
        // Prevent duplicate subjectIdNo
        $stmt = $conn->prepare("SELECT subjectIdNo FROM subject WHERE subjectIdNo = ?");
        $stmt->bind_param("i", $subjectIdNo);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Subject Identification Number already exists!";
        } else {
            $stmt = $conn->prepare("INSERT INTO subject 
                (subjectIdNo, subjTitle, subjDescription, mtLanguage, adminCreator, assignedEducator, schoolIdNo) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssiii", $subjectIdNo, $subjTitle, $subjDescription, $mtLanguage, $adminCreator, $educAccRefNo, $schoolIdNo);

            if ($stmt->execute()) {
                $newRefNo = $conn->insert_id;
                header("Location: addsubj_complete.php?refno=$newRefNo");
                exit();
            } else {
                $error = "Error saving subject. Please try again.";
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Subject</title>
  <link rel="icon" type="image/png" href="../images/MTB-MAL_logo.png">
  <link rel="stylesheet" href="../style/add_mtbsubject-style.css">
</head>
<body>

<!-- Sidebar Toggle Button and Sidebar code (unchanged)-->

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

    <div class="container">
        <div class="form-header">Add Subject Details</div>
        <?php if ($error): ?>
            <div style="color: red; text-align: center; margin-bottom: 10px;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-section">
                <div class="column">
                    <div class="section-header">
                        <div class="circle-number">1</div>
                        <h3>About the Subject</h3>
                    </div>
                    <div class="form-group">
                        <label>Subject Title</label>
                        <input name="subjTitle" type="text" placeholder="Enter the title of the subject" required>
                    </div>
                    <div class="form-group">
                        <label for="subjDescription">Subject Description</label>
                        <textarea id="subjDescription" name="subjDescription" class="description-box" placeholder="Enter the description of the subject" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>MTB-MLE Language</label>
                        <input name="mtLanguage" type="text" placeholder="Enter the language used for medium of instruction" required>
                    </div>
                </div>
                <div class="column">
                    <div class="section-header">
                        <div class="circle-number">2</div>
                        <h3>MTB-MLE Subject In-charge</h3>
                    </div>
                    <div class="form-group">
                        <label>School Identification Number</label>
                        <input name="schoolIdNo" type="text" placeholder="Enter the official school ID number" required>
                    </div>
                    <div class="form-group">
                        <label>Subject Identification Number</label>
                        <input name="subjectId" type="text" placeholder="Enter the school's unique identifier for the subject" required>
                    </div>
                    <div class="form-group">
                        <label>Educator </label>
                        <input name="educAccRefNo" type="text" placeholder="Enter the MTB-MAL reference number of the Educator" required>
                    </div>
                    <div class="form-group">
                        <label>Subject Creator</label>
                        <input name="adminCreator" type="text" value="<?php echo htmlspecialchars($adminCreator); ?>" readonly>
                    </div>
                </div>
            </div>
            <div class="button-group1">
                <button type="button" onclick="window.location.href='manage_subject_options.php'" class="cancel-btn">Cancel</button>
                <button type="submit" class="submit-btn">Submit</button>
            </div>
            <div class="button-group2">
                <button type="submit" class="submit-btn">Submit</button>
                <button type="button" onclick="window.location.href='manage_subject_options.php'" class="cancel-btn">Cancel</button>
            </div>
        </form>
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
