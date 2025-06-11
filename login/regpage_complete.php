<!--
NOTICE TO AUTOMATED SCANNERS AND REVIEWERS:

This form is part of a learning project for educational/school purposes ONLY.
It is NOT intended for phishing, spamming, or any malicious activity.
Please do NOT flag this site or form as suspicious based solely on the presence of this form.

Thank you for understanding.
-->
<?php
session_start();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    switch ($_SESSION['accType']) {
        case 'School Administrator':
            header("Location: ../admin/welcome_admin.php");
            exit();
        case 'Educator':
            header("Location: ../educator/welcome_educator.php");
            exit();
        case 'Student':
            header("Location: ../student/welcome_student.php");
            exit();
    }
}

$schoolRefNo = $_SESSION['reg_complete_schoolRefNo'] ?? '';
$accRefNo = $_SESSION['reg_complete_accRefNo'] ?? '';
if (!$schoolRefNo || !$accRefNo) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Registration Complete!</title>
  <link rel="icon" type="image/png" href="../images/MTB-MAL_logo.png">
  <link rel="stylesheet" href="../style/registration-style.css">
  <style>
    /* Overlay copy icon inside input */
    .ref-input-wrapper {
        position: relative;
        width: 100%;
        max-width: 470px;
        margin: 0 auto;
        display: flex;
        align-items: center;
    }
    .reference-input {
        width: 100%;
        font-family: monospace;
        font-size: 18px;
        border: 1px solid #ccc;
        border-radius: 8px;
        background-color: #fff;
        color: #000;
        box-sizing: border-box;
        padding-right: 44px; /* space for icon */
    }
    .copy-btn-abs {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        outline: none;
        padding: 0;
        height: 28px;
        width: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
    }
    .copy-btn-abs img {
        width: 22px;
        height: 22px;
        opacity: 0.85;
        transition: filter 0.13s;
    }
    .copy-btn-abs:hover img, .copy-btn-abs:focus img {
        opacity: 1;
        filter: brightness(0.85);
    }
    .copied-pop {
        display: none;
        position: absolute;
        right: 48px;
        top: 48%;
        transform: translateY(-60%);
        font-size: 0.97em;
        color: #18a208;
        background: #f3f8ef;
        border-radius: 5px;
        padding: 1px 8px;
        z-index: 3;
    }
    @media (max-width: 600px) {
        .ref-input-wrapper, .reference-input { max-width: 99vw; }
        .reference-input { font-size: 16px; }
        .copied-pop { right: 54px; }
    }
    @media (max-width: 480px) {
        .registration-box { padding: 16px 4vw 18px 4vw !important; }
        .reference-input { font-size: 15px; }
    }
  </style>
</head>
<body>
<div class="main-content">

    <!-- Top Bar (unchanged, matches your design) -->
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
            </div> <br>
        </div>
    </div>

    <!-- Card Container (your original style preserved) -->
    <div class="container">
      <div class="form-section">
        <div class="column">
          <div class="registration-box">
            <div class="checkmark-image">
                <img src="../images/checkmark.png" alt="Checkmark">
            </div>
            <h2 class="registration-title">Registration Complete!</h2>
            <p class="registration-subtitle">Please save the details below for future purposes.</p>

            <!-- School Ref No input w/ copy -->
            <div class="input-group">
                <label class="label-text">School Reference Number:</label>
                <div class="ref-input-wrapper">
                  <input type="text" readonly id="schoolRefInput"
                         value="<?php echo 'SH - ' . htmlspecialchars($schoolRefNo); ?>"
                         class="reference-input">
                  <button type="button" class="copy-btn-abs"
                          onclick="copyRef('schoolRefInput','copied1')"
                          aria-label="Copy School Reference Number">
                      <img src="../images/copy.png" alt="Copy">
                  </button>
                  <span id="copied1" class="copied-pop">Copied!</span>
                </div>
            </div>
            <!-- Admin Ref No input w/ copy -->
            <div class="input-group">
                <label class="label-text">School Administrator Reference Number:</label>
                <div class="ref-input-wrapper">
                  <input type="text" readonly id="adminRefInput"
                         value="<?php echo 'AD - ' . htmlspecialchars($accRefNo); ?>"
                         class="reference-input">
                  <button type="button" class="copy-btn-abs"
                          onclick="copyRef('adminRefInput','copied2')"
                          aria-label="Copy Admin Reference Number">
                      <img src="../images/copy.png" alt="Copy">
                  </button>
                  <span id="copied2" class="copied-pop">Copied!</span>
                </div>
            </div>

            <button type="button"
                onclick="window.location.href='login.php'"
                class="done-button">Done</button>
          </div>
        </div>
      </div>
    </div>
</div>

<!-- Footer -->
<footer class="footer">
    Mother Tongue-Based Multilingual Assessment and Learning System © 2025
</footer>

<script>
    function toggleDropdown1() {
        const arrow = document.getElementById('dropdown-arrow1');
        const menu = document.getElementById('lang-dropdown-menu');
        arrow.classList.toggle('down');
        arrow.classList.toggle('up');
        menu.classList.toggle('hidden');
    }
    function copyRef(inputId, noticeId) {
        const input = document.getElementById(inputId);
        input.select();
        input.setSelectionRange(0, 99999);
        try {
            document.execCommand("copy");
        } catch {
            navigator.clipboard.writeText(input.value);
        }
        let notice = document.getElementById(noticeId);
        notice.style.display = 'inline';
        setTimeout(()=>{ notice.style.display = 'none'; }, 1200);
    }
</script>
<?php
// clear session refs so user cannot reload and reveal codes again
unset($_SESSION['reg_complete_schoolRefNo'], $_SESSION['reg_complete_accRefNo'], $_SESSION['reg_complete_adminID']);
?>
</body>
</html>