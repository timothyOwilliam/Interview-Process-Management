<?php
require_once __DIR__ . '/config/database.php';

$database = new Database();
$conn = $database->getConnection();

$name = "Admin";
$email = "admin@ncia.com";
$department = "ICT";
$role = "admin";
$password = password_hash("admin123", PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO employees (name, department, role, email, password) VALUES (:name, :department, :role, :email, :password)");
$stmt->bindParam(':name', $name);
$stmt->bindParam(':department', $department);
$stmt->bindParam(':role', $role);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':password', $password);

if ($stmt->execute()) {
    echo "Default admin created successfully!<br>";
    echo "Email: admin@ncia.com<br>";
    echo "Password: admin123";
} else {
    echo "Error creating admin.";
}
?>
