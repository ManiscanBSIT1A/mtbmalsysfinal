<?php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Strict role-based redirect
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

require_once '../sql/db_connect.php';

// --- Always get schoolIdNo from session ---
$schoolIdNo = isset($_SESSION['schoolIdNo']) ? intval($_SESSION['schoolIdNo']) : 0;

// --- Handle Save/Edit School Data ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_school'])) {
    $schoolName   = trim($_POST['schoolName'] ?? '');
    $shortName    = trim($_POST['shortName'] ?? '');
    $schoolType   = $_POST['schoolType'] ?? '';
    $emailAddress = trim($_POST['emailAddress'] ?? '');
    $contactNo    = trim($_POST['contactNo'] ?? '');

    $stmt = $conn->prepare("UPDATE school SET schoolName=?, shortName=?, schoolType=?, emailAddress=?, contactNo=? WHERE schoolIdNo=?");
    $stmt->bind_param("sssssi", $schoolName, $shortName, $schoolType, $emailAddress, $contactNo, $schoolIdNo);
    $success = $stmt->execute();
    $stmt->close();

    $_SESSION['school_update_success'] = $success ? 1 : 0;
    header("Location: welcome_admin.php");
    exit();
}

// --- Fetch school profile ---
$school = [
    'scId' => '', 'scName' => '', 'scShort' => '', 'scType' => '', 'scEmail' => '', 'scContact' => ''
];
if ($schoolIdNo > 0) {
    $stmt = $conn->prepare("SELECT schoolIdNo, schoolName, shortName, schoolType, emailAddress, contactNo FROM school WHERE schoolIdNo = ?");
    $stmt->bind_param("i", $schoolIdNo);
    $stmt->execute();
    $stmt->bind_result($scId, $scName, $scShort, $scType, $scEmail, $scContact);
    if ($stmt->fetch()) {
        $school = [
            'scId' => $scId,
            'scName' => $scName,
            'scShort' => $scShort,
            'scType' => $scType,
            'scEmail' => $scEmail,
            'scContact' => $scContact
        ];
    }
    $stmt->close();
}

// --- Fetch authorized admin (accCreator=0) ---
$authAdmin = [
    'adFirstName' => '', 'adLastName' => '', 'adUsername' => '', 'adEmail' => '', 'adContact' => '', 'adRefNo' => ''
];
$stmt = $conn->prepare(
    "SELECT u.firstName, u.lastName, u.username, u.emailAddress, u.contactNo, u.accRefNo
     FROM mtbmalusers u
     JOIN schooladministrator sa ON u.accRefNo = sa.accRefNo
     WHERE u.schoolIdNo = ? AND u.accCreator = 0 LIMIT 1"
);
$stmt->bind_param("i", $schoolIdNo);
$stmt->execute();
$stmt->bind_result($adFirstName, $adLastName, $adUsername, $adEmail, $adContact, $adRefNo);
if ($stmt->fetch()) {
    $authAdmin = [
        'adFirstName' => $adFirstName,
        'adLastName' => $adLastName,
        'adUsername' => $adUsername,
        'adEmail' => $adEmail,
        'adContact' => $adContact,
        'adRefNo' => $adRefNo
    ];
}
$stmt->close();

$adminName = isset($_SESSION['firstName']) ? htmlspecialchars($_SESSION['firstName']) : 'Admin';

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: ../login/login.php?timeout=1");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <link rel="icon" type="image/png" href="../images/MTB-MAL_logo.png">
  <link rel="stylesheet" href="../style/welcome_admin-style.css">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Expires" content="0" />
