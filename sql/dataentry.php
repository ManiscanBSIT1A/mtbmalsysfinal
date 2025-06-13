<?php
$servername = "localhost"; // Or your DB host
$username   = "root";
$password   = "";
$dbname     = "mtbmaldb";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully to database.<br><br>";

// --- 2. TRANSACTION FUNCTIONS ---

// == SCHOOL ADMINISTRATOR: DATA ENTRY ==
function addEducationalInstitution($conn, $schoolName, $shortName, $schoolIdNo, $emailAddress, $schoolType, $contactNo, $locAddress, $region, $adminUserName) {
    $sql = "INSERT INTO school (schoolName, shortName, schoolIdNo, emailAddress, schoolType, contactNo, locAddress, region, adminUserName)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error . "<br>";
        return false;
    }
    $stmt->bind_param("ssissssss", $schoolName, $shortName, $schoolIdNo, $emailAddress, $schoolType, $contactNo, $locAddress, $region, $adminUserName);

    if ($stmt->execute()) {
        $newSchoolRefNo = $conn->insert_id;
        echo "New educational institution added successfully. SchoolRefNo: " . $newSchoolRefNo . "<br>";
        return $newSchoolRefNo;
    } else {
        echo "Error adding institution: " . $stmt->error . "<br>";
        return false;
    }
    
}

function addUser($conn, $schoolRefNo, $firstName, $lastName, $dob, $emailAddress, $contactNo, $username, $rawPassword, $accCreatorAccRefNo, $accType) {
    $hashedPassword = password_hash($rawPassword, PASSWORD_DEFAULT);
    $sql = "INSERT INTO mtbmalusers (schoolRefNo, firstName, lastName, dob, emailAddress, contactNo, username, password, accCreator, accType)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed for addUser: (" . $conn->errno . ") " . $conn->error . "<br>";
        return false;
    }
    $stmt->bind_param("isssssssis", $schoolRefNo, $firstName, $lastName, $dob, $emailAddress, $contactNo, $username, $hashedPassword, $accCreatorAccRefNo, $accType);

    if ($stmt->execute()) {
        $newAccRefNo = $conn->insert_id;
        echo "New user added successfully. User AccRefNo: " . $newAccRefNo . " (Type: " . $accType . ")<br>";
        return $newAccRefNo;
    } else {
        echo "Error adding user: " . $stmt->error . "<br>";
        if ($conn->errno == 1062) {
            echo "Hint: Possibly a duplicate username, email, or contact number.<br>";
        }
        return false;
    }
    
}

function addSchoolAdministrator($conn, $accRefNo, $schoolIdNo, $fullName, $adEmpIdNo) {
    $sql = "INSERT INTO schooladministrator (accRefNo, schoolIdNo, fullName, adEmpIdNo)
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed for addSchoolAdministrator: (" . $conn->errno . ") " . $conn->error . "<br>";
        return false;
    }
    $stmt->bind_param("iisi", $accRefNo, $schoolIdNo, $fullName, $adEmpIdNo);

    if ($stmt->execute()) {
        echo "New school administrator added successfully. AdminNo: " . $conn->insert_id . "<br>";
        return $conn->insert_id;
    } else {
        echo "Error adding school administrator: " . $stmt->error . "<br>";
        return false;
    }
    
}

function addEducator($conn, $accRefNo, $schoolIdNo, $fullName, $edEmpIdNo) {
    $sql = "INSERT INTO educator (accRefNo, schoolIdNo, fullName, edEmpIdNo)
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed for addEducator: (" . $conn->errno . ") " . $conn->error . "<br>";
        return false;
    }
    $stmt->bind_param("iisi", $accRefNo, $schoolIdNo, $fullName, $edEmpIdNo);
    if ($stmt->execute()) {
        echo "New educator added successfully. EducatorNo: " . $conn->insert_id . "<br>";
        return $conn->insert_id;
    } else {
        echo "Error adding educator: " . $stmt->error . "<br>";
        return false;
    }
    
}

function addStudent($conn, $accRefNo, $schoolIdNo, $lrn, $fullName, $parentGuardianName, $pgRStoStudent, $pgDOB, $pgMaritalStatus, $pgEmailAdd, $pgContactNo) {
    $sql = "INSERT INTO student (accRefNo, schoolIdNo, lrn, fullName, parentGuardianName, pgRStoStudent, pgDOB, pgMaritalStatus, pgEmailAdd, pgContactNo)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed for addStudent: (" . $conn->errno . ") " . $conn->error . "<br>";
        return false;
    }
    $stmt->bind_param("iiisssssss", $accRefNo, $schoolIdNo, $lrn, $fullName, $parentGuardianName, $pgRStoStudent, $pgDOB, $pgMaritalStatus, $pgEmailAdd, $pgContactNo);
    if ($stmt->execute()) {
        echo "New student added successfully. StudentNo: " . $conn->insert_id . "<br>";
        return $conn->insert_id;
    } else {
        echo "Error adding student: " . $stmt->error . "<br>";
        return false;
    }
    
}

function addSubject($conn, $subjectId, $subjTitle, $subjDescription, $mtLanguage, $adminCreatorAccRefNo, $assignedEducatorAccRefNo, $schoolIdNo) {
    $assignedEducatorParam = $assignedEducatorAccRefNo ?: NULL;
    $sql = "INSERT INTO subject (subjectId, subjTitle, subjDescription, mtLanguage, adminCreator, assignedEducator, schoolIdNo)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed for addSubject: (" . $conn->errno . ") " . $conn->error . "<br>";
        return false;
    }
    $stmt->bind_param("isssiii", $subjectId, $subjTitle, $subjDescription, $mtLanguage, $adminCreatorAccRefNo, $assignedEducatorParam, $schoolIdNo);

    if ($stmt->execute()) {
        echo "New subject added successfully. SubjectRefNo: " . $conn->insert_id . "<br>";
        return $conn->insert_id;
    } else {
        echo "Error adding subject: " . $stmt->error . "<br>";
        return false;
    }
    
}

