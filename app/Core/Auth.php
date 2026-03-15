<?php

namespace App\Core;

/**
 * Authentication and authorization handler following PSR-1 conventions.
 * Manages login, logout, session checks, and role-based access control.
 */
class Auth
{
    private SessionManager $sessionManager;

    /**
     * Constructor initializes SessionManager dependency.
     * 
     * @param SessionManager|null $sessionManager
     */
    public function __construct(?SessionManager $sessionManager = null)
    {
        // Default to a new SessionManager so the typed property is never null
        $this->sessionManager = $sessionManager ?? new SessionManager();
        SessionManager::start();
    }

    /**
     * Logs in a user with their credentials.
     * 
     * @param string $username
     * @param string $password
     * @param mysqli $connection
     * @return array ['success' => bool, 'error' => string, 'user' => array|null]
     */
    public function login(string $username, string $password, \mysqli $connection): array
    {
        $username = trim($username);
        $password = trim($password);

        if ($username === '' || $password === '') {
            return ['success' => false, 'error' => 'Username and password are required.', 'user' => null];
        }

        try {
            $stmt = $connection->prepare('SELECT id, username, password, account_type FROM users WHERE username = ?');
            if (!$stmt) {
                return ['success' => false, 'error' => 'Unable to process login right now.', 'user' => null];
            }

            $stmt->bind_param('s', $username);
            $stmt->execute();
            $stmt->bind_result($userId, $dbUsername, $hash, $accountType);

            if ($stmt->fetch() && password_verify($password, $hash)) {
                $stmt->close();

                // Successful login: regenerate session and store user data
                SessionManager::regenerateId(true);
                SessionManager::set('user_id', $userId);
                SessionManager::set('username', $dbUsername);
                SessionManager::set('account_type', $accountType);
                SessionManager::set('logged_in', true);

                return [
                    'success' => true,
                    'error' => '',
                    'user' => [
                        'id' => $userId,
                        'username' => $dbUsername,
                        'account_type' => $accountType
                    ]
                ];
            }

            $stmt->close();
            return ['success' => false, 'error' => 'Invalid username or password.', 'user' => null];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Unable to process login right now.', 'user' => null];
        }
    }

    /**
     * Logs out the current user.
     * 
     * @return void
     */
    public function logout(): void
    {
        SessionManager::destroy();
    }

    /**
     * Checks if a user is logged in.
     * 
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        return SessionManager::has('user_id');
    }

    /**
     * Gets the current user's data.
     * 
     * @return array|null User data or null if not logged in
     */
    public function getCurrentUser(): ?array
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'id' => SessionManager::get('user_id'),
            'username' => SessionManager::get('username'),
            'account_type' => SessionManager::get('account_type'),
        ];
    }

    /**
     * Checks if the current user has a specific role.
     * 
     * @param string|array $roles Single role or array of roles
     * @return bool
     */
    public function hasRole($roles): bool
    {
        if (!$this->isLoggedIn()) {
            return false;
        }

        $userRole = SessionManager::get('account_type');
        $rolesArray = is_array($roles) ? $roles : [$roles];

        return in_array($userRole, $rolesArray, true);
    }

    /**
     * Checks if the user is an admin.
     * 
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Checks if the user is staff or admin.
     * 
     * @return bool
     */
    public function isStaffOrAdmin(): bool
    {
        return $this->hasRole(['admin', 'staff']);
    }

    /**
     * Checks if the user can manage the catalog (subjects and programs).
     * 
     * @return bool
     */
    public function canManageCatalog(): bool
    {
        return $this->hasRole(['admin', 'staff']);
    }
}
