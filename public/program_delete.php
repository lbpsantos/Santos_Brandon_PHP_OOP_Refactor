<?php

/**
 * Program delete page - deletes a program.
 */

require_once __DIR__ . '/../config/config.php';

use App\Core\Database;
use App\Core\Auth;
use App\Models\Program;
use App\Helpers\Validator;
use App\Helpers\FlashMessage;
use App\Helpers\Redirect;

$auth = new Auth();

// Require admin access for deletion
if (!$auth->isAdmin()) {
    FlashMessage::set('Only admins can delete programs.', 'error');
    Redirect::to('program_list.php');
}

$programId = $_GET['program_id'] ?? '';

// Validate program ID
$idValidation = Validator::validateId($programId);
if (!$idValidation['valid']) {
    FlashMessage::set($idValidation['error'], 'error');
    Redirect::to('program_list.php');
}

$db = Database::getInstance();
$conn = $db->getConnection();
$programModel = new Program($conn);

$deleteResult = $programModel->delete($idValidation['value']);

if ($deleteResult['success']) {
    FlashMessage::success('Program deleted successfully.');
} else {
    FlashMessage::error($deleteResult['error']);
}

Redirect::to('program_list.php');
