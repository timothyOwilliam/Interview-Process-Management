<?php
require_once __DIR__ . '/config/database.php';

$database = new Database();
$conn = $database->getConnection();

$interviewers = [
    ['name' => 'Maria Santos',   'department' => 'HR',        'email' => 'maria.santos@ncia.com'],
    ['name' => 'James Reyes',    'department' => 'Finance',   'email' => 'james.reyes@ncia.com'],
    ['name' => 'Ana Cruz',       'department' => 'Marketing', 'email' => 'ana.cruz@ncia.com'],
    ['name' => 'Carlo Dimaculangan', 'department' => 'ICT',   'email' => 'carlo.d@ncia.com'],
    ['name' => 'Joy Mendoza',    'department' => 'Operations','email' => 'joy.mendoza@ncia.com'],
    ['name' => 'Rafael Torres',  'department' => 'Legal',     'email' => 'rafael.torres@ncia.com'],
];

$password = password_hash("interviewer123", PASSWORD_DEFAULT);
$role = "interviewer";

$stmt = $conn->prepare("INSERT INTO employees (name, department, role, email, password) VALUES (:name, :department, :role, :email, :password)");

$count = 0;
foreach ($interviewers as $person) {
    $stmt->bindParam(':name', $person['name']);
    $stmt->bindParam(':department', $person['department']);
    $stmt->bindParam(':role', $role);
    $stmt->bindParam(':email', $person['email']);
    $stmt->bindParam(':password', $password);

    if ($stmt->execute()) {
        $count++;
    } else {
        echo "Error creating interviewer: {$person['name']}<br>";
    }
}

echo "$count interviewers created successfully!<br><br>";
echo "Credentials:<br>";
echo "Password for all: interviewer123<br><br>";
foreach ($interviewers as $p) {
    echo "- {$p['name']} ({$p['department']}) — {$p['email']}<br>";
}
?>