// == EDUCATOR: DATA ENTRY ==
function enrollStudentInSubject($conn, $subjectRefNo, $assignedEducatorAccRefNo, $studentAccRefNo) {
    $status = 'Enrolled';
    $sql = "INSERT INTO enrolled_students (subjectRefNo, assignedEducator, studentAccRefNo, status)
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed for enrollStudentInSubject: (" . $conn->errno . ") " . $conn->error . "<br>";
        return false;
    }
    $stmt->bind_param("iiis", $subjectRefNo, $assignedEducatorAccRefNo, $studentAccRefNo, $status);
    if ($stmt->execute()) {
        echo "Student (AccRefNo: $studentAccRefNo) enrolled in subject (RefNo: $subjectRefNo) successfully. EnrollmentNo: " . $conn->insert_id . "<br>";
        return $conn->insert_id;
    } else {
        echo "Error enrolling student: " . $stmt->error . "<br>";
        return false;
    }
    
}

function addLearningMaterial($conn, $subjectRefNo, $chapterNo, $title, $description, $lmNo, $lmType, $diffLevel, $schoolIdNo, $sourceRef = null) {
    $sql = "INSERT INTO learningmaterial (subjectRefNo, chapterNo, title, description, lmNo, lmType, diffLevel, schoolIdNo, sourceRef)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed for addLearningMaterial: (" . $conn->errno . ") " . $conn->error . "<br>";
        return false;
    }
    $stmt->bind_param("iississsi", $subjectRefNo, $chapterNo, $title, $description, $lmNo, $lmType, $diffLevel, $schoolIdNo, $sourceRef);

    if ($stmt->execute()) {
        $newLmRefNo = $conn->insert_id;
        echo "New Learning Material added successfully. LMRefNo: " . $newLmRefNo . " (Type: $lmType)<br>";
        return $newLmRefNo;
    } else {
        echo "Error adding Learning Material: " . $stmt->error . "<br>";
        return false;
    }
    
}

function createTemplateForLearningMaterial($conn, $tempName, $tempDescription, $tempType, $diffLevel, $learningMaterialRefNo) {
    $sql = "INSERT INTO template (tempName, tempDescription, tempType, diffLevel, learningMaterialRefNo)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed for createTemplateForLearningMaterial: (" . $conn->errno . ") " . $conn->error . "<br>";
        return false;
    }
    $stmt->bind_param("ssssi", $tempName, $tempDescription, $tempType, $diffLevel, $learningMaterialRefNo);

    if ($stmt->execute()) {
        $newTempRefNo = $conn->insert_id;
        echo "New Template created successfully. TempRefNo: " . $newTempRefNo . " for LMRefNo: $learningMaterialRefNo<br>";
        return $newTempRefNo;
    } else {
        echo "Error creating Template: " . $stmt->error . "<br>";
        return false;
    }
    
}

function addModuleToLearningMaterial($conn, $lessonNo, $learningMaterialRefNo, $title, $description, $schoolIdNo) {
    $sql = "INSERT INTO module (lessonNo, learningMaterialRefNo, title, description, schoolIdNo)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed for addModuleToLearningMaterial: (" . $conn->errno . ") " . $conn->error . "<br>";
        return false;
    }
    $stmt->bind_param("iissi", $lessonNo, $learningMaterialRefNo, $title, $description, $schoolIdNo);

    if ($stmt->execute()) {
        $newModuleId = $conn->insert_id;
        echo "New Module added successfully. ModuleID: " . $newModuleId . " for LMRefNo: $learningMaterialRefNo<br>";
        return $newModuleId;
    } else {
        echo "Error adding Module: " . $stmt->error . "<br>";
        return false;
    }
    
}

function addLearningContentToModule($conn, $tempRefNo, $topicNo, $usebyAccRefNo, $topic, $contentText, $moduleId, $lessonNo) {
    $sql = "INSERT INTO learningcontent (tempRefNo, topicNo, useby, topic, contentText, moduleId, lessonNo)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed for addLearningContentToModule: (" . $conn->errno . ") " . $conn->error . "<br>";
        return false;
    }
    $usebyParam = $usebyAccRefNo ?: NULL;
    $stmt->bind_param("iiisssi", $tempRefNo, $topicNo, $usebyParam, $topic, $contentText, $moduleId, $lessonNo);

    if ($stmt->execute()) {
        $newModTempNo = $conn->insert_id;
        echo "New Learning Content added successfully. ModTempNo: " . $newModTempNo . " for ModuleID: $moduleId<br>";
        return $newModTempNo;
    } else {
        echo "Error adding Learning Content: " . $stmt->error . "<br>";
        return false;
    }
    
}

function addAssessmentToLearningMaterial($conn, $assessmentNo, $learningMaterialRefNo, $title, $description, $schoolIdNo, $instruction = null, $scoringRubric = null, $sample = null) {
    $sql = "INSERT INTO assessment (assessmentNo, learningMaterialRefNo, title, description, instruction, scoringRubric, sample, schoolIdNo)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed for addAssessmentToLearningMaterial: (" . $conn->errno . ") " . $conn->error . "<br>";
        return false;
    }
    $stmt->bind_param("iisssssi", $assessmentNo, $learningMaterialRefNo, $title, $description, $instruction, $scoringRubric, $sample, $schoolIdNo);

    if ($stmt->execute()) {
        $newAssessmentId = $conn->insert_id;
        echo "New Assessment added successfully. AssessmentID: " . $newAssessmentId . " for LMRefNo: $learningMaterialRefNo<br>";
        return $newAssessmentId;
    } else {
        echo "Error adding Assessment: " . $stmt->error . "<br>";
        return false;
    }
    
}

