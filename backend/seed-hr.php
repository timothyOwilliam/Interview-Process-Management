<?php
require_once __DIR__ . '/config/database.php';

$database = new Database();
$conn = $database->getConnection();

$name = "HR Staff";
$email = "hr@ncia.com";
$department = "HR";
$role = "hr";
$password = password_hash("hr123", PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO employees (name, department, role, email, password) VALUES (:name, :department, :role, :email, :password)");
$stmt->bindParam(':name', $name);
$stmt->bindParam(':department', $department);
$stmt->bindParam(':role', $role);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':password', $password);

if ($stmt->execute()) {
    echo "Default HR user created successfully!<br>";
    echo "Email: hr@ncia.com<br>";
    echo "Password: hr123";
} else {
    echo "Error creating HR user.";
}
?>
