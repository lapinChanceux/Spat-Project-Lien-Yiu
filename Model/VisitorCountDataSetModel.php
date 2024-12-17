<?php
require_once('Database.php');

class VisitorCountDataSetModel {
    protected $_dbHandle, $_dbInstance;

    // Constructor: Set up the database connection
    public function __construct()
    {
        $this->_dbInstance = Database::getInstance();
        $this->_dbHandle = $this->_dbInstance->getdbConnection();
    }
}