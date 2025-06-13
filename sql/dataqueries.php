<?php
// --- 1. DATABASE CONNECTION ---
$servername = "localhost"; // Or your DB host
$username   = "root";
$password   = "";
$dbname     = "mtbmaldb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully to database.<br><br>";

// --- Helper function to display results in a simple HTML table ---
function displayResults($result, $title = "Query Results") {
    echo "<h3>" . htmlspecialchars($title) . "</h3>";
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin-bottom: 20px;'>";
        // Table header
        echo "<tr>";
        $fields = $result->fetch_fields();
        foreach ($fields as $field) {
            echo "<th>" . htmlspecialchars($field->name) . "</th>";
        }
        echo "</tr>";
        // Table data
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $data) {
                echo "<td>" . htmlspecialchars($data ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } elseif ($result) {
        echo "<p>No results found.</p>";
    } else {
        echo "<p>Error executing query or no result object returned.</p>";
    }
}

// --- 2. DATA QUERY FUNCTIONS ---

// == SCHOOL ADMINISTRATOR: DATA QUERIES ==

/**
 * (a) Display the details of the affiliated educational institution.
 * Assumes we have a schoolRefNo for the current admin's school.
 */
function querySchoolDetails($conn, $schoolRefNo) {
    $sql = "SELECT * FROM school WHERE schoolRefNo = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo "Prepare failed (querySchoolDetails): " . $conn->error; return null; }
    $stmt->bind_param("i", $schoolRefNo);
    $stmt->execute();
    $result = $stmt->get_result();
    displayResults($result, "School Administrator: (a) Affiliated Educational Institution Details (SchoolRefNo: $schoolRefNo)");
    $stmt->close();
}

/**
 * (b) Display the list of all MTB-MAL accounts affiliated with the school, ordered by account type and user's name and its account creator.
 */
function querySchoolAccounts($conn, $schoolRefNo) {
    $sql = "SELECT u.accRefNo, u.firstName, u.lastName, u.username, u.accType, u.regDateTime, 
                   creator.username AS creatorUsername, creator.accType AS creatorAccType
            FROM mtbmalusers u
            LEFT JOIN mtbmalusers creator ON u.accCreator = creator.accRefNo
            WHERE u.schoolRefNo = ?
            ORDER BY u.accType, u.lastName, u.firstName";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo "Prepare failed (querySchoolAccounts): " . $conn->error; return null; }
    $stmt->bind_param("i", $schoolRefNo);
    $stmt->execute();
    $result = $stmt->get_result();
    displayResults($result, "School Administrator: (b) MTB-MAL Accounts for SchoolRefNo: $schoolRefNo");
    $stmt->close();
}

/**
 * (c) Display the list of all MTB-MLE Subjects offered by the school with its assigned educator, enrolled students, and subject creator.
 */
function querySchoolSubjects($conn, $schoolIdNo) {
    // This query is more complex as it needs counts and concatenated names.
    // For enrolled students count, we'll do a subquery.
    $sql = "SELECT 
                s.subjectRefNo, s.subjectId, s.subjTitle, s.mtLanguage,
                edu.fullName AS assignedEducatorName, edu_user.username AS assignedEducatorUsername,
                creator_user.username AS creatorAdminUsername,
                (SELECT COUNT(*) FROM enrolled_students es WHERE es.subjectRefNo = s.subjectRefNo AND es.status = 'Enrolled') AS enrolledStudentCount
            FROM subject s
            LEFT JOIN mtbmalusers edu_user ON s.assignedEducator = edu_user.accRefNo
            LEFT JOIN educator edu ON edu_user.accRefNo = edu.accRefNo 
            LEFT JOIN mtbmalusers creator_user ON s.adminCreator = creator_user.accRefNo
            WHERE s.schoolIdNo = ?
            ORDER BY s.subjTitle";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo "Prepare failed (querySchoolSubjects): " . $conn->error; return null; }
    $stmt->bind_param("i", $schoolIdNo);
    $stmt->execute();
    $result = $stmt->get_result();
    displayResults($result, "School Administrator: (c) MTB-MLE Subjects for SchoolID: $schoolIdNo");
    $stmt->close();
}

