<?php
session_start();

require_once __DIR__ . "/config/app.php";
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/config/email.php";
require_once __DIR__ . '/emails/templates/applicant_status_email.php';

// Ensure only HR can access this endpoint
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'hr') {
    header("Location: " . APP_BASE . "/login?error=Access+denied.+HR+role+required.");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicant_id = filter_input(INPUT_POST, 'applicant_id', FILTER_VALIDATE_INT);
    $new_status = filter_input(INPUT_POST, 'new_status', FILTER_SANITIZE_STRING);

    // Validate input
    if (!$applicant_id || !in_array($new_status, ['pending', 'shortlisted', 'rejected'])) {
        header("Location: " . APP_BASE . "/hr/manage-applicants?error=Invalid+input.");
        exit();
    }

    $database = new Database();
    $conn = $database->getConnection();

    try {
        $stmt = $conn->prepare("UPDATE applicants SET application_status = :new_status WHERE id = :applicant_id");
        $stmt->bindParam(':new_status', $new_status);
        $stmt->bindParam(':applicant_id', $applicant_id);
        $stmt->execute();

        //Send email to applicant
        //Get the email first
        $stmt = $conn-> prepare("SELECT email, name, position FROM applicants WHERE id = :applicant_id");
        $stmt-> execute([':applicant_id' => $applicant_id]);
        $applicant = $stmt->fetch(PDO::FETCH_ASSOC);

        $content = buildApplicantStatusEmail([
            'applicant_name'    => $applicant['name'],
            'position'          => $applicant['position'],
            'new_status'        => $new_status,
            'login_url'         =>'http://localhost/Interview-Process-Management',
            'support_email'     =>'hr@ncia.com'
        ]);


        $mail-> Subject = $content['subject'];
        $mail-> Body = $content['html'];
        $mail-> AltBody = $content['alt'];
        $mail-> addAddress($applicant['email']);

        $mail-> send();

        header("Location: " . APP_BASE . "/hr/manage-applicants?success=Applicant+status+updated.");
        exit();

    } catch (PDOException $e) {
        error_log("Error updating applicant status: " . $e->getMessage());
        header("Location: " . APP_BASE . "/hr/manage-applicants?error=Database+error+occurred.");
        exit();
    }
} else {
    header("Location: " . APP_BASE . "/hr/manage-applicants");
    exit();
}
