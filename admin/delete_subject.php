<?php
session_start();
include '../sql/db_connect.php';

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
if (!isset($_SESSION['schoolIdNo'])) {
    header("Location: ../login/login.php");
    exit();
}
$adminSchoolIdNo = $_SESSION['schoolIdNo'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: view_subjects.php");
    exit();
}

// Validate subjectRefNo
if (!isset($_POST['subjectRefNo']) || !is_numeric($_POST['subjectRefNo'])) {
    header("Location: view_subjects.php?msg=Invalid%20request");
    exit();
}

$subjectRefNo = intval($_POST['subjectRefNo']);

// Check subject belongs to admin's school
$stmt = $conn->prepare("SELECT * FROM subject WHERE subjectRefNo = ? AND schoolIdNo = ?");
$stmt->bind_param("ii", $subjectRefNo, $adminSchoolIdNo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    header("Location: view_subjects.php?msg=Not%20found%20or%20unauthorized");
    exit();
}
$stmt->close();

// 5. Delete the subject
$stmt = $conn->prepare("DELETE FROM subject WHERE subjectRefNo = ? AND schoolIdNo = ?");
$stmt->bind_param("ii", $subjectRefNo, $adminSchoolIdNo);
$success = $stmt->execute();
$stmt->close();

if ($success) {
    header("Location: view_subjects.php?msg=Subject%20deleted%20successfully");
    exit();
} else {
    header("Location: view_subjects.php?msg=Failed%20to%20delete%20subject");
    exit();
}
?>
