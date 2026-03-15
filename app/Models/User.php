<?php

namespace App\Models;

use mysqli;
use App\Helpers\Validator;

/**
 * User model handling CRUD operations for user accounts.
 * Manages user creation, updates, listing, and password changes.
 */
class User
{
    private mysqli $conn;
    private array $accountTypes = ['admin', 'staff', 'teacher', 'student'];

    /**
     * Constructor initializes the database connection.
     * 
     * @param mysqli $connection
     */
    public function __construct(mysqli $connection)
    {
        $this->conn = $connection;
    }

    /**
     * Gets all available account types.
     * 
     * @return array
     */
    public function getAccountTypes(): array
    {
        return $this->accountTypes;
    }

    /**
     * Creates a new user account.
     * 
     * @param string $username
     * @param string $accountType
     * @param string $password
     * @param string $confirmPassword
     * @param int $adminId
     * @return array ['success' => bool, 'error' => string]
     */
    public function create(string $username, string $accountType, string $password, string $confirmPassword, int $adminId): array
    {
        // Validate admin context
        if ($adminId <= 0) {
            return ['success' => false, 'error' => 'Invalid administrator context.'];
        }

        // Validate username
        $usernameValidation = Validator::validateUsername($username);
        if (!$usernameValidation['valid']) {
            return ['success' => false, 'error' => $usernameValidation['error']];
        }

        // Validate account type
        $accountTypeValidation = Validator::validateAccountType($accountType, $this->accountTypes);
        if (!$accountTypeValidation['valid']) {
            return ['success' => false, 'error' => $accountTypeValidation['error']];
        }

        // Validate password
        $passwordValidation = Validator::validatePassword($password);
        if (!$passwordValidation['valid']) {
            return ['success' => false, 'error' => $passwordValidation['error']];
        }

        // Validate password match
        $matchValidation = Validator::validatePasswordMatch($password, $confirmPassword);
        if (!$matchValidation['valid']) {
            return ['success' => false, 'error' => $matchValidation['error']];
        }

        // Check if username already exists
        $checkStmt = $this->conn->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
        if (!$checkStmt) {
            return ['success' => false, 'error' => 'Unable to check username availability.'];
        }

        $checkStmt->bind_param('s', $username);
        $checkStmt->execute();
        $checkStmt->bind_result($existingCount);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($existingCount > 0) {
            return ['success' => false, 'error' => 'Username already exists. Please choose a different username.'];
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert new user
        $stmt = $this->conn->prepare('INSERT INTO users (username, password, account_type, created_by) VALUES (?, ?, ?, ?)');
        if (!$stmt) {
            return ['success' => false, 'error' => 'Failed to prepare database statement.'];
        }

        $stmt->bind_param('sssi', $username, $hashedPassword, $accountType, $adminId);
        $created = $stmt->execute();
        $stmt->close();

        if (!$created) {
            return ['success' => false, 'error' => 'Unable to save user. Please try again.'];
        }

        return ['success' => true, 'error' => ''];
    }

    /**
     * Reads users from the database.
     * 
     * @param array $options ['id' => int] for single user or ['search' => string, 'sort' => string] for list
     * @return array ['success' => bool, 'error' => string, 'user' => array|null, 'users' => array]
     */
    public function read(array $options = []): array
    {
        $response = [
            'success' => false,
            'error' => '',
            'user' => null,
            'users' => []
        ];

        $userId = isset($options['id']) ? (int)$options['id'] : null;

        if ($userId !== null) {
            // Get single user by ID
            if ($userId <= 0) {
                $response['error'] = 'Invalid user selected.';
                return $response;
            }

            $stmt = $this->conn->prepare('SELECT id, username, account_type FROM users WHERE id = ?');
            if (!$stmt) {
                $response['error'] = 'Failed to load user.';
                return $response;
            }

            $stmt->bind_param('i', $userId);
            if (!$stmt->execute()) {
                $stmt->close();
                $response['error'] = 'Failed to load user.';
                return $response;
            }

            $stmt->bind_result($id, $username, $accountType);
            if ($stmt->fetch()) {
                $response['success'] = true;
                $response['user'] = [
                    'id' => $id,
                    'username' => $username,
                    'account_type' => $accountType
                ];
            } else {
                $response['error'] = 'User not found.';
            }

            $stmt->close();
            return $response;
        }

        // Get all users
        $sql = 'SELECT id, username, account_type FROM users ORDER BY username';
        $result = $this->conn->query($sql);

        if (!$result) {
            $response['error'] = 'Failed to load users.';
            return $response;
        }

        while ($row = $result->fetch_assoc()) {
            $response['users'][] = $row;
        }

        $result->free();
        $response['success'] = true;
        return $response;
    }

    /**
     * Updates a user account.
     * 
     * @param int $userId
     * @param string $username
     * @param string $accountType
     * @param int $updatedBy
     * @return array ['success' => bool, 'error' => string]
     */
    public function update(int $userId, string $username, string $accountType, int $updatedBy): array
    {
        if ($userId <= 0) {
            return ['success' => false, 'error' => 'Invalid user selected.'];
        }

        // Validate username
        $usernameValidation = Validator::validateUsername($username);
        if (!$usernameValidation['valid']) {
            return ['success' => false, 'error' => $usernameValidation['error']];
        }

        // Validate account type
        $accountTypeValidation = Validator::validateAccountType($accountType, $this->accountTypes);
        if (!$accountTypeValidation['valid']) {
            return ['success' => false, 'error' => $accountTypeValidation['error']];
        }

        // Check if username is already used by another user
        $checkStmt = $this->conn->prepare('SELECT COUNT(*) FROM users WHERE username = ? AND id != ?');
        if (!$checkStmt) {
            return ['success' => false, 'error' => 'Unable to check username availability.'];
        }

        $checkStmt->bind_param('si', $username, $userId);
        $checkStmt->execute();
        $checkStmt->bind_result($existingCount);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($existingCount > 0) {
            return ['success' => false, 'error' => 'Username already exists. Please choose a different username.'];
        }

        // Update user
        $stmt = $this->conn->prepare('UPDATE users SET username = ?, account_type = ?, updated_by = ?, updated_on = NOW() WHERE id = ?');
        if (!$stmt) {
            return ['success' => false, 'error' => 'Failed to prepare database statement.'];
        }

        $stmt->bind_param('ssii', $username, $accountType, $updatedBy, $userId);
        $updated = $stmt->execute();
        $stmt->close();

        if (!$updated) {
            return ['success' => false, 'error' => 'Unable to update user. Please try again.'];
        }

        return ['success' => true, 'error' => ''];
    }

    /**
     * Changes a user's password.
     * 
     * @param int $userId
     * @param string $currentPassword
     * @param string $newPassword
     * @param string $confirmPassword
     * @return array ['success' => bool, 'error' => string]
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword, string $confirmPassword): array
    {
        if ($userId <= 0) {
            return ['success' => false, 'error' => 'Invalid user context.'];
        }

        // Validate new password
        $passwordValidation = Validator::validatePassword($newPassword);
        if (!$passwordValidation['valid']) {
            return ['success' => false, 'error' => $passwordValidation['error']];
        }

        // Validate password match
        $matchValidation = Validator::validatePasswordMatch($newPassword, $confirmPassword);
        if (!$matchValidation['valid']) {
            return ['success' => false, 'error' => $matchValidation['error']];
        }

        // Get current password hash
        $stmt = $this->conn->prepare('SELECT password FROM users WHERE id = ?');
        if (!$stmt) {
            return ['success' => false, 'error' => 'Unable to verify current password.'];
        }

        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->bind_result($hash);

        if (!$stmt->fetch()) {
            $stmt->close();
            return ['success' => false, 'error' => 'User not found.'];
        }

        $stmt->close();

        // Verify current password
        if (!password_verify($currentPassword, $hash)) {
            return ['success' => false, 'error' => 'Current password is incorrect.'];
        }

        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        // Update password
        $stmt = $this->conn->prepare('UPDATE users SET password = ?, updated_by = ?, updated_on = NOW() WHERE id = ?');
        if (!$stmt) {
            return ['success' => false, 'error' => 'Failed to prepare database statement.'];
        }

        $stmt->bind_param('sii', $hashedPassword, $userId, $userId);
        $updated = $stmt->execute();
        $stmt->close();

        if (!$updated) {
            return ['success' => false, 'error' => 'Unable to change password. Please try again.'];
        }

        return ['success' => true, 'error' => ''];
    }

    /**
     * Deletes a user (for admin use).
     * 
     * @param int $userId
     * @return array ['success' => bool, 'error' => string]
     */
    public function delete(int $userId): array
    {
        if ($userId <= 0) {
            return ['success' => false, 'error' => 'Invalid user selected.'];
        }

        // Prevent deleting the admin user (id = 1)
        if ($userId === 1) {
            return ['success' => false, 'error' => 'Cannot delete the default admin user.'];
        }

        $stmt = $this->conn->prepare('DELETE FROM users WHERE id = ?');
        if (!$stmt) {
            return ['success' => false, 'error' => 'Failed to prepare database statement.'];
        }

        $stmt->bind_param('i', $userId);
        $deleted = $stmt->execute();
        $stmt->close();

        if (!$deleted) {
            return ['success' => false, 'error' => 'Unable to delete user. Please try again.'];
        }

        return ['success' => true, 'error' => ''];
    }
}
