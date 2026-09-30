<?php
session_start();
require_once "config/app.php";
require_once "config/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name       = trim($_POST['name']);
    $email      = trim($_POST['email']);
    $department = trim($_POST['department']);
    $role       = trim($_POST['role']);
    $password   = trim($_POST['password']);

    if (empty($name) || empty($email) || empty($department) || empty($role) || empty($password)) {
        header("Location: " . APP_BASE . "/ict/manage-users?action=add&error=Please+fill+in+all+fields");
        exit();
    }

    $allowedRoles = ['admin', 'ict', 'hr', 'interviewer'];
    if (!in_array($role, $allowedRoles)) {
        header("Location: " . APP_BASE . "/ict/manage-users?action=add&error=Invalid+role+selected");
        exit();
    }

    $database = new Database();
    $conn = $database->getConnection();

    // Check if email already exists in employees or applicants
    $stmt = $conn->prepare("SELECT id FROM employees WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->rowCount() > 0) {
        header("Location: " . APP_BASE . "/ict/manage-users?action=add&error=Email+already+exists+in+employees");
        exit();
    }

    $stmt = $conn->prepare("SELECT id FROM applicants WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->rowCount() > 0) {
        header("Location: " . APP_BASE . "/ict/manage-users?action=add&error=Email+already+exists+in+applicants");
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO employees (name, department, role, email, password) VALUES (:name, :department, :role, :email, :password)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':department', $department);
    $stmt->bindParam(':role', $role);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashed_password);

    if ($stmt->execute()) {
        //Send an email to the user 
        

        header("Location: " . APP_BASE . "/manage-employees?success=1");
    } else {
        header("Location: " . APP_BASE . "/manage-employees?action=add&error=Failed+to+create+user");
    }
    exit();

} else {
    header("Location: " . APP_BASE . "/manage-employees");
    exit();
}
?>
