<?php
require_once 'Database.php';

class VisitorModel
{
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getdbConnection();
    }

    // Check if the latest visit by the IP address was more than 10 minutes ago
    public function shouldRecordVisit($ipAddress) {
        $query = "SELECT MAX(visit_date) as last_visit 
                  FROM visitors 
                  WHERE ip_address = :ip_address";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':ip_address', $ipAddress, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // If no visits found, allow insertion
        if (!$result['last_visit']) {
            return true;
        }

        // Compare the last visit timestamp with the current time using SQLite directly
        $query = "SELECT CASE 
                            WHEN datetime('now') > datetime(:last_visit, '+10 minutes') THEN 1 
                            ELSE 0 
                        END as allow_insert";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':last_visit', $result['last_visit'], PDO::PARAM_STR);
        $stmt->execute();
        $check = $stmt->fetch(PDO::FETCH_ASSOC);

        return $check['allow_insert'] == 1;
    }

    // Insert the visit if it's been more than 10 minutes
    public function recordVisit($ipAddress) {
        if ($this->shouldRecordVisit($ipAddress)) {
            $insertQuery = "INSERT INTO visitors (ip_address, visit_date) VALUES (:ip_address, datetime('now'))";
            $insertStmt = $this->db->prepare($insertQuery);
            $insertStmt->bindParam(':ip_address', $ipAddress, PDO::PARAM_STR);
            $insertStmt->execute();
        }
    }

    // Get the total count of records in the table
    public function getVisitorCount() {
        $query = "SELECT COUNT(*) as total FROM visitors";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return isset($result['total']) ? $result['total'] : 0;
    }
}
