<?php

/**
 * Subject list page - displays all subjects.
 */

require_once __DIR__ . '/../config/config.php';

use App\Core\Database;
use App\Core\Auth;
use App\Models\Subject;
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

// Get all subjects
$result = $subjectModel->read();

// Extract subjects, errors, and check for flash messages from previous operations
$subjects = $result['success'] ? $result['subjects'] : [];
$error = $result['error'];
$flash = FlashMessage::get(); // Retrieve any flash message (e.g., from create/update/delete operations)

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject List</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; color: #222; }
        .container { max-width: 960px; margin: 0 auto; }
        h2 { display: flex; justify-content: space-between; align-items: center; }
        .nav { margin-bottom: 12px; }
        a.btnhome { display: inline-block; padding: 8px 12px; border-radius: 6px; text-decoration: none; color: #fff; background: #5c5d61; }
        a.btn-add { display: inline-block; padding: 8px 12px; border-radius: 6px; text-decoration: none; color: #fff; background: #28a745; }
        .alert { padding: 10px 14px; border-radius: 6px; margin-bottom: 12px; font-size: 14px; }
        .alert.error { background: #ffe3e3; color: #7a1c1c; border: 1px solid #f0b4b4; }
        .alert.success { background: #e2f5e9; color: #1b6b2c; border: 1px solid #b7e2c4; }
        .alert.info { background: #e0efff; color: #0f4b8f; border: 1px solid #b7d5ff; }
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
            SUBJECTS
            <a class="btn-add" href="subject_new.php">Add Subject</a>
        </h2>

        <!-- Display flash message from previous operations (create, update, delete) -->
        <?php if ($flash): ?>
            <div class="alert <?php echo htmlspecialchars($flash['type'], ENT_QUOTES); ?>">
                <?php echo htmlspecialchars($flash['message'], ENT_QUOTES); ?>
            </div>
        <?php endif; ?>

        <!-- Display page-level errors from current operation -->
        <?php if ($error !== ''): ?>
            <div class="alert error"><?php echo htmlspecialchars($error, ENT_QUOTES); ?></div>
        <?php endif; ?>

        <?php if (empty($subjects)): ?>
            <div class="no-data">No subjects found.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Title</th>
                        <th>Unit</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subjects as $subject): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($subject['code'], ENT_QUOTES); ?></td>
                            <td><?php echo htmlspecialchars($subject['title'], ENT_QUOTES); ?></td>
                            <td><?php echo htmlspecialchars((string)$subject['unit'], ENT_QUOTES); ?></td>
                            <td>
                                <div class="actions">
                                    <a class="btn-edit" href="subject_edit.php?subject_id=<?php echo htmlspecialchars((string)$subject['subject_id'], ENT_QUOTES); ?>">Edit</a>
                                    <a class="btn-delete" href="subject_delete.php?subject_id=<?php echo htmlspecialchars((string)$subject['subject_id'], ENT_QUOTES); ?>" onclick="return confirm('Are you sure?')">Delete</a>
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
