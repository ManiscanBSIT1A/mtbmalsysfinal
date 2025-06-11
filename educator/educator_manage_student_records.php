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
$educatorRefNo = $_SESSION['accRefNo'];
$subjectRefNo = isset($_GET['subjectRefNo']) ? intval($_GET['subjectRefNo']) : 0;

// Confirm that subject belongs to educator
$checkSql = "SELECT subjTitle FROM subject WHERE subjectRefNo = ? AND assignedEducator = ?";
$stmt = $conn->prepare($checkSql);
$stmt->bind_param("ii", $subjectRefNo, $educatorRefNo);
$stmt->execute();
$stmt->bind_result($subjTitle);
if (!$stmt->fetch()) {
    header("Location: educator_view_subjects.php");
    exit();
}
$stmt->close();

// Remove student if POSTed
if (isset($_POST['remove_student']) && isset($_POST['remove_student_accRefNo'])) {
    $studAccRefNo = intval($_POST['remove_student_accRefNo']);
    $removeSql = "UPDATE enrolled_students SET status='Removed' WHERE subjectRefNo=? AND studentAccRefNo=? AND assignedEducator=?";
    $removeStmt = $conn->prepare($removeSql);
    $removeStmt->bind_param("iii", $subjectRefNo, $studAccRefNo, $educatorRefNo);
    $removeStmt->execute();
    $removeStmt->close();
    header("Location: educator_manage-student-records.php?subjectRefNo=$subjectRefNo");
    exit();
}

// Get enrolled students for this subject
$students = [];
$sql = "SELECT s.accRefNo, s.fullName, s.lrn, s.schoolIdNo,
               s.parentGuardianName, s.pgRStoStudent, s.pgDOB, s.pgMaritalStatus, s.pgEmailAdd, s.pgContactNo,
               u.dob, u.username
        FROM enrolled_students es
        JOIN student s ON es.studentAccRefNo = s.accRefNo
        JOIN mtbmalusers u ON s.accRefNo = u.accRefNo
        WHERE es.subjectRefNo = ? AND es.status = 'Enrolled'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $subjectRefNo);
