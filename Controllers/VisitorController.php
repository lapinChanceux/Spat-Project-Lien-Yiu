<?php
require_once 'Model/VisitorModel.php';

class VisitorController {
    private $visitorModel;

    public function __construct() {
        $this->visitorModel = new VisitorModel();
    }

    public function recordVisit() {
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $this->visitorModel->recordVisit($ipAddress);
    }

    public function getVisitorCount() {
        return $this->visitorModel->getVisitorCount();
    }
}

// Main Logic
$visitorController = new VisitorController();
$visitorController->recordVisit();
$visitorCount = $visitorController->getVisitorCount();

