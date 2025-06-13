<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mtbmaldb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

// sql to create SCHOOL table
$sqlschool = "CREATE TABLE IF NOT EXISTS school (
    schoolRefNo INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
    schoolName TINYTEXT NOT NULL,
    shortName VARCHAR(155) NOT NULL,
    schoolIdNo INT(6) UNSIGNED NOT NULL UNIQUE,
    emailAddress VARCHAR(50) NOT NULL UNIQUE,
    schoolType ENUM ('Public Integrated School', 'Private Integrated School', 'Private Elementary School', 'Public Elementary School'),
    contactNo VARCHAR(12) NOT NULL UNIQUE,
    locAddress VARCHAR(70) NOT NULL,
    region VARCHAR(30) NOT NULL,
    adminUserName VARCHAR(15) NOT NULL UNIQUE,
    regDateTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sqlschool) === TRUE) 
    echo "Table for Schools created successfully" . "<br>";
else
    echo "Error creating table: " . $conn->error;


// sql to create MTBMAL USERS table (accRefNo to be shown after complete registration)
$sqlusers = "CREATE TABLE IF NOT EXISTS mtbmalusers (
    accRefNo INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    schoolRefNo INT(10) UNSIGNED NOT NULL,
    schoolIdNo INT(6) UNSIGNED NOT NULL,
    firstName VARCHAR(30) NOT NULL,
    lastName VARCHAR(30) NOT NULL,
    dob DATE NOT NULL,
    emailAddress VARCHAR(50) NOT NULL UNIQUE,
    contactNo VARCHAR(12) NOT NULL UNIQUE,
    username VARCHAR(15) NOT NULL UNIQUE,
    password VARCHAR(60) NOT NULL,
    accCreator INT(10) UNSIGNED NOT NULL,
    accType ENUM ('School Administrator', 'Educator', 'Student'),
    regDateTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (schoolRefNo) REFERENCES school(schoolRefNo) ON DELETE CASCADE,
    FOREIGN KEY (schoolIdNo) REFERENCES school(schoolIdNo) ON DELETE CASCADE
)";

if ($conn->query($sqlusers) === TRUE) 
    echo "<br>" . "Table for MTB-MAL Users created successfully." . "<br>";
else
    echo "Error creating table: " . $conn->error;


// sql to create SCHOOL ADMINISTRATOR table
$sqladmin = "CREATE TABLE IF NOT EXISTS schooladministrator (
    adminNo INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    accRefNo INT(10) UNSIGNED NOT NULL,
    schoolIdNo INT(6) UNSIGNED NOT NULL,
    fullName VARCHAR(70) NOT NULL,    
    adEmpIdNo INT(7) UNSIGNED NOT NULL UNIQUE,
    FOREIGN KEY (accRefNo) REFERENCES mtbmalusers(accRefNo) ON DELETE RESTRICT,
    FOREIGN KEY (schoolIdNo) REFERENCES school(schoolIdNo) ON DELETE CASCADE    
)";

if ($conn->query($sqladmin) === TRUE) 
    echo "<br>" . "Table School Administrators created successfully" . "<br>";
else
    echo "Error creating table: " . $conn->error;


// sql to create EDUCATOR table
$sqleducator = "CREATE TABLE IF NOT EXISTS educator (
    educatorNo INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    accRefNo INT(10) UNSIGNED NOT NULL,
    schoolIdNo INT(6) UNSIGNED NOT NULL,
    fullName VARCHAR(70) NOT NULL, 
    edEmpIdNo INT(7) UNSIGNED NOT NULL UNIQUE,
    FOREIGN KEY (accRefNo) REFERENCES mtbmalusers(accRefNo) ON DELETE RESTRICT,
    FOREIGN KEY (schoolIdNo) REFERENCES school(schoolIdNo) ON DELETE CASCADE
)";

if ($conn->query($sqleducator) === TRUE) 
    echo "<br>" . "Table for Educators created successfully." . "<br>";
else
    echo "Error creating table: " . $conn->error;


