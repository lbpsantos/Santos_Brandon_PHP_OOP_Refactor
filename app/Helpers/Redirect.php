<?php

namespace App\Helpers;

/**
 * Redirect helper following PSR-1 conventions.
 * Provides methods for redirecting users to other pages.
 */
class Redirect
{
    /**
     * Redirects to a specified URL.
     * 
     * @param string $url
     * @return void
     */
    public static function to(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Redirects to login page.
     * 
     * @return void
     */
    public static function toLogin(): void
    {
        self::to('login.php');
    }

    /**
     * Redirects to home page.
     * 
     * @return void
     */
    public static function toHome(): void
    {
        self::to('home.php');
    }

    /**
     * Redirects back to the previous page.
     * 
     * @param string $fallback Fallback URL if referrer is not available
     * @return void
     */
    public static function back(string $fallback = 'home.php'): void
    {
        $referrer = $_SERVER['HTTP_REFERER'] ?? $fallback;
        self::to($referrer);
    }
}
