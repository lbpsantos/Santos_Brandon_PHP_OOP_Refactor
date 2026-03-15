<?php

namespace App\Helpers;

/**
 * Input validation helper following PSR-1 conventions.
 * Provides methods for validating various input types.
 */
class Validator
{
    /**
     * Validates that a username is not empty.
     * 
     * @param string $username
     * @return array ['valid' => bool, 'error' => string]
     */
    public static function validateUsername(string $username): array
    {
        $username = trim($username);
        
        if ($username === '') {
            return ['valid' => false, 'error' => 'Username is required.'];
        }

        if (strlen($username) < 3) {
            return ['valid' => false, 'error' => 'Username must be at least 3 characters long.'];
        }

        return ['valid' => true, 'error' => ''];
    }

    /**
     * Validates password requirements.
     * 
     * @param string $password
     * @param int $minLength Minimum password length (default: 8)
     * @return array ['valid' => bool, 'error' => string]
     */
    public static function validatePassword(string $password, int $minLength = 8): array
    {
        $password = (string)$password;

        if ($password === '') {
            return ['valid' => false, 'error' => 'Password is required.'];
        }

        if (strlen($password) < $minLength) {
            return ['valid' => false, 'error' => "Password must be at least {$minLength} characters long."];
        }

        return ['valid' => true, 'error' => ''];
    }

    /**
     * Validates that passwords match.
     * 
     * @param string $password
     * @param string $confirmPassword
     * @return array ['valid' => bool, 'error' => string]
     */
    public static function validatePasswordMatch(string $password, string $confirmPassword): array
    {
        if ($password !== $confirmPassword) {
            return ['valid' => false, 'error' => 'Password confirmation does not match.'];
        }

        return ['valid' => true, 'error' => ''];
    }

    /**
     * Validates an account type against allowed types.
     * 
     * @param string $accountType
     * @param array $allowedTypes
     * @return array ['valid' => bool, 'error' => string]
     */
    public static function validateAccountType(string $accountType, array $allowedTypes): array
    {
        $accountType = trim($accountType);

        if ($accountType === '') {
            return ['valid' => false, 'error' => 'Account type is required.'];
        }

        if (!in_array($accountType, $allowedTypes, true)) {
            return ['valid' => false, 'error' => 'Invalid account type selected.'];
        }

        return ['valid' => true, 'error' => ''];
    }

    /**
     * Validates a subject code.
     * 
     * @param string $code
     * @return array ['valid' => bool, 'error' => string]
     */
    public static function validateSubjectCode(string $code): array
    {
        $code = trim($code);

        if ($code === '') {
            return ['valid' => false, 'error' => 'Subject code is required.'];
        }

        if (strlen($code) > 20) {
            return ['valid' => false, 'error' => 'Subject code must not exceed 20 characters.'];
        }

        return ['valid' => true, 'error' => ''];
    }

    /**
     * Validates a subject title.
     * 
     * @param string $title
     * @return array ['valid' => bool, 'error' => string]
     */
    public static function validateSubjectTitle(string $title): array
    {
        $title = trim($title);

        if ($title === '') {
            return ['valid' => false, 'error' => 'Subject title is required.'];
        }

        if (strlen($title) > 100) {
            return ['valid' => false, 'error' => 'Subject title must not exceed 100 characters.'];
        }

        return ['valid' => true, 'error' => ''];
    }

    /**
     * Validates a unit number.
     * 
     * @param mixed $unit
     * @return array ['valid' => bool, 'error' => string, 'value' => int|null]
     */
    public static function validateUnit(mixed $unit): array
    {
        $unitValue = is_numeric($unit) ? (int)$unit : null;

        if ($unitValue === null || $unitValue <= 0) {
            return ['valid' => false, 'error' => 'Unit must be a positive number.', 'value' => null];
        }

        return ['valid' => true, 'error' => '', 'value' => $unitValue];
    }

    /**
     * Validates a program code.
     * 
     * @param string $code
     * @return array ['valid' => bool, 'error' => string]
     */
    public static function validateProgramCode(string $code): array
    {
        return self::validateSubjectCode($code);
    }

    /**
     * Validates a program title.
     * 
     * @param string $title
     * @return array ['valid' => bool, 'error' => string]
     */
    public static function validateProgramTitle(string $title): array
    {
        return self::validateSubjectTitle($title);
    }

    /**
     * Validates years.
     * 
     * @param mixed $years
     * @return array ['valid' => bool, 'error' => string, 'value' => int|null]
     */
    public static function validateYears(mixed $years): array
    {
        $yearsValue = is_numeric($years) ? (int)$years : null;

        if ($yearsValue === null || $yearsValue <= 0 || $yearsValue > 10) {
            return ['valid' => false, 'error' => 'Years must be between 1 and 10.', 'value' => null];
        }

        return ['valid' => true, 'error' => '', 'value' => $yearsValue];
    }

    /**
     * Validates an ID is a positive integer.
     * 
     * @param mixed $id
     * @return array ['valid' => bool, 'error' => string, 'value' => int|null]
     */
    public static function validateId(mixed $id): array
    {
        if (!is_numeric($id) || !ctype_digit((string)$id)) {
            return ['valid' => false, 'error' => 'Invalid ID provided.', 'value' => null];
        }

        $idValue = (int)$id;

        if ($idValue <= 0) {
            return ['valid' => false, 'error' => 'Invalid ID provided.', 'value' => null];
        }

        return ['valid' => true, 'error' => '', 'value' => $idValue];
    }
}
