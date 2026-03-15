<?php

namespace App\Helpers;

use App\Core\SessionManager;

/**
 * Flash message handler following PSR-1 conventions.
 * Manages one-time messages stored in sessions for display on the next request.
 */
class FlashMessage
{
    private const FLASH_KEY = 'flash_message';

    /**
     * Sets a flash message in the session.
     * 
     * @param string $message
     * @param string $type Type of message: 'success', 'error', 'info', 'warning'
     * @return void
     */
    public static function set(string $message, string $type = 'info'): void
    {
        SessionManager::set(self::FLASH_KEY, [
            'message' => $message,
            'type' => $type,
        ]);
    }

    /**
     * Gets and clears the flash message from the session.
     * 
     * @return array|null Flash message data or null if none exists
     */
    public static function get(): ?array
    {
        if (!SessionManager::has(self::FLASH_KEY)) {
            return null;
        }

        $flash = SessionManager::get(self::FLASH_KEY);
        SessionManager::remove(self::FLASH_KEY);

        return $flash;
    }

    /**
     * Checks if a flash message exists.
     * 
     * @return bool
     */
    public static function has(): bool
    {
        return SessionManager::has(self::FLASH_KEY);
    }

    /**
     * Sets a success message.
     * 
     * @param string $message
     * @return void
     */
    public static function success(string $message): void
    {
        self::set($message, 'success');
    }

    /**
     * Sets an error message.
     * 
     * @param string $message
     * @return void
     */
    public static function error(string $message): void
    {
        self::set($message, 'error');
    }

    /**
     * Sets an info message.
     * 
     * @param string $message
     * @return void
     */
    public static function info(string $message): void
    {
        self::set($message, 'info');
    }

    /**
     * Sets a warning message.
     * 
     * @param string $message
     * @return void
     */
    public static function warning(string $message): void
    {
        self::set($message, 'warning');
    }
}
