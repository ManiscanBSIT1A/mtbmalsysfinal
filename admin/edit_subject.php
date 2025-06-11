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
if (!isset($_SESSION['accType']) || $_SESSION['accType'] !== 'School Administrator') {
    // Redirect educators and students to their respective dashboards
    switch ($_SESSION['accType']) {
        case 'Educator': header("Location: ../educator/welcome_educator.php"); exit();
        case 'Student': header("Location: ../student/welcome_student.php"); exit();
        default: header("Location: ../login/login.php"); exit();
    }
}

$adminFirstName = $_SESSION['firstName'];
$adminSchoolIdNo = $_SESSION['schoolIdNo'];
$adminAccRefNo = $_SESSION['accRefNo'];

$success = false;
$error = '';
$subject = [
    'subjectRefNo' => '',
    'subjectIdNo' => '',
    'subjTitle' => '',
    'subjDescription' => '',
    'mtLanguage' => '',
    'assignedEducator' => ''
];

// GET subject details
if (isset($_GET['subjectRefNo'])) {
    $subjectRefNo = intval($_GET['subjectRefNo']);
    $sql = "SELECT * FROM subject WHERE subjectRefNo=? AND schoolIdNo=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $subjectRefNo, $adminSchoolIdNo);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $subject = $result->fetch_assoc();
    } else {
        $error = "Subject not found.";
    }
    $stmt->close();
} elseif ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $error = "Invalid request. No subject specified.";
}

// For educator dropdown
function fetchEducators($conn, $schoolIdNo, $selected = null) {
    $educators = [];
    $sql = "SELECT e.accRefNo, u.firstName, u.lastName FROM educator e JOIN mtbmalusers u ON e.accRefNo = u.accRefNo WHERE e.schoolIdNo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $schoolIdNo);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $educators[] = $row;
    }
    $stmt->close();
    return $educators;
}
$educators = fetchEducators($conn, $adminSchoolIdNo, $subject['assignedEducator']);