<style>
/* Modal Styling */
.modal-overlay {
    display: none;
    position: fixed;
    z-index: 2000;
    left: 0; top: 0; right: 0; bottom: 0;
    background: rgba(10,59,82, 0.10);
    justify-content: center;
    align-items: center;
}
.modal-overlay.active { display: flex; }
.info-modal-card {
    background: #FFEBDD;
    border-radius: 22px;
    box-shadow: 0 6px 32px rgba(230,165,108,0.19);
    padding: 28px 40px 28px 40px;
    width: 900px;
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
    font-size: 2em;
    font-weight: bold;
    color: #0A3B52;
    text-align: center;
    margin-bottom: 4px;
    margin-top: 10px;
    letter-spacing: .5px;
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
    margin-top: 20px;
    text-align:left;
}
.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 13px;
    gap: 18px;
}
.info-label {
    flex: 1 1 0;
    min-width: 0;
    font-size: 1.05em;
    text-align: center;
    color: #502d00;
    font-weight: 500;
    word-break: break-all;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.info-label strong {
    font-size: 1.09em;
    font-weight: bold;
    letter-spacing: 0.5px;
}
input[type="text"], input[type="email"], select {
    font-size: 1em;
    border: 1.5px solid #e6a56c;
    border-radius: 8px;
    padding: 7px 10px;
    background: #fff;
    color: #382a10;
    margin-bottom: 3px;
    outline: none;
    font-weight: bold;
    width: 100%;
    box-sizing: border-box;
    text-align: center;
}
input[readonly], select[readonly] {
    background: #f5f5f5;
    color: #c9b791;
    font-weight: 600;
}
.save-btn, .close-btn {
    display: inline-block;
    margin: 32px 16px 0 16px;
    padding: 15px 38px;
    background: #FFA86E;
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
.save-btn { background: #FFA86E; }
.save-btn:hover, .close-btn:hover {
    background: #e6a56c;
    color: #fff;
}
.mini-label {
    font-size: 0.96em;
    font-weight: normal;
    color: #6c4916;
    margin-top: 2px;
}
.modal-buttons {
    width: 100%;
    display: flex;
    justify-content: center;
    gap: 38px;
    margin-top: 16px;
}
.success-msg {
    color: #24aa4d;
    font-size: 1.1em;
    font-weight: bold;
    margin-bottom: 8px;
    margin-top: 2px;
    text-align: center;
}
@media (max-width: 900px) {
    .info-modal-card { padding: 12px; width: 99vw; max-width: 99vw; }
    .info-row { flex-direction: column; gap: 4px; }
    .save-btn, .close-btn { width: 90%; margin: 13px auto 0 auto; font-size: 1.07em; }
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
        <a href="welcome_admin.php"><span class="icon"> 🏠 </span> Dashboard </a>
        <a href="view_subjects.php"><span class="icon"> 📚 </span> View MTB-MLE Subjects</a>
        <a href="mtbmal_accs.php"><span class="icon"> 👥 </span> View MTB-MAL Accounts </a>
        <a href="#" onclick="showAboutSchoolModal(); return false;"><span class="icon">🏫</span>School Information</a>
        <a href="../login/about.php"><span class="icon"> 📖 </span> About MTB-MAL </a>
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
        <span>Dashboard</span>
        <div class="profile-container" onclick="toggleDropdown2()">
            <div class="profile-circle"></div>
            <div class="profile-name"><?php echo $adminName; ?></div>
            <div id="dropdown-arrow2" class="dropdown-arrow2 down"></div>
            <div id="profile-dropdown-menu" class="profile-dropdown hidden">
                <a href="admin_view_profile.php"><div class="dropdown-item">View Profile</div></a>
                <a href="../login/logout.php"><div class="dropdown-item">Logout</div></a>
            </div>
        </div>
    </div>

    <!-- === Main Container === -->
    <div class="container">
        <div class="dashboard-container">
            <!-- Card: Manage Subjects -->
            <a href="manage_subject_options.php" class="dashboard-card-link">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2>Manage MTB-MLE Subjects</h2>
                        <span class="icon">📚</span>
                    </div>
                    <div class="card-body">
                        <p><strong>View MTB-MLE Subjects offered by your school or Create a new subject for your school and assign an educator to manage it.</strong></p>
                        <p>MTB-MLE subject becomes a virtual classroom where your chosen educator can upload modules and assessments for students to learn from.</p>
                    </div>
                </div>
            </a>
            <!-- Card: Manage User Accounts -->
            <a href="manage_user_options.php" class="dashboard-card-link">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2>Manage MTB-MAL Accounts</h2>
                        <span class="icon">👥</span>
                    </div>
                    <div class="card-body">
                        <p><strong>Manage all the Registered school administrators, educators, and students in your school.</strong></p>
                        <p>You can create new accounts, view user details, and take actions like editing or removing users when needed.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- About School Modal -->
    <div id="aboutSchoolModal" class="modal-overlay">
      <div class="info-modal-card">
        <div class="modal-title">School Information Sheet</div>
        <hr class="modal-divider">

        <?php if (!empty($_SESSION['school_update_success'])): ?>
          <div id="schoolUpdateSuccess" class="success-msg" style="color: #15913c; text-align:center; font-weight:bold; margin-bottom:12px;">
            School details updated successfully!
          </div>
          <?php unset($_SESSION['school_update_success']); ?>
        <?php endif; ?>

        <form method="post" id="aboutSchoolForm" autocomplete="off">
          <input type="hidden" name="update_school" value="1">
          <div class="modal-section-title">School Profile</div>
          <div class="info-row">
            <div class="info-label">
              <input type="text" name="schoolName" required value="<?php echo htmlspecialchars($school['scName']); ?>">
              <div class="mini-label">School Name</div>
            </div>
            <div class="info-label">
              <input type="text" name="shortName" required value="<?php echo htmlspecialchars($school['scShort']); ?>">
              <div class="mini-label">Short Name</div>
            </div>
            <div class="info-label">
              <input type="text" name="schoolIdNo" value="<?php echo htmlspecialchars($school['scId']); ?>" readonly>
              <div class="mini-label">School ID No.</div>
            </div>
          </div>
          <div class="info-row">
            <div class="info-label">
              <select name="schoolType" required>
                <option value="Public Integrated School" <?php if($school['scType']=="Public Integrated School") echo 'selected'; ?>>Public Integrated School</option>
                <option value="Private Integrated School" <?php if($school['scType']=="Private Integrated School") echo 'selected'; ?>>Private Integrated School</option>
                <option value="Private Elementary School" <?php if($school['scType']=="Private Elementary School") echo 'selected'; ?>>Private Elementary School</option>
                <option value="Public Elementary School" <?php if($school['scType']=="Public Elementary School") echo 'selected'; ?>>Public Elementary School</option>
              </select>
              <div class="mini-label">School Type</div>
            </div>
            <div class="info-label">
              <input type="email" name="emailAddress" required value="<?php echo htmlspecialchars($school['scEmail']); ?>">
              <div class="mini-label">Email Address</div>
            </div>
            <div class="info-label">
              <input type="text" name="contactNo" required value="<?php echo htmlspecialchars($school['scContact']); ?>">
              <div class="mini-label">Contact No.</div>
            </div>
          </div>
          <div class="modal-buttons">
            <button type="submit" class="save-btn">Save Changes</button>
            <button type="button" class="close-btn" onclick="closeAboutSchoolModal()">Close</button>
          </div>
        </form>

        <div class="modal-section-title" style="margin-top: 24px;">Authorized School Administrator</div>
        <div class="info-row">
          <div class="info-label">
            <strong><?php echo htmlspecialchars(trim(($authAdmin['adFirstName'] ?? '') . ' ' . ($authAdmin['adLastName'] ?? '')) ?: '-'); ?></strong>
            <div class="mini-label">Full Name</div>
          </div>
          <div class="info-label">
            <strong><?php echo htmlspecialchars($authAdmin['adUsername'] ?? '-'); ?></strong>
            <div class="mini-label">Username</div>
          </div>
          <div class="info-label">
            <strong><?php echo htmlspecialchars($authAdmin['adEmail'] ?? '-'); ?></strong>
            <div class="mini-label">Email Address</div>
          </div>
          <div class="info-label">
            <strong><?php echo htmlspecialchars($authAdmin['adContact'] ?? '-'); ?></strong>
            <div class="mini-label">Contact No.</div>
          </div>
          <div class="info-label">
            <strong><?php echo htmlspecialchars($authAdmin['adRefNo'] ?? '-'); ?></strong>
            <div class="mini-label">Acc. Reference No.</div>
          </div>
        </div>
      </div>
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
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    });

    function showAboutSchoolModal() {
        document.getElementById('aboutSchoolModal').classList.add('active');
    }
    function closeAboutSchoolModal() {
        document.getElementById('aboutSchoolModal').classList.remove('active');
        var msg = document.getElementById('schoolUpdateSuccess');
        if (msg) msg.style.display = 'none';
    }
</script>
</body>
</html>
