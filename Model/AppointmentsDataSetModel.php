<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('Database.php'); // Make sure this path is correct

class AppointmentsDataSetModel {
    protected $_dbHandle, $_dbInstance;

    // Constructor: Set up the database connection
    public function __construct() {
        $this->_dbInstance = Database::getInstance();
        $this->_dbHandle = $this->_dbInstance->getdbConnection();
    }

    public function getAppointments(){
        $sqlQuery = "SELECT * FROM Appointments";
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->execute();
        $appointments = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $appointments;
    }

    public function getAppointmentId($id) {
        $sqlQuery = "SELECT * FROM Appointments WHERE appointment_id = :id";
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->bindParam(':id', $id);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_OBJ);
    }

    public function getUsers(){
        $sqlQuery = "SELECT * FROM Users";
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->execute();
        $users = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $users;
    }
}

// Example usage
$model = new AppointmentsDataSetModel();
$users = $model->getUsers();

// Check connection explicitly
if (count($users) > 0) {
    echo "<html><head><title>Users List</title></head><body>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Info</th><th>Email</th><th>Password</th></tr>"; // Table header

    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($user['id']) . "</td>"; // Data cells
        echo "<td>" . htmlspecialchars($user['info']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . htmlspecialchars($user['password']) . "</td>";
        echo "</tr>";
    }

    echo "</table>"; // Closing table tag
    echo "</body></html>"; // Closing body and html tags
} else {
    echo "No users found.";
}
?>
