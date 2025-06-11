<?php
session_start();
include '../sql/db_connect.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['accType'] !== 'Educator') {
    header("Location: ../login/login.php");
    exit();
}
$educatorAccRefNo = $_SESSION['accRefNo'];
$schoolIdNo = $_SESSION['schoolIdNo'];

// From previous step
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$diffLevel = $_POST['diffLevel'] ?? '';
$topic = $_POST['topic'] ?? '';
$contentText = $_POST['contentText'] ?? '';
$subjectRefNo = $_POST['subjectRefNo'] ?? '';
$tempRefNo = $_POST['tempRefNo'] ?? '';

$success = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['final_submit'])) {
    $chapterNo = intval($_POST['chapterNo']);
    $lmNo = intval($_POST['lmNo']);
    $lmType = "Learning Module";
    $lessonNo = intval($_POST['lessonNo']);
    $topicNo = intval($_POST['topicNo']);

    // 1. Insert to learningmaterial
    $stmt = $conn->prepare("INSERT INTO learningmaterial (subjectRefNo, chapterNo, title, description, lmNo, lmType, diffLevel, schoolIdNo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iississi", $subjectRefNo, $chapterNo, $title, $description, $lmNo, $lmType, $diffLevel, $schoolIdNo);
    $stmt->execute();
    $learningMaterialRefNo = $stmt->insert_id;
    $stmt->close();

    // 2. Insert to module
    $stmt2 = $conn->prepare("INSERT INTO module (lessonNo, learningMaterialRefNo, title, description, schoolIdNo) VALUES (?, ?, ?, ?, ?)");
    $stmt2->bind_param("iissi", $lessonNo, $learningMaterialRefNo, $title, $description, $schoolIdNo);
    $stmt2->execute();
    $moduleId = $stmt2->insert_id;
    $stmt2->close();

    // 3. Insert to learningcontent
    $stmt3 = $conn->prepare("INSERT INTO learningcontent (tempRefNo, topicNo, useby, topic, contentText, moduleId, lessonNo) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt3->bind_param("iiissii", $tempRefNo, $topicNo, $educatorAccRefNo, $topic, $contentText, $moduleId, $lessonNo);
    $stmt3->execute();
    $modTempNo = $stmt3->insert_id;
    $stmt3->close();

    // 4. Insert all uploaded images to supportingmedia
    for ($i = 1; $i <= 4; $i++) {
        $field = 'mediaFile' . $i;
        if (!empty($_FILES[$field]['tmp_name'])) {
            $filename = $_FILES[$field]['name'];
            $filedata = file_get_contents($_FILES[$field]['tmp_name']);
            $stmt4 = $conn->prepare("INSERT INTO supportingmedia (learningMaterialRefNo, gameTempNo, modTempNo, accRefNo, filename, filedata) VALUES (?, NULL, ?, ?, ?, ?)");
            $null = NULL;
            // filedata is a BLOB, use send_long_data
            $stmt4->bind_param("iiiss", $learningMaterialRefNo, $modTempNo, $educatorAccRefNo, $filename, $null);
            $stmt4->send_long_data(4, $filedata);
            $stmt4->execute();
            $stmt4->close();
        }
    }

    $success = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Finalize Learning Module</title>
    <link rel="icon" type="image/png" href="../images/MTB-MAL_logo.png">
    <link rel="stylesheet" href="../style/welcome_educator-style.css">
    <style>
        body { font-family: Arial, sans-serif; background-color: #FFE9D5; margin: 0; }
        .container { display: flex; flex-direction: column; align-items: center; padding: 32px 0; min-height: 90vh; }
        .form-section { background: #FFEF9D; border-radius: 18px; box-shadow: 0 8px 30px rgba(0,0,0,0.13); width: 470px; padding: 28px 32px 32px 32px; }
        h2 { color: #3c005a; font-weight: bold; text-align: center; }
        .form-group { margin-bottom: 17px; }
        label { font-weight: bold; color: #4B2E14; }
        input[type="text"], select { width: 100%; padding: 10px; border-radius: 7px; border: 1.7px solid #C68D38; font-size: 1.05em; margin-top: 5px; background: #FFF9ED; }
        .readonly { background: #eee; }
        .submit-btn, .cancel-btn { border: none; border-radius: 8px; font-size: 18px; font-weight: bold; padding: 10px 60px; margin-top: 18px; cursor: pointer; transition: filter .13s, transform .13s; }
        .submit-btn { background: #aaffaa; color: #184b2d; }
        .cancel-btn { background: #ffaaaa; color: #8d2525; margin-left: 12px;}
        .footer { width: 100%; text-align: center; padding: 8px; font-weight: bold; font-size: 0.98em; margin-top: 38px; border-top: 2px solid #f7c69b; }
        .success-msg { background: #d6ffd6; color: #276328; font-weight: bold; border: 1.3px solid #86c486; border-radius: 6px; padding: 10px 20px; text-align: center; margin-bottom: 22px; margin-top: -15px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Finalize Learning Module</h2>
    <div class="form-section">
        <?php if ($success): ?>
            <div class="success-msg">Learning Module successfully added!</div>
            <script>
                setTimeout(function(){ window.location.href = "material_options.php?subjectRefNo=<?= htmlspecialchars($subjectRefNo) ?>"; }, 1500);
            </script>
        <?php else: ?>
        <form method="post" enctype="multipart/form-data">
            <!-- Summary of already filled info as read-only -->
            <div class="form-group">
                <label>Module Title</label>
                <input type="text" value="<?= htmlspecialchars($title) ?>" class="readonly" readonly>
            </div>
            <div class="form-group">
                <label>Description</label>
                <input type="text" value="<?= htmlspecialchars($description) ?>" class="readonly" readonly>
            </div>
            <div class="form-group">
                <label>Difficulty Level</label>
                <input type="text" value="<?= htmlspecialchars($diffLevel) ?>" class="readonly" readonly>
            </div>
            <div class="form-group">
                <label>Topic</label>
                <input type="text" value="<?= htmlspecialchars($topic) ?>" class="readonly" readonly>
            </div>
            <div class="form-group">
                <label>Content Text</label>
                <input type="text" value="<?= htmlspecialchars($contentText) ?>" class="readonly" readonly>
            </div>
            <!-- Remaining details to fill -->
            <div class="form-group">
                <label>Chapter No.</label>
                <input type="text" name="chapterNo" required>
            </div>
            <div class="form-group">
                <label>Learning Material No. (lmNo)</label>
                <input type="text" name="lmNo" required>
            </div>
            <div class="form-group">
                <label>Lesson No.</label>
                <input type="text" name="lessonNo" required>
            </div>
            <div class="form-group">
                <label>Topic No.</label>
                <input type="text" name="topicNo" required>
            </div>
            <div class="form-group">
                <label>Upload Up To 4 Images (visual aids):</label>
                <input type="file" name="mediaFile1" accept="image/*"><br>
                <input type="file" name="mediaFile2" accept="image/*"><br>
                <input type="file" name="mediaFile3" accept="image/*"><br>
                <input type="file" name="mediaFile4" accept="image/*">
            </div>
            <!-- Hidden fields to keep previous values -->
            <input type="hidden" name="title" value="<?= htmlspecialchars($title) ?>">
            <input type="hidden" name="description" value="<?= htmlspecialchars($description) ?>">
            <input type="hidden" name="diffLevel" value="<?= htmlspecialchars($diffLevel) ?>">
            <input type="hidden" name="topic" value="<?= htmlspecialchars($topic) ?>">
            <input type="hidden" name="contentText" value="<?= htmlspecialchars($contentText) ?>">
            <input type="hidden" name="subjectRefNo" value="<?= htmlspecialchars($subjectRefNo) ?>">
            <input type="hidden" name="tempRefNo" value="<?= htmlspecialchars($tempRefNo) ?>">
            <button type="submit" name="final_submit" class="submit-btn">Save</button>
            <button type="button" class="cancel-btn" onclick="window.location.href='material_options.php?subjectRefNo=<?= htmlspecialchars($subjectRefNo) ?>'">Cancel</button>
        </form>
        <?php endif; ?>
    </div>
</div>
<footer class="footer">
    Mother Tongue-Based Multilingual Assessment and Learning System © 2025
</footer>
</body>
</html>