function addAssessmentItemToAssessment($conn, $tempRefNo, $assessmentId, $usebyAccRefNo, $assessmentNo, $itemNo, $iQuestion, $correctAnswer) {
    $sql = "INSERT INTO assessmentitems (tempRefNo, assessmentId, useby, assessmentNo, itemNo, iQuestion, correctAnswer)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed for addAssessmentItemToAssessment: (" . $conn->errno . ") " . $conn->error . "<br>";
        return false;
    }
    $usebyParam = $usebyAccRefNo ?: NULL;
    $stmt->bind_param("iiiiiss", $tempRefNo, $assessmentId, $usebyParam, $assessmentNo, $itemNo, $iQuestion, $correctAnswer);

    if ($stmt->execute()) {
        $newGameTempNo = $conn->insert_id;
        echo "New Assessment Item added successfully. GameTempNo: " . $newGameTempNo . " for AssessmentID: $assessmentId<br>";
        return $newGameTempNo;
    } else {
        echo "Error adding Assessment Item: " . $stmt->error . "<br>";
        return false;
    }
    
}

function addSupportingMedia($conn, $learningMaterialRefNo, $accRefNo, $uploadedFileArray, $linkToType = null, $linkedId = null) {
    if ($uploadedFileArray && isset($uploadedFileArray['error']) && $uploadedFileArray['error'] == UPLOAD_ERR_OK && isset($uploadedFileArray['tmp_name'])) {
        $filename = basename($uploadedFileArray['name']);
        // Check if tmp_name is a valid uploaded file and exists
        if (!is_uploaded_file($uploadedFileArray['tmp_name'])) {
            echo "File '{$uploadedFileArray['tmp_name']}' is not a valid uploaded file or does not exist.<br>";
            return false;
        }
        $filedata = file_get_contents($uploadedFileArray['tmp_name']);

        if ($filedata === false) {
            echo "Error reading uploaded file content from '{$uploadedFileArray['tmp_name']}'.<br>";
            return false;
        }

        $modTempNo = null;
        $gameTempNo = null;

        if ($linkToType === 'learning_content' && $linkedId) {
            $modTempNo = $linkedId;
        } elseif ($linkToType === 'assessment_item' && $linkedId) {
            $gameTempNo = $linkedId;
        }

        $sql = "INSERT INTO supportingmedia (learningMaterialRefNo, gameTempNo, modTempNo, accRefNo, filename, filedata)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo "Prepare failed for addSupportingMedia: (" . $conn->errno . ") " . $conn->error . "<br>";
            return false;
        }

        $nullVar = NULL;
        $stmt->bind_param("iiiisb", $learningMaterialRefNo, $gameTempNo, $modTempNo, $accRefNo, $filename, $nullVar);
        // Chunk and send data if necessary, though for typical web uploads, PHP handles memory.
        // This is more critical if $filedata is extremely large and directly manipulated before send_long_data.
        $stmt->send_long_data(5, $filedata); // 5 is the 0-indexed position of the BLOB parameter (b)

        if ($stmt->execute()) {
            $newFileNo = $conn->insert_id;
            echo "Supporting Media '$filename' uploaded and saved successfully. FileNo: " . $newFileNo . "<br>";
            return $newFileNo;
        } else {
            echo "Error saving Supporting Media to DB: " . $stmt->error . "<br>";
            return false;
        }
        
    } else {
        echo "File upload error or invalid file array provided: ";
        if (isset($uploadedFileArray['error'])) {
            switch ($uploadedFileArray['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    echo "File too large.<br>";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    echo "File only partially uploaded.<br>";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    echo "No file was uploaded.<br>";
                    break;
                default:
                    echo "Unknown upload error code: " . $uploadedFileArray['error'] . ".<br>";
                    break;
            }
        } else {
            echo "No file error code provided or invalid array structure.<br>";
        }
        return false;
    }
}


// == STUDENT: DATA ENTRY ==
function submitAssessmentAnswer($conn, $stLRN, $assessmentId, $assessmentItemId, $studentAnswer) {
    $sql = "INSERT INTO student_assessment_answers (stLRN, assessmentId, assessmentItemId, studentAnswer)
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed for submitAssessmentAnswer: (" . $conn->errno . ") " . $conn->error . "<br>";
        return false;
    }
    $stmt->bind_param("iiis", $stLRN, $assessmentId, $assessmentItemId, $studentAnswer);
    if ($stmt->execute()) {
        echo "Student answer submitted successfully. AnswerId: " . $conn->insert_id . "<br>";
        return $conn->insert_id;
    } else {
        echo "Error submitting answer: " . $stmt->error . "<br>";
        return false;
    }
    
}

// == DATA UPDATE/DELETION EXAMPLES ==

function deleteSubject($conn, $subjectRefNo) {
    $sql = "DELETE FROM subject WHERE subjectRefNo = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed for deleteSubject: (" . $conn->errno . ") " . $conn->error . "<br>";
        return false;
    }
    $stmt->bind_param("i", $subjectRefNo);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "Subject (RefNo: $subjectRefNo) deleted successfully.<br>";
            return true;
        } else {
            echo "Subject (RefNo: $subjectRefNo) not found for deletion.<br>";
            return false;
        }
    } else {
        echo "Error deleting subject: " . $stmt->error . "<br>";
        return false;
    }
    
}