/**
 * (d) Identify the MTB-MLE Subjects handled by an Educator.
 * $educatorAccRefNo is the accRefNo of the educator.
 */
function querySubjectsByEducator($conn, $educatorAccRefNo) {
    $sql = "SELECT s.subjectRefNo, s.subjectId, s.subjTitle, s.mtLanguage
            FROM subject s
            WHERE s.assignedEducator = ?
            ORDER BY s.subjTitle";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo "Prepare failed (querySubjectsByEducator): " . $conn->error; return null; }
    $stmt->bind_param("i", $educatorAccRefNo);
    $stmt->execute();
    $result = $stmt->get_result();
    displayResults($result, "School Administrator: (d) Subjects Handled by Educator AccRefNo: $educatorAccRefNo");
    $stmt->close();
}

/**
 * (e) Identify the MTB-MAL accounts created by a School Administrator.
 * $adminAccRefNo is the accRefNo of the school administrator who created the accounts.
 */
function queryAccountsCreatedByAdmin($conn, $adminAccRefNo) {
    $sql = "SELECT accRefNo, firstName, lastName, username, accType, regDateTime
            FROM mtbmalusers
            WHERE accCreator = ?
            ORDER BY regDateTime DESC";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo "Prepare failed (queryAccountsCreatedByAdmin): " . $conn->error; return null; }
    $stmt->bind_param("i", $adminAccRefNo);
    $stmt->execute();
    $result = $stmt->get_result();
    displayResults($result, "School Administrator: (e) Accounts Created by Admin AccRefNo: $adminAccRefNo");
    $stmt->close();
}

/**
 * (f) Identify the School Administrator that made over 10 MTB-MAL accounts.
 */
function queryAdminsWithOver10Accounts($conn) {
    $sql = "SELECT sa.fullName AS adminFullName, u.username AS adminUsername, COUNT(created.accRefNo) AS accountsCreatedCount
            FROM mtbmalusers u
            JOIN schooladministrator sa ON u.accRefNo = sa.accRefNo
            JOIN mtbmalusers created ON u.accRefNo = created.accCreator
            WHERE u.accType = 'School Administrator'
            GROUP BY u.accRefNo, sa.fullName, u.username
            HAVING COUNT(created.accRefNo) > 10
            ORDER BY accountsCreatedCount DESC";
    $result = $conn->query($sql); // Simple query, no params
    displayResults($result, "School Administrator: (f) Admins Who Created Over 10 Accounts");
}


// == EDUCATOR: DATA QUERIES ==

/**
 * (a) Display all learning materials under a specific subject, ordered by learning material type, unit number and lesson number.
 * $subjectRefNo is the reference number of the subject.
 */
function queryLearningMaterialsBySubject($conn, $subjectRefNo) {
    // This requires joining learningmaterial with module for lessonNo if lmType is 'Learning Module'
    // The ordering is a bit tricky: lmType, then chapterNo (unit), then lmNo or module.lessonNo
    $sql = "SELECT 
                lm.learningMaterialRefNo, lm.title, lm.lmType, lm.chapterNo, lm.lmNo, lm.diffLevel,
                m.lessonNo AS moduleLessonNo, m.title AS moduleTitle,
                a.assessmentNo, a.title AS assessmentTitle
            FROM learningmaterial lm
            LEFT JOIN module m ON lm.learningMaterialRefNo = m.learningMaterialRefNo AND lm.lmType = 'Learning Module'
            LEFT JOIN assessment a ON lm.learningMaterialRefNo = a.learningMaterialRefNo AND lm.lmType = 'Learning Assessment'
            WHERE lm.subjectRefNo = ?
            ORDER BY lm.lmType, lm.chapterNo, COALESCE(m.lessonNo, a.assessmentNo, lm.lmNo)"; // COALESCE for sorting based on type
    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo "Prepare failed (queryLearningMaterialsBySubject): " . $conn->error; return null; }
    $stmt->bind_param("i", $subjectRefNo);
    $stmt->execute();
    $result = $stmt->get_result();
    displayResults($result, "Educator: (a) Learning Materials for SubjectRefNo: $subjectRefNo");
    $stmt->close();
}

