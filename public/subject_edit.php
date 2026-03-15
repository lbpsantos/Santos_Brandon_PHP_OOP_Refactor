<?php

/**
 * Subject edit page - allows staff and admin to modify subjects.
 */

require_once __DIR__ . '/../config/config.php';

use App\Core\Database;
use App\Core\Auth;
use App\Models\Subject;
use App\Helpers\Validator;
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
$subjectModel = new Subject($conn);

$error = '';
$subjectId = $_GET['subject_id'] ?? $_POST['subject_id'] ?? '';
$codeValue = '';
$titleValue = '';
$unitValue = '';

// Validate subject ID
$idValidation = Validator::validateId($subjectId);
if (!$idValidation['valid']) {
    $error = $idValidation['error'];
    $subjectId = null;
} else {
    $subjectId = $idValidation['value'];
}

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';

if ($error === '' && $isPost) {
    $codeValue = trim($_POST['subjectCode'] ?? '');
    $titleValue = trim($_POST['subjectTitle'] ?? '');
    $unitValue = trim((string)($_POST['subjectUnit'] ?? ''));

    $updateResult = $subjectModel->update($subjectId, $codeValue, $titleValue, $unitValue);
    if ($updateResult['success']) {
        FlashMessage::success('Subject updated successfully.');
        Redirect::to('subject_list.php');
    }

    $error = $updateResult['error'] ?? 'Unable to update subject. Please try again.';
}

if ($error === '' && !$isPost && $subjectId) {
    $readResult = $subjectModel->read(['id' => $subjectId]);
    if ($readResult['success'] && $readResult['subject']) {
        $codeValue = (string)$readResult['subject']['code'];
        $titleValue = (string)$readResult['subject']['title'];
        $unitValue = (string)$readResult['subject']['unit'];
    } else {
        $error = $readResult['error'] ?: 'Failed to load subject.';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Subject</title>
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
            <a class="btnhome" href="subject_list.php">Back to Subjects</a>
        </div>
        
        <h2>EDIT SUBJECT</h2>
        
        <div class="form">
            <?php if ($error !== ''): ?>
                <div class="alert error"><?php echo htmlspecialchars($error, ENT_QUOTES); ?></div>
            <?php endif; ?>

            <?php if ($error === '' && $subjectId): ?>
                <form action="subject_edit.php" method="post">
                    <input type="hidden" name="subject_id" value="<?php echo htmlspecialchars((string)$subjectId, ENT_QUOTES); ?>">
                    <div class="field">
                        <label for="subjectCode">Subject code:</label>
                        <input type="text" id="subjectCode" name="subjectCode" required value="<?php echo htmlspecialchars($codeValue, ENT_QUOTES); ?>">
                    </div>
                    <div class="field">
                        <label for="subjectTitle">Subject title:</label>
                        <input type="text" id="subjectTitle" name="subjectTitle" required value="<?php echo htmlspecialchars($titleValue, ENT_QUOTES); ?>">
                    </div>
                    <div class="field">
                        <label for="subjectUnit">Unit:</label>
                        <input type="number" id="subjectUnit" name="subjectUnit" min="1" required value="<?php echo htmlspecialchars($unitValue, ENT_QUOTES); ?>">
                    </div>
                    <div class="actions">
                        <button type="button" class="btn-cancel" onclick="window.location.href='subject_list.php'">Cancel</button>
                        <button type="submit" class="btn-save">Update</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
