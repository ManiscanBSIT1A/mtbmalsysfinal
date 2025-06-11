<?php
session_start();

// Prevent browser from caching this page
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies

include '../sql/db_connect.php';


$error = "";

// If user is already logged in, redirect based on their role
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    switch ($_SESSION['accType']) {
        case "School Administrator":
            header("Location: ../admin/welcome_admin.php");
            exit();
        case "Educator":
            header("Location: ../educator/welcome_educator.php");
            exit();
        case "Student":
            header("Location: ../student/welcome_student.php");
            exit();
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($role) || empty($username) || empty($password)) {
        $error = "All fields are required.";
    } else {
        // Map role values to DB accType
        $role_map = [
            'school_administrator' => 'School Administrator',
            'educator' => 'Educator',
            'student' => 'Student'
        ];
        $db_role = $role_map[$role] ?? '';

        // Use prepared statement for security
        $stmt = $conn->prepare("SELECT * FROM mtbmalusers WHERE username=? AND accType=? LIMIT 1");
        $stmt->bind_param("ss", $username, $db_role);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Check password (assuming hashed with password_hash)
            if (password_verify($password, $row['password'])) { 
        session_regenerate_id(true);
                // Set session variables
                $_SESSION['logged_in'] = true;
                $_SESSION['schoolIdNo'] = $row['schoolIdNo'];
                $_SESSION['accRefNo'] = $row['accRefNo'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['accType'] = $row['accType'];
                $_SESSION['firstName'] = $row['firstName'];

                // Redirect by role
                switch ($role) {
                    case "school_administrator":
                        header("Location: ../admin/welcome_admin.php");
                        break;
                    case "educator":
                        header("Location: ../educator/welcome_educator.php");
                        break;
                    case "student":
                        header("Location: ../student/welcome_student.php");
                        break;
                }
                exit();
            } else {
                $error = "Incorrect username or password!";
            }
        } else {
            $error = "Account does not exist!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MTB-MAL Login</title>
    <link rel="icon" type="image/png" href="../images/MTB-MAL_logo.png">
    <link href="../style/login-style.css" rel="stylesheet" />
  
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />

</head>
<body>
    <button class="back-button" onclick="window.location.href='frontpage.php'">⬅ Return to Front Page</button>
    <div class="left-panel"> 
        <div class="left-panel-content">
            <img src="../images/MTB-MAL_logos.png" alt="MTB-MAL Logo">
            <div class="left-header-text">
                Mother Tongue-Based Multilingual Assessment and Learning System
            </div>
        </div>
    </div>

    <div class="right-container">
        <div class="right-panel">
            <div class="form-wrapper">
                <form method="POST" action="">
                    <h2>Log in as:</h2>
                    <select id="role" name="role" required>
                        <option value="">-- Select Role --</option>
                        <option value="school_administrator" <?php if(isset($_POST['role']) && $_POST['role']=='school_administrator') echo "selected"; ?>>School Administrator</option>
                        <option value="educator" <?php if(isset($_POST['role']) && $_POST['role']=='educator') echo "selected"; ?>>Educator</option>
                        <option value="student" <?php if(isset($_POST['role']) && $_POST['role']=='student') echo "selected"; ?>>Student</option>
                    </select>

                    <label for="ref">Username</label>
                    <input type="text" id="ref" name="username" placeholder="Enter username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>

                    <label for="password">Password</label>
                    <div class="form-group">
                        <input type="password" id="password" name="password" placeholder="Enter password" required>
                        <span class="eye" id="toggle-eye" onclick="togglePassword()">
                            <img src="../images/pass_hidden.png" alt="Password Hidden" id="eye-icon">
                        </span>
                    </div>

                    <?php if ($error): ?>
                        <div style="color: red; margin-top:10px;"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <div class="buttons">
                        <button type="submit">Log in</button>
                        <button type="button" onclick="handleRegister()">Register a School</button>
                    </div>
                </form>

                <div class="footer">
                    <button id="cookie-notice">Cookies notice</button>
                    <button id="language-select">🌐 English</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cookie Modal -->
    <div id="cookiesNotice" class="modal" style="display: none;">
        <div class="modal-content">
            <h2>Cookies must be enabled in your browser</h2><hr>
            <p>
                Two cookies are used on this site:<br><br>
                The essential one is the session cookie, usually called <b>MoodleSession</b>. You must allow this cookie in your browser to provide continuity and to remain logged in when browsing the site.
                When you log out or close the browser, this cookie is destroyed (in your browser and on the server).<br><br>
                The other cookie is purely for convenience, usually called <b>MOODLEID</b> or similar. It just remembers your username in the browser. This means that when you return to this site, the username field on the login page is already filled in for you. It is safe to refuse this cookie — you will just have to retype your username each time you log in.
            </p>
            <button class="modal-close" onclick="closeModal()">OKAY</button>
        </div>
    </div>

    <script>
        function togglePassword() {
            const password = document.getElementById("password");
            const eye = document.getElementById("toggle-eye");
            const isHidden = password.type === "password";
            password.type = isHidden ? "text" : "password";
            eye.innerHTML = isHidden
                ? '<img src="../images/pass_visible.png" alt="Password Visible">'
                : '<img src="../images/pass_hidden.png" alt="Password Hidden">';
        }

        document.getElementById("cookie-notice").addEventListener("click", function () {
            document.getElementById("cookiesNotice").style.display = "flex";
        });

        function closeModal() {
            document.getElementById("cookiesNotice").style.display = "none";
        }

        function handleRegister() {
            window.location.href = "regpage_school.php";
        }
    </script>
</body>
</html>
