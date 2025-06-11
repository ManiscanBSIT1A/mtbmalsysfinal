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

// Prevent access if steps not completed
if (!isset($_SESSION['schoolReg_schoolRefNo']) || !isset($_SESSION['adminReg'])) {
    header("Location: regpage_school.php?error=nopermission");
    exit();
}

$error = "";

// Handle cancel action
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['cancel_real']) && $_POST['cancel_real'] === '1') {
    $schoolRefNo = $_SESSION['schoolReg_schoolRefNo'];

    // Delete user accounts linked to the school
    $del1 = $conn->prepare("DELETE FROM mtbmalusers WHERE schoolRefNo = ?");
    $del1->bind_param("i", $schoolRefNo);
    $del1->execute();
    $del1->close();

    // Delete the school
    $stmt = $conn->prepare("DELETE FROM school WHERE schoolRefNo = ?");
    $stmt->bind_param("i", $schoolRefNo);
    $stmt->execute();
    $stmt->close();

    // Clear session
    unset($_SESSION['schoolReg_schoolRefNo'], $_SESSION['schoolReg_data'], $_SESSION['adminReg']);
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit'])) {
    $adminName = trim($_POST['adminName'] ?? '');
    $adminID = trim($_POST['adminID'] ?? '');

    // Basic validation
    if ($adminName === "" || $adminID === "") {
        $error = "All fields are required.";
    } elseif (!preg_match('/^[0-9]{7}$/', $adminID)) {
        $error = "Employee ID must be exactly 7 digits.";
    } else {
        $admin = $_SESSION['adminReg'];
        $schoolRefNo = $_SESSION['schoolReg_schoolRefNo'];

        // Get schoolIdNo
        $schoolStmt = $conn->prepare("SELECT schoolIdNo FROM school WHERE schoolRefNo = ?");
        $schoolStmt->bind_param("i", $schoolRefNo);
        $schoolStmt->execute();
        $schoolStmt->bind_result($schoolID);
        $schoolStmt->fetch();
        $schoolStmt->close();

        if (!$schoolID) {
            $error = "Could not verify School ID. Registration failed.";
        } else {
            // Check for duplicate user
            $checkUser = $conn->prepare("SELECT accRefNo FROM mtbmalusers WHERE username=? OR emailAddress=? OR contactNo=?");
            $checkUser->bind_param("sss", $admin['username'], $admin['email'], $admin['contact']);
            $checkUser->execute();
            $checkUser->store_result();

            if ($checkUser->num_rows > 0) {
                $error = "This username, email, or contact number is already registered as a user.";
            } else {
                // Insert into mtbmalusers
                $stmt = $conn->prepare("INSERT INTO mtbmalusers (schoolRefNo, schoolIdNo, firstName, lastName, dob, emailAddress, contactNo, username, password, accCreator, accType)
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $zero = 0;
                $stmt->bind_param(
                    "iisssssssis",
                    $schoolRefNo,
                    $schoolID,
                    $admin['firstName'],
                    $admin['lastName'],
                    $admin['dob'],
                    $admin['email'],
                    $admin['contact'],
                    $admin['username'],
                    $admin['password'],
                    $zero,
                    $admin['accType']
                );

                if ($stmt->execute()) {
                    $accRefNo = $conn->insert_id;

                    // Insert into schooladministrator
                    $stmt2 = $conn->prepare("INSERT INTO schooladministrator (accRefNo, schoolIdNo, fullName, adEmpIdNo) VALUES (?, ?, ?, ?)");
                    $stmt2->bind_param("iisi", $accRefNo, $schoolID, $adminName, $adminID);

                    if ($stmt2->execute()) {
                        $_SESSION['reg_complete_schoolRefNo'] = $schoolRefNo;
                        $_SESSION['reg_complete_accRefNo'] = $accRefNo;
                        $_SESSION['reg_complete_adminID'] = $adminID;

                        // Cleanup
                        unset($_SESSION['schoolReg_schoolRefNo'], $_SESSION['schoolReg_data'], $_SESSION['adminReg']);
                        header("Location: regpage_complete.php");
                        exit();
                    } else {
                        $error = "Failed to create School Administrator: " . $stmt2->error;
                    }
                    $stmt2->close();
                } else {
                    $error = "Failed to create user account: " . $stmt->error;
                }
                $stmt->close();
            }
            $checkUser->close();
        }
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
  <style>
    .button-row {
        display: flex;
        justify-content: center;
        gap: 10px; 
        margin-top: 20px;
    }
    .button-row .submit-btn, .button-row .cancel-btn {
        padding: 20px 200px;
        border: none;
        border-radius: 10px;
        font-weight: bold;
        font-size: 20px;
        cursor: pointer;
    }
    .button-row .cancel-btn { background: #ffb3b3; order: 1;}
    .button-row .submit-btn { background: #baffc3; order: 2;}
    .button-row .submit-btn:hover, .button-row .cancel-btn:hover {
      filter: brightness(0.94);
      cursor: pointer;
    }
    @media (max-width: 600px) {
      .button-row {
        flex-direction: column;
        gap: 0.8em;
      }
      .button-row .submit-btn, .button-row .cancel-btn {
        max-width: none;
      }
    }
  </style>
</head>
<body>
<div class="main-content">
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
        <div class="form-header">Complete the details for the Account Creation</div>
        <form action="" method="post" id="regForm" onsubmit="return validateForm();">
            <div class="form-section">
                <div class="column">
                    <div class="section-header">
                        <div class="circle-number">3</div>
                        <h3>Authorized School Administrator</h3>
                    </div>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input name="adminName" id="adminName" required placeholder="Enter your full name" value="<?php echo htmlspecialchars($_POST['adminName'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Employee ID</label>
                        <input name="adminID" id="adminID" type="text" required pattern="[0-9]{7}" maxlength="7" placeholder="0000000" value="<?php echo htmlspecialchars($_POST['adminID'] ?? ''); ?>">
                    </div>
                    <?php if ($error): ?>
                        <div class="form-group" style="color: red;">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="button-row">
                <!-- Cancel button submits the cancel form via JS -->
                <button type="button" class="cancel-btn" onclick="document.getElementById('cancelForm').submit();">Cancel</button>
                <button type="submit" name="submit" value="1" class="submit-btn">Submit</button>
            </div>
            <input type="hidden" name="form_type" value="main">
        </form>
        <!-- Hidden cancel form (no required fields) -->
        <form action="" method="post" id="cancelForm" style="display:none;">
            <input type="hidden" name="cancel_real" value="1">
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
function validateForm() {
    var adminName = document.getElementById('adminName').value.trim();
    var empid = document.getElementById('adminID').value.trim();
    if (adminName === "" || empid === "") {
        alert("All fields are required.");
        return false;
    }
    if (!/^[0-9]{7}$/.test(empid)) {
        alert("Employee ID must be exactly 7 digits.");
        return false;
    }
    return true;
}
</script>
</body>
</html>
