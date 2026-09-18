<?php
session_start();
require_once "config/app.php";
require_once "config/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id         = intval($_POST['id']);
    $name       = trim($_POST['name']);
    $email      = trim($_POST['email']);
    $department = trim($_POST['department']);
    $role       = trim($_POST['role']);
    $password   = trim($_POST['password']);

    if (empty($name) || empty($email) || empty($department) || empty($role)) {
        header("Location: " . APP_BASE . "/manage-employees?action=edit&id=" . $id . "&error=Please+fill+in+all+required+fields");
        exit();
    }

    $allowedRoles = ['admin', 'ict', 'hr','interviewer'];
    if (!in_array($role, $allowedRoles)) {
        header("Location: " . APP_BASE . "/manage-employees?action=edit&id=" . $id . "&error=Invalid+role+selected");
        exit();
    }

    $database = new Database();
    $conn = $database->getConnection();

    // Check if email is taken by another user (exclude current)
    $stmt = $conn->prepare("SELECT id FROM employees WHERE email = :email AND id != :id");
    $stmt->execute([':email' => $email, ':id' => $id]);
    if ($stmt->rowCount() > 0) {
        header("Location: " . APP_BASE . "/manage-employees?action=edit&id=" . $id . "&error=Email+already+exists+for+another+user");
        exit();
    }

    // Update with or without password
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE employees SET name = :name, email = :email, department = :department, role = :role, password = :password WHERE id = :id");
        $stmt->bindParam(':password', $hashed_password);
    } else {
        $stmt = $conn->prepare("UPDATE employees SET name = :name, email = :email, department = :department, role = :role WHERE id = :id");
    }

    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':department', $department);
    $stmt->bindParam(':role', $role);

    if ($stmt->execute()) {
        header("Location: " . APP_BASE . "/manage-employees?success=1");
    } else {
        header("Location: " . APP_BASE . "/manage-employees?action=edit&id=" . $id . "&error=Failed+to+update+user");
    }
    exit();

} else {
    header("Location: " . APP_BASE . "/manage-employees");
    exit();
}
?>