/**
 * (b) Identify the number of students enrolled in a subject.
 */
function queryEnrolledStudentCount($conn, $subjectRefNo) {
    $sql = "SELECT COUNT(*) AS numberOfEnrolledStudents
            FROM enrolled_students
            WHERE subjectRefNo = ? AND status = 'Enrolled'";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo "Prepare failed (queryEnrolledStudentCount): " . $conn->error; return null; }
    $stmt->bind_param("i", $subjectRefNo);
    $stmt->execute();
    $result = $stmt->get_result();
    displayResults($result, "Educator: (b) Number of Students Enrolled in SubjectRefNo: $subjectRefNo");
    $stmt->close();
}

/**
 * (c) Display the list of all learning assessments, ordered by difficulty level, title, unit number, and lesson number.
 * Assuming "unit number" refers to learningmaterial.chapterNo and "lesson number" refers to assessment.assessmentNo
 */
function queryAllLearningAssessments($conn, $schoolIdNo) { // School specific
    $sql = "SELECT 
                a.assessmentId, a.title, a.assessmentNo, 
                lm.diffLevel, lm.chapterNo AS unitNumber, lm.subjectRefNo
            FROM assessment a
            JOIN learningmaterial lm ON a.learningMaterialRefNo = lm.learningMaterialRefNo
            WHERE lm.schoolIdNo = ? AND lm.lmType = 'Learning Assessment'
            ORDER BY lm.diffLevel, a.title, lm.chapterNo, a.assessmentNo";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo "Prepare failed (queryAllLearningAssessments): " . $conn->error; return null; }
    $stmt->bind_param("i", $schoolIdNo);
    $stmt->execute();
    $result = $stmt->get_result();
    displayResults($result, "Educator: (c) All Learning Assessments for SchoolID $schoolIdNo");
    $stmt->close();
}


/**
 * (d) Display the list of all subjects and the students enrolled in, ordered by subject title and student’s name.
 * $educatorAccRefNo is to filter subjects handled by this educator.
 */
function querySubjectsAndEnrolledStudentsByEducator($conn, $educatorAccRefNo) {
    $sql = "SELECT 
                s.subjTitle,
                st_user.firstName AS studentFirstName, st_user.lastName AS studentLastName, st.lrn
            FROM subject s
            JOIN enrolled_students es ON s.subjectRefNo = es.subjectRefNo
            JOIN mtbmalusers st_user ON es.studentAccRefNo = st_user.accRefNo
            JOIN student st ON st_user.accRefNo = st.accRefNo
            WHERE s.assignedEducator = ? AND es.status = 'Enrolled'
            ORDER BY s.subjTitle, st_user.lastName, st_user.firstName";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo "Prepare failed (querySubjectsAndEnrolledStudentsByEducator): " . $conn->error; return null; }
    $stmt->bind_param("i", $educatorAccRefNo);
    $stmt->execute();
    $result = $stmt->get_result();
    displayResults($result, "Educator: (d) Subjects and Enrolled Students for Educator AccRefNo: $educatorAccRefNo");
    $stmt->close();
}

/**
 * (e) Identify the educator that has the most handled students.
 *  Across all subjects they handle.
 */
