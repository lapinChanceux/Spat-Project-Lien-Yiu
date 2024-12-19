<?php

class Database {

    protected static $_dbInstance = null;

    protected $_dbHandle;

    public static function getInstance() {
        // Check if an instance already exists
        if (self::$_dbInstance === null) {
            // If not, create a new instance
            self::$_dbInstance = new self();
        }
        return self::$_dbInstance; // Return the single instance
    }

    // Private constructor to prevent creating new instances directly
    private function __construct() {
        try {
            // Create a new PDO connection to the SQLite database
            $this->_dbHandle = new PDO("sqlite:lienyiuDB.sqlite");
        } catch (PDOException $e) {
            // Handle any connection errors
            echo "Database connection failed: " . $e->getMessage();
        }
    }

    public function getdbConnection() {
        return $this->_dbHandle; // Provide the PDO handle for use in other classes
    }

    // Destructor to clean up the database connection
    public function __destruct() {
        $this->_dbHandle = null; // Close the PDO connection when the object is destroyed
    }
}
