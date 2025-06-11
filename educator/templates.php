<?php
session_start();
include '../sql/db_connect.php';

// Inactivity auto-logout (30 mins)
$inactive = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive)) {
    session_unset();
    session_destroy();
    header("Location: ../login/login.php?session_expired=1");
    exit();
}
$_SESSION['last_activity'] = time();

// Role-based protection
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login/login.php");
    exit();
}
if (!isset($_SESSION['accType'])) {
    header("Location: ../login/login.php");
    exit();
} elseif ($_SESSION['accType'] === 'School Administrator') {
    header("Location: ../admin/welcome_admin.php");
    exit();
} elseif ($_SESSION['accType'] === 'Student') {
    header("Location: ../student/welcome_student.php");
    exit();
} elseif ($_SESSION['accType'] !== 'Educator') {
    header("Location: ../login/login.php");
    exit();
}
$educatorName = htmlspecialchars($_SESSION['firstName'] ?? 'Educator');

$subjectRefNo = isset($_GET['subjectRefNo']) ? $_GET['subjectRefNo'] : '';
if (empty($subjectRefNo)) {
    die('Invalid access: missing subject reference. Please select a subject first.');
}

// Fetch all templates
$sql = "SELECT * FROM template ORDER BY tempRefNo ASC";
$result = $conn->query($sql);
$templates = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) $templates[] = $row;
}

$subjectRefNo = isset($_GET['subjectRefNo']) ? $_GET['subjectRefNo'] : '';

