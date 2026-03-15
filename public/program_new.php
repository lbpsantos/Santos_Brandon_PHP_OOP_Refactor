<?php

/**
 * Program creation page - allows staff and admin to create new programs.
 */

require_once __DIR__ . '/../config/config.php';

use App\Core\Database;
use App\Core\Auth;
use App\Models\Program;
use App\Helpers\FlashMessage;
use App\Helpers\Redirect;

$auth = new Auth();

// Require staff or admin access
if (!$auth->isStaffOrAdmin()) {
    FlashMessage::set('Access denied for your role.', 'error');
    Redirect::toHome();
}

$db = Database::getInstance();
$conn = $db->getConnection();
$programModel = new Program($conn);

$error = '';
$codeValue = '';
$titleValue = '';
$yearsValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codeValue = trim($_POST['programCode'] ?? '');
    $titleValue = trim($_POST['programTitle'] ?? '');
    $yearsValue = trim((string)($_POST['programYears'] ?? ''));

    $createResult = $programModel->create($codeValue, $titleValue, $yearsValue);
    
    if ($createResult['success']) {
        FlashMessage::success('Program created successfully.');
        Redirect::to('program_list.php');
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
    <title>New Program</title>
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
        .field input { flex: 1; padding: 8px; border: 1px solid #bbb; border-radius: 4px; }
        .actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 8px; }
        button { border: none; border-radius: 6px; padding: 8px 14px; font-size: 14px; cursor: pointer; }
        .btn-cancel { background: #d9534f; color: #fff; }
        .btn-save { background: #198754; color: #fff; }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav">
            <a class="btnhome" href="program_list.php">Back to Programs</a>
        </div>
        
        <h2>CREATE NEW PROGRAM</h2>
        
        <div class="form">
            <?php if ($error !== ''): ?>
                <div class="alert error"><?php echo htmlspecialchars($error, ENT_QUOTES); ?></div>
            <?php endif; ?>

            <form action="program_new.php" method="post">
                <div class="field">
                    <label for="programCode">Program code:</label>
                    <input type="text" id="programCode" name="programCode" required value="<?php echo htmlspecialchars($codeValue, ENT_QUOTES); ?>">
                </div>
                <div class="field">
                    <label for="programTitle">Program title:</label>
                    <input type="text" id="programTitle" name="programTitle" required value="<?php echo htmlspecialchars($titleValue, ENT_QUOTES); ?>">
                </div>
                <div class="field">
                    <label for="programYears">Years:</label>
                    <input type="number" id="programYears" name="programYears" min="1" max="10" required value="<?php echo htmlspecialchars($yearsValue, ENT_QUOTES); ?>">
                </div>
                <div class="actions">
                    <button type="button" class="btn-cancel" onclick="window.location.href='program_list.php'">Cancel</button>
                    <button type="submit" class="btn-save">Create</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
