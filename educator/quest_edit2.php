<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['accType'] !== 'Educator') {
    header("Location: ../login/login.php");
    exit();
}
$educatorName = htmlspecialchars($_SESSION['firstName'] ?? 'Educator');
$ref = $_GET['ref'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit: Quest to Learn</title>
    <link rel="icon" href="../images/MTB-MAL_logo.png" type="image/png">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0ffe6; margin:0;}
        .container { max-width: 650px; margin: 32px auto; background: #fff; border-radius: 22px; box-shadow: 0 4px 20px #9affb633; padding: 36px; }
        h2 { color: #00695c; text-align: center; margin-bottom: 18px;}
        .form-group { margin-bottom: 22px; }
        .form-group label { font-weight: bold; display: block; margin-bottom: 7px; }
        .form-group input, .form-group textarea { width:100%; border: 1px solid #5abf81; border-radius: 8px; padding: 8px; background: #f3fffa; }
        .btns { display: flex; justify-content: space-between; margin-top: 24px;}
        .btn { padding: 11px 36px; font-size: 1rem; border-radius: 9px; border: none; cursor: pointer; font-weight: 600;}
        .btn-primary { background: #26a69a; color: #fff; }
        .btn-cancel { background: #e57373; color: #fff; }
        .btn-primary:hover { filter: brightness(0.91);}
        .btn-cancel:hover { filter: brightness(0.93);}
        .profile {position: absolute; right: 38px; top: 14px; color:#00897b; font-weight: bold;}
    </style>
</head>
<body>
    <div class="profile"><?= $educatorName ?></div>
    <div class="container">
        <h2>Edit Quest to Learn</h2>
        <form action="" method="post">
            <div class="form-group">
                <label for="questTitle">Quest Title</label>
                <input type="text" name="questTitle" id="questTitle" required>
            </div>
            <div class="form-group">
                <label for="questDesc">Description</label>
                <textarea name="questDesc" id="questDesc" rows="3" required></textarea>
            </div>
            <div class="form-group">
                <label for="questScenario">Story/Scenario (Optional)</label>
                <textarea name="questScenario" id="questScenario" rows="4"></textarea>
            </div>
            <div class="form-group">
                <label for="questQuestions">Questions (one per line)</label>
                <textarea name="questQuestions" id="questQuestions" rows="6" required></textarea>
            </div>
            <div class="btns">
                <button type="button" class="btn btn-cancel" onclick="window.location.href='material_options.php'">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</body>
</html>