function queryEducatorWithMostStudents($conn, $schoolIdNo) {
    $sql = "SELECT 
                e_user.accRefNo AS educatorAccRefNo, edu.fullName AS educatorFullName, 
                COUNT(DISTINCT es.studentAccRefNo) AS totalHandledStudents
            FROM educator edu
            JOIN mtbmalusers e_user ON edu.accRefNo = e_user.accRefNo
            JOIN subject s ON e_user.accRefNo = s.assignedEducator
            JOIN enrolled_students es ON s.subjectRefNo = es.subjectRefNo
            WHERE edu.schoolIdNo = ? AND es.status = 'Enrolled'
            GROUP BY e_user.accRefNo, edu.fullName
            ORDER BY totalHandledStudents DESC
            LIMIT 1"; // Get only the top one
    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo "Prepare failed (queryEducatorWithMostStudents): " . $conn->error; return null; }
    $stmt->bind_param("i", $schoolIdNo);
    $stmt->execute();
    $result = $stmt->get_result();
    displayResults($result, "Educator: (e) Educator with Most Handled Students in SchoolID $schoolIdNo");
    $stmt->close();
}

/**
 * (f) Display the students' names and assessment scores in a subject, ordered by name and score.
 * $subjectRefNo is the specific subject.
 */
function queryStudentScoresInSubject($conn, $subjectRefNo) {
    $sql = "SELECT 
                st_user.firstName, st_user.lastName,
                a.title AS assessmentTitle,
                sar.rawScore, sar.remarks
            FROM student_assessment_result sar
            JOIN assessment a ON sar.assessmentId = a.assessmentId
            JOIN learningmaterial lm ON a.learningMaterialRefNo = lm.learningMaterialRefNo
            JOIN student s ON sar.stLRN = s.lrn
            JOIN mtbmalusers st_user ON s.accRefNo = st_user.accRefNo
            WHERE lm.subjectRefNo = ?
            ORDER BY st_user.lastName, st_user.firstName, sar.rawScore DESC";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo "Prepare failed (queryStudentScoresInSubject): " . $conn->error; return null; }
    $stmt->bind_param("i", $subjectRefNo);
    $stmt->execute();
    $result = $stmt->get_result();
    displayResults($result, "Educator: (f) Student Scores in SubjectRefNo: $subjectRefNo");
    $stmt->close();
}

/**
 * (g) Display the list of all subjects handled, ordered by subject title.
 * $educatorAccRefNo for the specific educator.
 */
function queryHandledSubjects($conn, $educatorAccRefNo) {
    // Same as querySubjectsByEducator (School Admin query d)
    querySubjectsByEducator($conn, $educatorAccRefNo); // Re-use function, adjust title if needed in calling code
}


// == STUDENT: DATA QUERIES ==

/**
 * (a) Identify the total number of subjects enrolled.
 * $studentAccRefNo is the student's user account reference.
 */
function queryStudentTotalEnrolledSubjects($conn, $studentAccRefNo) {
    $sql = "SELECT COUNT(*) AS totalEnrolledSubjects
            FROM enrolled_students
            WHERE studentAccRefNo = ? AND status = 'Enrolled'";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo "Prepare failed (queryStudentTotalEnrolledSubjects): " . $conn->error; return null; }
    $stmt->bind_param("i", $studentAccRefNo);
    $stmt->execute();
    $result = $stmt->get_result();
    displayResults($result, "Student: (a) Total Enrolled Subjects for Student AccRefNo: $studentAccRefNo");
    $stmt->close();
}

/**
 * (b) Display the details of the student, ordered by name and date of birth. (This is for *a* student, so ordering isn't for multiple)
 * $studentAccRefNo for the specific student.
 */