// === Handle Update ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subjectRefNo'])) {
    $subjectRefNo = intval($_POST['subjectRefNo']);
    $subjectIdNo = trim($_POST['subjectIdNo']);
    $subjTitle = trim($_POST['subjTitle']);
    $subjDescription = trim($_POST['subjDescription']);
    $mtLanguage = trim($_POST['mtLanguage']);
    $assignedEducator = intval($_POST['assignedEducator']);

    // Validate
    if ($subjectIdNo === '' || $subjTitle === '' || $subjDescription === '' || $mtLanguage === '') {
        $error = "All fields are required.";
    } else {
        $sql = "UPDATE subject SET subjectIdNo=?, subjTitle=?, subjDescription=?, mtLanguage=?, assignedEducator=? WHERE subjectRefNo=? AND schoolIdNo=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssiii", $subjectIdNo, $subjTitle, $subjDescription, $mtLanguage, $assignedEducator, $subjectRefNo, $adminSchoolIdNo);
        if ($stmt->execute()) {
            $success = true;
            // Reload latest data
            $subject = [
                'subjectRefNo' => $subjectRefNo,
                'subjectIdNo' => $subjectIdNo,
                'subjTitle' => $subjTitle,
                'subjDescription' => $subjDescription,
                'mtLanguage' => $mtLanguage,
                'assignedEducator' => $assignedEducator
            ];
        } else {
            $error = "Failed to update subject.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Subject</title>
    <link rel="icon" type="image/png" href="../images/MTB-MAL_logo.png">
    <link rel="stylesheet" href="../style/view_subjects-style.css">
    <style>
    /* Main UI tweaks */
    .edit-card {
        background: #fff;
        max-width: 650px;
        margin: 48px auto 0 auto;
        border-radius: 20px;
        box-shadow: 0 6px 32px rgba(0,0,0,0.18);
        padding: 40px 48px 32px 48px;
        min-width: 320px;
    }
    @media (max-width: 800px) {
      .edit-card { max-width: 98vw; padding: 16px; }
    }
    .edit-card h2 {
        margin-top: 0; font-size: 2rem; text-align: center; color: #ae24b4;
    }
    .edit-form .form-group {
        margin-bottom: 24px;
        display: flex; flex-direction: column;
    }
    .edit-form label { font-weight: bold; margin-bottom: 7px; }
    .edit-form input, .edit-form textarea, .edit-form select {
        border: 1px solid #bbb; border-radius: 8px; padding: 12px;
        font-size: 1.1rem; width: 100%; box-sizing: border-box;
    }
    .edit-form textarea { min-height: 90px; resize: vertical;}
    .edit-form .button-group {
        display: flex; justify-content: center; gap: 20px; margin-top: 28px;
    }
    .edit-form button {
        font-size: 1.1rem;
        font-weight: bold;
        padding: 14px 36px;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: background 0.18s;
    }
    .submit-btn { background: #91ed93; color: #3e3e3e;}
    .submit-btn:hover { background: #68df72;}
    .cancel-btn { background: #fca2a2;}
    .cancel-btn:hover { background: #f87e7e;}
    .success-msg { background: #b8ffc0; color: #226822; text-align: center; border-radius: 12px; padding: 14px; margin-bottom: 20px;}
    .error-msg { background: #ffe1e1; color: #900; text-align: center; border-radius: 12px; padding: 14px; margin-bottom: 20px;}
    </style>
</head>
<body>
<!-- Sidebar -->
<div id="sidebar" class="sidebar">
    <div class="logo2" id="sidebarToggler" style="cursor:pointer;">
        <img src="../images/MTB-MAL_logo_side.png" alt="MTB-MAL Logo" />
    </div>
    <nav class="nav-links">
        <a href="welcome_admin.php"><span class="icon">🏠</span> Dashboard</a>
        <a href="view_subjects.php"><span class="icon">📚</span> View MTB-MLE Subjects</a>
        <a href="mtbmal_accs.php"><span class="icon">👥</span> View MTB-MAL Accounts</a>
    </nav>
</div>
<div id="sidebar-overlay" class="sidebar-overlay"></div>

<div class="main-content">
    <!-- Top Bar -->
    <div class="topbar">
        <div class="left">
            <div class="logo-topbar" id="logoTopbar" style="cursor:pointer;">
                <img src="../images/MTB-MAL_logo-alt.png" alt="MTB-MAL Logo">
            </div>
            <div class="system-title">Mother Tongue-Based Multilingual Assessment and Learning System</div>
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
        <span>Edit Subject</span>
        <div class="profile-container" id="profileSel">
            <div class="profile-circle"></div>
            <span class="profile-name" style="margin-left:10px; font-weight:bold;"><?php echo htmlspecialchars($adminFirstName); ?></span>
            <div id="dropdown-arrow2" class="dropdown-arrow2 down"></div>
            <div id="profile-dropdown-menu" class="profile-dropdown hidden">
                <a href="admin_view_profile.php"><div class="dropdown-item">View Profile</div></a>
                <a href="../login/logout.php"><div class="dropdown-item">Logout</div></a>
            </div>
        </div>
    </div>
    <!-- Edit Form Section -->
    <div class="container" style="min-height:65vh;">
        <div class="edit-card">
            <h2>Edit Subject Details</h2>
            <?php if ($success): ?>
                <div class="success-msg">Subject updated successfully!<br>Redirecting to View Subjects...</div>
                <script>
                    setTimeout(function(){ window.location.href = "view_subjects.php"; }, 2000);
                </script>
            <?php elseif ($error): ?>
                <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form class="edit-form" method="POST" action="edit_subject.php?subjectRefNo=<?php echo intval($subject['subjectRefNo']); ?>">
                <input type="hidden" name="subjectRefNo" value="<?php echo intval($subject['subjectRefNo']); ?>">
                <div class="form-group">
                    <label for="subjectIdNo">Subject Identification Number</label>
                    <input type="text" name="subjectIdNo" id="subjectIdNo" required maxlength="12"
                        value="<?php echo htmlspecialchars($subject['subjectIdNo']); ?>">
                </div>
                <div class="form-group">
                    <label for="subjTitle">Subject Title</label>
                    <input type="text" name="subjTitle" id="subjTitle" required maxlength="80"
                        value="<?php echo htmlspecialchars($subject['subjTitle']); ?>">
                </div>
                <div class="form-group">
                    <label for="mtLanguage">Language</label>
                    <input type="text" name="mtLanguage" id="mtLanguage" required maxlength="30"
                        value="<?php echo htmlspecialchars($subject['mtLanguage']); ?>">
                </div>
                <div class="form-group">
                    <label for="assignedEducator">Assigned Educator</label>
                    <select name="assignedEducator" id="assignedEducator" required>
                        <option value="">-- Select Educator --</option>
                        <?php foreach ($educators as $ed): ?>
                            <option value="<?php echo $ed['accRefNo']; ?>"
                                <?php if ($ed['accRefNo'] == $subject['assignedEducator']) echo "selected"; ?>>
                                <?php echo htmlspecialchars($ed['firstName'] . ' ' . $ed['lastName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="subjDescription">Subject Description</label>
                    <textarea name="subjDescription" id="subjDescription" required maxlength="500"><?php echo htmlspecialchars($subject['subjDescription']); ?></textarea>
                </div>
                <div class="button-group">
                    <button type="submit" class="submit-btn">Update</button>
                    <button type="button" class="cancel-btn" onclick="window.location.href='view_subjects.php'">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <footer class="footer">
        Mother Tongue-Based Multilingual Assessment and Learning System © 2025
    </footer>
</div>
<!-- JS for toggles -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle via logo (both in sidebar and topbar)
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('visible');
        document.getElementById('sidebar-overlay').classList.toggle('visible');
        document.body.classList.toggle('sidebar-open');
    }
    document.getElementById('sidebarToggler').onclick = toggleSidebar;
    if (document.getElementById('logoTopbar')) {
        document.getElementById('logoTopbar').onclick = toggleSidebar;
    }
    document.getElementById('sidebar-overlay').onclick = function() {
        document.getElementById('sidebar').classList.remove('visible');
        document.getElementById('sidebar-overlay').classList.remove('visible');
        document.body.classList.remove('sidebar-open');
    };

    // Language dropdown
    document.getElementById('langSel').onclick = function(e) {
        document.getElementById('dropdown-arrow1').classList.toggle('down');
        document.getElementById('dropdown-arrow1').classList.toggle('up');
        document.getElementById('lang-dropdown-menu').classList.toggle('hidden');
        e.stopPropagation();
    };
    // Profile dropdown
    document.getElementById('profileSel').onclick = function(e) {
        document.getElementById('dropdown-arrow2').classList.toggle('down');
        document.getElementById('dropdown-arrow2').classList.toggle('up');
        document.getElementById('profile-dropdown-menu').classList.toggle('hidden');
        e.stopPropagation();
    };
    // Hide dropdowns when clicking outside
    window.onclick = function(e) {
        if (!e.target.closest('#profileSel')) {
            document.getElementById('profile-dropdown-menu').classList.add('hidden');
            document.getElementById('dropdown-arrow2').classList.remove('up');
            document.getElementById('dropdown-arrow2').classList.add('down');
        }
        if (!e.target.closest('#langSel')) {
            document.getElementById('lang-dropdown-menu').classList.add('hidden');
            document.getElementById('dropdown-arrow1').classList.remove('up');
            document.getElementById('dropdown-arrow1').classList.add('down');
        }
    };
});
</script>
</body>
</html>