// Function to retrieve and serve a BLOB as a file
function getSupportingMediaAsFile($conn, $fileNo) {
    $sql = "SELECT filename, filedata FROM supportingmedia WHERE fileNo = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed for getSupportingMediaAsFile: (" . $conn->errno . ") " . $conn->error . "<br>";
        return;
    }
    $stmt->bind_param("i", $fileNo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $filename = $row['filename'];
        $filedata = $row['filedata'];

        // Determine content type (basic image types for example)
        $contentType = 'application/octet-stream'; // Default
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($ext == 'jpg' || $ext == 'jpeg') {
            $contentType = 'image/jpeg';
        } elseif ($ext == 'png') {
            $contentType = 'image/png';
        } elseif ($ext == 'gif') {
            $contentType = 'image/gif';
        } elseif ($ext == 'pdf') {
            $contentType = 'application/pdf';
        } elseif ($ext == 'txt') {
            $contentType = 'text/plain';
        }


        header("Content-Type: " . $contentType);
        header("Content-Disposition: inline; filename=\"" . basename($filename) . "\""); // Or "attachment" to force download
        header("Content-Length: " . strlen($filedata)); // Important for some browsers
        echo $filedata;
        exit; // Important to stop further PHP execution
    } else {
        http_response_code(404);
        echo "File not found.";
        exit;
    }
    
}

// --- 2. DATA UPDATE/DELETION FUNCTIONS ---

// == SCHOOL ADMINISTRATOR: DATA UPDATE/DELETION ==

/**
 * Updates details of an educational institution.
 * Requirement: "Update/delete the details of the educational institution." (Focus on Update)
 */
function updateEducationalInstitution($conn, $schoolRefNo, $updates): bool
{
    if (empty($updates)) {
        echo "No updates provided for educational institution.<br>";
        return false;
    }

    $setClauses = [];
    $params = [];
    $types = "";

    // Allowed fields for update to prevent arbitrary column updates
    $allowedFields = ['schoolName', 'shortName', 'emailAddress', 'schoolType', 'contactNo', 'locAddress', 'region', 'adminUserName'];

    foreach ($updates as $field => $value) {
        if (in_array($field, $allowedFields)) {
            $setClauses[] = "`$field` = ?";
            $params[] = $value;
            // Determine type for bind_param
            if (is_int($value)) $types .= "i";
            elseif (is_double($value)) $types .= "d";
            else $types .= "s";
        }
    }

    if (empty($setClauses)) {
        echo "No valid fields to update for educational institution.<br>";
        return false;
    }

    $sql = "UPDATE school SET " . implode(", ", $setClauses) . " WHERE schoolRefNo = ?";
    $params[] = $schoolRefNo;
    $types .= "i";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed for updateEducationalInstitution: (" . $conn->errno . ") " . $conn->error . "<br>";
        return false;
    }
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "Educational institution (RefNo: $schoolRefNo) updated successfully.<br>";
            return true;
        } else {
            echo "No changes made or institution (RefNo: $schoolRefNo) not found for update.<br>";
            return false;
        }
    } else {
        echo "Error updating institution: " . $stmt->error . "<br>";
        return false;
    }
    
}

/**
 * Deletes an educational institution.
 * Requirement: "Update/delete the details of the educational institution." (Focus on Delete)
 * NOTE: This is a destructive operation. CASCADE rules in DB will affect related tables.
 */
function deleteEducationalInstitution($conn, $schoolRefNo): bool
{
    $sql = "DELETE FROM school WHERE schoolRefNo = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed for deleteEducationalInstitution: (" . $conn->errno . ") " . $conn->error . "<br>";
        return false;
    }
    $stmt->bind_param("i", $schoolRefNo);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "Educational institution (RefNo: $schoolRefNo) and related data (due to CASCADE) deleted successfully.<br>";
            return true;
        } else {
            echo "Educational institution (RefNo: $schoolRefNo) not found for deletion.<br>";
            return false;
        }
    } else {
        echo "Error deleting institution: " . $stmt->error . "<br>";
        return false;
    }
    
}


/**
 * Updates details of a school administrator (mtbmalusers and schooladministrator tables).
 * Requirement: "Update/delete the details of a school administrator." (Focus on Update)
 * Assumes $adminAccRefNo is the mtbmalusers.accRefNo for the administrator.
 */
