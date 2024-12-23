<?php
// DisplayServiceModel.php
require_once 'Database.php';

class DisplayServiceModel
{
    protected $_dbHandle;

    public function __construct()
    {
        $dbInstance = Database::getInstance();
        $this->_dbHandle = $dbInstance->getdbConnection();
    }

    public function getPendingAppointments($date = null)
    {
        return $this->getAppointmentsByStatus('Pending', $date);
    }

    public function getOnServiceAppointments($date = null)
    {
        return $this->getAppointmentsByStatus('On Service', $date);
    }

    public function getCompletedAppointments($date = null)
    {
        return $this->getAppointmentsByStatus('Completed', $date);
    }

    private function getAppointmentsByStatus($status, $date = null)
    {
        try {
            if (is_null($date)) {
                $date = date('Y-m-d');
            }

            // Prepare SQL query
            $query = "SELECT a.*, s.status
FROM appointments a
JOIN serviceStatus s ON a.appointment_id = s.appointment_id
WHERE a.appointment_date = :date
  AND s.status = :status;";

            $statement = $this->_dbHandle->prepare($query);

            // Bind parameters
            $statement->bindParam(':date', $date);
            $statement->bindParam(':status', $status);

            // Execute query
            $statement->execute();

            // Fetch results
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Handle exceptions
            error_log('Database Query Error: ' . $e->getMessage());
            return [];
        }
    }
}



