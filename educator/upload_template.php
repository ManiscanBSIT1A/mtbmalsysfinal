<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Learning Materials</title>
    <link rel="icon" type="image/png" href="images/MTB-MAL_logo.png">
    <link rel="stylesheet" href="style/upload_template-style.css">
</head>

<body>

<!-- Sidebar Toggle Button (you can place this at top left corner of your app) -->
<button onclick="toggleSidebar()" class="toggle-btn" style="cursor: pointer;"></button>

<!-- Sidebar Toggle Button -->
<button onclick="toggleSidebar()" class="toggle-btn" style="cursor: pointer;"></button>

<!-- Sidebar -->
<div id="sidebar" class="sidebar">
    <div class="logo2" onclick="toggleSidebar()">
        <img src="images/MTB-MAL_logo_side.png" alt="MTB-MAL Logo" />
    </div>
    <nav class="nav-links">
        <a href="/mtbmalsysfinal/educators/dashboard/dashboard_educators.php"><span class="icon">
          🏠 </span> Dashboard </a>
        <a href="/mtbmalsysfinal/educators/view-subjects/educator_subject-view.php"><span class="icon">
          📚 </span> View Subjects </a>
        <a href="/mtbmalsysfinal/educators/view-subjects/educator_manage-student-records.php"><span class="icon">
          👤 </span> Manage Student Records </a>
        <a href="/mtbmalsysfinal/educators/manage-subjects/subject-learning-materials/upload_template.php"><span class="icon">
          📤 </span> Upload New Subject Materials </a>
    </nav>
</div>

<!-- Overlay -->
<div id="sidebar-overlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>

<div class="main-content">
        <!-- Top Bar -->
    <div class="topbar">
        <div class="left">
            <div class="logo-topbar" onclick="toggleSidebar()">
                <img src="images/MTB-MAL_logo-alt.png" alt="MTB-MAL Logo">
            </div>
            <div class="system-title">
                Mother Tongue-Based Multilingual Assessment and Learning System
            </div>
        </div>
        <div class="right">
            <div class="button">Cookies Notice</div>
            <div class="language-selector" onclick="toggleDropdown1()">
                <div class="language">🌐 English</div> 
                <div id="dropdown-arrow1" class="dropdown-arrow1 down"></div>

                <!-- Use a unique class or ID here -->
                <div id="lang-dropdown-menu" class="lang-dropdown hidden">
                    <div class="dropdown-item">Feature Available Soon</div>
                </div>
            </div> 
        </div>
    </div>

    <!-- Second Navigation Bar -->
    <div class="second-bar">
    <span>Upload New Learning Materials</span>
        <div class="profile-container" onclick="toggleDropdown2()">
            <div class="profile-circle"></div>
            <div id="dropdown-arrow2" class="dropdown-arrow2 down"></div>

            <!-- Use a unique class or ID here -->
            <div id="profile-dropdown-menu" class="profile-dropdown hidden">
                <div class="dropdown-item">Settings</div>
                <a href="/mtbmalsysfinal/auth/login.php">
                  <div class="dropdown-item">Logout</div>
                </a>
            </div>
        </div>
    </div>

    <!-- Form Header -->
     <div class="form-header">
        <div class="button-group">
            <button class="btn" id="btn-module">Learning Module</button>
            <button class="btn" id="btn-assessment">Learning Assessment</button>
            <button class="btn selected" id="btn-both">Display Both</button>
            <div class="search-wrapper">
                <img src="images/search.png" alt="Search" class="search-icon">
                <input type="text" class="search-box" placeholder="Search Template">
            </div>
        </div>
     </div>

    <!-- Main Form Section -->
    <div class="container">
        <div class="form-section">
        <div class="assessments">
            <?php
                $assessments = [
                    ["title" => "Assessment - 01", "name" => "Quest to Learn", "items" => "10 items", "type" => "Answer the Questions"],
                    ["title" => "Assessment - 02", "name" => "Memory Trace", "items" => "15 items", "type" => "True or False"],
                    ["title" => "Assessment - 03", "name" => "Flip and Match", "items" => "15 items", "type" => "Pair Up Images"]
                ];

                $modules = [
                    ["title" => "Module - 01", "name" => "Text and Media", "type" => "Max: 4 image files and 1 video file"],
                    ["title" => "Module - 02", "name" => "Image Only", "items" => "15 items", "type" => "Pair Up Images"],
                    ["title" => "Module - 03", "name" => "Upload a PDF file", "type" => "Upload a pdf file accessible for students."]
                ];

                foreach ($assessments as $a) {
                    $cardId = str_replace(' ', '-', strtolower($a['name'])); // create ID like flip-and-match
                    echo "<div class='card' id='{$cardId}'>
                        <div class='card-header'>" . htmlspecialchars($a['title']) . "</div>
                        <div class='card-body'>
                            <strong>" . htmlspecialchars($a['name']) . "</strong><br>
                            " . (isset($a['items']) ? htmlspecialchars($a['items']) . "<br>" : "") . "
                            Text and Images<br>
                            " . htmlspecialchars($a['type']) . "
                            <div class='edit'>Edit <span title='Edit Info'><img src='images/info.png' alt='info logo'></span></div>
                        </div>
                    </div>";
                }

                foreach ($modules as $m) {
                    $cardId = str_replace(' ', '-', strtolower($m['name'])); // same logic for ID
                    echo "<div class='card' id='{$cardId}'>
                        <div class='card-header'>" . htmlspecialchars($m['title']) . "</div>
                        <div class='card-body'>
                            <strong>" . htmlspecialchars($m['name']) . "</strong><br>
                            " . (isset($m['items']) ? htmlspecialchars($m['items']) . "<br>" : "") . "
                            " . htmlspecialchars($m['type']) . "
                            <div class='edit'>Edit <span title='Edit Info'><img src='images/info.png' alt='info logo'></span></div>
                        </div>
                    </div>";
                }

            ?>
        </div>
        </div>
    </div>
