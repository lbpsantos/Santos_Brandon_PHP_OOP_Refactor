<?php

namespace App\Models;

use mysqli;
use App\Helpers\Validator;

/**
 * Program model handling CRUD operations for programs.
 * Manages program creation, updates, reading, and deletion.
 */
class Program
{
    private mysqli $conn;

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
     * Creates a new program.
     * 
     * @param string $code
     * @param string $title
     * @param mixed $years
     * @return array ['success' => bool, 'error' => string]
     */
    public function create(string $code, string $title, mixed $years): array
    {
        // Validate code
        $codeValidation = Validator::validateProgramCode($code);
        if (!$codeValidation['valid']) {
            return ['success' => false, 'error' => $codeValidation['error']];
        }

        // Validate title
        $titleValidation = Validator::validateProgramTitle($title);
        if (!$titleValidation['valid']) {
            return ['success' => false, 'error' => $titleValidation['error']];
        }

        // Validate years
        $yearsValidation = Validator::validateYears($years);
        if (!$yearsValidation['valid']) {
            return ['success' => false, 'error' => $yearsValidation['error']];
        }

        $code = trim($code);
        $title = trim($title);
        $yearsValue = $yearsValidation['value'];

        // Check for duplicate code
        $checkStmt = $this->conn->prepare('SELECT COUNT(*) FROM program WHERE code = ?');
        if (!$checkStmt) {
            return ['success' => false, 'error' => 'Failed to prepare duplicate check statement.'];
        }

        $checkStmt->bind_param('s', $code);
        $checkStmt->execute();
        $checkStmt->bind_result($existingCount);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($existingCount > 0) {
            return ['success' => false, 'error' => 'Program code already exists. Please use a different code.'];
        }

        // Insert new program
        $stmt = $this->conn->prepare('INSERT INTO program (code, title, years) VALUES (?, ?, ?)');
        if (!$stmt) {
            return ['success' => false, 'error' => 'Failed to prepare database statement.'];
        }

        $stmt->bind_param('ssi', $code, $title, $yearsValue);
        $created = $stmt->execute();
        $stmt->close();

        if (!$created) {
            return ['success' => false, 'error' => 'Unable to save program. Please try again.'];
        }

        return ['success' => true, 'error' => ''];
    }

    /**
     * Reads programs from the database.
     * 
     * @param array $options ['id' => int] for single program or ['search' => string, 'sort' => string] for list
     * @return array ['success' => bool, 'error' => string, 'program' => array|null, 'programs' => array]
     */
    public function read(array $options = []): array
    {
        $response = [
            'success' => false,
            'error' => '',
            'program' => null,
            'programs' => []
        ];

        $programId = isset($options['id']) ? (int)$options['id'] : null;

        if ($programId !== null) {
            // Get single program by ID
            if ($programId <= 0) {
                $response['error'] = 'Invalid program selected.';
                return $response;
            }

            $stmt = $this->conn->prepare('SELECT program_id, code, title, years FROM program WHERE program_id = ?');
            if (!$stmt) {
                $response['error'] = 'Failed to load program.';
                return $response;
            }

            $stmt->bind_param('i', $programId);
            if (!$stmt->execute()) {
                $stmt->close();
                $response['error'] = 'Failed to load program.';
                return $response;
            }

            $stmt->bind_result($id, $code, $title, $years);
            if ($stmt->fetch()) {
                $response['success'] = true;
                $response['program'] = [
                    'program_id' => $id,
                    'code' => $code,
                    'title' => $title,
                    'years' => $years
                ];
            } else {
                $response['error'] = 'Program not found.';
            }

            $stmt->close();
            return $response;
        }

        // Get all programs with optional search and sort
        $search = trim((string)($options['search'] ?? ''));
        $sort = (string)($options['sort'] ?? 'title');

        $sql = 'SELECT program_id, code, title, years FROM program';

        if ($search !== '') {
            $escaped = $this->conn->real_escape_string($search);
            $like = '%' . $escaped . '%';
            $sql .= " WHERE code LIKE '{$like}' OR title LIKE '{$like}' OR CAST(years AS CHAR) LIKE '{$like}'";
        }

        $validSort = ['title' => 'title', 'code' => 'code', 'years' => 'years'];
        $orderColumn = $validSort[$sort] ?? 'title';
        $sql .= " ORDER BY {$orderColumn}";

        $result = $this->conn->query($sql);
        if (!$result) {
            $response['error'] = 'Failed to load programs.';
            return $response;
        }

        while ($row = $result->fetch_assoc()) {
            $response['programs'][] = $row;
        }

        $result->free();
        $response['success'] = true;
        return $response;
    }

    /**
     * Updates an existing program.
     * 
     * @param int $programId
     * @param string $code
     * @param string $title
     * @param mixed $years
     * @return array ['success' => bool, 'error' => string]
     */
    public function update(int $programId, string $code, string $title, mixed $years): array
    {
        if ($programId <= 0) {
            return ['success' => false, 'error' => 'Invalid program selected.'];
        }

        // Validate code
        $codeValidation = Validator::validateProgramCode($code);
        if (!$codeValidation['valid']) {
            return ['success' => false, 'error' => $codeValidation['error']];
        }

        // Validate title
        $titleValidation = Validator::validateProgramTitle($title);
        if (!$titleValidation['valid']) {
            return ['success' => false, 'error' => $titleValidation['error']];
        }

        // Validate years
        $yearsValidation = Validator::validateYears($years);
        if (!$yearsValidation['valid']) {
            return ['success' => false, 'error' => $yearsValidation['error']];
        }

        $code = trim($code);
        $title = trim($title);
        $yearsValue = $yearsValidation['value'];

        // Check if code is unique (excluding current program)
        $checkStmt = $this->conn->prepare('SELECT COUNT(*) FROM program WHERE code = ? AND program_id != ?');
        if (!$checkStmt) {
            return ['success' => false, 'error' => 'Unable to check code availability.'];
        }

        $checkStmt->bind_param('si', $code, $programId);
        $checkStmt->execute();
        $checkStmt->bind_result($existingCount);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($existingCount > 0) {
            return ['success' => false, 'error' => 'Program code already exists. Please use a different code.'];
        }

        // Update program
        $stmt = $this->conn->prepare('UPDATE program SET code = ?, title = ?, years = ? WHERE program_id = ?');
        if (!$stmt) {
            return ['success' => false, 'error' => 'Failed to prepare database statement.'];
        }

        $stmt->bind_param('ssii', $code, $title, $yearsValue, $programId);
        $updated = $stmt->execute();
        $stmt->close();

        if (!$updated) {
            return ['success' => false, 'error' => 'Unable to update program. Please try again.'];
        }

        return ['success' => true, 'error' => ''];
    }

    /**
     * Deletes a program.
     * 
     * @param int $programId
     * @return array ['success' => bool, 'error' => string]
     */
    public function delete(int $programId): array
    {
        if ($programId <= 0) {
            return ['success' => false, 'error' => 'Invalid program selected.'];
        }

        $stmt = $this->conn->prepare('DELETE FROM program WHERE program_id = ?');
        if (!$stmt) {
            return ['success' => false, 'error' => 'Failed to prepare database statement.'];
        }

        $stmt->bind_param('i', $programId);
        $deleted = $stmt->execute();
        $stmt->close();

        if (!$deleted) {
            return ['success' => false, 'error' => 'Unable to delete program. Please try again.'];
        }

        return ['success' => true, 'error' => ''];
    }
}