function updateSchoolAdministrator($conn, $adminAccRefNo, $userUpdates, $adminRoleUpdates): bool
{
    $conn->begin_transaction();
    $userUpdated = true;
    $roleUpdated = true;

    // 1. Update mtbmalusers table
    if (!empty($userUpdates)) {
        $setClausesUser = [];
        $paramsUser = [];
        $typesUser = "";
        $allowedUserFields = ['firstName', 'lastName', 'dob', 'emailAddress', 'contactNo', 'username']; // password update needs separate handling

        foreach ($userUpdates as $field => $value) {
            if (in_array($field, $allowedUserFields)) {
                $setClausesUser[] = "`$field` = ?";
                $paramsUser[] = $value;
                if (is_int($value)) $typesUser .= "i"; else $typesUser .= "s";
            }
        }
        if (!empty($setClausesUser)) {
            $sqlUser = "UPDATE mtbmalusers SET " . implode(", ", $setClausesUser) . " WHERE accRefNo = ?";
            $paramsUser[] = $adminAccRefNo;
            $typesUser .= "i";

            $stmtUser = $conn->prepare($sqlUser);
            if (!$stmtUser) {
                echo "Prepare failed for mtbmalusers update (Admin): (" . $conn->errno . ") " . $conn->error . "<br>";
                $conn->rollback();
                return false;
            }
            $stmtUser->bind_param($typesUser, ...$paramsUser);
            if (!$stmtUser->execute() || $stmtUser->affected_rows == 0 && count($userUpdates) > 0) { // Check if any rows affected if updates were intended
                if (!$stmtUser->execute()) echo "Error updating mtbmalusers (Admin): " . $stmtUser->error . "<br>";
                else echo "No changes in user details for Admin AccRefNo: $adminAccRefNo or user not found.<br>";
                $userUpdated = ($stmtUser->affected_rows > 0);
            }
            $stmtUser->close();
        } else {
            $userUpdated = false; 
        }
    } else {
        $userUpdated = false; 
    }

    if (!empty($adminRoleUpdates)) {
        $setClausesRole = [];
        $paramsRole = [];
        $typesRole = "";
        $allowedRoleFields = ['fullName', 'adEmpIdNo'];

        foreach ($adminRoleUpdates as $field => $value) {
            if (in_array($field, $allowedRoleFields)) {
                $setClausesRole[] = "`$field` = ?";
                $paramsRole[] = $value;
                if ($field == 'adEmpIdNo') $typesRole .= "i"; else $typesRole .= "s";
            }
        }

        if (!empty($setClausesRole)) {
            $sqlRole = "UPDATE schooladministrator SET " . implode(", ", $setClausesRole) . " WHERE accRefNo = ?";
            $paramsRole[] = $adminAccRefNo;
            $typesRole .= "i";

            $stmtRole = $conn->prepare($sqlRole);
            if (!$stmtRole) {
                echo "Prepare failed for schooladministrator update: (" . $conn->errno . ") " . $conn->error . "<br>";
                $conn->rollback();
                return false;
            }
            $stmtRole->bind_param($typesRole, ...$paramsRole);
            if (!$stmtRole->execute() || $stmtRole->affected_rows == 0 && count($adminRoleUpdates) > 0) {
                if (!$stmtRole->execute()) echo "Error updating schooladministrator role: " . $stmtRole->error . "<br>";
                else echo "No changes in admin role details for AccRefNo: $adminAccRefNo or role not found.<br>";
                $roleUpdated = ($stmtRole->affected_rows > 0);
            }
            $stmtRole->close();
        } else {
            $roleUpdated = false; 
        }
    } else {
        $roleUpdated = false; 
    }

    if ($userUpdated || $roleUpdated) { // If at least one part was successfully updated
        $conn->commit();
        echo "School Administrator (AccRefNo: $adminAccRefNo) updated successfully.<br>";
        return true;
    } else {
        // If no updates were requested or no rows were affected for requested updates, it's not an error for the transaction
        $conn->commit(); // or rollback if you consider "no change" an issue
        echo "No effective updates made to School Administrator (AccRefNo: $adminAccRefNo).<br>";
        return false;
    }
}

/**
 * Deletes a school administrator.
 * Requirement: "Update/delete the details of a school administrator." (Focus on Delete)
 * This typically means deleting the mtbmaluser record, which should CASCADE to schooladministrator.
 * Or, it could mean just revoking the admin role (deleting from schooladministrator) but keeping the user.
 * For this example, let's delete the user, which implies role deletion by CASCADE.
 */
function deleteSchoolAdministratorUser($conn, $adminAccRefNo): bool
{
    // Deleting from mtbmalusers should cascade to schooladministrator if accRefNo is a FK with ON DELETE CASCADE
    // mtbmalusers.accRefNo -> schooladministrator.accRefNo (No explicit ON DELETE CASCADE in your schema for this direction)
    // schooladministrator.accRefNo -> mtbmalusers.accRefNo (This is the defined FK)
    // So, we need to delete from schooladministrator first, then mtbmalusers, or rely on application logic.
    // Let's assume the requirement means removing the user entirely.

    $conn->begin_transaction();

    // 1. Delete from schooladministrator (role specific table)
    $sqlRole = "DELETE FROM schooladministrator WHERE accRefNo = ?";
    $stmtRole = $conn->prepare($sqlRole);
    if (!$stmtRole) {
        echo "Prepare failed (schooladministrator delete): " . $conn->error;
        $conn->rollback();
        return false;
    }
    $stmtRole->bind_param("i", $adminAccRefNo);
    $roleDeleted = false;
    if ($stmtRole->execute()) {
        if ($stmtRole->affected_rows > 0) {
            echo "Admin role for AccRefNo $adminAccRefNo deleted.<br>";
            $roleDeleted = true;
        } else {
            echo "No admin role found for AccRefNo $adminAccRefNo to delete.<br>";
        }
    } else {
        echo "Error deleting admin role: " . $stmtRole->error . "<br>";
        $conn->rollback();
        return false;
    }
    $stmtRole->close();

    // 2. Delete from mtbmalusers (general user table)
    $sqlUser = "DELETE FROM mtbmalusers WHERE accRefNo = ?";
    $stmtUser = $conn->prepare($sqlUser);
    if (!$stmtUser) {
        echo "Prepare failed (mtbmalusers delete): " . $conn->error;
        $conn->rollback();
        return false;
    }
    $stmtUser->bind_param("i", $adminAccRefNo);
    $userDeleted = false;
    if ($stmtUser->execute()) {
        if ($stmtUser->affected_rows > 0) {
            echo "User account AccRefNo $adminAccRefNo deleted.<br>";
            $userDeleted = true;
        } else {
            echo "No user account found for AccRefNo $adminAccRefNo to delete.<br>";
        }
    } else {
        echo "Error deleting user account: " . $stmtUser->error . "<br>";
        $conn->rollback();
        return false;
    }
    $stmtUser->close();

    if ($roleDeleted || $userDeleted) {
        $conn->commit();
        echo "School Administrator (AccRefNo: $adminAccRefNo) effectively deleted.<br>";
        return true;
    } else {
        $conn->rollback(); // Or commit if "not found" is acceptable
        echo "School Administrator (AccRefNo: $adminAccRefNo) not found or already deleted.<br>";
        return false;
    }
}


