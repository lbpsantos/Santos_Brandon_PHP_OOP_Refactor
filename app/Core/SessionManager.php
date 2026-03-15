<?php

namespace App\Core;

/**
 * Manages PHP sessions following PSR-1 conventions.
 * Handles session start, value storage/retrieval, and destruction.
 */
class SessionManager
{
    /**
     * Starts the session if it hasn't been started yet.
     * 
     * @return void
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Regenerates the session ID to prevent session fixation attacks.
     * 
     * @param bool $deleteOldSession Whether to delete the old session data
     * @return void
     */
    public static function regenerateId(bool $deleteOldSession = true): void
    {
        session_regenerate_id($deleteOldSession);
    }

    /**
     * Sets a value in the session.
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Gets a value from the session.
     * 
     * @param string $key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Checks if a key exists in the session.
     * 
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Removes a value from the session.
     * 
     * @param string $key
     * @return void
     */
    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Destroys the entire session.
     * 
     * @return void
     */
    public static function destroy(): void
    {
        self::start();
        $_SESSION = [];
        session_destroy();
    }

    /**
     * Gets all session data.
     * 
     * @return array
     */
    public static function all(): array
    {
        self::start();
        return $_SESSION;
    }
}
