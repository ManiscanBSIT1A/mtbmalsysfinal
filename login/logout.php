<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: frontpage.php");
    exit();
}

session_unset();
session_destroy();
header("Location: login.php?logout=1");
exit();
?>