function queryStudentDetails($conn, $studentAccRefNo) {
    $sql = "SELECT u.*, s.lrn, s.fullName AS studentFullName, s.parentGuardianName, s.pgRStoStudent, s.pgDOB, s.pgMaritalStatus, s.pgEmailAdd, s.pgContactNo
            FROM mtbmalusers u
            JOIN student s ON u.accRefNo = s.accRefNo
            WHERE u.accRefNo = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo "Prepare failed (queryStudentDetails): " . $conn->error; return null; }
    $stmt->bind_param("i", $studentAccRefNo);
    $stmt->execute();
    $result = $stmt->get_result();
    displayResults($result, "Student: (b) Details for Student AccRefNo: $studentAccRefNo");
    $stmt->close();
}

/**
 * (c) List the details of a student whose learner reference number starts with ‘15’.
 * This implies searching across students.
 */
function queryStudentsByLRNPrefix($conn, $schoolIdNo, $lrnPrefix = '15') {
    $sql = "SELECT u.firstName, u.lastName, s.lrn, s.fullName, school.schoolName
            FROM student s
            JOIN mtbmalusers u ON s.accRefNo = u.accRefNo
            JOIN school `school` ON s.schoolIdNo = school.schoolIdNo
            WHERE s.schoolIdNo = ? AND CAST(s.lrn AS CHAR) LIKE CONCAT(?, '%')
            ORDER BY s.lrn";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo "Prepare failed (queryStudentsByLRNPrefix): " . $conn->error; return null; }
    $stmt->bind_param("is", $schoolIdNo, $lrnPrefix);
    $stmt->execute();
    $result = $stmt->get_result();
    displayResults($result, "Student: (c) Students in SchoolID $schoolIdNo with LRN starting with '$lrnPrefix'");
    $stmt->close();
}

/**
 * (d) Identify the students that are not enrolled in a subject.
 * This means students existing in the 'student' table but having no 'Enrolled' records in 'enrolled_students'.
 */
function queryStudentsNotEnrolled($conn, $schoolIdNo) {
    $sql = "SELECT u.firstName, u.lastName, s.lrn, sch.schoolName
            FROM student s
            JOIN mtbmalusers u ON s.accRefNo = u.accRefNo
            JOIN school sch ON s.schoolIdNo = sch.schoolIdNo
            WHERE s.schoolIdNo = ? AND NOT EXISTS (
                SELECT 1 FROM enrolled_students es 
                WHERE es.studentAccRefNo = s.accRefNo AND es.status = 'Enrolled'
            )
            ORDER BY u.lastName, u.firstName";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo "Prepare failed (queryStudentsNotEnrolled): " . $conn->error; return null; }
    $stmt->bind_param("i", $schoolIdNo);
    $stmt->execute();
    $result = $stmt->get_result();
    displayResults($result, "Student: (d) Students in SchoolID $schoolIdNo Not Enrolled in Any Subject");
    $stmt->close();
}

// == ALL SYSTEM USERS: DATA QUERIES ==

/**
 * (a) Sort the subject from the most to least uploaded learning materials, ordered by subject title.
 */
function querySubjectsByLearningMaterialCount($conn, $schoolIdNo) {
    $sql = "SELECT 
                s.subjTitle, COUNT(lm.learningMaterialRefNo) AS learningMaterialCount
            FROM subject s
            LEFT JOIN learningmaterial lm ON s.subjectRefNo = lm.subjectRefNo
            WHERE s.schoolIdNo = ?
            GROUP BY s.subjectRefNo, s.subjTitle
            ORDER BY learningMaterialCount DESC, s.subjTitle";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo "Prepare failed (querySubjectsByLearningMaterialCount): " . $conn->error; return null; }
    $stmt->bind_param("i", $schoolIdNo);
    $stmt->execute();
    $result = $stmt->get_result();
    displayResults($result, "All Users: (a) Subjects by Learning Material Count (SchoolID: $schoolIdNo)");
    $stmt->close();
}

/**
 * (b) Display the details of the MTB-MAL accounts created on a specified date.
 */
