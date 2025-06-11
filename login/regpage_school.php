<!--
NOTICE TO AUTOMATED SCANNERS AND REVIEWERS:

This form is part of a learning project for educational/school purposes ONLY.
It is NOT intended for phishing, spamming, or any malicious activity.
Please do NOT flag this site or form as suspicious based solely on the presence of this form.

Thank you for understanding.
-->

<?php
session_start();

// Block access for already logged-in users
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    switch ($_SESSION['accType']) {
        case "School Administrator":
            header("Location: ../admin/welcome_admin.php");
            exit();
        case "Educator":
            header("Location: ../educator/welcome_educator.php");
            exit();
        case "Student":
            header("Location: ../student/welcome_student.php");
            exit();
    }
}

include '../sql/db_connect.php';

$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $schoolName = trim($_POST['schoolName'] ?? '');
    $shortName = trim($_POST['shortName'] ?? '');
    $schoolID = trim($_POST['schoolID'] ?? '');
    $schoolType = trim($_POST['schoolType'] ?? '');
    $schoolEmail = trim($_POST['schoolEmail'] ?? '');
    $schoolContact = trim($_POST['schoolContact'] ?? '');
    $authorizedAdmin = trim($_POST['authorizedAdmin'] ?? '');
    $locAddress = trim($_POST['locAddress'] ?? '');
    $region = trim($_POST['region'] ?? '');

    // Server-side validation
    if (
        $schoolName === "" || $shortName === "" || $schoolID === "" || $schoolType === "" ||
        $schoolEmail === "" || $schoolContact === "" || $authorizedAdmin === "" ||
        $locAddress === "" || $region === ""
    ) {
        $error = "All fields are required!";
    } elseif (!filter_var($schoolEmail, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } elseif (!preg_match('/^[0-9]{6}$/', $schoolID)) {
        $error = "School ID must be 6 digits!";
    } elseif (!preg_match('/^[0-9]{11}$/', $schoolContact)) {
        $error = "Contact Number must be 11 digits!";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $authorizedAdmin)) {
        $error = "Admin username must be alphanumeric (underscores allowed)!";
    } else {
        // Check for duplicate School ID, Email, Contact, or Username
        $checkSql = "SELECT * FROM school WHERE schoolIdNo=? OR emailAddress=? OR contactNo=? OR adminUserName=?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("isss", $schoolID, $schoolEmail, $schoolContact, $authorizedAdmin);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        if ($checkResult->num_rows > 0) {
            $error = "A school with this ID, Email, Contact, or Admin Username already exists!";
        } else {
            // Insert into DB using prepared statements to avoid SQL Injection
            $sql = "INSERT INTO school 
                (schoolName, shortName, schoolIdNo, emailAddress, schoolType, contactNo, locAddress, region, adminUserName)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $error = "Database error: " . $conn->error;
            } else {
                $stmt->bind_param(
                    "ssissssss",
                    $schoolName,
                    $shortName,
                    $schoolID,
                    $schoolEmail,
                    $schoolType,
                    $schoolContact,
                    $locAddress,
                    $region,
                    $authorizedAdmin
                );
                if ($stmt->execute()) {
                    $schoolRefNo = $conn->insert_id;
                    $_SESSION['schoolReg_schoolRefNo'] = $schoolRefNo;
                    $_SESSION['schoolReg_schoolID'] = $schoolID;
                    $_SESSION['schoolReg_data'] = $_POST;
                    header("Location: regpage_mtbadmin.php");
                    exit();
                } else {
                    $error = "Error: " . $stmt->error;
                }
                $stmt->close();
            }
        }
        $checkStmt->close();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register School</title>
  <link rel="icon" type="image/png" href="../images/MTB-MAL_logo.png">
  <link rel="stylesheet" href="../style/registration-style.css">

  <!-- Prevent Caching -->
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Expires" content="0" />
</head>
<body>
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
            </div> <br>
        </div>
    </div>

  <!-- Form Container -->
  <div class="container">
    <div class="form-header">Sign up for MTB-MAL</div>

    <form action="" method="post" onsubmit="return validateForm();">

      <!-- Form Section -->
      <div class="form-section">

        <!-- Column 1: School Profile -->
        <div class="column">

          <div class="section-header">
            <div class="circle-number">1</div>
            <h3>School Profile</h3>
          </div>

          <div class="form-group">
            <label>School Name</label>
            <input name="schoolName" type="text" placeholder="Enter the complete name of the school" required>
          </div>

          <div class="form-group">
            <label>Short Name</label>
            <input name="shortName" type="text" placeholder="Enter the abbreviated name of the school" required>
          </div>

          <div class="form-group">
            <label>School ID Number</label>
            <input name="schoolID" type="text" pattern="[0-9]{6}" maxlength="6" placeholder="Enter the official registered id number of the school (000000)" required title="School ID must be 6 digits">
          </div>

        <div class="form-group">
          <label>School Type</label>
          <select id="selectSchoolType" name="schoolType" required>
            <option value="" disabled selected>Select an option</option>
            <option value="Public Elementary School">Public Elementary School</option>
            <option value="Private Elementary School">Private Elementary School</option>
            <option value="Public Integrated School">Public Integrated School</option>
            <option value="Private Integrated School">Private Integrated School</option>
          </select>
        </div>

          <div class="form-group">
            <label>Email Address</label>
            <input name="schoolEmail" type="email" placeholder="Enter the official email of the school" required>
          </div>

          <div class="form-group">
            <label>Contact Number</label>
            <input name="schoolContact" type="tel" pattern="[0-9]{11}" maxlength="11" placeholder="Enter the active contact number of the school" required title="Contact Number must be 11 digits">
          </div>

          <div class="form-group">
            <label>Admin Username</label>
            <input name="authorizedAdmin" type="text" pattern="^[a-zA-Z0-9_]+$" placeholder="Enter authorized school administrator's username" required title="Username must be alphanumeric (underscores allowed)">
          </div>

          <div class="form-group">
            <label>Location Address</label>
            <input name="locAddress" type="text" placeholder="Enter the local address of the school" required>
          </div>

          <div class="form-group">
            <label>Region</label>
            <input name="region" type="text" placeholder="Enter the region residency of the school" required>
          </div>

          <?php if (!empty($error)): ?>
            <div style="color: red; margin-top: 10px;"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>

        </div>
      </div>

      <!-- Button Group Web -->
      <div class="button-group1">
        <button type="reset" onclick="window.location.href='login.php'" class="cancel-btn">Cancel</button>
        <button type="submit" class="submit-btn">Next</button>
      </div>

      <!-- Button Group Responsive -->
      <div class="button-group2">
        <button type="submit" class="submit-btn">Next</button>
        <button type="reset" onclick="window.location.href='login.php'" class="cancel-btn">Cancel</button>
      </div>

    </form>
  </div>
