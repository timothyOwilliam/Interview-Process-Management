<?php
require_once __DIR__ . "/../../backend/config/database.php";
require_once __DIR__ . "/../../backend/config/app.php"; // Needed for APP_BASE

if(!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "applicant"){
    header("Location: " . APP_BASE . "/login");
    exit();    
}

$database = new Database();
$conn = $database->getConnection();

$applicant_id = $_SESSION["user_id"];

// Fetch applicant details
$stmt = $conn->prepare("SELECT name, email, phone, resume_link, position FROM applicants WHERE id = :id");
$stmt->execute([':id' => $applicant_id]);
$applicant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$applicant) {
    // Handle case where applicant somehow not found (shouldn't happen if session is valid)
    header("Location: " . APP_BASE . "/login?error=Applicant+profile+not+found.");
    exit();
}

// Extract variables for easier use in HTML
$name = htmlspecialchars($applicant['name']);
$email = htmlspecialchars($applicant['email']);
$phone = htmlspecialchars($applicant['phone'] ?? ''); // Null-coalescing for optional fields
$resume_link = htmlspecialchars($applicant['resume_link'] ?? '');
$position = htmlspecialchars($applicant['position'] ?? '');

$message = '';
$messageType = '';

if (isset($_GET['success'])) {
    $message = htmlspecialchars($_GET['success']);
    $messageType = 'success';
} elseif (isset($_GET['error'])) {
    $message = htmlspecialchars($_GET['error']);
    $messageType = 'error';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= APP_BASE ?>/frontend/style.css">
    <link rel="icon" href="<?= APP_BASE ?>/frontend/assets/favicon.ico">
    <title>My Profile - Applicant</title>
</head>
<body>

    <header class="topbar">
        <img src="<?= APP_BASE ?>/frontend/assets/NCIA-Logo-2022.png" alt="NCIA logo" width="150" height="53">
    </header>

    <main class="page-content">
        <a href="<?= APP_BASE ?>/applicant" class="back-link">&larr; Back to Dashboard</a>

        <h1>My Profile</h1>

        <?php if (!empty($message)): ?>
            <div class="<?= $messageType === 'success' ? 'success-message' : 'error-message' ?>" style="display:block;">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <div class="login-section">
            <form class="login-details" action="<?= APP_BASE ?>/backend/update-profile.php" method="POST">
                
                <div>
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="<?= $name ?>" readonly>
                </div>

                <div>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= $email ?>" readonly>
                </div>

                <div>
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?= $phone ?>" placeholder="+254 700 000000" inputmode="tel" autocomplete="tel">
                </div>

                <div>
                    <label for="resume_link">Resume Link</label>
                    <input type="url" id="resume_link" name="resume_link" value="<?= $resume_link ?>" placeholder="https://drive.google.com/your-resume" inputmode="url" autocomplete="url">
                </div>

                <div>
                    <label for="position">Position Applied For</label>
                    <input type="text" id="position" name="position" value="<?= $position ?>" placeholder="Software Developer" autocomplete="organization-title">
                </div>

                <button type="submit" class="sign-in-button">Update Profile</button>
            </form>
        </div>
    </main>

</body>
</html>
