<?php

/**
 * Application entry point.
 * Redirects to home page.
 */

require_once __DIR__ . '/../config/config.php';

// Redirect to home page
header('Location: home.php');
exit;