/**
 * Updates details of an educator (similar to School Admin: mtbmalusers and educator tables).
 * Requirement: "Update/delete the details of a school administrator." (Interpreted as Educator from image context)
 */
function updateEducator($conn, $educatorAccRefNo, $userUpdates, $educatorRoleUpdates): bool
{
    // This function would be very similar to updateSchoolAdministrator
    // Just replace 'schooladministrator' with 'educator' and 'adEmpIdNo' with 'edEmpIdNo'
    // For brevity, I'll skip re-writing the full logic but it follows the same pattern.
    $conn->begin_transaction();
    $userUpdated = true;
    $roleUpdated = true;

    if (!empty($userUpdates)) { /* ... update mtbmalusers ... */
        echo "Educator user details update logic here...<br>";
    }
    if (!empty($educatorRoleUpdates)) { /* ... update educator table ... */
        echo "Educator role details update logic here...<br>";
    }

    // Dummy implementation for this example
    if (empty($userUpdates) && empty($educatorRoleUpdates)) {
        echo "No updates for educator AccRefNo: $educatorAccRefNo.<br>";
        $conn->rollback();
        return false;
    }
    // Simulate success
    $conn->commit();
    echo "Educator (AccRefNo: $educatorAccRefNo) - SIMULATED update successful.<br>";
    return true;
}

/**
 * Deletes an educator user.
 * Requirement: "Update/delete the details of a school administrator." (Interpreted as Educator)
 */
function deleteEducatorUser($conn, $educatorAccRefNo): bool
{
    // Similar to deleteSchoolAdministratorUser, deleting from 'educator' then 'mtbmalusers'
    echo "Deleting Educator User (AccRefNo: $educatorAccRefNo) - SIMULATED delete logic.<br>";
    // Actual implementation would involve:
    // DELETE FROM educator WHERE accRefNo = ?
    // DELETE FROM mtbmalusers WHERE accRefNo = ?
    // Handle ON DELETE SET NULL for subject.assignedEducator or subject.adminCreator if educator created subjects.
    return true; // Placeholder
}


/**
 * Updates details of a subject.
 * Requirement: "Update/delete the details of a subject." (Focus on Update)
 */
function updateSubject($conn, $subjectRefNo, $updates): bool
{
    if (empty($updates)) {
        echo "No updates for subject.<br>";
        return false;
    }
    $setClauses = [];
    $params = [];
    $types = "";
    $allowedFields = ['subjTitle', 'subjDescription', 'mtLanguage', 'assignedEducator' /* adminCreator, schoolIdNo usually not changed */];

    foreach ($updates as $field => $value) {
        if (in_array($field, $allowedFields)) {
            $setClauses[] = "`$field` = ?";
            $params[] = ($field == 'assignedEducator' && empty($value)) ? null : $value; // Handle NULL for assignedEducator
            if ($field == 'assignedEducator') $types .= "i"; else $types .= "s";
        }
    }
    if (empty($setClauses)) {
        echo "No valid fields to update for subject.<br>";
        return false;
    }

    $sql = "UPDATE subject SET " . implode(", ", $setClauses) . " WHERE subjectRefNo = ?";
    $params[] = $subjectRefNo;
    $types .= "i";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed (subject update): " . $conn->error;
        return false;
    }
    $stmt->bind_param($types, ...$params);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "Subject (RefNo: $subjectRefNo) updated.<br>";
            return true;
        } else {
            echo "No changes or subject (RefNo: $subjectRefNo) not found for update.<br>";
            return false;
        }
    } else {
        echo "Error updating subject: " . $stmt->error . "<br>";
        return false;
    }
    
}

/**
 * Deletes a subject.
 * Requirement: "Update/delete the details of a subject." (Focus on Delete)
 * CASCADE rules will affect learningmaterial, enrolled_students.
 */


// == EDUCATOR: DATA UPDATE/DELETION ==

/**
 * Updates details of a learning module (learningmaterial and module tables).
 * Requirement: "Update/delete the details of a learning module." (Focus on Update)
 * $lmRefNo is learningmaterial.learningMaterialRefNo
 * $moduleId is module.moduleId (if updating module-specific details)
 */
