<?php

namespace App\Models;

use mysqli;
use App\Helpers\Validator;

/**
 * Subject model handling CRUD operations for subjects.
 * Manages subject creation, updates, reading, and deletion.
 */
class Subject
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
     * Creates a new subject.
     * 
     * @param string $code
     * @param string $title
     * @param mixed $unit
     * @return array ['success' => bool, 'error' => string]
     */
    public function create(string $code, string $title, mixed $unit): array
    {
        // Validate code
        $codeValidation = Validator::validateSubjectCode($code);
        if (!$codeValidation['valid']) {
            return ['success' => false, 'error' => $codeValidation['error']];
        }

        // Validate title
        $titleValidation = Validator::validateSubjectTitle($title);
        if (!$titleValidation['valid']) {
            return ['success' => false, 'error' => $titleValidation['error']];
        }

        // Validate unit
        $unitValidation = Validator::validateUnit($unit);
        if (!$unitValidation['valid']) {
            return ['success' => false, 'error' => $unitValidation['error']];
        }

        $code = trim($code);
        $title = trim($title);
        $unitValue = $unitValidation['value'];

        // Check for duplicate code
        $checkStmt = $this->conn->prepare('SELECT COUNT(*) FROM subject WHERE code = ?');
        if (!$checkStmt) {
            return ['success' => false, 'error' => 'Failed to prepare duplicate check statement.'];
        }

        $checkStmt->bind_param('s', $code);
        $checkStmt->execute();
        $checkStmt->bind_result($existingCount);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($existingCount > 0) {
            return ['success' => false, 'error' => 'Subject code already exists. Please use a different code.'];
        }

        // Insert new subject
        $stmt = $this->conn->prepare('INSERT INTO subject (code, title, unit) VALUES (?, ?, ?)');
        if (!$stmt) {
            return ['success' => false, 'error' => 'Failed to prepare database statement.'];
        }

        $stmt->bind_param('ssi', $code, $title, $unitValue);
        $created = $stmt->execute();
        $stmt->close();

        if (!$created) {
            return ['success' => false, 'error' => 'Unable to save subject. Please try again.'];
        }

        return ['success' => true, 'error' => ''];
    }

    /**
     * Reads subjects from the database.
     * 
     * @param array $options ['id' => int] for single subject or ['search' => string, 'sort' => string] for list
     * @return array ['success' => bool, 'error' => string, 'subject' => array|null, 'subjects' => array]
     */
    public function read(array $options = []): array
    {
        $response = [
            'success' => false,
            'error' => '',
            'subject' => null,
            'subjects' => []
        ];

        $subjectId = isset($options['id']) ? (int)$options['id'] : null;

        if ($subjectId !== null) {
            // Get single subject by ID
            if ($subjectId <= 0) {
                $response['error'] = 'Invalid subject selected.';
                return $response;
            }

            $stmt = $this->conn->prepare('SELECT subject_id, code, title, unit FROM subject WHERE subject_id = ?');
            if (!$stmt) {
                $response['error'] = 'Failed to load subject.';
                return $response;
            }

            $stmt->bind_param('i', $subjectId);
            if (!$stmt->execute()) {
                $stmt->close();
                $response['error'] = 'Failed to load subject.';
                return $response;
            }

            $stmt->bind_result($id, $code, $title, $unit);
            if ($stmt->fetch()) {
                $response['success'] = true;
                $response['subject'] = [
                    'subject_id' => $id,
                    'code' => $code,
                    'title' => $title,
                    'unit' => $unit
                ];
            } else {
                $response['error'] = 'Subject not found.';
            }

            $stmt->close();
            return $response;
        }

        // Get all subjects with optional search and sort
        $search = trim((string)($options['search'] ?? ''));
        $sort = (string)($options['sort'] ?? 'title');

        $sql = 'SELECT subject_id, code, title, unit FROM subject';

        if ($search !== '') {
            $escaped = $this->conn->real_escape_string($search);
            $like = '%' . $escaped . '%';
            $sql .= " WHERE code LIKE '{$like}' OR title LIKE '{$like}' OR CAST(unit AS CHAR) LIKE '{$like}'";
        }

        $validSort = ['title' => 'title', 'code' => 'code', 'unit' => 'unit'];
        $orderColumn = $validSort[$sort] ?? 'title';
        $sql .= " ORDER BY {$orderColumn}";

        $result = $this->conn->query($sql);
        if (!$result) {
            $response['error'] = 'Failed to load subjects.';
            return $response;
        }

        while ($row = $result->fetch_assoc()) {
            $response['subjects'][] = $row;
        }

        $result->free();
        $response['success'] = true;
        return $response;
    }

    /**
     * Updates an existing subject.
     * 
     * @param int $subjectId
     * @param string $code
     * @param string $title
     * @param mixed $unit
     * @return array ['success' => bool, 'error' => string]
     */
    public function update(int $subjectId, string $code, string $title, mixed $unit): array
    {
        if ($subjectId <= 0) {
            return ['success' => false, 'error' => 'Invalid subject selected.'];
        }

        // Validate code
        $codeValidation = Validator::validateSubjectCode($code);
        if (!$codeValidation['valid']) {
            return ['success' => false, 'error' => $codeValidation['error']];
        }

        // Validate title
        $titleValidation = Validator::validateSubjectTitle($title);
        if (!$titleValidation['valid']) {
            return ['success' => false, 'error' => $titleValidation['error']];
        }

        // Validate unit
        $unitValidation = Validator::validateUnit($unit);
        if (!$unitValidation['valid']) {
            return ['success' => false, 'error' => $unitValidation['error']];
        }

        $code = trim($code);
        $title = trim($title);
        $unitValue = $unitValidation['value'];

        // Check if code is unique (excluding current subject)
        $checkStmt = $this->conn->prepare('SELECT COUNT(*) FROM subject WHERE code = ? AND subject_id != ?');
        if (!$checkStmt) {
            return ['success' => false, 'error' => 'Unable to check code availability.'];
        }

        $checkStmt->bind_param('si', $code, $subjectId);
        $checkStmt->execute();
        $checkStmt->bind_result($existingCount);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($existingCount > 0) {
            return ['success' => false, 'error' => 'Subject code already exists. Please use a different code.'];
        }

        // Update subject
        $stmt = $this->conn->prepare('UPDATE subject SET code = ?, title = ?, unit = ? WHERE subject_id = ?');
        if (!$stmt) {
            return ['success' => false, 'error' => 'Failed to prepare database statement.'];
        }

        $stmt->bind_param('ssii', $code, $title, $unitValue, $subjectId);
        $updated = $stmt->execute();
        $stmt->close();

        if (!$updated) {
            return ['success' => false, 'error' => 'Unable to update subject. Please try again.'];
        }

        return ['success' => true, 'error' => ''];
    }

    /**
     * Deletes a subject.
     * 
     * @param int $subjectId
     * @return array ['success' => bool, 'error' => string]
     */
    public function delete(int $subjectId): array
    {
        if ($subjectId <= 0) {
            return ['success' => false, 'error' => 'Invalid subject selected.'];
        }

        $stmt = $this->conn->prepare('DELETE FROM subject WHERE subject_id = ?');
        if (!$stmt) {
            return ['success' => false, 'error' => 'Failed to prepare database statement.'];
        }

        $stmt->bind_param('i', $subjectId);
        $deleted = $stmt->execute();
        $stmt->close();

        if (!$deleted) {
            return ['success' => false, 'error' => 'Unable to delete subject. Please try again.'];
        }

        return ['success' => true, 'error' => ''];
    }
}
