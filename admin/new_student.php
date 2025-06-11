<?php
session_start();
include '../sql/db_connect.php';

// Only School Admin allowed
if (!isset($_SESSION['logged_in']) || $_SESSION['accType'] !== 'School Administrator') {
    header("Location: ../login/login.php");
    exit();
}

switch ($_SESSION['accType']) {
    case "School Administrator":
        break;
    case "Educator":
        header("Location: ../educator/welcome_educator.php");
        exit();
    case "Student":
        header("Location: ../student/welcome_student.php");
        exit();
    default:
        session_unset();
        session_destroy();
        header("Location: ../login/login.php");
        exit();
}

$adminAccRefNo = $_SESSION['accRefNo'];
$adminFirstName = $_SESSION['firstName'];
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $schoolRefNo = trim($_POST['school_ref']);
    $schoolIdNo = trim($_POST['school_id']);
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $dob = $_POST['dob'];
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $re_password = $_POST['re_password'];
    $fullName = $firstName . ' ' . $lastName;
    $lrn = trim($_POST['lrn']); // VARCHAR(12), keep as string
    $parentName = trim($_POST['parent_name']);
    $pgRelation = trim($_POST['pg_relation']);
    $pgDOB = $_POST['pg_dob'];
    $pgMaritalStatus = trim($_POST['pg_marital_status']);
    $pgEmailAdd = trim($_POST['pg_email']);
    $pgContactNo = trim($_POST['pg_contact']);

    // --- Uniqueness Checks ---
    // LRN
    $checkLrn = $conn->prepare("SELECT accRefNo FROM student WHERE lrn = ?");
    $checkLrn->bind_param("s", $lrn);
    $checkLrn->execute();
    $checkLrn->store_result();
    if ($checkLrn->num_rows > 0) {
        $errors[] = "A student with this LRN already exists.";
    }
    $checkLrn->close();

    // Username
    $checkUsername = $conn->prepare("SELECT accRefNo FROM mtbmalusers WHERE username = ?");
    $checkUsername->bind_param("s", $username);
    $checkUsername->execute();
    $checkUsername->store_result();
    if ($checkUsername->num_rows > 0) {
        $errors[] = "This username is already taken.";
    }
    $checkUsername->close();

    // Email
    $checkEmail = $conn->prepare("SELECT accRefNo FROM mtbmalusers WHERE emailAddress = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmail->store_result();
    if ($checkEmail->num_rows > 0) {
        $errors[] = "This email address is already in use.";
    }
    $checkEmail->close();

    // Contact
    $checkContact = $conn->prepare("SELECT accRefNo FROM mtbmalusers WHERE contactNo = ?");
    $checkContact->bind_param("s", $contact);
    $checkContact->execute();
    $checkContact->store_result();
    if ($checkContact->num_rows > 0) {
        $errors[] = "This contact number is already in use.";
    }
    $checkContact->close();

    // --- Validation ---
    if (!ctype_digit($schoolRefNo)) $errors[] = "Invalid School Reference Number.";
    if (!ctype_digit($schoolIdNo)) $errors[] = "Invalid School ID Number.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid Email.";
    if (!preg_match('/^\d{11}$/', $contact)) $errors[] = "Contact number must be 11 digits.";
    if (strlen($username) < 5) $errors[] = "Username must be at least 5 characters.";
    if ($password !== $re_password) $errors[] = "Passwords do not match.";
    if (!preg_match('/^\d{12}$/', $lrn)) $errors[] = "LRN must be exactly 12 digits.";
    if (!preg_match('/^\d{11}$/', $pgContactNo)) $errors[] = "Guardian contact number must be 11 digits.";
    if (!filter_var($pgEmailAdd, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid Guardian Email.";

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt1 = $conn->prepare("INSERT INTO mtbmalusers (schoolRefNo, schoolIdNo, firstName, lastName, dob, emailAddress, contactNo, username, password, accCreator, accType) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Student')");
        $stmt1->bind_param("iisssssssi", $schoolRefNo, $schoolIdNo, $firstName, $lastName, $dob, $email, $contact, $username, $hashedPassword, $adminAccRefNo);

        if ($stmt1->execute()) {
            $accRefNo = $stmt1->insert_id;
            $stmt2 = $conn->prepare("INSERT INTO student (accRefNo, schoolIdNo, lrn, fullName, parentGuardianName, pgRStoStudent, pgDOB, pgMaritalStatus, pgEmailAdd, pgContactNo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param("iissssssss", $accRefNo, $schoolIdNo, $lrn, $fullName, $parentName, $pgRelation, $pgDOB, $pgMaritalStatus, $pgEmailAdd, $pgContactNo);

            if ($stmt2->execute()) {
                $_SESSION['new_student_refno'] = $accRefNo;
                header("Location: student_complete.php");
                exit();
            } else {
                $errors[] = "Student table error: " . $stmt2->error;
                // Remove orphaned user record
                $deleteUser = $conn->prepare("DELETE FROM mtbmalusers WHERE accRefNo = ?");
                $deleteUser->bind_param("i", $accRefNo);
                $deleteUser->execute();
                $deleteUser->close();
            }
            $stmt2->close();
        } else {
            $errors[] = "User table error: " . $stmt1->error;
        }
        $stmt1->close();
    }
}
if (isset($_POST['cancel'])) {
    header('Location: manage_user_options.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Student Account</title>
    <link rel="stylesheet" href="../style/acc_create-style.css">
    <link rel="icon" href="../images/MTB-MAL_logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
    @media (max-width: 900px) {
        .container { padding: 8px !important;}
        .form-section { flex-direction: column; padding: 5px !important;}
        .form-group input, .form-group label, .form-group select { width: 99% !important; max-width: 99vw !important;}
        .column { padding: 0 !important; }
        .button-group1, .button-group2 { width: 100%; text-align: center; }
        .button-group1 button, .button-group2 button { width: 98vw !important; margin-bottom: 8px; }
    }
    </style>
    <script>
    function validateForm() {
        let password = document.forms["studentForm"]["password"].value;
        let re_password = document.forms["studentForm"]["re_password"].value;
        if (password !== re_password) {
            alert("Passwords do not match!");
            return false;
        }
        return true;
    }
    </script>
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
<!-- Top Bar -->
<div class="topbar">
  <div class="left">
    <div class="logo-topbar" onclick="toggleSidebar()">
      <img src="../images/MTB-MAL_logo-alt.png" alt="MTB-MAL Logo">
    </div>
    <div class="system-title">Mother Tongue-Based Multilingual Assessment and Learning System</div>
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
  <span>Create New Student Account</span>
  <div class="profile-container" onclick="toggleDropdown2()">
    <div class="profile-circle"></div>
    <span style="font-weight:bold;"><?php echo htmlspecialchars($adminFirstName); ?></span>
    <div id="dropdown-arrow2" class="dropdown-arrow2 down"></div>
    <div id="profile-dropdown-menu" class="profile-dropdown hidden">
      <a href="admin_view_profile.php"><div class="dropdown-item">View Profile</div></a>
      <a href="../login/logout.php"><div class="dropdown-item">Logout</div></a>
    </div>
  </div>
</div>
<?php if (!empty($errors)): ?>
  <div style="color: red; text-align: center;">
    <?php foreach ($errors as $err) echo "<p>$err</p>"; ?>
  </div>
<?php endif; ?>
<form name="studentForm" method="POST" action="" onsubmit="return validateForm();">
  <div class="container">
    <h2>Student’s Profile</h2>
    <div class="form-section">
      <div class="column">
        <div class="form-group">
          <label for="school_ref">School Reference Number</label>
          <input type="text" name="school_ref" required>
          <label for="first_name">Student's First Name</label>
          <input type="text" name="first_name" required>
          <label for="last_name">Student's Last Name</label>
          <input type="text" name="last_name" required>
          <label for="dob">Student's Date of Birth</label>
          <input type="date" name="dob" required>
          <label for="email">Student's Email Address</label>
          <input type="email" name="email" required>
          <label for="contact">Student's Contact Number</label>
          <input type="text" name="contact" required>
          <label for="username">Student's Username</label>
          <input type="text" name="username" required>
          <label for="password">Student's Password</label>
          <input type="password" name="password" required>
          <label for="re_password">Confirm Student's Password</label>
          <input type="password" name="re_password" required>
        </div>
      </div>
      <div class="column">
        <div class="form-group">
          <label for="school_id">School Identification Number</label>
          <input type="text" name="school_id" required>
          <label for="lrn">Learner's Reference Number</label>
          <input type="text" name="lrn" required>
          <label for="parent_name">Parent/Guardian Full Name</label>
          <input type="text" name="parent_name" required>
          <label for="pg_relation">Relation to Student</label>
          <select name="pg_relation" required>
            <option value="">--Select--</option>
            <option value="Father">Father</option>
            <option value="Mother">Mother</option>
            <option value="Guardian">Guardian</option>
            <option value="Sibling">Sibling</option>
            <option value="Close Relative">Close Relative</option>
          </select>
          <label for="pg_dob">Parent/Guardian Date of Birth</label>
          <input type="date" name="pg_dob" required>
          <label for="pg_marital_status">Parent/Guardian Marital Status</label>
          <input type="text" name="pg_marital_status" required>
          <label for="pg_email">Parent/Guardian Email</label>
          <input type="email" name="pg_email" required>
          <label for="pg_contact">Parent/Guardian Contact Number</label>
          <input type="text" name="pg_contact" required>
          <label for="acc_creator">Account Creator</label>
          <input type="text" name="acc_creator_display" value="<?php echo (int)$adminAccRefNo; ?>" readonly>
        </div>
      </div>
    </div>
    <div class="button-group1">
      <button type="button" onclick="window.location.href='manage_user_options.php'" class="cancel-btn">Cancel</button>
      <button type="submit" name="submit" class="submit-btn" style="margin-left: 10px;">Submit</button>
    </div>

    <div class="button-group2">
      <button type="button" onclick="window.location.href='manage_user_options.php'" class="cancel-btn">Cancel</button>
      <button type="submit" name="submit" class="submit-btn" style="margin-left: 10px;">Submit</button>
    </div>
  </div>
</form>
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
