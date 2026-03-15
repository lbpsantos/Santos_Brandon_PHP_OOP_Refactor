<?php

/**
 * User creation page - allows admin and staff to create new users.
 */

require_once __DIR__ . '/../config/config.php';

use App\Core\Database;
use App\Core\Auth;
use App\Models\User;
use App\Helpers\FlashMessage;
use App\Helpers\Redirect;

$auth = new Auth();
$user = $auth->getCurrentUser();

// Require admin or staff access
if (!$auth->isStaffOrAdmin()) {
    FlashMessage::set('Access denied for your role.', 'error');
    Redirect::toHome();
}

$db = Database::getInstance();
$conn = $db->getConnection();
$userModel = new User($conn);

$error = '';
$usernameValue = '';
$accountTypeValue = '';
$passwordValue = '';
$confirmPasswordValue = '';

$accountTypes = $userModel->getAccountTypes();
$accountTypeLabels = [
    'admin' => 'Admin',
    'staff' => 'Staff',
    'teacher' => 'Teacher',
    'student' => 'Student',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameValue = trim($_POST['username'] ?? '');
    $accountTypeValue = trim($_POST['accountType'] ?? '');
    $passwordValue = $_POST['password'] ?? '';
    $confirmPasswordValue = $_POST['confirmPassword'] ?? '';

    $createResult = $userModel->create($usernameValue, $accountTypeValue, $passwordValue, $confirmPasswordValue, $user['id']);
    
    if ($createResult['success']) {
        FlashMessage::success('User created successfully.');
        Redirect::to('users_list.php');
    } else {
        $error = $createResult['error'];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New User</title>
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
        
        <h2>CREATE NEW USER</h2>
        
        <div class="form">
            <?php if ($error !== ''): ?>
                <div class="alert error"><?php echo htmlspecialchars($error, ENT_QUOTES); ?></div>
            <?php endif; ?>

            <form action="users_new.php" method="post">
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
                <div class="field">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="field">
                    <label for="confirmPassword">Confirm Password:</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                </div>
                <div class="actions">
                    <button type="button" class="btn-cancel" onclick="window.location.href='users_list.php'">Cancel</button>
                    <button type="submit" class="btn-save">Create</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