// sql to create STUDENT table
$sqlstudent = "CREATE TABLE IF NOT EXISTS student (
    studentNo INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    accRefNo INT(10) UNSIGNED NOT NULL,
    schoolIdNo INT(6) UNSIGNED NOT NULL,
    lrn VARCHAR(12) NOT NULL UNIQUE,
    fullName VARCHAR(70) NOT NULL, 
    parentGuardianName VARCHAR(70) NOT NULL,
    pgRStoStudent ENUM ('Father', 'Mother', 'Guardian', 'Sibling', 'Close Relative'),
    pgDOB DATE NOT NULL,
    pgMaritalStatus VARCHAR(15) NOT NULL,
    pgEmailAdd VARCHAR(30) NOT NULL, 
    pgContactNo VARCHAR(12) NOT NULL,
    FOREIGN KEY (accRefNo) REFERENCES mtbmalusers(accRefNo) ON DELETE RESTRICT,
    FOREIGN KEY (schoolIdNo) REFERENCES school(schoolIdNo) ON DELETE CASCADE
)";

if ($conn->query($sqlstudent) === TRUE) 
    echo "<br>" . "Table for Student created successfully." . "<br>";
else
    echo "Error creating table: " . $conn->error;


// sql to create SUBJECT table
$sqlsubject = "CREATE TABLE IF NOT EXISTS subject (
    subjectRefNo INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subjectIdNo INT(7) UNSIGNED NOT NULL, 
    subjTitle VARCHAR(155) NOT NULL,
    subjDescription TEXT NOT NULL,
    mtLanguage VARCHAR(30) NOT NULL,
    adminCreator INT(6) UNSIGNED NOT NULL,
    assignedEducator INT(10) UNSIGNED NULL,
    schoolIdNo INT(6) UNSIGNED NOT NULL,
    FOREIGN KEY (adminCreator) REFERENCES mtbmalusers(accRefNo) ON DELETE RESTRICT,
    FOREIGN KEY (assignedEducator) REFERENCES mtbmalusers(accRefNo) ON DELETE SET NULL,
    FOREIGN KEY (schoolIdNo) REFERENCES school(schoolIdNo) ON DELETE CASCADE 
)";

if ($conn->query($sqlsubject) === TRUE) 
    echo "<br>" . "Table for Subjects created successfully." . "<br>";
else
    echo "Error creating table: " . $conn->error;


// sql to create LEARNING MATERIAL table "CHAPTER" <learningMaterialRefNo -> successful upload>
$sqllms = "CREATE TABLE IF NOT EXISTS learningmaterial (
    learningMaterialRefNo INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subjectRefNo INT(10) UNSIGNED NOT NULL,
    chapterNo INT(2) UNSIGNED NOT NULL,
    title VARCHAR(155) NOT NULL,
    description TEXT NOT NULL,
    lmNo INT(3) UNSIGNED NOT NULL,
    lmType ENUM('Learning Module','Learning Assessment'),
    diffLevel ENUM ('Easy', 'Intermediate','Hard'),
    schoolIdNo INT(6) UNSIGNED NOT NULL,
    sourceRef TEXT NULL,
    FOREIGN KEY (schoolIdNo) REFERENCES school(schoolIdNo) ON DELETE CASCADE,
    FOREIGN KEY (subjectRefNo) REFERENCES subject(subjectRefNo) ON DELETE CASCADE
)";

if ($conn->query($sqllms) === TRUE) 
    echo "<br>" . "Table for Learning Materials created successfully." . "<br>";
else
    echo "Error creating table: " . $conn->error;


// sql to create LEARNING MODULE table "LESSON"
$sqlmodule = "CREATE TABLE IF NOT EXISTS module (
    moduleId INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lessonNo INT(2) UNSIGNED NOT NULL,
    learningMaterialRefNo INT(10) UNSIGNED NOT NULL,
    title VARCHAR(155) NOT NULL,
    description TEXT NOT NULL,
    schoolIdNo INT(6) UNSIGNED NOT NULL,
    FOREIGN KEY (schoolIdNo) REFERENCES school(schoolIdNo) ON DELETE CASCADE,    
    FOREIGN KEY (learningMaterialRefNo) REFERENCES learningmaterial(learningMaterialRefNo) ON DELETE CASCADE
)";

if ($conn->query($sqlmodule) === TRUE) 
    echo "<br>" . "Table for Learning Modules created successfully." . "<br>";
else
    echo "Error creating table: " . $conn->error;


