<?php

/**
 * User list page - displays all users (admin and staff only).
 */

require_once __DIR__ . '/../config/config.php';

use App\Core\Database;
use App\Core\Auth;
use App\Models\User;
use App\Helpers\FlashMessage;
use App\Helpers\Redirect;

$auth = new Auth();

// Require admin or staff access
if (!$auth->isStaffOrAdmin()) {
    FlashMessage::set('Access denied for your role.', 'error');
    Redirect::toHome();
}

$db = Database::getInstance();
$conn = $db->getConnection();
$userModel = new User($conn);

// Get all users
$result = $userModel->read();

$users = $result['success'] ? $result['users'] : [];
$error = $result['error'];

$accountTypeLabels = [
    'admin' => 'Admin',
    'staff' => 'Staff',
    'teacher' => 'Teacher',
    'student' => 'Student',
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; color: #222; }
        .container { max-width: 960px; margin: 0 auto; }
        h2 { display: flex; justify-content: space-between; align-items: center; }
        .nav { margin-bottom: 12px; }
        a.btnhome { display: inline-block; padding: 8px 12px; border-radius: 6px; text-decoration: none; color: #fff; background: #5c5d61; }
        a.btn-add { display: inline-block; padding: 8px 12px; border-radius: 6px; text-decoration: none; color: #fff; background: #28a745; }
        .alert { padding: 10px 14px; border-radius: 6px; margin-bottom: 12px; font-size: 14px; }
        .alert.error { background: #ffe3e3; color: #7a1c1c; border: 1px solid #f0b4b4; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: 600; }
        tr:hover { background: #f8f9fa; }
        .actions { display: flex; gap: 8px; }
        a.btn-edit { padding: 6px 10px; background: #0066cc; color: #fff; text-decoration: none; border-radius: 4px; font-size: 12px; }
        a.btn-delete { padding: 6px 10px; background: #dc3545; color: #fff; text-decoration: none; border-radius: 4px; font-size: 12px; }
        .no-data { text-align: center; padding: 40px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav">
            <a class="btnhome" href="home.php">Back to Home</a>
        </div>
        
        <h2>
            USER MANAGEMENT
            <a class="btn-add" href="users_new.php">Add User</a>
        </h2>

        <?php if ($error !== ''): ?>
            <div class="alert error"><?php echo htmlspecialchars($error, ENT_QUOTES); ?></div>
        <?php endif; ?>

        <?php if (empty($users)): ?>
            <div class="no-data">No users found.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Account Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?></td>
                            <td><?php echo htmlspecialchars($accountTypeLabels[$user['account_type']] ?? $user['account_type'], ENT_QUOTES); ?></td>
                            <td>
                                <div class="actions">
                                    <a class="btn-edit" href="users_edit.php?user_id=<?php echo htmlspecialchars((string)$user['id'], ENT_QUOTES); ?>">Edit</a>
                                    <?php if ($user['id'] !== 1): ?>
                                        <a class="btn-delete" href="users_delete.php?user_id=<?php echo htmlspecialchars((string)$user['id'], ENT_QUOTES); ?>" onclick="return confirm('Are you sure?')">Delete</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
