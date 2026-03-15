<?php

/**
 * Login page - allows users to authenticate.
 */

require_once __DIR__ . '/../config/config.php';

use App\Core\Database;
use App\Core\Auth;
use App\Helpers\FlashMessage;
use App\Helpers\Redirect;

// Prevent logged-in users from accessing login page
$auth = new Auth();
if ($auth->isLoggedIn()) {
    Redirect::toHome();
}

$error = '';
$usernameValue = trim($_POST['username'] ?? '');
$flash = FlashMessage::get();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    
    if ($usernameValue === '' || $password === '') {
        $error = 'Username and password are required.';
    } else {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $loginResult = $auth->login($usernameValue, $password, $conn);
        
        if ($loginResult['success']) {
            FlashMessage::success('Welcome back!');
            Redirect::toHome();
        } else {
            $error = $loginResult['error'];
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; color: #222; background: #f0f2ff; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .container { width: 100%; max-width: 420px; background: #fff; padding: 40px; border-radius: 16px; box-shadow: 0 24px 58px rgba(36, 54, 156, 0.18); }
        h1 { margin-bottom: 8px; text-align: center; font-size: 28px; }
        p.description { text-align: center; margin-bottom: 24px; color: #555; }
        form { display: flex; flex-direction: column; gap: 18px; }
        .field { display: flex; flex-direction: column; }
        label { margin-bottom: 6px; font-weight: 600; font-size: 14px; }
        input { padding: 12px; border: 1px solid #bbb; border-radius: 4px; font-size: 14px; }
        button { padding: 12px; background: #8c4faf; color: #fff; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; font-weight: 600; }
        button:hover { background: #6f3d8a; }
        .alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; font-size: 14px; }
        .alert.error { background: #ffe3e3; color: #7a1c1c; border: 1px solid #f0b4b4; }
        .alert.success { background: #e2f5e9; color: #1b6b2c; border: 1px solid #b7e2c4; }
        .alert.info { background: #e0efff; color: #0f4b8f; border: 1px solid #b7d5ff; }
    </style>
</head>
<body>
    <div class="container">
        <h1>School Encoding Module</h1>
        <p class="description">Please log in to continue</p>

        <?php if ($flash): ?>
            <div class="alert <?php echo htmlspecialchars($flash['type'], ENT_QUOTES); ?>">
                <?php echo htmlspecialchars($flash['message'], ENT_QUOTES); ?>
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="alert error"><?php echo htmlspecialchars($error, ENT_QUOTES); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="field">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($usernameValue, ENT_QUOTES); ?>">
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>

        <p style="text-align: center; margin-top: 20px; font-size: 12px; color: #666;">
            Demo credentials: username: <strong>admin</strong> | password: <strong>admin123</strong>
        </p>
    </div>
</body>
</html>