// sql to create LEARNING ASSESSMENT table "GAMIFIED ASSESSMENTS"
$sqlassessment = "CREATE TABLE IF NOT EXISTS assessment (
    assessmentId INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    assessmentNo INT(2) UNSIGNED NOT NULL,
    learningMaterialRefNo INT(10) UNSIGNED NOT NULL,
    title VARCHAR(155) NOT NULL,
    description TEXT NOT NULL, 
    instruction TEXT NULL,
    scoringRubric TEXT NULL,
    sample TEXT NULL,
    schoolIdNo INT(6) UNSIGNED NOT NULL,
    FOREIGN KEY (schoolIdNo) REFERENCES school(schoolIdNo) ON DELETE CASCADE,  
    FOREIGN KEY (learningMaterialRefNo) REFERENCES learningmaterial(learningMaterialRefNo) ON DELETE CASCADE
)";

if ($conn->query($sqlassessment) === TRUE) 
    echo "<br>" . "Table for Learning Assessments created successfully." . "<br>";
else
    echo "Error creating table: " . $conn->error;


// sql to create TEMPLATE table <insert data for template tables>
$sqltemplate = "CREATE TABLE IF NOT EXISTS template (
    tempRefNo INT(2) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tempName VARCHAR(70) NOT NULL,
    tempDescription TEXT NOT NULL,
    tempType ENUM ('Learning Module', 'Learning Assessment')
)";

if ($conn->query($sqltemplate) === TRUE) 
    echo "<br>" . "Table for Templates created successfully." . "<br>";
else
    echo "Error creating table: " . $conn->error;


// sql to create LEARNING CONTENT table "MODULE TEMPLATE"
$sqlmodtemp = "CREATE TABLE IF NOT EXISTS learningcontent (
    modTempNo INT(3) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tempRefNo INT(2) UNSIGNED NOT NULL,
    topicNo INT(2) NOT NULL, 
    useby INT(10) UNSIGNED NULL,
    topic VARCHAR(155) NOT NULL,
    contentText TEXT NOT NULL,
    moduleId INT(6) UNSIGNED NOT NULL, 
    lessonNo INT(2) UNSIGNED NOT NULL, 
    uploadDateTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tempRefNo) REFERENCES template(tempRefNo) ON DELETE RESTRICT,
    FOREIGN KEY (moduleId) REFERENCES module(moduleId) ON DELETE CASCADE,
    FOREIGN KEY (useby) REFERENCES mtbmalusers(accRefNo) ON DELETE SET NULL
)";

if ($conn->query($sqlmodtemp) === TRUE) 
    echo "<br>" . "Table for Learning Contents created successfully." . "<br>";
else
    echo "Error creating table: " . $conn->error;



// sql to create ASSESSMENT ITEM table "GAME TEMPLATE"
$sqlitemtemp = "CREATE TABLE IF NOT EXISTS assessmentitems (
    gameTempNo INT(3) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tempRefNo INT(2) UNSIGNED NOT NULL,    
    assessmentId INT(6) UNSIGNED NOT NULL,
    useby INT(10) UNSIGNED NULL,
    assessmentNo INT(2) UNSIGNED NOT NULL, 
    itemNo INT(2) NOT NULL,
    iQuestion TEXT NOT NULL,
    correctAnswer VARCHAR(255) NOT NULL,
    uploadDateTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tempRefNo) REFERENCES template(tempRefNo) ON DELETE RESTRICT,
    FOREIGN KEY (assessmentId) REFERENCES assessment(assessmentId) ON DELETE CASCADE,
    FOREIGN KEY (useby) REFERENCES mtbmalusers(accRefNo) ON DELETE SET NULL
)";

if ($conn->query($sqlitemtemp) === TRUE) 
    echo "<br>" . "Table for Assessment Items created successfully." . "<br>";
else
    echo "Error creating table: " . $conn->error;



