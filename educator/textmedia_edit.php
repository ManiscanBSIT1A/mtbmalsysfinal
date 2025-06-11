<?php
session_start();
include '../sql/db_connect.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['accType'] !== 'Educator') {
    // Only educators allowed, others redirected to their dashboards
    if (isset($_SESSION['accType'])) {
        switch ($_SESSION['accType']) {
            case 'School Administrator': header('Location: ../admin/welcome_admin.php'); exit();
            case 'Student': header('Location: ../student/welcome_student.php'); exit();
        }
    }
    header("Location: ../login/login.php");
    exit();
}

$educatorAccRefNo = $_SESSION['accRefNo'];
$schoolIdNo = $_SESSION['schoolIdNo'];
$educatorName = htmlspecialchars($_SESSION['firstName'] ?? 'Educator');
$subjectRefNo = $_GET['subjectRefNo'] ?? ($_POST['subjectRefNo'] ?? null);
$tempRefNo = $_GET['tempRefNo'] ?? ($_POST['tempRefNo'] ?? null);

$success = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    // Get all fields
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $diffLevel = $_POST['diffLevel'] ?? 'Easy';
    $topic = trim($_POST['topic'] ?? '');
    $contentText = trim($_POST['contentText'] ?? '');
    $chapterNo = intval($_POST['chapterNo'] ?? 1);
    $lmNo = intval($_POST['lmNo'] ?? 1);
    $lmType = "Learning Module";
    $lessonNo = intval($_POST['lessonNo'] ?? 1);
    $topicNo = intval($_POST['topicNo'] ?? 1);

    // Validate required
    if (!$title || !$description || !$topic || !$contentText || !$subjectRefNo || !$tempRefNo) {
        $error = "All fields are required.";
    } else {
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

        // 4. Insert images
        for ($i = 1; $i <= 4; $i++) {
            $field = 'mediaFile' . $i;
            if (!empty($_FILES[$field]['tmp_name'])) {
                $filename = $_FILES[$field]['name'];
                $filedata = file_get_contents($_FILES[$field]['tmp_name']);
                $stmt4 = $conn->prepare("INSERT INTO supportingmedia (learningMaterialRefNo, gameTempNo, modTempNo, accRefNo, filename, filedata) VALUES (?, NULL, ?, ?, ?, ?)");
                $null = NULL;
                $stmt4->bind_param("iiiss", $learningMaterialRefNo, $modTempNo, $educatorAccRefNo, $filename, $null);
                $stmt4->send_long_data(4, $filedata);
                $stmt4->execute();
                $stmt4->close();
            }
        }
        $success = true;
    }
}

