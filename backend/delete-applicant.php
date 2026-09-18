<?php
session_start();
require_once "config/app.php";
require_once "config/database.php";

// Only allow GET requests with id
if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_GET['id'])) {
    header("Location: " . APP_BASE . "/ict/manage-applicants");
    exit();
}

// Only ICT role can delete applicants
$role = $_SESSION['user_role'] ?? '';
if ($role !== 'ict') {
    header("Location: " . APP_BASE . "/login?error=Access+denied.+ICT+role+required.");
    exit();
}

$id = intval($_GET['id']);

$database = new Database();
$conn = $database->getConnection();

// Temporarily disable FK checks so cascade works correctly
$conn->exec("SET FOREIGN_KEY_CHECKS = 0");

$stmt = $conn->prepare("DELETE FROM applicants WHERE id = :id");
$stmt->bindParam(':id', $id);

if ($stmt->execute()) {
    $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
    header("Location: " . APP_BASE . "/ict/manage-applicants?success=1");
} else {
    $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
    header("Location: " . APP_BASE . "/ict/manage-applicants?error=Failed+to+delete+applicant");
}
exit();
?>
