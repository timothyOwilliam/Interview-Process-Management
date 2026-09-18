<?php
require_once __DIR__ . '/config/database.php';

$database = new Database();
$conn = $database->getConnection();

$name = "ICT User";
$email = "ict@ncia.com";
$department = "ICT";
$role = "ict";
$password = password_hash("ict123", PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO employees (name, department, role, email, password) VALUES (:name, :department, :role, :email, :password)");
$stmt->bindParam(':name', $name);
$stmt->bindParam(':department', $department);
$stmt->bindParam(':role', $role);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':password', $password);

if ($stmt->execute()) {
    echo "Default ICT user created successfully!<br>";
    echo "Email: ict@ncia.com<br>";
    echo "Password: ict123";
} else {
    echo "Error creating ICT user.";
}
?>
