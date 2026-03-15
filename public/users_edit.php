<?php

/**
 * User edit page - allows admin and staff to modify users.
 */

require_once __DIR__ . '/../config/config.php';

use App\Core\Database;
use App\Core\Auth;
use App\Models\User;
use App\Helpers\Validator;
use App\Helpers\FlashMessage;
use App\Helpers\Redirect;

$auth = new Auth();
$currentUser = $auth->getCurrentUser();

// Require admin or staff access
if (!$auth->isStaffOrAdmin()) {
    FlashMessage::set('Access denied for your role.', 'error');
    Redirect::toHome();
}

$db = Database::getInstance();
$conn = $db->getConnection();
$userModel = new User($conn);

$error = '';
$userId = $_GET['user_id'] ?? $_POST['user_id'] ?? '';
$usernameValue = '';
$accountTypeValue = '';

// Validate user ID
$idValidation = Validator::validateId($userId);
if (!$idValidation['valid']) {
    $error = $idValidation['error'];
    $userId = null;
} else {
    $userId = $idValidation['value'];
}

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';

$accountTypes = $userModel->getAccountTypes();
$accountTypeLabels = [
    'admin' => 'Admin',
    'staff' => 'Staff',
    'teacher' => 'Teacher',
    'student' => 'Student',
];

if ($error === '' && $isPost) {
    $usernameValue = trim($_POST['username'] ?? '');
    $accountTypeValue = trim($_POST['accountType'] ?? '');

    $updateResult = $userModel->update($userId, $usernameValue, $accountTypeValue, $currentUser['id']);
    if ($updateResult['success']) {
        FlashMessage::success('User updated successfully.');
        Redirect::to('users_list.php');
    }

    $error = $updateResult['error'] ?? 'Unable to update user. Please try again.';
}

if ($error === '' && !$isPost && $userId) {
    $readResult = $userModel->read(['id' => $userId]);
    if ($readResult['success'] && $readResult['user']) {
        $usernameValue = (string)$readResult['user']['username'];
        $accountTypeValue = (string)$readResult['user']['account_type'];
    } else {
        $error = $readResult['error'] ?: 'Failed to load user.';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; color: #222; }
        .container { max-width: 960px; margin: 0 auto; }
        h2 { display: flex; justify-content: space-between; align-items: center; }
        .nav { margin-bottom: 12px; }
        a.btnhome { display: inline-block; padding: 8px 12px; border-radius: 6px; text-decoration: none; color: #fff; background: #5c5d61; }
        .form { border: 1px solid #ddd; border-radius: 8px; padding: 20px; }
        .alert { padding: 10px 14px; border-radius: 6px; margin-bottom: 12px; font-size: 14px; }
        .alert.error { background: #ffe3e3; color: #7a1c1c; border: 1px solid #f0b4b4; }
        form { display: flex; flex-direction: column; gap: 16px; }
        .field { display: flex; align-items: center; gap: 16px; }
        .field label { width: 220px; font-weight: 600; }
        .field input, .field select { flex: 1; padding: 8px; border: 1px solid #bbb; border-radius: 4px; }
        .actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 8px; }
        button { border: none; border-radius: 6px; padding: 8px 14px; font-size: 14px; cursor: pointer; }
        .btn-cancel { background: #d9534f; color: #fff; }
        .btn-save { background: #198754; color: #fff; }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav">
            <a class="btnhome" href="users_list.php">Back to Users</a>
        </div>
        
        <h2>EDIT USER</h2>
        
        <div class="form">
            <?php if ($error !== ''): ?>
                <div class="alert error"><?php echo htmlspecialchars($error, ENT_QUOTES); ?></div>
            <?php endif; ?>

            <?php if ($error === '' && $userId): ?>
                <form action="users_edit.php" method="post">
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars((string)$userId, ENT_QUOTES); ?>">
                    <div class="field">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($usernameValue, ENT_QUOTES); ?>">
                    </div>
                    <div class="field">
                        <label for="accountType">Account Type:</label>
                        <select id="accountType" name="accountType" required>
                            <option value="">-- Select Account Type --</option>
                            <?php foreach ($accountTypes as $type): ?>
                                <option value="<?php echo htmlspecialchars($type, ENT_QUOTES); ?>" <?php echo $accountTypeValue === $type ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($accountTypeLabels[$type] ?? $type, ENT_QUOTES); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="actions">
                        <button type="button" class="btn-cancel" onclick="window.location.href='users_list.php'">Cancel</button>
                        <button type="submit" class="btn-save">Update</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