$stmt->execute();
$stmt->bind_result($accRefNo, $fullName, $lrn, $schoolIdNo, $parentGuardianName, $pgRStoStudent, $pgDOB, $pgMaritalStatus, $pgEmailAdd, $pgContactNo, $dob, $username);
while ($stmt->fetch()) {
    $students[] = [
        'accRefNo' => $accRefNo,
        'fullName' => $fullName,
        'lrn' => $lrn,
        'schoolIdNo' => $schoolIdNo,
        'parentGuardianName' => $parentGuardianName,
        'pgRStoStudent' => $pgRStoStudent,
        'pgDOB' => $pgDOB,
        'pgMaritalStatus' => $pgMaritalStatus,
        'pgEmailAdd' => $pgEmailAdd,
        'pgContactNo' => $pgContactNo,
        'dob' => $dob,
        'username' => $username
    ];
}
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students – <?php echo htmlspecialchars($subjTitle); ?></title>
    <link rel="icon" type="image/png" href="../images/MTB-MAL_logo.png">
    <link rel="stylesheet" href="../style/welcome_educator-style.css">
    <style>
        .students-table-container {
            max-width: 1350px;
            margin: 40px auto 0 auto;
            background: #fff6e6;
            border-radius: 14px;
            box-shadow: 0 4px 15px rgba(230, 165, 108, 0.12);
            padding: 28px 18px 24px 18px;
        }
        .students-table {
            width: 100%;
            border-collapse: collapse;
            background: none;
        }
        .students-table th, .students-table td {
            padding: 11px 20px;
            text-align: center !important;
        }
        .students-table th {
            background: #E6A56C;
            color: #543309;
        }
        .students-table tr {
            border-bottom: 1px solid #ffe2c0;
        }
        .students-table td {
            background: #ffebdd;
        }
        .action-btn, .enroll-btn {
            margin: 0 4px 3px 0;
            padding: 6px 13px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            background: #FFD2B3;
            color: #2c1500;
            border: 2px solid #e0c2a0;
            cursor: pointer;
            transition: background 0.19s;
            text-decoration: none;
            display: inline-block;
        }
        .action-btn:hover, .enroll-btn:hover {
            background: #e6a56c;
            color: #fff;
        }
        .enroll-btn { float: right; margin-bottom: 16px; }
        .no-students { color: #bb741a; text-align: center; font-size: 1.1em; padding: 40px 0; }

        .modal-overlay {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0; top: 0; right: 0; bottom: 0;
            background: rgba(10,59,82, 0.10);
            justify-content: center;
            align-items: center;
        }
        .modal-overlay.active {
            display: flex;
        }
        .info-modal-card {
            background: #FFEBDD;
            border-radius: 22px;
            box-shadow: 0 6px 32px rgba(230,165,108,0.19);
            padding: 28px 32px 24px 32px;
            width: 690px;
            max-width: 98vw;
            font-family: Arial, sans-serif;
            position: relative;
            animation: modalIn 0.23s cubic-bezier(.42,0,.58,1);
            border: 2px solid #E6A56C;
        }
        @keyframes modalIn {
            from {transform: translateY(60px) scale(.97); opacity:.2;}
            to {transform: translateY(0) scale(1); opacity:1;}
        }
        .modal-title {
            font-size: 1.65em;
            font-weight: bold;
            color: #0A3B52;
            text-align: center;
            margin-bottom: 4px;
        }
        .modal-divider {
            margin: 0 auto 18px auto;
            width: 70%;
            border: none;
            border-top: 2px solid #E6A56C;
        }
        .modal-section-title {
            font-size: 1.18em;
            font-weight: bold;
            color: #2b1000;
            margin-bottom: 7px;
            margin-top: 6px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 7px;
            gap: 5px;
        }
        .info-label {
            flex: 1;
            min-width: 0;
            font-size: 1.05em;
            text-align: center;
            padding: 8px 2px 1px 2px;
            color: #502d00;
            font-weight: 500;
            word-break: break-all;
        }
        .close-btn {
            display: block;
            margin: 27px auto 0 auto;
            padding: 11px 38px;
            background: #e6a56c;
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 1.2em;
            font-weight: bold;
            cursor: pointer;
            text-align: center;
            transition: background 0.17s;
            box-shadow: 0 2px 6px #e6a56c2f;
            letter-spacing: 1px;
        }
        .close-btn:hover {
            background: #e6a56c;
            color: black;
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
        <a href="educator_view_subjects.php"><span class="icon">📚</span> My Subjects</a>
        <a href="educator_manage-student-records.php?subjectRefNo=<?php echo urlencode($subjectRefNo); ?>"><span class="icon">👥</span> My Students</a>
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
    <!-- Second Bar -->
    <div class="second-bar">
        <span>Manage Students</span>
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

    <!-- Students Table -->
    <div class="students-table-container">
        <h2 style="text-align:center; color:#c27204;">Students Enrolled in <?php echo htmlspecialchars($subjTitle); ?></h2>
        <button class="enroll-btn" onclick="window.location.href='enroll_student.php?subjectRefNo=<?php echo urlencode($subjectRefNo); ?>'">Enroll Student</button>
        <?php if (count($students) > 0): ?>
        <table class="students-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Account's Reference Number</th>
                    <th>Full Name</th>
                    <th>LRN</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php $rowNo = 1; foreach ($students as $stud): ?>
                <tr>
                    <td><?php echo $rowNo++; ?></td>
                    <td><?php echo htmlspecialchars($stud['accRefNo']); ?></td>
                    <td><?php echo htmlspecialchars($stud['fullName']); ?></td>
                    <td><?php echo htmlspecialchars($stud['lrn']); ?></td>
                    <td>
                        <button 
                            type="button"
                            class="action-btn"
                            onclick="showProfileModal(this)"
                            data-fullname="<?php echo htmlspecialchars($stud['fullName']); ?>"
                            data-dob="<?php echo htmlspecialchars($stud['dob']); ?>"
                            data-lrn="<?php echo htmlspecialchars($stud['lrn']); ?>"
                            data-schoolidno="<?php echo htmlspecialchars($stud['schoolIdNo']); ?>"
                            data-accrefno="<?php echo htmlspecialchars($stud['accRefNo']); ?>"
                            data-username="<?php echo htmlspecialchars($stud['username']); ?>"
                            data-parentguardianname="<?php echo htmlspecialchars($stud['parentGuardianName']); ?>"
                            data-pgmaritalstatus="<?php echo htmlspecialchars($stud['pgMaritalStatus']); ?>"
                            data-pgrstostudent="<?php echo htmlspecialchars($stud['pgRStoStudent']); ?>"
                            data-pgdob="<?php echo htmlspecialchars($stud['pgDOB']); ?>"
                            data-pgemailadd="<?php echo htmlspecialchars($stud['pgEmailAdd']); ?>"
                            data-pgcontactno="<?php echo htmlspecialchars($stud['pgContactNo']); ?>"
                        >View Profile</button>

                        <a href="view_student_progress.php?studentAccRefNo=<?php echo urlencode($stud['accRefNo']); ?>&subjectRefNo=<?php echo urlencode($subjectRefNo); ?>" class="action-btn">View Progress</a>
                        <a href="check_student_achievements.php?studentAccRefNo=<?php echo urlencode($stud['accRefNo']); ?>&subjectRefNo=<?php echo urlencode($subjectRefNo); ?>" class="action-btn">Check Achievements</a>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Remove this student?');">
                            <input type="hidden" name="remove_student_accRefNo" value="<?php echo htmlspecialchars($stud['accRefNo']); ?>">
                            <button type="submit" class="action-btn" name="remove_student">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="no-students">No students enrolled yet.</div>
        <?php endif; ?>

        <!-- Student Information Sheet Modal -->
        <div id="studentProfileModal" class="modal-overlay">
          <div class="info-modal-card">
            <div class="modal-title">Student Information Sheet</div>
            <hr class="modal-divider">

            <div class="modal-section-title">Student’s Profile</div>
            <div class="info-row">
              <div class="info-label"><strong id="modalFullName"></strong><br>Full Name</div>
              <div class="info-label"><strong id="modalDob"></strong><br>Date of Birth</div>
              <div class="info-label"><strong id="modalLrn"></strong><br>Learner Reference Number</div>
            </div>
            <div class="info-row">
              <div class="info-label"><strong id="modalSchoolIdNo"></strong><br>School Identification Number</div>
              <div class="info-label"><strong id="modalAccRefNo"></strong><br>MTB-MAL Reference Number</div>
              <div class="info-label"><strong id="modalUsername"></strong><br>Username</div>
            </div>
            <div class="modal-section-title" style="margin-top: 20px;">Parent/Guardian Profile</div>
            <div class="info-row">
              <div class="info-label"><strong id="modalParentGuardianName"></strong><br>Full Name</div>
              <div class="info-label"><strong id="modalPgMaritalStatus"></strong><br>Marital Status</div>
              <div class="info-label"><strong id="modalPgRStoStudent"></strong><br>Relationship to Student</div>
            </div>
            <div class="info-row">
              <div class="info-label"><strong id="modalPgDOB"></strong><br>Date of Birth</div>
              <div class="info-label"><strong id="modalPgEmailAdd"></strong><br>Email Address</div>
              <div class="info-label"><strong id="modalPgContactNo"></strong><br>Contact Number</div>
            </div>
            <button class="close-btn" onclick="closeProfileModal()">Close</button>
          </div>
        </div>
    </div>
    <footer class="footer">Mother Tongue-Based Multilingual Assessment and Learning System © 2025</footer>
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

    function showProfileModal(btn) {
        document.getElementById('modalFullName').textContent = btn.getAttribute('data-fullname') || '';
        document.getElementById('modalDob').textContent = btn.getAttribute('data-dob') || '';
        document.getElementById('modalLrn').textContent = btn.getAttribute('data-lrn') || '';
        document.getElementById('modalSchoolIdNo').textContent = btn.getAttribute('data-schoolidno') || '';
        document.getElementById('modalAccRefNo').textContent = btn.getAttribute('data-accrefno') || '';
        document.getElementById('modalUsername').textContent = btn.getAttribute('data-username') || '';
        document.getElementById('modalParentGuardianName').textContent = btn.getAttribute('data-parentguardianname') || '';
        document.getElementById('modalPgMaritalStatus').textContent = btn.getAttribute('data-pgmaritalstatus') || '';
        document.getElementById('modalPgRStoStudent').textContent = btn.getAttribute('data-pgrstostudent') || '';
        document.getElementById('modalPgDOB').textContent = btn.getAttribute('data-pgdob') || '';
        document.getElementById('modalPgEmailAdd').textContent = btn.getAttribute('data-pgemailadd') || '';
        document.getElementById('modalPgContactNo').textContent = btn.getAttribute('data-pgcontactno') || '';
        document.getElementById('studentProfileModal').classList.add('active');
    }
    function closeProfileModal() {
        document.getElementById('studentProfileModal').classList.remove('active');
    }
</script>
</body>
</html>