function queryAccountsByCreationDate($conn, $creationDate) { // Format 'YYYY-MM-DD'
    $sql = "SELECT accRefNo, username, accType, regDateTime, schoolRefNo 
            FROM mtbmalusers
            WHERE DATE(regDateTime) = ?
            ORDER BY regDateTime";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo "Prepare failed (queryAccountsByCreationDate): " . $conn->error; return null; }
    $stmt->bind_param("s", $creationDate);
    $stmt->execute();
    $result = $stmt->get_result();
    displayResults($result, "All Users: (b) Accounts Created On: $creationDate");
    $stmt->close();
}

/**
 * (c) Display the details of all MTB-MLE Subjects offered by the school, including the subject creator,
 *     assigned educator, learning materials and enrolled students. Ordered by the subject title,
 *     reference number of the subject creator and assigned educator, student’s name.
 */
function queryAllSubjectDetailsExtended($conn, $schoolIdNo) {
    // This is a very comprehensive query, likely best broken down or presented carefully.
    // For enrolled students and learning materials, we might list them or count them.
    // Here, let's aim for counts and key names.
    $sql = "SELECT 
                s.subjectRefNo, s.subjTitle,
                creator_user.username AS creatorUsername, creator_user.accRefNo AS creatorAccRefNo,
                edu_user.username AS educatorUsername, edu_user.accRefNo AS educatorAccRefNo,
                (SELECT COUNT(*) FROM learningmaterial lm WHERE lm.subjectRefNo = s.subjectRefNo) AS learningMaterialCount,
                (SELECT COUNT(*) FROM enrolled_students es WHERE es.subjectRefNo = s.subjectRefNo AND es.status = 'Enrolled') AS enrolledStudentCount
            FROM subject s
            LEFT JOIN mtbmalusers creator_user ON s.adminCreator = creator_user.accRefNo
            LEFT JOIN mtbmalusers edu_user ON s.assignedEducator = edu_user.accRefNo
            WHERE s.schoolIdNo = ?
            ORDER BY s.subjTitle, creator_user.accRefNo, edu_user.accRefNo";
    // Student name ordering would require joining enrolled_students and mtbmalusers again, making it more complex for a summary.
    // If individual student names are needed per subject, a separate detailed query per subject is better.
    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo "Prepare failed (queryAllSubjectDetailsExtended): " . $conn->error; return null; }
    $stmt->bind_param("i", $schoolIdNo);
    $stmt->execute();
    $result = $stmt->get_result();
    displayResults($result, "All Users: (c) Extended Subject Details for SchoolID: $schoolIdNo");
    $stmt->close();
}

/**
 * (d) Identify the MTB-MAL account whose reference number starts with ‘20’, sort the result by account type, and user’s name.
 */
function queryAccountsByRefPrefix($conn, $refPrefix = '20') {
    $sql = "SELECT accRefNo, username, firstName, lastName, accType, schoolRefNo
            FROM mtbmalusers
            WHERE CAST(accRefNo AS CHAR) LIKE CONCAT(?, '%')
            ORDER BY accType, lastName, firstName";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo "Prepare failed (queryAccountsByRefPrefix): " . $conn->error; return null; }
    $stmt->bind_param("s", $refPrefix);
    $stmt->execute();
    $result = $stmt->get_result();
    displayResults($result, "All Users: (d) Accounts with RefNo starting with '$refPrefix'");
    $stmt->close();
}

/**
 * (e) List the details of a user who was born in June.
 */
function queryUsersBornInMonth($conn, $monthNumber = 6) { // 6 for June
    $sql = "SELECT accRefNo, firstName, lastName, username, dob, accType, schoolRefNo
            FROM mtbmalusers
            WHERE MONTH(dob) = ?
            ORDER BY lastName, firstName";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo "Prepare failed (queryUsersBornInMonth): " . $conn->error; return null; }
    $stmt->bind_param("i", $monthNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    displayResults($result, "All Users: (e) Users Born in Month: $monthNumber");
    $stmt->close();
}
