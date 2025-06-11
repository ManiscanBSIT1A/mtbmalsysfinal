<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login/frontpage.php");
    exit();
}
// Set correct welcome page for each user type
$welcomePage = "../admin/welcome_admin.php";
switch ($_SESSION['accType'] ?? '') {
    case "Educator": $welcomePage = "../educator/welcome_educator.php"; break;
    case "Student":  $welcomePage = "../student/welcome_student.php";  break;
    case "School Administrator": $welcomePage = "../admin/welcome_admin.php";   break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Basic document setup and custom fonts -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>About MTB-MAL</title>
  <link rel="icon" type="image/png" href="../images/MTB-MAL_logos.png">
  <link href="https://fonts.googleapis.com/css2?family=Segoe+UI&display=swap" rel="stylesheet">
</head>
<body>
  <div class="container">
    <!-- Top Section: Logo, Title, Subheading, and Back Button -->
    <div class="top-section">
      <div class="top-content">
        <img src="../images/MTB-MAL_logo-alt.png" alt="MTB-MAL Logo" class="logo-image">
        <div class="text-wrapper">
          <strong class="portal-name">MTB-MAL</strong>
          <div class="subheading">Mother Tongue-Based Multilingual Assessment and Learning System</div>
        </div>
      </div>
      <!-- Back Button (top right) -->
      <a href="<?php echo $welcomePage; ?>" class="login-trigger">Back</a>
    </div>

  <style>
  * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
  }

    html, body {
    height: 100%;
    font-family: 'Segoe UI', sans-serif;
    /* min-width: 1024px;  ← REMOVE THIS LINE */
    overflow-x: hidden;
    overflow-y: auto;
    }

  .container {
    height: 100vh;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }

  /* --- Top Section (Header) --- */
  .top-section {
    height: 250px;
    background: linear-gradient(to right, #fcd34d, #f472b6);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-left: 4rem;
    padding-right: 4rem;
    color: #1f2937;
    position: sticky;
    top: 0;
    z-index: 1000;
    flex-shrink: 0;
    transition: all 0.3s ease;
    padding-top: 2rem;
    padding-bottom: 2rem;
  }

  .top-section.shrunk {
    height: 300px;
    padding-top: 1rem;
    padding-bottom: 1rem;
  }

  .top-content {
    display: flex;
    align-items: center;
    gap: 2rem;
    flex-wrap: nowrap;
  }

  .logo-image {
    height: 150px;
    width: auto;
    flex-shrink: 0;
  }

  .text-wrapper {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
  }

  .portal-name {
    font-size: 2.5rem;
    font-weight: bold;
    color: #1f2937;
    margin-bottom: 0.5rem;
  }

  .subheading {
    font-size: 1.5rem;
    max-width: 600px; /* Keep original max-width as it's for the short text */
    color: #374151;
    line-height: 1.4;
  }

  .login-trigger {
    position: absolute;
    top: 1rem;
    right: 1.5rem;
    font-size: 1rem;
    font-weight: bold;
    padding: 0.75rem 1.25rem;
    border-radius: 0.75rem;
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #1f2937;
    cursor: pointer;
    background: rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    z-index: 10;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    line-height: 1;
  }

  .login-trigger:hover {
    background: rgba(255, 255, 255, 0.5);
  }

  /* --- Bottom Section (Main) --- */
  .bottom-section {
    flex: 1;
    background: #fff;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: center;
    padding: 3rem 2rem;
    position: relative;
    overflow-y: auto;
    background-color: #f8f8fc;
  }

  /* --- Original "A Digital Learning Playground" Description Box --- */
  .description-box {
    text-align: center;
    margin-bottom: 1.5rem; /* Reduce margin-bottom to make space for the new text */
    max-width: 700px;
    width: 100%;
  }

  .description-box .title {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0.75rem;
    color: #1f2937;
  }

  .description-box .description {
    font-size: 1.1rem;
    color: #4b5563;
    line-height: 1.6;
  }

    /* --- Long Description text --- */
  .long-description {
      max-width: 700px; /* Align with description box */
      width: 100%;
      text-align: center;
      margin: 1.5rem auto 3rem auto; /* Add margin top/bottom */
      font-size: 1.1rem;
      color: #4b5563;
      line-height: 1.6;
  }


    /* --- Section for Educational Institutions List --- */
  .institutional-features-list {
      max-width: 700px; /* Adjusted max-width to align with description box */
      width: 100%;
      background-color: #f9fafb; /* Changed background-color */
      padding: 1.5rem 2rem;
      border-radius: 1rem;
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.05);
      color: #1f2937; /* Changed text color */
      margin-top: 0;
      margin-bottom: 3rem; /* Add space below this list */
      text-align: left;
  }

    .institutional-features-list h3 {
      margin-bottom: 1rem;
      font-size: 1.5rem;
      color: #111827; /* Changed heading color */
      text-align: center;
    }

    .institutional-features-list ul {
      list-style-type: disc;
      padding-left: 1.5rem;
    }

    .institutional-features-list li {
      margin-bottom: 0.8rem;
      font-size: 1.1rem;
      color: #374151; /* Changed list item color */
    }


  /* --- NEW: Section for Tabs and Feature Content --- */
  .features-tabbed-section {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 1rem;
    padding-top: 3rem;
    background-color: #fff;
    box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.05);
  }

  .features-heading {
    font-size: 1.8rem;
    font-weight: bold;
    color: #1f2937;
    margin-bottom: 3rem;
    text-align: center;
    max-width: 700px;
    width: 100%;
  }

  /* --- Feature Tabs --- */
  .feature-tabs {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 2rem;
    justify-content: center;
    flex-wrap: wrap;
    border-bottom: 2px solid #eee;
    padding-bottom: 0.5rem;
    width: 100%;
    max-width: 700px;
  }

  .feature-tab-button {
    background: none;
    border: none;
    padding: 0.5rem 1rem;
    font-size: 1.2rem;
    font-weight: bold;
    color: #6b7280;
    cursor: pointer;
    transition: color 0.3s ease;
    position: relative;
  }

  .feature-tab-button:hover {
    color: #1f2937;
  }

  .feature-tab-button.active {
    color: #4f46e5;
  }

  .feature-tab-button.active::after {
    content: '';
    position: absolute;
    bottom: -0.6rem;
    left: 0;
    width: 100%;
    height: 2px;
    background-color: #4f46e5;
    transition: width 0.3s ease;
  }

  /* --- Tab Content Area (Max-width increased) --- */
  .tab-content-area {
    width: 100%;
    max-width: 1200px; /* Increased max-width here */
    padding: 0 1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding-bottom: 3rem;
  }

  /* --- Individual Tab Content Container --- */
  .tab-content {
    display: none;
    width: 100%;
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.5s ease-out, transform 0.5s ease-out;
  }

  .tab-content.active {
    display: flex;
    flex-direction: column;
    align-items: center;
    opacity: 1;
    transform: translateY(0);
  }

  /* --- Original Feature Lists (the checkmark lists, now within tabs) --- */
  .features-list {
    max-width: 600px;
    width: 100%;
    background-color: #f9fafb;
    padding: 1.5rem 2rem;
    border-radius: 1rem;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.05);
    color: #1f2937;
    display: block;
    margin-top: 0;
    margin-bottom: 2rem;
    text-align: left;
  }

  .features-list h3 {
    margin-bottom: 1rem;
    font-size: 1.5rem;
    color: #111827;
    text-align: center;
  }

  .features-list ul {
    list-style-type: disc;
    padding-left: 1.5rem;
  }

  .features-list li {
    margin-bottom: 0.8rem;
    font-size: 1.1rem;
    color: #374151;
  }

  /* --- Feature Cards Display Area (now also within tabs) --- */
  .feature-card-group {
    width: 100%;
    display: flex;
    gap: 2rem;
    justify-content: center;
    align-items: flex-start;
    flex-wrap: wrap;
    padding-top: 1rem;
    margin-top: 1rem;
  }

  /* --- Styling for individual Feature Cards --- */
  .feature-card {
    flex: 1;
    min-width: 280px;
    max-width: 320px;
    background-color: #fff;
    padding: 1.5rem;
    border-radius: 0.75rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    color: #1f2937;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    text-align: left;
    transition: transform 0.3s ease;
  }

  .feature-card:hover {
    transform: translateY(-5px);
  }

  .feature-card h4 {
    font-size: 1.2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
    color: #111827;
  }

  .feature-card p {
    font-size: 1rem;
    color: #4b5563;
    line-height: 1.6;
  }

  @media (max-width: 768px) {
  html, body {
    width: 100%;
    max-width: 100%;
    overflow-x: hidden;
    margin: 0;
    padding: 0;
  }

  .container {
    width: 100%;
    max-width: 100%;
    margin: 0 auto;
  }

  .top-section {
    flex-direction: row;
    height: auto;
    padding: 1.5rem 1rem;
    width: 100%;
    max-width: 100%;
    text-align: center;
  }

  .top-content {
    flex-direction: row;
    align-items: center;
    gap: 1rem;
    text-align: center;
  }

  .logo-image {
    height: 100px;
  }

  .portal-name {
    font-size: 2rem;
  }

  .subheading {
    font-size: 1.2rem;
    text-align: left;
    width: 300px; /* Adjusted width for mobile */
  }

  .login-trigger {
    position: static;
    margin-top: 1rem;
  }

  .bottom-section {
    padding: 2rem 1rem;
  }

    .long-description {
        margin: 1rem auto 2rem auto; /* Adjusted margins for mobile */
        font-size: 1rem; /* Slightly smaller font */
    }


  .features-tabbed-section {
    padding-top: 2rem;
  }

  .features-heading {
    font-size: 1.5rem;
  }

  .feature-tabs {
    flex-direction: row;
    overflow-x: auto;
    gap: 0.75rem;
    padding-bottom: 1rem;
  }

  .feature-tab-button {
    white-space: nowrap;
    font-size: 1rem;
  }

  .tab-content-area {
    padding: 0 0.5rem;
     /* Max-width should ideally not limit on mobile, default behavior of max-width less than viewport width handles this */
  }

  .features-list {
    padding: 1rem;
  }

  .institutional-features-list {
      padding: 1rem;
  }

  .feature-card-group {
    flex-direction: column;
    align-items: center;
  }

  .feature-card {
    width: 100%;
    max-width: 100%;
  }
}