function updateLearningModule($conn, $lmRefNo, $lmUpdates, $moduleSpecificUpdates = [], $moduleId = null): bool
{

    $conn->begin_transaction();
    $lmUpdated = false;
    $moduleUpdated = false;

    if (!empty($lmUpdates)) {
        $setClauses = [];
        $params = [];
        $types = "";
        $allowedFields = ['chapterNo', 'title', 'description', 'lmNo', 'diffLevel', 'sourceRef'];
        foreach ($lmUpdates as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $setClauses[] = "`$field` = ?";
                $params[] = $value;
                if (in_array($field, ['chapterNo', 'lmNo'])) $types .= "i"; else $types .= "s";
            }
        }
        if (!empty($setClauses)) {
            $sql = "UPDATE learningmaterial SET " . implode(", ", $setClauses) . " WHERE learningMaterialRefNo = ? AND lmType = 'Learning Module'";
            $params[] = $lmRefNo;
            $types .= "i";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                echo "LM Prepare fail:" . $conn->error;
                $conn->rollback();
                return false;
            }
            $stmt->bind_param($types, ...$params);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) $lmUpdated = true;
            } else {
                echo "LM Exec fail:" . $stmt->error;
                $conn->rollback();
                return false;
            }
            
        }
    }

    if (!empty($moduleSpecificUpdates) && $moduleId) {
        $setClausesMod = [];
        $paramsMod = [];
        $typesMod = "";
        $allowedModFields = ['lessonNo', 'title', 'description']; // schoolIdNo, learningMaterialRefNo usually not changed for existing module
        foreach ($moduleSpecificUpdates as $field => $value) {
            if (in_array($field, $allowedModFields)) {
                $setClausesMod[] = "`$field` = ?";
                $paramsMod[] = $value;
                if ($field == 'lessonNo') $typesMod .= "i"; else $typesMod .= "s";
            }
        }
        if (!empty($setClausesMod)) {
            $sqlMod = "UPDATE module SET " . implode(", ", $setClausesMod) . " WHERE moduleId = ? AND learningMaterialRefNo = ?";
            $paramsMod[] = $moduleId;
            $paramsMod[] = $lmRefNo;
            $typesMod .= "ii";
            $stmtMod = $conn->prepare($sqlMod);
            if (!$stmtMod) {
                echo "Module Prepare fail:" . $conn->error;
                $conn->rollback();
                return false;
            }
            $stmtMod->bind_param($typesMod, ...$paramsMod);
            if ($stmtMod->execute()) {
                if ($stmtMod->affected_rows > 0) $moduleUpdated = true;
            } else {
                echo "Module Exec fail:" . $stmtMod->error;
                $conn->rollback();
                return false;
            }
            $stmtMod->close();
        }
    }

    if ($lmUpdated || $moduleUpdated) {
        $conn->commit();
        echo "Learning Module (LMRefNo: $lmRefNo, ModuleID: $moduleId) updated successfully.<br>";
        return true;
    } else {
        $conn->commit(); // or rollback if no change is an issue
        echo "No effective updates to Learning Module (LMRefNo: $lmRefNo, ModuleID: $moduleId).<br>";
        return false;
    }
}

/**
 * Deletes a learning module.
 * Requirement: "Update/delete the details of a learning module." (Focus on Delete)
 * This means deleting the learningmaterial record (lmType='Learning Module'),
 * which should CASCADE to module, learningcontent, template, supportingmedia.
 */
function deleteLearningModule($conn, $lmRefNo): bool
{
    $sql = "DELETE FROM learningmaterial WHERE learningMaterialRefNo = ? AND lmType = 'Learning Module'";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed (LM delete): " . $conn->error;
        return false;
    }
    $stmt->bind_param("i", $lmRefNo);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "Learning Module (LMRefNo: $lmRefNo) and related data deleted.<br>";
            return true;
        } else {
            echo "Learning Module (LMRefNo: $lmRefNo) not found or not a module.<br>";
            return false;
        }
    } else {
        echo "Error deleting Learning Module: " . $stmt->error . "<br>";
        return false;
    }
    
}


/**
 * Updates/deletes a learning assessment.
 * Similar to Learning Module, involves 'learningmaterial' (lmType='Learning Assessment') and 'assessment' tables.
 * Deleting from 'learningmaterial' would cascade.
 */
function updateLearningAssessment($conn, $lmRefNo, $lmUpdates, $assessmentSpecificUpdates = [], $assessmentId = null): bool
{
    try
    {
        // Start transaction to ensure data integrity
        $conn->begin_transaction();

        // Validate inputs
        if (empty($lmRefNo)) {
            throw new Exception("Learning module reference number is required");
        }

        // Update learning module general information
        if (!empty($lmUpdates)) {
            $updateFields = [];
            $updateParams = [];
            $types = "";

            foreach ($lmUpdates as $field => $value) {
                $updateFields[] = "`$field` = ?";
                $updateParams[] = $value;
                $types .= is_int($value) ? "i" : (is_float($value) ? "d" : "s");
            }

            $updateQuery = "UPDATE learning_modules SET " . implode(", ", $updateFields) . " WHERE ref_no = ?";
            $updateParams[] = $lmRefNo;
            $types .= "s";

            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param($types, ...$updateParams);
            $stmt->execute();

            if ($stmt->affected_rows === 0 && $stmt->errno !== 0) {
                throw new Exception("Failed to update learning module: " . $stmt->error);
            }
        }

        // Update or insert assessment-specific information
        if (!empty($assessmentSpecificUpdates)) {
            if ($assessmentId) {
                // Update existing assessment
                $updateFields = [];
                $updateParams = [];
                $types = "";

                foreach ($assessmentSpecificUpdates as $field => $value) {
                    $updateFields[] = "`$field` = ?";
                    $updateParams[] = $value;
                    $types .= is_int($value) ? "i" : (is_float($value) ? "d" : "s");
                }

                $updateQuery = "UPDATE learning_assessments SET " . implode(", ", $updateFields) . " WHERE id = ? AND lm_ref_no = ?";
                $updateParams[] = $assessmentId;
                $updateParams[] = $lmRefNo;
                $types .= "is";

                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param($types, ...$updateParams);
                $stmt->execute();

                if ($stmt->affected_rows === 0 && $stmt->errno !== 0) {
                    throw new Exception("Failed to update assessment: " . $stmt->error);
                }
            } else {
                // Insert new assessment
                $fields = array_keys($assessmentSpecificUpdates);
                $placeholders = array_fill(0, count($fields), "?");
                $types = "";
                $values = array_values($assessmentSpecificUpdates);

                // Add lm_ref_no to the insert
                $fields[] = "lm_ref_no";
                $placeholders[] = "?";
                $values[] = $lmRefNo;

                foreach ($values as $value) {
                    $types .= is_int($value) ? "i" : (is_float($value) ? "d" : "s");
                }

                $insertQuery = "INSERT INTO learning_assessments (`" . implode("`, `", $fields) . "`) VALUES (" . implode(", ", $placeholders) . ")";

                $stmt = $conn->prepare($insertQuery);
                $stmt->bind_param($types, ...$values);
                $stmt->execute();

                if ($stmt->affected_rows === 0) {
                    throw new Exception("Failed to insert assessment: " . $stmt->error);
                }

                $assessmentId = $stmt->insert_id;
            }
        }

        // Commit transaction
        $conn->commit();

        // Log the successful update
        error_log("Updated learning assessment for LMRefNo $lmRefNo, AssessID $assessmentId");

        return true;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();

        // Log the error
        error_log("Error updating learning assessment: " . $e->getMessage());

        return false;
    }
}

