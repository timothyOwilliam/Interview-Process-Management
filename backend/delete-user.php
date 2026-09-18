<?php
session_start();
require_once "config/app.php";
require_once "config/database.php";

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_GET['id'])) {
    header("Location: " . APP_BASE . "/ict/manage-users");
    exit();
}

// Only ICT role can delete users
$role = $_SESSION['user_role'] ?? '';
if ($role !== 'ict') {
    header("Location: " . APP_BASE . "/login?error=Access+denied.+ICT+role+required.");
    exit();
}

$id = intval($_GET['id']);

// Prevent self-deletion
if ($id === intval($_SESSION['user_id'])) {
    header("Location: " . APP_BASE . "/ict/manage-users?error=You+cannot+delete+your+own+account");
    exit();
}

$database = new Database();
$conn = $database->getConnection();

$stmt = $conn->prepare("DELETE FROM employees WHERE id = :id");
$stmt->bindParam(':id', $id);

if ($stmt->execute()) {
    header("Location: " . APP_BASE . "/ict/manage-users?success=1");
} else {
    header("Location: " . APP_BASE . "/ict/manage-users?error=Failed+to+delete+user");
}
exit();
?>