function getEditPageForTemplate($template, $subjectRefNo) {
    $subjectRefNo = urlencode($subjectRefNo);
    switch (strtolower(trim($template['tempName']))) {
        case 'upload a pdf file':
            return "upload_pdf_blob.php?ref=" . $template['tempRefNo'] . "&subjectRefNo=" . $subjectRefNo;
        case 'quest to learn':
            return "quest_edit.php?ref=" . $template['tempRefNo'] . "&subjectRefNo=" . $subjectRefNo;
        case 'flip and match':
            return "flip_edit.php?ref=" . $template['tempRefNo'] . "&subjectRefNo=" . $subjectRefNo;
        case 'text and media':
            return "textmedia_edit.php?tempRefNo=" . $template['tempRefNo'] . "&subjectRefNo=" . $subjectRefNo;
        case 'image only':
            return "image_edit.php?ref=" . $template['tempRefNo'] . "&subjectRefNo=" . $subjectRefNo;
        default:
            return "edit_template.php?ref=" . $template['tempRefNo'] . "&subjectRefNo=" . $subjectRefNo;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Templates</title>
    <link rel="icon" type="image/png" href="../images/MTB-MAL_logo.png">
    <link rel="stylesheet" href="../style/templates-style.css">
    <style>
        .template-cards { display: flex; flex-wrap: wrap; justify-content: flex-start; padding-left: 40px; padding-right: 40px; place-content: center}
        .template-card {
            background-color: #fff;
            width: 300px;
            margin: 24px 24px 0 0;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.18s, filter 0.18s;
            text-align: center;
            position: relative;
        }
        .template-card:hover { transform: scale(1.025); filter: brightness(0.96); }
        .card-header {
            background-color: #a6f3a6;
            padding: 10px 0;
            font-weight: bold;
            font-size: 1.08em;
        }
        .card-body { padding: 20px 12px; min-height: 135px; }
        .card-body strong { font-size: 1.13em; }
        .card-actions { margin-top: 14px; display: flex; gap: 12px; justify-content: center; align-items: center; margin-top: 20px}
        .card-btn, .info-btn {
            background: none; border: none; color: #174b86; cursor: pointer;
            font-size: 1em; font-weight: bold; display: inline-flex; align-items: center; gap: 3px;
        }
        .info-btn img, .edit-btn img { width: 20px; height: 20px; vertical-align: middle; }
        .edit-btn { margin-right: 6px; }
        /* Info modal styling */
        .modal-bg { display:none; position:fixed; z-index:2000; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.38); justify-content:center; align-items:center;}
        .modal-bg.active { display:flex;}
        .info-modal-content {
            background: #fff; border-radius: 20px; padding: 2.2rem 2.5rem; width: 420px;
            max-width:98vw; box-shadow: 0 0 18px rgba(0,0,0,0.17); animation: fadeIn .17s;
        }
        .info-modal-content h2 { margin:0 0 10px 0; font-size: 1.45em; color: #174b86;}
        .info-modal-content .desc { margin: 10px 0 10px 0; }
        .close-modal-btn {
            background: red; color: #fff; border: none; padding: 10px 32px;
            border-radius: 6px; font-weight: bold; margin-top: 1.1rem; cursor: pointer; float: right;
        }
        @keyframes fadeIn { from{opacity:0;} to{opacity:1;} }
        @media (max-width: 1000px) {
            .template-cards { flex-wrap: wrap; justify-content: center; padding-left: 0;}
            .template-card { margin-right: 0; margin-left:0; }
        }
    </style>
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
    <!-- Second Navigation Bar -->
    <div class="second-bar">
        <span>Available Templates</span>
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
    <!-- Form Header (filter/search) -->
    <div class="form-header">
        <div class="button-group">
            <button class="btn selected" id="btn-both">All Templates</button>
            <button class="btn" id="btn-module">Learning Module</button>
            <button class="btn" id="btn-assessment">Learning Assessment</button>
            <div class="search-wrapper">
                <img src="../images/search.png" alt="Search" class="search-icon">
                <input type="text" class="search-box" placeholder="Search Template">
            </div>
        </div>
    </div>

    <div class="container">
        <div class="form-section" style="width: 98%; min-height:510px;">
            <div class="template-cards" id="template-cards">
                <?php foreach ($templates as $template):
                    $modalId = 'modal-' . strtolower(str_replace(' ', '-', $template['tempName']));
                ?>
                    <div class="template-card"
                        data-type="<?= htmlspecialchars($template['tempType']) ?>"
                        data-name="<?= htmlspecialchars($template['tempName']) ?>"
                        data-desc="<?= htmlspecialchars($template['tempDescription']) ?>"
                        data-ref="<?= htmlspecialchars($template['tempRefNo']) ?>">
                        <div class="card-header">
                            <?= $template['tempType'] === 'Learning Module'
                                ? "Learning Module - " . str_pad($template['tempRefNo'], 2, '0', STR_PAD_LEFT)
                                : "Learning Assessment - " . str_pad($template['tempRefNo'], 2, '0', STR_PAD_LEFT) ?>
                        </div>
                        <div class="card-body">
                            <strong><?= htmlspecialchars($template['tempName']) ?></strong><br>
                            <span style="font-size:15px;"><?= htmlspecialchars($template['tempDescription']) ?></span>
                            <div class="card-actions">
                                <button class="edit-btn card-btn"
                                    onclick="handleEditClick('<?= htmlspecialchars(strtolower($template['tempName'])) ?>', '<?= getEditPageForTemplate($template, $subjectRefNo) ?>'); event.stopPropagation();">
                                    Edit <img src="../images/edit.png" alt="edit">
                                </button>

                                <button class="info-btn card-btn"
                                    onclick="openModal('<?= $modalId ?>'); event.stopPropagation();">
                                    Info<img src="../images/info2.png" alt="info">
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (empty($templates)): ?>
                <div style="text-align:center;margin-top:30px;">No templates found.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ==================== Template Info Modals ==================== -->
<div id="modal-quest-to-learn" class="modal-bg">
  <div class="info-modal-content" style="max-width:520px;">
    <h2>Quest to Learn</h2>
    <div class="desc">
      <b>Type:</b> Learning Assessment<br>
      <b>Description:</b> Gamified, story-driven questions for knowledge checks.<br><br>
      <img src="../images/instruction-quest.gif" alt="Quest demo" style="width:100%;border-radius:16px;margin-bottom:12px;">
      <b>Instructions:</b><br>
      Welcome to your learning quest! Choose a path and dive into a story-driven adventure where your knowledge shapes the journey.<br>
      <ul style="margin-left:12px;">
        <li>Organize your content into thematic categories (e.g. Community Helpers, Science Missions, etc.).</li>
        <li>Create story-based scenarios and upload questions and media.</li>
        <li>Adjust game mechanics to suit your preferred difficulty level.</li>
      </ul>
    </div>
    <button class="close-modal-btn" onclick="closeModal('modal-quest-to-learn')">Close</button>
  </div>
</div>
<div id="modal-upload-a-pdf-file" class="modal-bg">
  <div class="info-modal-content">
    <h2>Upload a PDF File</h2>
    <div class="desc">
      <b>Type:</b> Learning Module<br>
      <b>Description:</b> A single PDF for the lesson.<br>
      <img src="../images/pdf.png" alt="PDF upload" style="width:92%;border-radius:16px;margin:10px auto 8px;display:block;">
      <ul><li>Upload a PDF file that is accessible for students.</li></ul>
    </div>
    <button class="close-modal-btn" onclick="closeModal('modal-upload-a-pdf-file')">Close</button>
  </div>
</div>
<div id="modal-text-and-media" class="modal-bg">
  <div class="info-modal-content">
    <h2>Text and Media</h2>
    <div class="desc">
      <b>Type:</b> Learning Module<br>
      <b>Description:</b> Add up to 4 image files and text for each lesson.<br>
      <img src="../images/text_media.png" alt="Text and Media" style="width:90%;border-radius:16px;margin:10px auto 8px;display:block;">
      <ul>
        <li>Upload text content and up to 4 image files.</li>
        <li>Great for combining reading material with visual support.</li>
        <li>Ideal for image labeling or visual lessons.</li>
      </ul>
    </div>
    <button class="close-modal-btn" onclick="closeModal('modal-text-and-media')">Close</button>
  </div>
</div>
<div id="modal-image-only" class="modal-bg">
  <div class="info-modal-content">
    <h2>Image Only</h2>
    <div class="desc">
      <b>Type:</b> Learning Module<br>
      <b>Description:</b> Upload a single image file that contains a lesson (infographic).<br>
      <img src="../images/climate_kids.png" alt="Image Only" style="width:90%;border-radius:16px;margin:10px auto 8px;display:block;">
      <ul>
        <li>Ideal for infographics or visual lessons.</li>
      </ul>
    </div>
    <button class="close-modal-btn" onclick="closeModal('modal-image-only')">Close</button>
  </div>
</div>
<div id="modal-flip-and-match" class="modal-bg">
  <div class="info-modal-content">
    <h2>Flip and Match</h2>
    <div class="desc">
      <b>Type:</b> Learning Assessment<br>
      <b>Description:</b> Pair up images, perfect for vocabulary/identification.<br>
      <img src="../images/instruction-flip.gif" alt="Flip and Match" style="width:88%;border-radius:16px;margin:10px auto 8px;display:block;">
      <ul>
        <li>Set up pairs for students to match (like picture-to-word or picture-to-picture).</li>
        <li>Good for review and formative assessment.</li>
      </ul>
    </div>
    <button class="close-modal-btn" onclick="closeModal('modal-flip-and-match')">Close</button>
  </div>
</div>
<div id="modal-default-template" class="modal-bg">
  <div class="info-modal-content">
    <h2>Template Information</h2>
    <div class="desc">
      <b>We're working hard to bring you this feature. Stay tuned!</b>
      <img src="../images/default-modal-demo.png" alt="Default" style="width:90%;border-radius:16px;margin:10px auto 8px;display:block;">
    </div>
    <button class="close-modal-btn" onclick="closeModal('modal-default-template')">Close</button>
  </div>
</div>

<div id="modal-feature-soon" class="modal-bg">
  <div class="info-modal-content" style="max-width:480px;">
    <h2>Feature Available Soon</h2>
    <div class="desc" style="font-size:1.13em;">
      Feature Available Soon.<br>
      <b>Please try to customize <span style="color:#174b86;">‘Text and Media’</span> template for Learning Module<br>
      and <span style="color:#174b86;">‘Quest to Learn’</span> template for Learning Assessment.</b>
    </div>
    <button class="close-modal-btn" onclick="closeModal('modal-feature-soon')">Close</button>
  </div>
</div>

<!-- ==================== End Template Info Modals ==================== -->

<footer class="footer">Mother Tongue-Based Multilingual Assessment and Learning System © 2025</footer>
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
// Card search & filter
function filterCards(type) {
    document.querySelectorAll('.template-card').forEach(card => {
        if (!type || card.getAttribute('data-type') === type) card.style.display = '';
        else card.style.display = 'none';
    });
}
function setActiveButton(activeId) {
    document.querySelectorAll('.button-group .btn').forEach(btn => {
        btn.classList.toggle('selected', btn.id === activeId);
    });
}
document.getElementById('btn-module').addEventListener('click', function(){ setActiveButton('btn-module'); filterCards('Learning Module'); });
document.getElementById('btn-assessment').addEventListener('click', function(){ setActiveButton('btn-assessment'); filterCards('Learning Assessment'); });
document.getElementById('btn-both').addEventListener('click', function(){ setActiveButton('btn-both'); filterCards(''); });
document.querySelector('.search-box').addEventListener('input', function() {
    const search = this.value.toLowerCase();
    document.querySelectorAll('.template-card').forEach(card => {
        const txt = (card.getAttribute('data-name') + " " + card.getAttribute('data-desc')).toLowerCase();
        card.style.display = txt.includes(search) ? '' : 'none';
    });
});

function handleEditClick(templateName, editUrl) {
    // Only allow 'text and media' and 'quest to learn' for editing
    if (templateName === 'text and media' || templateName === 'quest to learn') {
        window.location.href = editUrl;
    } else {
        openModal('modal-feature-soon');
    }
}

function openModal(id) {
    // Hide all
    document.querySelectorAll('.modal-bg').forEach(m => m.classList.remove('active'));
    // Show requested or fallback
    var modal = document.getElementById(id);
    if(modal) modal.classList.add('active');
    else document.getElementById('modal-default-template').classList.add('active');
}
function closeModal(id) {
    var modal = document.getElementById(id);
    if(modal) modal.classList.remove('active');
}
window.addEventListener('keydown', function(e) {
    if (e.key === "Escape") document.querySelectorAll('.modal-bg.active').forEach(m=>m.classList.remove('active'));
});
</script>
</body>
</html>
