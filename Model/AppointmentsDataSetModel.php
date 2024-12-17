<?php

// AppointmentsDataSetModel.php
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

    public function getCarBrands()
    {
        $statement = $this->_dbHandle->prepare('SELECT * FROM carBrands');
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCarModels($brand_id)
    {
        $statement = $this->_dbHandle->prepare('SELECT * FROM carModels WHERE brand_id = :brand_id');
        $statement->bindParam(':brand_id', $brand_id, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertAppointment($fullName, $carNumber, $carBrandId, $carModelId, $appointmentDate, $appointmentTime, $phoneNumber, $email, $remark)
    {
        // Set timezone to Malaysia
        date_default_timezone_set('Asia/Kuala_Lumpur');

        // Query the brand name using the brand ID
        $brandQuery = "SELECT brand_name FROM carBrands WHERE brand_id = :brand_id";
        $brandStatement = $this->_dbHandle->prepare($brandQuery);
        $brandStatement->bindParam(':brand_id', $carBrandId, PDO::PARAM_INT);
        $brandStatement->execute();
        $carBrandName = $brandStatement->fetchColumn();

        // If no match, use a fallback name
        $carBrandName = $carBrandName ?: "Unknown Brand";

        // Query the model name using the model ID
        $modelQuery = "SELECT model_name FROM carModels WHERE model_id = :model_id";
        $modelStatement = $this->_dbHandle->prepare($modelQuery);
        $modelStatement->bindParam(':model_id', $carModelId, PDO::PARAM_INT);
        $modelStatement->execute();
        $carModelName = $modelStatement->fetchColumn();

        // If no match, use a fallback name
        $carModelName = $carModelName ?: "Unknown Model";

        // Concatenate brand name and model name to form car type
        $carType = $carBrandName . ' ' . $carModelName;

        // Get current Malaysian time
        $createdAt = date('Y-m-d H:i:s');

        // Insert the appointment into the database
        $sqlQuery = "INSERT INTO Appointments (customer_name, car_number, car_type, appointment_date, appointment_time, phone_number, email, remark, created_at)
                 VALUES (:full_name, :car_number, :car_type, :appointment_date, :appointment_time, :phone_number, :email, :remark, :created_at)";

        $statement = $this->_dbHandle->prepare($sqlQuery);

        $statement->bindParam(':full_name', $fullName);
        $statement->bindParam(':car_number', $carNumber);
        $statement->bindParam(':car_type', $carType);
        $statement->bindParam(':appointment_date', $appointmentDate);
        $statement->bindParam(':appointment_time', $appointmentTime);
        $statement->bindParam(':phone_number', $phoneNumber);
        $statement->bindValue(':email', $email ?: null, PDO::PARAM_STR);
        $statement->bindValue(':remark', $remark ?: null, PDO::PARAM_STR);
        $statement->bindParam(':created_at', $createdAt);

        return $statement->execute();
    }



}

// Example usage
//$model = new AppointmentsDataSetModel();
//$users = $model->getUsers();

// Check connection explicitly
//if (count($users) > 0) {
// echo "<html><head><title>Users List</title></head><body>";
// echo "<table border='1'>";
//  echo "<tr><th>ID</th><th>Info</th><th>Email</th><th>Password</th></tr>"; // Table header

//  foreach ($users as $user) {
//     echo "<tr>";
//    echo "<td>" . htmlspecialchars($user['id']) . "</td>"; // Data cells
//    echo "<td>" . htmlspecialchars($user['info']) . "</td>";
//     echo "<td>" . htmlspecialchars($user['email']) . "</td>";
//    echo "<td>" . htmlspecialchars($user['password']) . "</td>";
//    echo "</tr>";
//  }

//  echo "</table>"; // Closing table tag
//  echo "</body></html>"; // Closing body and html tags
//} else {
//   echo "No users found.";
//}
