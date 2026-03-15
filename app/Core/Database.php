<?php

namespace App\Core;

use mysqli;
use mysqli_sql_exception;

/**
 * Singleton Database connection handler following PSR-1 conventions.
 * Manages MySQLi database connections and provides access to the connection object.
 */
class Database
{
    private static ?Database $instance = null;
    private mysqli $connection;
    
    private const DB_HOST = 'localhost';
    private const DB_USER = 'root';
    private const DB_PASSWORD = '';
    private const DB_NAME = 'school';

    /**
     * Constructor is private to enforce singleton pattern.
     * Establishes connection to MySQL database.
     * 
     * @throws mysqli_sql_exception if connection fails
     */
    private function __construct()
    {
        // Enable exception throwing for errors
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        
        try {
            $this->connection = new mysqli(
                self::DB_HOST,
                self::DB_USER,
                self::DB_PASSWORD,
                self::DB_NAME
            );
            
            // Set charset to utf8mb4
            $this->connection->set_charset('utf8mb4');
        } catch (mysqli_sql_exception $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Gets the singleton instance of the Database.
     * 
     * @return Database
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Returns the MySQLi connection object.
     * 
     * @return mysqli
     */
    public function getConnection(): mysqli
    {
        return $this->connection;
    }

    /**
     * Closes the database connection.
     * 
     * @return void
     */
    public function closeConnection(): void
    {
        if ($this->connection) {
            $this->connection->close();
        }
    }

    /**
     * Prevent cloning of the singleton.
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserialization of the singleton.
     */
    public function __wakeup()
    {
        throw new \Exception('Cannot unserialize singleton');
    }
}
