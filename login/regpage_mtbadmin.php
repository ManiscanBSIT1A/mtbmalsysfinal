<!--
NOTICE TO AUTOMATED SCANNERS AND REVIEWERS:

This form is part of a learning project for educational/school purposes ONLY.
It is NOT intended for phishing, spamming, or any malicious activity.
Please do NOT flag this site or form as suspicious based solely on the presence of this form.

Thank you for understanding.
-->
<?php
session_start();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    switch ($_SESSION['accType']) {
        case 'School Administrator':
            header("Location: ../admin/welcome_admin.php");
            exit();
        case 'Educator':
            header("Location: ../educator/welcome_educator.php");
            exit();
        case 'Student':
            header("Location: ../student/welcome_student.php");
            exit();
    }
}

include '../sql/db_connect.php';

// Prevent access if user hasn't completed school registration
if (!isset($_SESSION['schoolReg_schoolRefNo']) || !isset($_SESSION['schoolReg_schoolID'])) {
    header("Location: regpage_school.php?error=nopermission");
    exit();
}

$error = "";

// Handle cancel
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST['action'] ?? '') === 'cancel') {
    if (isset($_SESSION['schoolReg_schoolRefNo'])) {
        $schoolRefNo = $_SESSION['schoolReg_schoolRefNo'];
        $stmt = $conn->prepare("DELETE FROM school WHERE schoolRefNo = ?");
        $stmt->bind_param("i", $schoolRefNo);
        $stmt->execute();
        $stmt->close();
    }
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Handle next
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST['action'] ?? '') === 'next') {
    $required = [
        'adminFirstName', 'adminLastName', 'adminDOB', 'adminEmail', 'adminContact',
        'username', 'password', 'rePassword', 'AcountType', 'adminSchoolID'
    ];

    foreach ($required as $field) {
        if (empty(trim($_POST[$field] ?? ''))) {
            $error = "All fields are required.";
            break;
        }
    }

    if (empty($error)) {
        if (!filter_var($_POST['adminEmail'], FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } elseif ($_POST['password'] !== $_POST['rePassword']) {
            $error = "Passwords did not match.";
        } elseif (strlen($_POST['password']) < 6) {
            $error = "Password must be at least 6 characters.";
        } elseif (!preg_match('/^[0-9]{6}$/', $_POST['adminSchoolID'])) {
            $error = "School ID should be exactly 6 digits.";
        } elseif (!preg_match('/^[0-9]{10,12}$/', $_POST['adminContact'])) {
            $error = "Contact number should be 10-12 digits.";
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $_POST['username'])) {
            $error = "Username must be alphanumeric (underscores allowed).";
        }
    }

    if (empty($error)) {
        // Save everything in session
        $_SESSION['adminReg'] = [
            'firstName'   => trim($_POST['adminFirstName']),
            'lastName'    => trim($_POST['adminLastName']),
            'dob'         => $_POST['adminDOB'],
            'email'       => trim($_POST['adminEmail']),
            'contact'     => trim($_POST['adminContact']),
            'username'    => trim($_POST['username']),
            'password'    => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'accType'     => $_POST['AcountType'],
            'schoolID'    => $_POST['adminSchoolID'] // stored for use in mtbmalusers
        ];
        header("Location: regpage_admintable.php");
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register Admin</title>
  <link rel="icon" type="image/png" href="../images/MTB-MAL_logo.png">
  <link rel="stylesheet" href="../style/registration-style.css">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Expires" content="0" />
</head>
<body>
<div class="main-content">
    <!-- Top Bar -->
    <div class="topbar">
        <div class="left">
            <div class="logo-topbar">
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
    <div class="container">
        <div class="form-header">Account Creation for MTB-MAL</div>
        <form id="adminForm" action="" method="post" onsubmit="return validateForm();">
            <div class="form-section">
                <div class="column">
                    <div class="section-header">
                        <div class="circle-number">2</div>
                        <h3>Authorized School Administrator</h3>
                    </div>
                    <div class="form-group">
                        <label>First Name</label>
                        <input name="adminFirstName" type="text" required
                            placeholder="Enter the first name of the authorized School Administrator"
                            value="<?php echo htmlspecialchars($_POST['adminFirstName'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input name="adminLastName" type="text" required
                            placeholder="Enter the last name of the authorized School Administrator"
                            value="<?php echo htmlspecialchars($_POST['adminLastName'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input name="adminDOB" type="date" required
                            value="<?php echo htmlspecialchars($_POST['adminDOB'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input name="adminEmail" type="email" required
                            placeholder="Enter the email address of the authorized School Administrator"
                            value="<?php echo htmlspecialchars($_POST['adminEmail'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Contact Number</label>
                        <input name="adminContact" type="tel" required pattern="[0-9]{10,12}" maxlength="12"
                            placeholder="Enter an active contact number"
                            value="<?php echo htmlspecialchars($_POST['adminContact'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input name="username" type="text" required
                            placeholder="Enter a unique username"
                            pattern="^[a-zA-Z0-9_]+$"
                            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input id="password" name="password" type="password" required minlength="6"
                            placeholder="Enter your password">
                    </div>
                    <div class="form-group">
                        <label>Re-enter Password</label>
                        <input id="rePassword" name="rePassword" type="password" required minlength="6"
                            placeholder="Re-enter your password">
                    </div>
                    <div class="form-group">
                        <label>Account Type</label>
                        <select id="selectAccountType" name="AcountType" required>
                            <option value="" disabled <?php echo !isset($_POST['AcountType']) ? 'selected' : ''; ?>>Select an option</option>
                            <option value="School Administrator" <?php if(($_POST['AcountType'] ?? '')=='School Administrator') echo 'selected'; ?>>School Administrator</option>
                            <option value="Educator" <?php if(($_POST['AcountType'] ?? '')=='Educator') echo 'selected'; ?>>Educator</option>
                            <option value="Student" <?php if(($_POST['AcountType'] ?? '')=='Student') echo 'selected'; ?>>Student</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>School ID Number</label>
                        <input name="adminSchoolID" type="text" required pattern="[0-9]{6}" maxlength="6"
                            placeholder="Enter the official ID Number of the School (000000)"
                            value="<?php echo htmlspecialchars($_POST['adminSchoolID'] ?? ''); ?>">
                    </div>
                    <?php if ($error): ?>
                        <div class="form-group" id="formError" style="color: red;">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Button Groups -->
            <div class="button-group1">
                <button type="button" onclick="doCancel()" class="cancel-btn">Cancel</button>
                <button type="submit" name="action" value="next" class="submit-btn">Next</button>
            </div>
            <div class="button-group2">
                <button type="submit" name="action" value="next" class="submit-btn">Next</button>
                <button type="button" onclick="doCancel()" class="cancel-btn">Cancel</button>
            </div>
        </form>
        <!-- Hidden form for Cancel action -->
        <form id="cancelForm" method="post" style="display:none;">
            <input type="hidden" name="action" value="cancel">
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
    // Client-side validation (Next only)
    function validateForm() {
        // Only validate if not cancel
        return true; // Let HTML5 required fields handle validation
    }
    // Cancel button: submit the separate cancel form (bypasses field validation)
    function doCancel() {
        document.getElementById('cancelForm').submit();
    }
</script>
</body>
</html>