</div>

    <!-- Upload PDF Modal -->
    <div id="Upload-PDF-File" class="modal" style="display: none;">
        <div class="modal-content">
            <h1>Upload a PDF File</h1>
            <hr style="width: 70%; height: 2px; background-color: black; border: none; margin: 1rem auto;">

            <form id="pdfUploadForm" enctype="multipart/form-data" style="text-align: center;">
                <p>Select a PDF file to upload:</p>

                <!-- Custom File Input -->
                <label for="pdfFile" class="custom-file-label">Choose File</label>
                <input type="file" id="pdfFile" name="pdfFile" accept="application/pdf" required style="display: none;">

                <!-- Display selected file name -->
                <div id="fileNameDisplay" style="margin-top:20px; margin-bottom: 20px; font-size: 24px; color: #333;"></div>

                <div style="text-align: center; display: flex; justify-content: center; gap: 1rem;">
                    <button class="modal-close3" type="button" onclick="closeModal('Upload-PDF-File')" style="position: relative; right: -150px;">Close</button>
                    <button class="modal-btn2" type="submit" style="position: relative; right: 150px;">Upload</button>
                </div>
            </form>

        </div>
    </div>

<!-- Flip and Match Modal -->
<div id="Flip-and-Match" class="modal" style="display: none;">
    <div class="modal-content">
        <h1>Template Info</h1>

        <hr style="width: 70%; height: 2px; background-color: black; border: none; margin: 1rem auto; margin-bottom:20px;">
        <!-- Info Bar Table -->
        <table class="info-bar-table">
            <tr>
            <td>Template Number</td>
            <td>Assessment - 03</td>
            </tr>
        </table>

        <hr style="margin: 1rem auto; height: 1px; background-color: black; border: none; width: 100%;">

        <!-- Container for Instructions and Video Clip of Flip and Match -->
        <div style="margin-top: 20px; max-width: 1500px; margin-left: auto; margin-right: auto;">

            <h2 style="text-align: center;">Instructions</h2>

            <!-- Player Instructions -->
            <p style="text-align: center; max-width: 1500px; margin: 0 auto 20px; line-height: 2em;">
                Match each picture with the correct word. Click a card to flip it over and reveal what’s underneath.
                <br>Try to match all pairs before the timer runs out!
            </p>    

            <!-- Educator Setup Instructions -->
            <h3 style="text-align: center;">For Educators: How to Set Up</h3>
            <ol style="padding-left: 2rem; font-size: 1rem; text-align: left; line-height: 2em; max-width: 1000px; margin-left:50px;">
                <li>Prepare a set of image files and corresponding word labels. Ensure each image has a matching word.</li>
                <li>Upload the images and words into the system's designated upload panel or form <b>(e.g., drag-and-drop or file select)</b>.</li>
                <li>Each pair <b>(image + word)</b> will be randomized and turned into a clickable card.</li>
                <li>Set the game timer in the settings menu. You can choose a fixed duration <b>(e.g., 30s, 60s, or 90s)</b> depending on the level of difficulty.</li>
                <li>Once configured, click “Start Game” to preview and test your card pairs and timing.</li>
            </ol>

                <div style="position: relative; padding-top: 0%; padding-bottom: 48%; height: 0; overflow: hidden; max-width: 90%; margin: 0 auto;">
                    <img src="images/instruction-flip.gif" alt="Instruction Flip Animation" style="width: 900px; height: auto; display: block; margin: 0 auto;">
                </div>

        </div>
                <div class="modal-buttons-row" style="text-align: center; margin-top: 0; display: flex; justify-content: center; gap: 1rem;">
                    <button class="modal-close" onclick="closeModal('Flip-and-Match')">Close</button>
                    <button class="modal-btn2" onclick="openModal('UploadModal')">Next</button>
                </div>
    </div>