</div>

<!-- Footer -->
<footer class="footer">
    Mother Tongue-Based Multilingual Assessment and Learning System © 2025
</footer>

<!-- Dropdown and Options Script -->
<script>
    // Toggle language dropdown visibility
    function toggleDropdown1() {
        const arrow = document.getElementById('dropdown-arrow1');
        const menu = document.getElementById('lang-dropdown-menu');
        arrow.classList.toggle('down');
        arrow.classList.toggle('up');
        menu.classList.toggle('hidden');
    }

    // Client-side form validation (additional custom checks)
    function validateForm() {
        // School ID: 6 digits
        const schoolID = document.querySelector('input[name="schoolID"]').value;
        if (!/^[0-9]{6}$/.test(schoolID)) {
            alert("School ID Number must be exactly 6 digits.");
            return false;
        }
        // Contact Number: 11 digits
        const contact = document.querySelector('input[name="schoolContact"]').value;
        if (!/^[0-9]{11}$/.test(contact)) {
            alert("Contact Number must be exactly 11 digits.");
            return false;
        }
        // Username: alphanumeric + underscores
        const username = document.querySelector('input[name="authorizedAdmin"]').value;
        if (!/^[a-zA-Z0-9_]+$/.test(username)) {
            alert("Admin Username must be alphanumeric (underscores allowed).");
            return false;
        }
        return true;
    }
</script>

</body>
</html>
