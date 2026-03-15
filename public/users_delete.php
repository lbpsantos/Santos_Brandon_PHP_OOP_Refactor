<?php

/**
 * User delete page - deletes a user.
 */

require_once __DIR__ . '/../config/config.php';

use App\Core\Database;
use App\Core\Auth;
use App\Models\User;
use App\Helpers\Validator;
use App\Helpers\FlashMessage;
use App\Helpers\Redirect;

$auth = new Auth();

// Require admin access for deletion
if (!$auth->isAdmin()) {
    FlashMessage::set('Only admins can delete users.', 'error');
    Redirect::to('users_list.php');
}

$userId = $_GET['user_id'] ?? '';

// Validate user ID
$idValidation = Validator::validateId($userId);
if (!$idValidation['valid']) {
    FlashMessage::set($idValidation['error'], 'error');
    Redirect::to('users_list.php');
}

// Prevent deleting the default admin user
if ($idValidation['value'] === 1) {
    FlashMessage::set('Cannot delete the default admin user.', 'error');
    Redirect::to('users_list.php');
}

$db = Database::getInstance();
$conn = $db->getConnection();
$userModel = new User($conn);

$deleteResult = $userModel->delete($idValidation['value']);

if ($deleteResult['success']) {
    FlashMessage::success('User deleted successfully.');
} else {
    FlashMessage::error($deleteResult['error']);
}

Redirect::to('users_list.php');