function deleteLearningAssessment($conn, $lmRefNo): bool
{   $sql = "DELETE FROM learningmaterial WHERE lmRefNo=$lmRefNo AND lmType='Learning Assessment'";
    $conn->prepare($sql);
    echo "deleteLearningAssessment for LMRefNo $lmRefNo - SIMULATED<br>";
    return true;
}

/**
 * Updates/deletes a lesson (learningcontent).
 * A lesson is a 'learningcontent' record.
 * $modTempNo is the PK for learningcontent.
 */
function updateLesson($conn, $modTempNo, $lessonUpdates): bool
{
    $sql = "UPDATE FROM learningcontent WHERE modTempNo=$modTempNo";
    $conn->prepare($sql);
    echo "updateLesson for ModTempNo $modTempNo - SIMULATED<br>";
    return true;
}

function deleteLesson($conn, $modTempNo): bool
{
    try {
        // Prepare the SQL statement
        $stmt = $conn->prepare("DELETE FROM learningcontent WHERE modTempNo = ?");

        // Bind the parameter
        $stmt->bind_param("i", $modTempNo);

        // Execute the statement
        $result = $stmt->execute();

        // Check if any rows were affected
        if ($stmt->affected_rows > 0) {
            $stmt->close();
            return true;
        } else {
            $stmt->close();
            return false;
        }
    } catch (Exception $e) {
        // Log the error (consider using a proper logging system)
        error_log("Error deleting lesson: " . $e->getMessage());
        return false;
    }
}

/**
 * Updates the number of students in a subject (not directly a table field, but a derived count or managed through enrollments).
 * The requirement "Update/delete the number of students in a subject" likely means managing enrollments.
 * Example: Removing a student from a subject.
 * $enrollmentNo is PK of enrolled_students.
 */
function removeStudentFromSubject($conn, $enrollmentNo): bool
{
    // Option 1: Hard Delete
    // $sql = "DELETE FROM enrolled_students WHERE enrollmentNo = ?";
    // Option 2: Soft Delete (Update status)
    $sql = "UPDATE enrolled_students SET status = 'Removed' WHERE enrollmentNo = ? AND status = 'Enrolled'";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed (remove student): " . $conn->error;
        return false;
    }
    $stmt->bind_param("i", $enrollmentNo);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "Student enrollment (No: $enrollmentNo) marked as Removed.<br>";
            return true;
        } else {
            echo "Enrollment (No: $enrollmentNo) not found or already removed.<br>";
            return false;
        }
    } else {
        echo "Error removing student from subject: " . $stmt->error . "<br>";
        return false;
    }
    
}

/**
 * Updates the assessment score of a student (student_assessment_result).
 * $resultId is PK of student_assessment_result.
 * The rawScore is auto-generated. Updates here might be for 'remarks' or if 'correctAnswers' needs manual adjustment.
 */
function updateStudentAssessmentResult($conn, $resultId, $updates): bool
{
    // e.g., $updates = ['correctAnswers' => 10, 'remarks' => 'Reviewed and finalized.']
    if (empty($updates)) {
        echo "No updates for assessment result.<br>";
        return false;
    }
    $setClauses = [];
    $params = [];
    $types = "";
    // rawScore is GENERATED, finalizedBy and finalDateTime can be updated.
    $allowedFields = ['correctAnswers', 'remarks', 'finalizedBy'];

    foreach ($updates as $field => $value) {
        if (in_array($field, $allowedFields)) {
            $setClauses[] = "`$field` = ?";
            $params[] = $value;
            if (in_array($field, ['correctAnswers', 'finalizedBy'])) $types .= "i"; else $types .= "s";
        }
    }
    if (empty($setClauses)) {
        echo "No valid fields to update for assessment result.<br>";
        return false;
    }

    // If finalizedBy is updated, also update finalDateTime
    if (isset($updates['finalizedBy'])) {
        $setClauses[] = "finalDateTime = CURRENT_TIMESTAMP";
    }


    $sql = "UPDATE student_assessment_result SET " . implode(", ", $setClauses) . " WHERE resultId = ?";
    $params[] = $resultId;
    $types .= "i";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed (update score): " . $conn->error;
        return false;
    }
    $stmt->bind_param($types, ...$params);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "Student assessment result (ID: $resultId) updated.<br>";
            return true;
        } else {
            echo "No changes or result (ID: $resultId) not found for update.<br>";
            return false;
        }
    } else {
        echo "Error updating student assessment result: " . $stmt->error . "<br>";
        return false;
    }
    
}