<?php
// When the user wants to edit it reloads this page an displays the form for adding new details

require_once __DIR__ . "/../backend/config/database.php";

// Handle flash messages
if (isset($_GET['success'])) {
    $message = 'Operation completed successfully';
    $messageType = 'success';
} elseif (isset($_GET['error'])) {
    $message = htmlspecialchars($_GET['error']);
    $messageType = 'error';
}

$database = new Database();
$conn = $database->getConnection();

// Determine mode: add, edit, or list
$action = $_GET['action'] ?? 'list';

$editUser = null; //User details are stored here as an array

if ($action === 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM employees WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $editUser = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$editUser) {
        header("Location: " . APP_BASE . "/ict/manage-users?error=User+not+found");
        exit();
    }
}

// Fetch all users for the table
$stmt = $conn->prepare("SELECT * FROM employees ORDER BY name ASC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= APP_BASE ?>/frontend/style.css">
    <link rel="icon" href="<?= APP_BASE ?>/frontend/assets/favicon.ico">
    <title>Manage Employees</title>
</head>
<body>

    <header class="topbar">
        <img src="<?= APP_BASE ?>/frontend/assets/NCIA-Logo-2022.png" alt="NCIA logo" width="150" height="53">
    </header>

    <main class="page-content">
        <a href="<?= APP_BASE ?>/<?= $_SESSION['user_role']?>" class="back-link">&larr; Back to Dashboard</a>

        <div class="page-header">
            <h1>Employees</h1>
            <?php if ($action === 'list'): ?>
                <a href="<?= APP_BASE ?>/manage-employees?action=add" class="export-btn">+ Add User</a>
            <?php endif; ?>
        </div>

        <!-- Success/Error message -->
        <?php if (!empty($message)): ?>
            <div class="<?= $messageType === 'success' ? 'success-message' : 'error-message' ?>" style="display:block;">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if ($action === 'add' || $action === 'edit'): ?>
        <!-- Add/Edit User Form -->
        <div class="login-section"> <!--The class is the same style used in the login page, should consider creating new class -->
            <form action="<?= APP_BASE ?>/backend/<?= $action === 'edit' ? 'edit-user.php' : 'create-user.php' ?>" method="POST" class="login-details">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="id" value="<?= $editUser['id'] ?>">
                <?php endif; ?>

                <label for="name">Full Name</label>
                <input type="text" name="name" id="name" value="<?= htmlspecialchars($editUser['name'] ?? '') ?>" placeholder="Enter full name" required>

                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($editUser['email'] ?? '') ?>" placeholder="user@ncia.com" required>

                <label for="department">Department</label>
                <input type="text" name="department" id="department" value="<?= htmlspecialchars($editUser['department'] ?? '') ?>" placeholder="e.g. ICT, HR, Finance" required>

                <label for="role">Role</label>
                <select name="role" id="role" required>
                    <option value="" disabled selected>Select a Role</option>
                    <option value="admin" <?= ($editUser['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="ict" <?= ($editUser['role'] ?? '') === 'ict' ? 'selected' : '' ?>>ICT</option>
                    <option value="hr" <?= ($editUser['role'] ?? '') === 'hr' ? 'selected' : '' ?>>HR</option>
                    <option value="interviewer" <?= ($editUser['role'] ?? '') === 'interviewer' ? 'selected' : '' ?>>Interviewer</option>

                </select>

                <label for="password">Password <?= $action === 'edit' ? '(leave blank to keep current)' : '' ?></label>
                <input type="password" name="password" id="password" placeholder="<?= $action === 'edit' ? 'Leave blank to keep current' : 'Enter password' ?>" <?= $action === 'add' ? 'required' : '' ?>>

                <button type="submit" class="sign-in-button"><?= $action === 'edit' ? 'Update User' : 'Create User' ?></button>
            </form>
        </div>

        <?php else: ?>
        <!-- Users Table -->
        <?php if (empty($users)): ?>
            <div class="empty-state">No users found.</div>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['department'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($u['role'] ?? '—') ?></td>
                            <td>
                                <a href="<?= APP_BASE ?>/manage-employees?action=edit&id=<?= $u['id'] ?>" class="table-link">Edit</a>
                                &nbsp;|&nbsp;
                                <a href="<?= APP_BASE ?>/backend/delete-user.php?id=<?= $u['id'] ?>" class="table-link" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <?php endif; ?>

    </main>

</body>
</html>
