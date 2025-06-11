<?php
session_start();
include '../sql/db_connect.php';

// Role-based security
if (!isset($_SESSION['logged_in']) || $_SESSION['accType'] !== 'Educator') {
    if (isset($_SESSION['accType']) && $_SESSION['accType'] === 'School Administrator') header("Location: ../admin/welcome_admin.php");
    elseif (isset($_SESSION['accType']) && $_SESSION['accType'] === 'Student') header("Location: ../student/welcome_student.php");
    else header("Location: ../login/login.php");
    exit();
}

$educatorRefNo = $_SESSION['accRefNo'];
$schoolIdNo = $_SESSION['schoolIdNo'];
$subjectRefNo = $_GET['subjectRefNo'] ?? null;
$tempRefNo = $_GET['ref'] ?? null;

$success = false;
$error = '';

if (isset($_POST['cancel'])) {
    header("Location: material_options.php?subjectRefNo=" . urlencode($subjectRefNo));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $instruction = trim($_POST['instruction'] ?? '');
    $scoringRubric = trim($_POST['scoringRubric'] ?? '');
    $diffLevel = $_POST['diffLevel'] ?? 'Easy';

    $questions = $_POST['question'] ?? [];
    $answers = $_POST['answer'] ?? [];

    // Images
    $start_img = $_FILES['start_image'] ?? null;
    $finish_img = $_FILES['finish_image'] ?? null;
    $bg_img = $_FILES['bg_image'] ?? null;

    // Check all fields
    $img_ok = !empty($start_img['tmp_name']) && !empty($finish_img['tmp_name']) && !empty($bg_img['tmp_name']);
    $valid_types = ['image/jpeg','image/png','image/gif','image/jpg'];

    function valid_img($file, $valid_types) {
        return $file && $file['tmp_name'] && in_array(mime_content_type($file['tmp_name']), $valid_types);
    }

    if (
        !$title || !$description || !$instruction || !$scoringRubric ||
        count($questions) !== 10 || count($answers) !== 10 ||
        in_array('', $questions, true) || in_array('', $answers, true) ||
        !$img_ok ||
        !valid_img($start_img, $valid_types) ||
        !valid_img($finish_img, $valid_types) ||
        !valid_img($bg_img, $valid_types)
    ) {
        $error = "Please fill in all fields, complete all 10 items, and upload all 3 required images (PNG, JPG, JPEG, GIF only).";
    } else {
        // --- DB Transaction ---
        $conn->begin_transaction();

        try {
            // 1. learningmaterial
            $chapterNo = 1;
            $lmNo = 1;
            $lmType = "Learning Assessment";
            $stmt = $conn->prepare("INSERT INTO learningmaterial 
                (subjectRefNo, chapterNo, title, description, lmNo, lmType, diffLevel, schoolIdNo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iississi", $subjectRefNo, $chapterNo, $title, $description, $lmNo, $lmType, $diffLevel, $schoolIdNo);
            $stmt->execute();
            $learningMaterialRefNo = $stmt->insert_id;
            $stmt->close();

            // 2. assessment
            $assessmentNo = rand(1000,99999);
            $stmt = $conn->prepare("INSERT INTO assessment 
                (assessmentNo, learningMaterialRefNo, title, description, instruction, scoringRubric, schoolIdNo)
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iissssi", $assessmentNo, $learningMaterialRefNo, $title, $description, $instruction, $scoringRubric, $schoolIdNo);
            $stmt->execute();
            $assessmentId = $stmt->insert_id;
            $stmt->close();

            // 3. assessmentitems
            $assessmentItemsGameTempNos = [];
            for ($i = 0; $i < 10; $i++) {
                $itemNo = $i + 1;
                $q = $questions[$i];
                $a = $answers[$i];
                $stmt = $conn->prepare("INSERT INTO assessmentitems
                    (tempRefNo, assessmentId, useby, assessmentNo, itemNo, iQuestion, correctAnswer)
                    VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iiiisss", $tempRefNo, $assessmentId, $educatorRefNo, $assessmentNo, $itemNo, $q, $a);
                $stmt->execute();
                $gameTempNo = $stmt->insert_id;
                $assessmentItemsGameTempNos[] = $gameTempNo;
                $stmt->close();
            }
            // 4. supportingmedia for images
            $null = NULL;

            // Start Character
            $filename = $start_img['name'];
            $filedata = file_get_contents($start_img['tmp_name']);
            $stmt = $conn->prepare("INSERT INTO supportingmedia 
                (learningMaterialRefNo, gameTempNo, modTempNo, accRefNo, filename, filedata) 
                VALUES (?, ?, NULL, ?, ?, ?)");
            $stmt->bind_param("iiiss", $learningMaterialRefNo, $assessmentItemsGameTempNos[0], $educatorRefNo, $filename, $null);
            $stmt->send_long_data(4, $filedata);
            $stmt->execute();
            $stmt->close();

            // Finish Character
            $filename = $finish_img['name'];
            $filedata = file_get_contents($finish_img['tmp_name']);
            $stmt = $conn->prepare("INSERT INTO supportingmedia 
                (learningMaterialRefNo, gameTempNo, modTempNo, accRefNo, filename, filedata) 
                VALUES (?, ?, NULL, ?, ?, ?)");
            $stmt->bind_param("iiiss", $learningMaterialRefNo, $assessmentItemsGameTempNos[9], $educatorRefNo, $filename, $null);
            $stmt->send_long_data(4, $filedata);
            $stmt->execute();
            $stmt->close();

            // Background
            $filename = $bg_img['name'];
            $filedata = file_get_contents($bg_img['tmp_name']);
            $stmt = $conn->prepare("INSERT INTO supportingmedia 
                (learningMaterialRefNo, gameTempNo, modTempNo, accRefNo, filename, filedata) 
                VALUES (?, ?, NULL, ?, ?, ?)");
            $stmt->bind_param("iiiss", $learningMaterialRefNo, $assessmentItemsGameTempNos[0], $educatorRefNo, $filename, $null);
            $stmt->send_long_data(4, $filedata);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            $success = true;
        } catch (Exception $e) {
            $conn->rollback();
            $error = "An error occurred while saving. Please try again. Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quest to Learn - Create Assessment</title>
    <link rel="icon" type="image/png" href="../images/MTB-MAL_logo.png">
    <link rel="stylesheet" href="../style/quest_edit-style.css">
    <style>
    body { background: #FFE9D5; }
    .container { max-width: 900px; margin: 35px auto; background: #fff6e6; border-radius: 20px; box-shadow: 0 4px 15px #E6A56C44; padding: 35px 35px 18px 35px; }
    .form-section { background: #FFEF9D; border-radius: 18px; padding: 22px 28px; }
    .form-group label { font-weight:bold; color: #4B2E14; }
    .error-msg { background: #ffcccc; color: #c22323; font-weight:bold; border-radius:6px; margin-bottom: 20px; padding: 12px 0; text-align:center; }
    .success-msg { background: #d6ffd6; color: #276328; font-weight:bold; border-radius:6px; margin-bottom: 20px; padding: 12px 0; text-align:center; }
    .qtable { width:100%; border-collapse:collapse; margin:18px 0; }
    .qtable th, .qtable td { border:1px solid #d6ad8c; padding:8px 5px; text-align:left; }
    .qtable th { background:#ffd2b3; }
    .qtable td { background:#fff9ed; }
    .file-row { margin-bottom:10px; }
    .file-row img { max-width:180px; border-radius:12px; margin-top:6px; }
    .topbar, .second-bar, .sidebar, .profile-container, .profile-circle, .footer { background-color: #E6A56C; color: #0A3B52; }
    .submit-btn { background: #aaffaa; border: none; border-radius: 8px; font-size: 18px; font-weight: bold; padding: 8px 52px; color: #184b2d; margin-top:10px; cursor:pointer; }
    .cancel-btn { background: #ffaaaa; border: none; border-radius: 8px; font-size: 18px; font-weight: bold; padding: 8px 52px; color: #932018; margin-top:10px; cursor:pointer; margin-left: 18px;}
    .submit-btn:hover { filter:brightness(0.92);}
    .cancel-btn:hover { filter:brightness(0.9);}
    @media (max-width:700px){ .container{padding: 7px;} .form-section{padding:9px;} }
    </style>
    <script>
    function showPreview(input, id) {
        const preview = document.getElementById(id);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; }
            reader.readAsDataURL(input.files[0]);
        } else { preview.style.display = 'none'; }
    }
    </script>
</head>
<body>
<!-- SIDEBAR, TOPBAR, PROFILE -->
<button onclick="toggleSidebar()" class="toggle-btn" style="cursor: pointer;"></button>
<div id="sidebar" class="sidebar">
    <div class="logo2" onclick="toggleSidebar()">
        <img src="../images/MTB-MAL_logo_side.png" alt="MTB-MAL Logo" />
    </div>
    <nav class="nav-links">
        <a href="welcome_educator.php"><span class="icon">🏠</span> Dashboard</a>
        <a href="educator_view_subjects.php"><span class="icon">📚</span> My Subjects</a>
        <a href="educator_manage_student_records.php"><span class="icon">👥</span> My Students</a>
        <a href="../login/about.php"><span class="icon">📖</span> About MTB-MAL</a>
    </nav>
</div>
<div id="sidebar-overlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>
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
            </div>
        </div>
    </div>
    <div class="second-bar">
        <span>Quest to Learn - Create Assessment</span>
        <div class="profile-container" onclick="toggleDropdown2()">
            <div class="profile-circle"></div>
            <span style="font-weight:bold; margin-left:8px; color:#343b43;"><?= htmlspecialchars($_SESSION['firstName'] ?? 'Educator') ?></span>
            <div id="dropdown-arrow2" class="dropdown-arrow2 down"></div>
            <div id="profile-dropdown-menu" class="profile-dropdown hidden">
                <a href="educator_view_profile.php"><div class="dropdown-item">View Profile</div></a>
                <a href="../login/logout.php"><div class="dropdown-item">Logout</div></a>
            </div>
        </div>
    </div>
    <!-- Form -->
    <div class="container">
    <div class="form-section">
        <?php if ($error): ?><div class="error-msg"><?= $error ?></div><?php endif; ?>
        <?php if ($success): ?>
            <div class="success-msg">Assessment successfully saved!</div>
            <script>
                setTimeout(function(){
                    window.location.href = "material_options.php?subjectRefNo=<?= urlencode($subjectRefNo) ?>";
                }, 1800);
            </script>
        <?php else: ?>
        <form method="post" enctype="multipart/form-data" autocomplete="off">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" maxlength="155" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" maxlength="400" required></textarea>
            </div>
            <div class="form-group">
                <label>Instructions</label>
                <textarea name="instruction" maxlength="300" required></textarea>
            </div>
            <div class="form-group">
                <label>Scoring Rubric</label>
                <textarea name="scoringRubric" maxlength="400" required></textarea>
            </div>
            <div class="form-group">
                <label>Difficulty Level</label>
                <select name="diffLevel" required>
                    <option value="Easy">Easy</option>
                    <option value="Intermediate">Intermediate</option>
                    <option value="Hard">Hard</option>
                </select>
            </div>
            <!-- Images -->
            <div class="form-group file-row">
                <label>Start:</label>
                <input type="file" name="start_image" accept="image/png,image/jpeg,image/jpg,image/gif" onchange="showPreview(this,'start_preview')" required>
                <br><img id="start_preview" style="display:none; margin-top:5px;" />
            </div>
            <div class="form-group file-row">
                <label>Finish:</label>
                <input type="file" name="finish_image" accept="image/png,image/jpeg,image/jpg,image/gif" onchange="showPreview(this,'finish_preview')" required>
                <br><img id="finish_preview" style="display:none; margin-top:5px;" />
            </div>
            <div class="form-group file-row">
                <label>Background Image (gif, png, jpg, jpeg):</label>
                <input type="file" name="bg_image" accept="image/png,image/jpeg,image/jpg,image/gif" onchange="showPreview(this,'bg_preview')" required>
                <br><img id="bg_preview" style="display:none; margin-top:5px;" />
            </div>
            <!-- 10 items -->
            <table class="qtable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Question</th>
                        <th>Correct Answer</th>
                    </tr>
                </thead>
                <tbody>
                <?php for ($i=1; $i<=10; $i++): ?>
                <tr>
                    <td><?= $i ?></td>
                    <td><input type="text" name="question[]" required maxlength="255" style="width:99%"></td>
                    <td><input type="text" name="answer[]" required maxlength="255" style="width:98%"></td>
                </tr>
                <?php endfor; ?>
                </tbody>
            </table>
            <div style="text-align:center;">
                <button type="submit" name="cancel" class="cancel-btn">Cancel</button>
                <button type="submit" name="save" class="submit-btn">Save</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
    </div>
    <footer class="footer">
        Mother Tongue-Based Multilingual Assessment and Learning System © 2025
    </footer>
</div>
<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const logoImg = document.querySelector('.logo2 img');
    sidebar.classList.toggle('visible');
    overlay.classList.toggle('visible');
    logoImg.src = '../images/MTB-MAL_logo_side.png';
}
function toggleDropdown1() {
    const arrow = document.getElementById('dropdown-arrow1');
    const menu = document.getElementById('lang-dropdown-menu');
    arrow.classList.toggle('down'); arrow.classList.toggle('up'); menu.classList.toggle('hidden');
}
function toggleDropdown2() {
    const arrow = document.getElementById('dropdown-arrow2');
    const menu = document.getElementById('profile-dropdown-menu');
    arrow.classList.toggle('down'); arrow.classList.toggle('up'); menu.classList.toggle('hidden');
}
</script>
</body>
</html>
