<?php

/**
 * Logout page - logs out the current user.
 */

require_once __DIR__ . '/../config/config.php';

use App\Core\Auth;
use App\Helpers\FlashMessage;
use App\Helpers\Redirect;

$auth = new Auth();
$auth->logout();

FlashMessage::set('You have been logged out successfully.', 'success');
Redirect::toLogin();
