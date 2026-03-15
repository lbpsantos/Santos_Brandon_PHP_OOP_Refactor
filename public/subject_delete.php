<?php

/**
 * Subject delete page - deletes a subject.
 */

require_once __DIR__ . '/../config/config.php';

use App\Core\Database;
use App\Core\Auth;
use App\Models\Subject;
use App\Helpers\Validator;
use App\Helpers\FlashMessage;
use App\Helpers\Redirect;

$auth = new Auth();

// Require admin access for deletion
if (!$auth->isAdmin()) {
    FlashMessage::set('Only admins can delete subjects.', 'error');
    Redirect::to('subject_list.php');
}

$subjectId = $_GET['subject_id'] ?? '';

// Validate subject ID
$idValidation = Validator::validateId($subjectId);
if (!$idValidation['valid']) {
    FlashMessage::set($idValidation['error'], 'error');
    Redirect::to('subject_list.php');
}

$db = Database::getInstance();
$conn = $db->getConnection();
$subjectModel = new Subject($conn);

$deleteResult = $subjectModel->delete($idValidation['value']);

if ($deleteResult['success']) {
    FlashMessage::success('Subject deleted successfully.');
} else {
    FlashMessage::error($deleteResult['error']);
}

Redirect::to('subject_list.php');
