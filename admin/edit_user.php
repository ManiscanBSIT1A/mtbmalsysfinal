<?php
session_start();
include '../sql/db_connect.php';

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
if (!isset($_GET['accRefNo'])) {
    header("Location: mtbmal_accs.php");
    exit();
}

$accRefNo = (int)$_GET['accRefNo'];
$error = "";
$success = "";

// Get current user info
$stmt = $conn->prepare("SELECT * FROM mtbmalusers WHERE accRefNo=?");
$stmt->bind_param("i", $accRefNo);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    $error = "User not found!";
    $firstName = $lastName = $accType = "";
} else {
    $firstName = $user['firstName'];
    $lastName  = $user['lastName'];
    $accType   = $user['accType'];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstName = trim($_POST['firstName']);
    $lastName  = trim($_POST['lastName']);

    if (empty($firstName) || empty($lastName)) {
        $error = "Both fields are required!";
    } else {
        // Update mtbmalusers
        $stmt = $conn->prepare("UPDATE mtbmalusers SET firstName=?, lastName=? WHERE accRefNo=?");
        $stmt->bind_param("ssi", $firstName, $lastName, $accRefNo);
        $stmt->execute();
        $stmt->close();

        // Update fullName in the respective role table
        $fullName = $firstName . ' ' . $lastName;

        if ($accType === 'School Administrator') {
            $stmt = $conn->prepare("UPDATE schooladministrator SET fullName=? WHERE accRefNo=?");
            $stmt->bind_param("si", $fullName, $accRefNo);
        } elseif ($accType === 'Educator') {
            $stmt = $conn->prepare("UPDATE educator SET fullName=? WHERE accRefNo=?");
            $stmt->bind_param("si", $fullName, $accRefNo);
        } elseif ($accType === 'Student') {
            $stmt = $conn->prepare("UPDATE student SET fullName=? WHERE accRefNo=?");
            $stmt->bind_param("si", $fullName, $accRefNo);
        }
        if (isset($stmt)) {
            $stmt->execute();
            $stmt->close();
        }

        if ($accRefNo == $_SESSION['accRefNo']) {
            $_SESSION['firstName'] = $firstName;
        }

        $success = "Name updated successfully!";
        echo '<meta http-equiv="refresh" content="1.7;URL=mtbmal_accs.php">';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit User Name</title>
  <link rel="icon" type="image/png" href="../images/MTB-MAL_logo.png">
  <style>
    body {
      background: #F7D5FF;
      font-family: 'Segoe UI', Arial, sans-serif;
      margin: 0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .edit-form-wrapper {
      background: #fff0fa;
      border-radius: 16px;
      box-shadow: 0 10px 24px rgba(190,90,225,0.12), 0 1.5px 3px #e2b3ee;
      padding: 36px 42px 28px 42px;
      max-width: 390px;
      width: 98%;
      margin: 32px auto;
    }
    .edit-form-wrapper h2 {
      text-align: center;
      font-size: 2em;
      color: #8844ad;
      margin-bottom: 8px;
      margin-top: 0;
      letter-spacing: 0.02em;
    }
    .edit-form-wrapper label {
      font-weight: 600;
      margin-bottom: 6px;
      display: block;
      color: #44416a;
      font-size: 1.07em;
    }
    .edit-form-wrapper input[type="text"] {
      width: 100%;
      padding: 12px 12px;
      border: 2px solid #e5c0ff;
      border-radius: 8px;
      margin-bottom: 18px;
      font-size: 1.06em;
      transition: border 0.2s;
      background: #fcf7ff;
    }
    .edit-form-wrapper input[type="text"]:focus {
      outline: none;
      border-color: #aa4cf8;
      background: #fff;
    }
    .edit-form-wrapper .button-row {
      display: flex;
      justify-content: space-between;
      gap: 14px;
    }
    .edit-form-wrapper button,
    .edit-form-wrapper .cancel-btn {
      width: 48%;
      padding: 13px;
      border: none;
      border-radius: 8px;
      font-size: 1.07em;
      font-weight: bold;
      cursor: pointer;
      transition: background .18s, color .16s;
    }
    .edit-form-wrapper button {
      background: #de8ce0;
      color: #3a224b;
      box-shadow: 0 2px 7px rgba(205, 120, 235, 0.08);
    }
    .edit-form-wrapper button:hover {
      background: #bf6bdb;
      color: #fff;
    }
    .edit-form-wrapper .cancel-btn {
      background: #f3b2b2;
      color: #902d2d;
      text-align: center;
      text-decoration: none;
      display: inline-block;
    }
    .edit-form-wrapper .cancel-btn:hover {
      background: #ff9898;
      color: #700606;
    }
    .edit-form-wrapper .success,
    .edit-form-wrapper .error {
      text-align: center;
      margin-bottom: 12px;
      margin-top: -10px;
      border-radius: 7px;
      padding: 10px 8px;
      font-weight: 600;
      letter-spacing: 0.03em;
    }
    .edit-form-wrapper .success { background: #c7ffdf; color: #21744a;}
    .edit-form-wrapper .error   { background: #ffd9df; color: #c0134a;}
  </style>
</head>
<body>
<div class="edit-form-wrapper">
    <h2>Edit Name</h2>
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <form method="POST" autocomplete="off">
        <label for="firstName">First Name</label>
        <input type="text" name="firstName" id="firstName" maxlength="40" value="<?= htmlspecialchars($firstName) ?>" required>
        <label for="lastName">Last Name</label>
        <input type="text" name="lastName" id="lastName" maxlength="40" value="<?= htmlspecialchars($lastName) ?>" required>
        <div class="button-row">
          <button type="submit">Update</button>
          <a href="mtbmal_accs.php" class="cancel-btn">Cancel</a>
        </div>
    </form>
</div>
</body>
</html>
