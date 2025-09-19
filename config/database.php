<?php
/**
 * Database Configuration
 * Learning Materials Management System
 */

class DatabaseConfig {
    // Database connection parameters
    private const DB_HOST = 'localhost';
    private const DB_NAME = 'learning_materials';
    private const DB_USER = 'root';  // Change in production
    private const DB_PASS = '';      // Change in production
    private const DB_CHARSET = 'utf8mb4';
    
    /**
     * Get database connection parameters
     */
    public static function getConfig() {
        return [
            'host' => self::DB_HOST,
            'dbname' => self::DB_NAME,
            'username' => self::DB_USER,
            'password' => self::DB_PASS,
            'charset' => self::DB_CHARSET
        ];
    }
    
    /**
     * Get DSN string for PDO connection
     */
    public static function getDSN() {
        return sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            self::DB_HOST,
            self::DB_NAME,
            self::DB_CHARSET
        );
    }
    
    /**
     * Get PDO options for secure connection
     */
    public static function getPDOOptions() {
        return [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
    }
}
?>