</style>

</head>
<body>

  <div class="container">

    <!-- Bottom Section: Main content below the header -->
    <div class="bottom-section">

      <!-- Introductory description -->
      <div class="description-box">
        <div class="title">A Digital Learning Playground</div>

      <!-- ADDED THE LONG DESCRIPTION HERE, BELOW THE DESCRIPTION BOX -->
      <div class="long-description">
        Mother Tongue-Based Multilingual Assessment and Learning System, in short, MTB-MAL— is an open gamified digital learning platform for educational institutions that supports the Mother Tongue-Based Multilingual Education in short (MTB MLE) curriculum of DepEd, Philippines. It’s a cross-platform virtual education system that utilizes regional languages as the medium of instruction to improve learning literacy and cognitive development for early-grade students through interactive learning and gamified assessments — modernizing ink and paper-based education.
      </div>


      <!-- List for Educational Institutions (Colors adjusted) -->
      <div class="institutional-features-list">
          <h3>For Educational Institutions</h3>
          <ul>
              <li>Makes the implementation of MTB-MLE policy more appealing to the stakeholders</li>
              <li>MTB-MAL system is a reliable platform for using Regional Languages as a Medium of Instruction</li>
              <li>The system is an open virtual educational platform, allowing educational institutions to advance from ink-and-paper-based learning.</li>
              <li>A secure but fun and interactive learning environment for children!</li>
          </ul>
      </div>


      <!-- Tabbed content section -->
      <div class="features-tabbed-section">

        <!-- Section heading -->
        <h2 class="features-heading">Why MTB-MAL Works</h2>

        <!-- Tab buttons for switching views -->
        <div class="feature-tabs" id="featureTabs">
          <button class="feature-tab-button" data-target="teachersTabContent">For Educators</button>
          <button class="feature-tab-button" data-target="studentsTabContent">For Students</button>
          <button class="feature-tab-button" data-target="platformTabContent">Platform Features</button>
        </div>

        <!-- Tab content area (Max-width increased) -->
        <div class="tab-content-area">

          <!-- Tab 1: Teacher Features -->
          <div class="tab-content" id="teachersTabContent">
            <div class="features-list">
              <h3>For Educators</h3>
              <ul>
                <li>Simplifies content delivery with template-driven uploads</li>
                <li>Supports multilingual instruction in regional languages</li>
                <li>Provides a clear view of student engagement and performance</li>
                <li>Ensures curriculum alignment with MTB-MLE standards</li>
                <li>Manageable organization of Learning Materials</li>
              </ul>
            </div>
            <div class="feature-card-group">
              <!-- Teacher-specific cards -->
              <div class="feature-card">
                <h4>Content Management</h4>
                <p>Upload and organize mother tongue-based lessons and assessments using built-in templates. Materials are categorized by difficulty level and subject.</p>
              </div>
              <div class="feature-card">
                <h4>Gamified Assessments</h4>
                <p>Create interactive, game-based quizzes that align with learning goals. Customize difficulty and format to support varied learner needs.</p>
              </div>
              <div class="feature-card">
                <h4>Student Monitoring</h4>
                <p>Track student progress and assessment results through the educator dashboard, and manually validate scores when needed for accuracy.</p>
              </div>
            </div>
          </div>

          <!-- Tab 2: Student Features -->
          <div class="tab-content" id="studentsTabContent">
            <div class="features-list">
              <h3>For Students</h3>
              <ul>
                <li>Enhances learning through language familiarity</li>
                <li>Makes education engaging with gamified assessments</li>
                <li>Encourages continuous improvement with achievement tracking</li>
                <li>Offers accessible and child-friendly content anytime and anywhere!</li>
              </ul>
            </div>
            <div class="feature-card-group">
              <!-- Student-specific cards -->
              <div class="feature-card">
                <h4>Localized Learning</h4>
                <p>Access learning modules and assessments in your mother tongue language, helping to improve comprehension and build a strong educational foundation.</p>
              </div>
              <div class="feature-card">
                <h4>Gamified Activities</h4>
                <p>Engage with lessons through engaging and interactive gamified assessments that promote vocabulary-building and reading comprehension at different difficulty levels.</p>
              </div>
              <div class="feature-card">
                <h4>Progress Dashboard</h4>
                <p>View achievements and track learning progress within each subject, encouraging self-paced learning and motivation through visual feedback.</p>
              </div>
            </div>
          </div>

          <!-- Tab 3: Platform Features -->
          <div class="tab-content" id="platformTabContent">
            <div class="feature-card-group">
              <!-- Platform-focused cards -->
              <div class="feature-card">
                <h4>Cross-Platform Access</h4>
                <p>Access MTB-MAL on both web and mobile devices, ensuring flexibility for all users whether at school or at home.</p>
              </div>
              <div class="feature-card">
                <h4>Role-Based System</h4>
                <p>Separate access and dashboards for school administrators, educators, and students. Ensures focused and secure interactions within the system.</p>
              </div>
              <!-- Added a third card for the new content -->
              <div class="feature-card">
                <h4>Secure User Management</h4>
                <p>All accounts and data are managed exclusively by the system developers. General Terms and Conditions of the MTB-MAL System are strongly implemented to Educational Institutions and School Administrators.</p>
              </div>
            </div>
          </div>

        </div> <!-- End tab-content-area -->

      </div> <!-- End features-tabbed-section -->

    </div> <!-- End bottom-section -->

  </div> <!-- End container -->

  <script>
    // Wait for the page to fully load
    document.addEventListener('DOMContentLoaded', () => {

      // Get all tab buttons and content containers
      const featureTabButtons = document.querySelectorAll('.feature-tab-button');
      const tabContentContainers = document.querySelectorAll('.tab-content');

      // Function to activate a tab and show corresponding content
      function activateFeatureTab(targetId) {
        featureTabButtons.forEach(button => button.classList.remove('active'));
        tabContentContainers.forEach(container => container.classList.remove('active'));

        // Highlight the clicked tab button
        const clickedButton = document.querySelector(`.feature-tab-button[data-target="${targetId}"]`);
        if (clickedButton) clickedButton.classList.add('active');

        // Show corresponding tab content
        const targetContainer = document.getElementById(targetId);
        if (targetContainer) targetContainer.classList.add('active');
      }

      // Attach click event listeners to all tab buttons
      featureTabButtons.forEach(button => {
        button.addEventListener('click', () => {
          const targetId = button.getAttribute('data-target');
          activateFeatureTab(targetId);
        });
      });

      // Activate the first tab by default on load
      if (featureTabButtons.length > 0) {
         const firstTabTarget = featureTabButtons[0].getAttribute('data-target');
         activateFeatureTab(firstTabTarget);
      }


    }); // End of DOMContentLoaded

    window.addEventListener('scroll', () => {
      const topSection = document.querySelector('.top-section');
      if (window.scrollY > 10) {
        topSection.classList.add('shrunk');
      } else {
        topSection.classList.remove('shrunk');
      }
    });

  </script>

</body>
</html>