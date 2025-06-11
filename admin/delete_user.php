<?php 
session_start();
include '../sql/db_connect.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login/login.php");
    exit();
}
if (!isset($_SESSION['accType']) || $_SESSION['accType'] !== 'School Administrator') {
    // Redirect other user types to their dashboards
    switch ($_SESSION['accType']) {
        case 'Educator': header("Location: ../educator/welcome_educator.php"); exit();
        case 'Student': header("Location: ../student/welcome_student.php"); exit();
        default: header("Location: ../login/login.php"); exit();
    }
}

$schoolIdNo = $_SESSION['schoolIdNo'];
$accRefNo = isset($_GET['accRefNo']) ? intval($_GET['accRefNo']) : 0;

// Prevent deleting self
if ($accRefNo == $_SESSION['accRefNo'] || $accRefNo <= 0) {
    header("Location: mtbmal_accs.php?error=cannotdelete");
    exit();
}

// Check if user exists and belongs to the school, get accType
$stmt = $conn->prepare("SELECT schoolIdNo, accType FROM mtbmalusers WHERE accRefNo=? LIMIT 1");
$stmt->bind_param("i", $accRefNo);
$stmt->execute();
$stmt->bind_result($targetSchoolIdNo, $accType);
if (!$stmt->fetch() || $targetSchoolIdNo != $schoolIdNo) {
    $stmt->close();
    header("Location: mtbmal_accs.php?error=notfound");
    exit();
}
$stmt->close();

// Delete from role table first
if ($accType == 'School Administrator') {
    $roleTable = 'schooladministrator';
} elseif ($accType == 'Educator') {
    $roleTable = 'educator';
} elseif ($accType == 'Student') {
    $roleTable = 'student';
} else {
    header("Location: mtbmal_accs.php?error=notfound");
    exit();
}

$stmt = $conn->prepare("DELETE FROM $roleTable WHERE accRefNo=?");
$stmt->bind_param("i", $accRefNo);
$stmt->execute();
$stmt->close();

// Delete from mtbmalusers
$stmt = $conn->prepare("DELETE FROM mtbmalusers WHERE accRefNo=?");
$stmt->bind_param("i", $accRefNo);
if ($stmt->execute()) {
    $stmt->close();
    header("Location: mtbmal_accs.php?deleted=1");
    exit();
} else {
    $stmt->close();
    header("Location: mtbmal_accs.php?error=failed");
    exit();
}
?>
