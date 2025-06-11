<?php
session_start();
require_once '../sql/db_connect.php';

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["upload_quest"])) {
    // Get schoolIdNo from session (set default if not logged in)
    $schoolIdNo = $_SESSION['schoolIdNo'] ?? 555555; // use real session var in production

    // Use static values for template links
    $learningMaterialRefNo = 3; // <-- This must EXIST in the learningmaterial table!
    $tempRefNo = 1; // <-- "Quest to Learn" in your template table

    // Required fields
    $assessmentNo = uniqid("QST-");
    $title = "Quest to Learn";
    $description = "Gamified, story-driven questions for knowledge checks.";
    $instruction = trim($_POST['scenarioText'] ?? '');
    $scoringRubric = trim($_POST['passingScore'] ?? '');
    $sample = ""; // Optional, you can update as needed

    // --- 1. Insert into assessment table ---
    $stmt = $conn->prepare("INSERT INTO assessment 
        (assessmentNo, learningMaterialRefNo, title, description, instruction, scoringRubric, sample, schoolIdNo)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sisssssi", 
        $assessmentNo, 
        $learningMaterialRefNo, 
        $title, 
        $description, 
        $instruction, 
        $scoringRubric, 
        $sample, 
        $schoolIdNo
    );
    if ($stmt->execute()) {
        $assessmentId = $conn->insert_id;

        // --- 2. Insert 10 Q&A into assessmentitems ---
        $itemStmt = $conn->prepare("INSERT INTO assessmentitems 
            (assessmentId, tempRefNo, itemNo, iQuestion, correctAnswer, uploadDateTime) 
            VALUES (?, ?, ?, ?, ?, NOW())");

        for ($i = 1; $i <= 10; $i++) {
            $question = trim($_POST["question{$i}"] ?? '');
            $answer   = trim($_POST["answer{$i}"] ?? '');
            if ($question !== '' && $answer !== '') {
                $itemStmt->bind_param("iiiss", $assessmentId, $tempRefNo, $i, $question, $answer);
                $itemStmt->execute();
            }
        }
        $itemStmt->close();

        // --- 3. Redirect or show success ---
        $_SESSION['quest_success'] = "Quest to Learn assessment uploaded successfully!";
        header("Location: templates.php");
        exit();
    } else {
        $_SESSION['quest_error'] = "Database error: " . $stmt->error;
        header("Location: quest_edit.php");
        exit();
    }
} else {
    // If form not submitted properly
    header("Location: quest_edit.php");
    exit();
}
?>