</div>

<!-- Feature Available Soon Modal -->
<div id="Feature-Soon" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
                                            background: rgba(0, 0, 0, 0.6); justify-content: center; align-items: center; z-index: 1000;">

    <div style="background-color:#D7FFDF; padding: 2rem; border-radius: 10px; text-align: center; max-width: 600px; width: 90%; position: relative;">
        <h2 style="margin-bottom: 1rem;">Feature Coming Soon!</h2>
        <p style="margin-bottom: 1.5rem">This feature is currently under development. Stay tuned for updates!</p>
        <button class="modal-close3" onclick="closeModal('Feature-Soon')">Close</button>
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
        logoImg.src = 'images/MTB-MAL_logo_side.png';
    }

    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.style.display = 'flex';
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Button Group Toggle Logic
        const buttons = document.querySelectorAll('.button-group .btn');

        function setActiveButton(activeId) {
            buttons.forEach(btn => {
                btn.classList.toggle('selected', btn.id === activeId);
            });
        }

        document.getElementById('btn-module').addEventListener('click', () => {
            setActiveButton('btn-module');
            document.querySelectorAll('.card').forEach(card => {
                const title = card.querySelector('.card-header').textContent.toLowerCase();
                card.style.display = title.includes('module') ? 'block' : 'none';
            });
        });

        document.getElementById('btn-assessment').addEventListener('click', () => {
            setActiveButton('btn-assessment');
            document.querySelectorAll('.card').forEach(card => {
                const title = card.querySelector('.card-header').textContent.toLowerCase();
                card.style.display = title.includes('assessment') ? 'block' : 'none';
            });
        });

        document.getElementById('btn-both').addEventListener('click', () => {
            setActiveButton('btn-both');
            document.querySelectorAll('.card').forEach(card => {
                card.style.display = 'block';
            });
        });

        // Unified Click Logic for All Cards
        document.querySelectorAll('.card').forEach(card => {
            card.style.cursor = 'pointer';
            card.addEventListener('click', () => {
                const cardId = card.id;
                if (cardId === 'flip-and-match') {
                    openModal('Flip-and-Match');
                } else if (cardId === 'upload-a-pdf-file') {
                    openModal('Upload-PDF-File');
                } else {
                    openModal('Feature-Soon');
                }
            });
        });

        // PDF Upload Logic
        const uploadForm = document.getElementById('pdfUploadForm');
        if (uploadForm) {
            uploadForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const fileInput = this.querySelector('input[type="file"]');
                const file = fileInput.files[0];

                if (file && file.type === "application/pdf") {
                    alert(`File "${file.name}" uploaded successfully!`);
                    // Optional: add logic to actually send the file
                } else {
                    alert("Please upload a valid PDF file.");
                }
            });
        }

    document.getElementById('pdfFile').addEventListener('change', function() {
        const fileInput = this;
        const fileName = fileInput.files.length > 0 ? fileInput.files[0].name : 'No file selected';
        const icon = fileInput.files.length > 0 ? '📄 ' : '';  // Show icon only if file selected
        document.getElementById('fileNameDisplay').textContent = icon + fileName;
    });

    document.getElementById('pdfUploadForm').addEventListener('submit', function(event) {
        event.preventDefault();

        const fileInput = document.getElementById('pdfFile');

        if (fileInput.files.length === 0) {
            alert('Please select a PDF file before uploading.');
            return;
        }

        closeModal('Upload-PDF-File');

        // Clear file input and filename display after upload
        fileInput.value = '';
        document.getElementById('fileNameDisplay').textContent = 'No file selected';
    });


    });
</script>

</body>
</html>