// sql to create BLOB table "SUPPORTING MEDIA"
$sqlfile = "CREATE TABLE IF NOT EXISTS supportingmedia (
    fileNo INT(4) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    learningMaterialRefNo INT(10) UNSIGNED NOT NULL,
    gameTempNo INT(3) UNSIGNED NULL,
    modTempNo INT(3) UNSIGNED NULL,
    accRefNo INT(10) UNSIGNED NOT NULL,
    filename VARCHAR(255) NULL,
    filedata BLOB NULL,
    FOREIGN KEY (learningMaterialRefNo) REFERENCES learningmaterial(learningMaterialRefNo) ON DELETE RESTRICT,
    FOREIGN KEY (gameTempNo) REFERENCES assessmentitems(gameTempNo) ON DELETE SET NULL,
    FOREIGN KEY (modTempNo) REFERENCES learningcontent(modTempNo) ON DELETE SET NULL,
    FOREIGN KEY (accRefNo) REFERENCES mtbmalusers(accRefNo) ON DELETE RESTRICT
)";

if ($conn->query($sqlfile) === TRUE) 
    echo "<br>" . "Table for Supporting Media created successfully." . "<br>";
else
    echo "Error creating table: " . $conn->error;


// sql to create ENROLLED STUDENTS table 
$sqlenroll = "CREATE TABLE IF NOT EXISTS enrolled_students (
    enrollmentNo INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
    subjectRefNo INT(10) UNSIGNED NOT NULL,
    assignedEducator INT(10) UNSIGNED NOT NULL,
    studentAccRefNo INT(10) UNSIGNED NOT NULL,
    status ENUM('Enrolled', 'Removed'),
    FOREIGN KEY (subjectRefNo) REFERENCES subject(subjectRefNo) ON DELETE CASCADE,
    FOREIGN KEY (assignedEducator) REFERENCES mtbmalusers(accRefNo) ON DELETE RESTRICT,
    FOREIGN KEY (studentAccRefNo) REFERENCES mtbmalusers(accRefNo) ON DELETE RESTRICT
)";

if ($conn->query($sqlenroll) === TRUE) 
    echo "<br>" . "Table for Enrolled Students created successfully." . "<br>";
else
    echo "Error creating table: " . $conn->error;


// sql to create STUDENT ASSESSMENT ANSWERS table 
$sqlans = "CREATE TABLE IF NOT EXISTS student_assessment_answers (
    answerId INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    stLRN VARCHAR(12) NOT NULL,
    assessmentId INT(6) UNSIGNED NOT NULL,
    assessmentItemId INT(3) UNSIGNED NOT NULL,
    studentAnswer VARCHAR(255) NOT NULL,
    isCorrect BOOLEAN DEFAULT NULL,
    reviewedBy INT(10) UNSIGNED NULL,
    reviewDateTime TIMESTAMP NULL DEFAULT NULL,
    remarks TEXT NULL,
    FOREIGN KEY (stLRN) REFERENCES student(lrn) ON DELETE CASCADE,
    FOREIGN KEY (assessmentId) REFERENCES assessment(assessmentId) ON DELETE CASCADE,
    FOREIGN KEY (assessmentItemId) REFERENCES assessmentitems(gameTempNo) ON DELETE CASCADE,
    FOREIGN KEY (reviewedBy) REFERENCES mtbmalusers(accRefNo) ON DELETE SET NULL
)";

if ($conn->query($sqlans) === TRUE) 
    echo "<br>" . "Table for Student Assessment Answers created successfully." . "<br>";
else
    echo "Error creating table: " . $conn->error;


// sql to create STUDENT ASSESSMENT RESULTS table 
$sqlresult = "CREATE TABLE IF NOT EXISTS student_assessment_result (
    resultId INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    stLRN VARCHAR(12) NOT NULL,
    assessmentId INT(6) UNSIGNED NOT NULL,
    totalItems INT UNSIGNED NOT NULL,
    correctAnswers INT UNSIGNED NOT NULL,
    rawScore DECIMAL(5,2) GENERATED ALWAYS AS (correctAnswers / totalItems * 100) STORED,
    remarks TEXT NULL,
    finalizedBy INT(10) UNSIGNED NULL,
    finalDateTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (stLRN) REFERENCES student(lrn) ON DELETE CASCADE,
    FOREIGN KEY (assessmentId) REFERENCES assessment(assessmentId) ON DELETE CASCADE,
    FOREIGN KEY (finalizedBy) REFERENCES mtbmalusers(accRefNo) ON DELETE SET NULL
)";

if ($conn->query($sqlresult) === TRUE) 
    echo "<br>" . "Table for Student Assessment Results created successfully." . "<br>";
else
    echo "Error creating table: " . $conn->error;


// Close the database connection
$conn->close();
?>


