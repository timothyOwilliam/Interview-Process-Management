<?php
session_start();
require_once "config/app.php";
require_once "config/database.php";

// Session guard: Ensure only logged-in applicants can access this page
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "applicant") {
    header("Location: " . APP_BASE . "/login?error=Access+denied.+Applicant+role+required.");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $applicant_id = $_SESSION["user_id"];

    $phone = trim($_POST['phone']);
    $resume_link = trim($_POST['resume_link']);
    $position = trim($_POST['position']);

    // Basic validation
    if (empty($phone) && empty($resume_link) && empty($position)) {
        header("Location: " . APP_BASE . "/applicant/profile?error=Please+fill+in+at+least+one+field+to+update.");
        exit();
    }

    $database = new Database();
    $conn = $database->getConnection();

    try {
        $stmt = $conn->prepare("UPDATE applicants SET phone = :phone, resume_link = :resume_link, position = :position WHERE id = :id");
        
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':resume_link', $resume_link);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':id', $applicant_id);

        if ($stmt->execute()) {
            header("Location: " . APP_BASE . "/applicant/profile?success=Profile+updated+successfully!");
            exit();
        } else {
            header("Location: " . APP_BASE . "/applicant/profile?error=Failed+to+update+profile.+Please+try+again.");
            exit();
        }
    } catch (PDOException $e) {
        // Log error (for debugging, not for user display)
        error_log("Profile update failed: " . $e->getMessage());
        header("Location: " . APP_BASE . "/applicant/profile?error=An+unexpected+error+occurred.");
        exit();
    }

} else {
    // If not a POST request, redirect to the profile page
    header("Location: " . APP_BASE . "/applicant/profile");
    exit();
}

?>