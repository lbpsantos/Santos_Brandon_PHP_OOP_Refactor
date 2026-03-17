<?php

/**
 * Home page - main dashboard after login.
 */

require_once __DIR__ . '/../config/config.php';

use App\Core\Auth;
use App\Helpers\FlashMessage;
use App\Helpers\Redirect;

$auth = new Auth();

// Require login
if (!$auth->isLoggedIn()) {
    FlashMessage::set('Please log in to continue.', 'error');
    Redirect::toLogin();
}

// Regenerate session ID for security
\App\Core\SessionManager::regenerateId(true);

$user = $auth->getCurrentUser();
$flash = FlashMessage::get();

$accountTypeLabels = [
    'admin' => 'Admin',
    'staff' => 'Staff',
    'teacher' => 'Teacher',
    'student' => 'Student',
];
$accountLabel = $accountTypeLabels[$user['account_type']] ?? 'User';
$username = $user['username'] ?? 'User';

// Prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <title><?php echo htmlspecialchars(APP_NAME, ENT_QUOTES); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; color: #222; text-align: center; }
        .container { max-width: 720px; margin: 0 auto; }
        h1 { margin-bottom: 16px; font-size: 40px; }
        .card { border: 1px solid #ddd; border-radius: 8px; padding: 20px; }
        .links { display: flex; gap: 12px; margin-top: 12px; justify-content: center; flex-wrap: wrap; }
        a.btn { display: inline-block; padding: 10px 14px; border-radius: 6px; text-decoration: none; color: #fff; background: #8c4faf; }
        a.btn.secondary { background: #2e9b9b; }
        a.btn.third { background: #1aa9e2; }
        a.btn.warning { background: #f0ad4e; }
        a.btn.logout { background: red; }
        p { font-size: 30px; }
        .alert { padding: 10px 14px; border-radius: 6px; margin-bottom: 12px; font-size: 14px; text-align: left; }
        .alert.error { background: #ffe3e3; color: #7a1c1c; border: 1px solid #f0b4b4; }
        .alert.success { background: #e2f5e9; color: #1b6b2c; border: 1px solid #b7e2c4; }
        .alert.info { background: #e0efff; color: #0f4b8f; border: 1px solid #b7d5ff; }
        .user-info { font-size: 14px; color: #666; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars(APP_NAME, ENT_QUOTES); ?></h1>
        
        <?php if ($flash): ?>
            <div class="alert <?php echo htmlspecialchars($flash['type'], ENT_QUOTES); ?>">
                <?php echo htmlspecialchars($flash['message'], ENT_QUOTES); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <p>Welcome, <?php echo htmlspecialchars($username, ENT_QUOTES); ?></p>
            <div class="user-info"><?php echo htmlspecialchars($accountLabel, ENT_QUOTES); ?></div>

            <div class="links">
                <a class="btn" href="program_list.php">Program List</a>
                <a class="btn secondary" href="subject_list.php">Subject List</a>
                
                <?php if ($auth->isAdmin()): ?>
                    <a class="btn third" href="users_list.php">User Management</a>
                <?php endif; ?>

                <a class="btn warning" href="change_password.php">Change Password</a>
                <a class="btn logout" href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</body>
</html>
