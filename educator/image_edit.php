<?php
session_start();
include '../sql/db_connect.php';

// --- Security and timeout ---
$inactive = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive)) {
    session_unset();
    session_destroy();
    header("Location: ../login/login.php?session_expired=1");
    exit();
}
$_SESSION['last_activity'] = time();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login/login.php");
    exit();
}
if (!isset($_SESSION['accType']) || $_SESSION['accType'] !== 'Educator') {
    if ($_SESSION['accType'] === 'School Administrator') header("Location: ../admin/welcome_admin.php");
    else if ($_SESSION['accType'] === 'Student') header("Location: ../student/welcome_student.php");
    else header("Location: ../login/login.php");
    exit();
}

$educatorName = htmlspecialchars($_SESSION['firstName'] ?? 'Educator');
$schoolIdNo = $_SESSION['schoolIdNo'];
$accRefNo = $_SESSION['accRefNo'];
$subjects = [];
$sqlSub = "SELECT subjectRefNo, subjTitle FROM subject WHERE assignedEducator = ?";
$stmtSub = $conn->prepare($sqlSub);
$stmtSub->bind_param("i", $accRefNo);
$stmtSub->execute();
$resultSub = $stmtSub->get_result();
while ($row = $resultSub->fetch_assoc()) {
    $subjects[] = $row;
}
$stmtSub->close();


$showForm = true;
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['final_submit'])) {
    // Handle FINAL submit (step 2): insert to DB
    $subjectRefNo = $_POST['subjectRefNo'];
    $chapterNo = $_POST['chapterNo'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $diffLevel = $_POST['diffLevel'];

    // 1. Insert to learningmaterial
    $stmt = $conn->prepare("INSERT INTO learningmaterial (subjectRefNo, chapterNo, title, description, lmNo, lmType, diffLevel, schoolIdNo) VALUES (?, ?, ?, ?, ?, 'Learning Module', ?, ?)");
    $lmNo = rand(1,99); // or however you want to set this
    $stmt->bind_param("iissisi", $subjectRefNo, $chapterNo, $title, $description, $lmNo, $diffLevel, $schoolIdNo);
    if ($stmt->execute()) {
        $learningMaterialRefNo = $stmt->insert_id;
        // 2. Handle image upload (to supportingmedia)
        if (isset($_FILES['imagefile']) && $_FILES['imagefile']['error'] == 0) {
            // Validate type (image only)
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $_FILES['imagefile']['tmp_name']);
            finfo_close($finfo);

            $allowed = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($mimeType, $allowed)) {
                $message = "Please upload a valid image file (jpg, png, gif).";
            } else {
                $imgData = file_get_contents($_FILES['imagefile']['tmp_name']);
                $filename = $_FILES['imagefile']['name'];
                $sql2 = "INSERT INTO supportingmedia (learningMaterialRefNo, accRefNo, filename, filedata) VALUES (?, ?, ?, ?)";
                $stmt2 = $conn->prepare($sql2);
                $stmt2->bind_param("iiss", $learningMaterialRefNo, $accRefNo, $filename, $imgData);
                $stmt2->send_long_data(3, $imgData);
                $stmt2->execute();
                $stmt2->close();
                $message = "Learning Material uploaded successfully!";
                $showForm = false;
            }
        } else {
            $message = "No image uploaded or upload error.";
        }
    } else {
        $message = "Database error: " . $conn->error;
    }
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Image Module</title>
    <style>
    body { font-family: 'Segoe UI', sans-serif; background: #fff5fb; }
    .container { margin: 40px auto; width: 430px; background: #fff; border-radius: 17px; padding: 36px 32px 28px; box-shadow: 0 4px 20px #f7b4ee55;}
    h2 { color: #c84db1; font-size: 1.35em; margin-top:0;}
    .field { margin: 16px 0; }
    .field label { display:block; font-weight:bold; color:#ab4599; margin-bottom:4px;}
    .field input, .field textarea, .field select { width: 100%; padding: 8px; border-radius: 7px; border: 1px solid #eee; background:#fffafc; font-size:1em;}
    .field textarea { min-height: 60px;}
    .btn { padding: 10px 28px; border-radius: 8px; border:none; background: #c84db1; font-weight: bold; color: #fff; cursor:pointer; font-size:1em;}
    .btn.cancel { background: #d07676; color: #fff;}
    .msg { background: #f7b4ee; color:#7b266b; padding: 9px 15px; border-radius: 7px; margin: 18px 0;}
    hr { border:none; border-top:1px solid #f1cde8; margin:22px 0 15px;}
    .field select:focus, .field input:focus, .field textarea:focus { outline: 2px solid #e7a7d2; }
    .actions { display:flex;gap:18px;justify-content:right;margin-top:20px;}
    .actions .btn.cancel { background: #f86e8d; }
    .actions .btn { background: #c84db1; }
    .profile-bar {
        margin-bottom: 12px;
        text-align: right;
        font-weight: bold;
        color: #b152b3;
        letter-spacing: .4px;
        font-size: 1.1em;
    }
    </style>
</head>
<body>
<div class="container">
    <div class="profile-bar"><?= $educatorName ?></div>
    <h2>Upload Image Module</h2>
    <?php if($message) echo "<div class='msg'>$message</div>"; ?>

    <?php if($showForm): ?>
    <form method="POST" enctype="multipart/form-data">
        <!-- IMAGE UPLOAD -->
        <div class="field">
            <label>Upload Lesson Image</label>
            <input type="file" name="imagefile" accept="image/*" required>
        </div>
        <hr>
        <!-- LEARNING MATERIAL FIELDS -->
        <div class="field">
            <label>Subject</label>
            <select name="subjectRefNo" required>
                <option value="">Select Subject</option>
                <?php foreach ($subjects as $sub): ?>
                    <option value="<?= htmlspecialchars($sub['subjectRefNo']) ?>">
                        <?= htmlspecialchars($sub['subjTitle']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>Chapter No</label>
            <input type="number" name="chapterNo" min="1" max="99" required>
        </div>
        <div class="field">
            <label>Title</label>
            <input type="text" name="title" maxlength="155" required>
        </div>
        <div class="field">
            <label>Description</label>
            <textarea name="description" required></textarea>
        </div>
        <div class="field">
            <label>Difficulty</label>
            <select name="diffLevel" required>
                <option value="">Select Difficulty</option>
                <option value="Easy">Easy</option>
                <option value="Intermediate">Intermediate</option>
                <option value="Hard">Hard</option>
            </select>
        </div>
        <input type="hidden" name="final_submit" value="1">
        <div class="actions">
            <a href="material_options.php" class="btn cancel" style="text-decoration:none;">Cancel</a>
            <button type="submit" class="btn">Submit</button>
        </div>
    </form>
    <?php endif; ?>
</div>
</body>
</html>