if (isset($_POST['cancel'])) {
    header("Location: material_options.php?subjectRefNo=" . urlencode($subjectRefNo));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Text and Media Learning Module</title>
    <link rel="icon" type="image/png" href="../images/MTB-MAL_logo.png">
    <style>
        body { font-family: Arial, sans-serif; background-color: #FFE9D5; margin: 0; }
        .topbar { position: relative; background-color: #E6A56C; display: flex; justify-content: space-between; align-items: center; padding: 10px 20px; overflow: hidden; }
        .topbar::before { content: ""; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-image: url('../images/book_bg.jpg'); background-size: cover; background-position: center; opacity: 0.3; z-index: 0; }
        .topbar > * { position: relative; z-index: 1; }
        .logo-topbar { width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; font-weight: bold; cursor: pointer; }
        .logo-topbar img { height: 80px; width: auto; transition: all 0.3s ease; cursor: pointer; }
        .system-title { font-size: 18px; font-weight: bold; max-width: 300px; }
        .topbar .right { display: flex; align-items: center; gap: 10px; }
        .language-selector { cursor: pointer; border-radius: 4px; transition: background 0.2s ease; display: flex; align-items: center; gap: 10px; padding: 8px 14px; background-color: #fff3a6; border: 2px solid #d1b939; font-weight: bold; }
        .second-bar { background-color: #FFD2B3; padding: 15px 30px; display: flex; align-items: center; justify-content: space-between; font-size: 20px; font-weight: bold; border-bottom: 4px solid #0A3B52; }
        .profile-container { display: flex; align-items: center; gap: 10px; cursor: pointer; position: relative; }
        .profile-circle { width: 50px; height: 50px; background-color: #f0f0f0; border: 2px solid #d49972; border-radius: 50%; }
        .dropdown-arrow2 { width: 0; height: 0; border-left: 8px solid transparent; border-right: 8px solid transparent; margin-left: 6px;}
        .dropdown-arrow2.down { border-top: 10px solid #043344; }
        .dropdown-arrow2.up { border-bottom: 10px solid #043344; }
        .lang-dropdown, .profile-dropdown { position: absolute; background-color: white; border: 2px solid #6f6f6f; border-radius: 5px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); width: 200px; z-index: 1000; font-size: 15px; line-height: 1.5em; text-align: center;}
        .lang-dropdown { top: -3px; right: 40px;}
        .profile-dropdown { top: -20px; right: 30px;}
        .dropdown-item { padding: 10px; cursor: pointer; border-bottom: 1px solid #eee;}
        .dropdown-item:last-child { border-bottom: none;}
        .dropdown-item:hover { background-color: rgb(200, 199, 199);}
        .hidden { display: none;}
        .container { display: flex; flex-direction: column; align-items: center; padding: 32px 0; min-height: 90vh; }
        .form-section { background: #FFEF9D; border-radius: 18px; box-shadow: 0 8px 30px rgba(0,0,0,0.13); width: 600px; padding: 28px 32px 32px 32px; }
        h2 { color: #3c005a; font-weight: bold; text-align: center; }
        .form-group { margin-bottom: 17px; padding-right: 20px}
        label { font-weight: bold; color: #4B2E14; }
        input[type="text"], textarea, select { width: 100%; padding: 10px; border-radius: 7px; border: 1.7px solid #C68D38; font-size: 1.05em; margin-top: 5px; background: #FFF9ED; }
        textarea { min-height: 64px; }
        .images-label { font-weight: bold; color: #4B2E14; margin-top: 10px; }
        .file-input-row { margin-bottom: 8px; }
        .submit-btn, .cancel-btn { border: none; border-radius: 8px; font-size: 18px; font-weight: bold; padding: 10px 60px; margin-top: 18px; cursor: pointer; transition: filter .13s, transform .13s; }
        .submit-btn { background: #aaffaa; color: #184b2d; }
        .submit-btn:hover { filter: brightness(0.85);}
        .cancel-btn { background: #ffaaaa; color: #8d2525; margin-left: 12px;}
        .cancel-btn:hover { filter: brightness(0.85);}
        .footer { width: 100%; text-align: center; padding: 8px; font-weight: bold; font-size: 0.98em; margin-top: 38px; border-top: 2px solid #f7c69b; }
        .success-msg { background: #d6ffd6; color: #276328; font-weight: bold; border: 1.3px solid #86c486; border-radius: 6px; padding: 10px 20px; text-align: center; margin-bottom: 22px; margin-top: -15px; }
        .error-msg { background: #ffdddd; color: #ab2323; border: 1.3px solid #f7a9a9; border-radius: 6px; padding: 10px 20px; text-align: center; margin-bottom: 22px; margin-top: -15px; }
        @media (max-width: 600px) { .form-section { width: 97vw; padding: 12px 7vw; } }
        .sidebar { position: fixed; top: 0; left: 0; width: 350px; height: 100%; background: #E6A56C; box-shadow: 2px 0 5px rgba(0,0,0,0.2); z-index: 1000; transform: translateX(-100%); transition: transform 0.3s ease; overflow-y: auto; cursor: pointer;}
        .sidebar.visible { transform: translateX(0);}
        .logo2 { position: relative; }
        .logo2::before { content: ""; position: absolute; top: 0; left: 0; right: 0; bottom: 0; height: 100px; background-image: url('../images/book_bg.jpg'); background-size: cover; background-position: center; opacity: 0.3; z-index: 0;}
        .logo2 img { position: relative; z-index: 1; height: auto; width: 250px; display: block; justify-content: center; align-items: center; top: 12px;}
        .sidebar-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 999; display: none; opacity: 0; pointer-events: none; transition: opacity 0.3s ease;}
        .sidebar-overlay.visible { display: block; opacity: 1; pointer-events: all;}
        .nav-links { display: flex; flex-direction: column; margin-top: 15px;}
        .nav-links a { display: flex; align-items: center; gap: 10px; padding: 15px 10px; width: 320px; margin-left: 5px; background: #FFEF9D; border: 2px solid #C68D38; border-radius: 5px; color: #000; text-decoration: none; font-size: 1.1rem; transition: background 0.2s;}
        .nav-links a:hover { transform: scale(1.01); filter: brightness(0.80);}
        .nav-links a:active { transform: scale(0.95); filter: brightness(0.70);}
    </style>
</head>
<body>
<!-- Sidebar -->
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
            <div class="language-selector">
                <div class="language">🌐 English</div>
            </div>
        </div>
    </div>
    <!-- Second Navigation Bar -->
    <div class="second-bar">
        <span>Add New Learning Module: Text and Media</span>
        <div class="profile-container" onclick="toggleDropdown2()">
            <div class="profile-circle"></div>
            <span style="font-weight:bold; margin-left:8px; color:#343b43;"><?= $educatorName ?></span>
            <div id="dropdown-arrow2" class="dropdown-arrow2 down"></div>
            <div id="profile-dropdown-menu" class="profile-dropdown hidden">
                <a href="educator_view_profile.php"><div class="dropdown-item">View Profile</div></a>
                <a href="../login/logout.php"><div class="dropdown-item">Logout</div></a>
            </div>
        </div>
    </div>

    <div class="container">
    <div class="form-section">
        <?php if ($success): ?>
            <div class="success-msg">Learning Material successfully uploaded!</div>
            <script>
                setTimeout(function(){ window.location.href = "material_options.php?subjectRefNo=<?= htmlspecialchars($subjectRefNo) ?>"; }, 1500);
            </script>
        <?php elseif ($error): ?>
            <div class="error-msg"><?= $error ?></div>
        <?php endif; ?>
        <?php if (!$success): ?>
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
                <label>Topic</label>
                <input type="text" name="topic" maxlength="155" required>
            </div>
            <div class="form-group">
                <label>Content Text</label>
                <textarea name="contentText" required></textarea>
            </div>
            <div class="form-group">
                <label>Difficulty Level</label>
                <select name="diffLevel">
                    <option value="Easy">Easy</option>
                    <option value="Intermediate">Intermediate</option>
                    <option value="Hard">Hard</option>
                </select>
            </div>
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
                <label class="images-label">Upload Up To 4 Images (visual aids):</label>
                <div class="file-input-row"><input type="file" name="mediaFile1" accept="image/*"></div>
                <div class="file-input-row"><input type="file" name="mediaFile2" accept="image/*"></div>
                <div class="file-input-row"><input type="file" name="mediaFile3" accept="image/*"></div>
                <div class="file-input-row"><input type="file" name="mediaFile4" accept="image/*"></div>
            </div>
            <input type="hidden" name="subjectRefNo" value="<?= htmlspecialchars($subjectRefNo) ?>">
            <input type="hidden" name="tempRefNo" value="<?= htmlspecialchars($tempRefNo) ?>">
            <button type="submit" name="save" class="submit-btn">Save</button>
            <button type="submit" name="cancel" class="cancel-btn">Cancel</button>
        </form>
        <?php endif; ?>
    </div>
    </div>
</div>
<footer class="footer">
    Mother Tongue-Based Multilingual Assessment and Learning System © 2025
</footer>
<script>
    function toggleDropdown2() {
        const arrow = document.getElementById('dropdown-arrow2');
        const menu = document.getElementById('profile-dropdown-menu');
        arrow.classList.toggle('down');
        arrow.classList.toggle('up');
        menu.classList.toggle('hidden');
    }
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const logoImg = document.querySelector('.logo2 img');
        sidebar.classList.toggle('visible');
        overlay.classList.toggle('visible');
        logoImg.src = '../images/MTB-MAL_logo_side.png';
    }
</script>
</body>
</html>
