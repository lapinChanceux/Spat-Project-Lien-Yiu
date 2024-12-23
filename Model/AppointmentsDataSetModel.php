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
        $sqlQuery = "SELECT appointments.*, serviceStatus.status 
                     FROM appointments
                     LEFT JOIN serviceStatus 
                     ON serviceStatus.appointment_id = appointments.appointment_id";
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->execute();
        $appointments = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $appointments;
    }

    public function getAppointmentsCount() {
        $sqlQuery = "SELECT COUNT(*) FROM appointments";
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->execute();
        return $statement->fetchColumn();
    }

    public function getAppointmentsCountByStatus($status) {
        $sqlQuery = "SELECT COUNT(*) FROM appointments
                 LEFT JOIN serviceStatus ON appointments.appointment_id = serviceStatus.appointment_id
                 WHERE serviceStatus.status = :status";
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->bindParam(':status', $status, PDO::PARAM_STR);
        $statement->execute();
        return $statement->fetchColumn();
    }

    public function getAppointmentId($id) {
        $sqlQuery = "SELECT * FROM appointments WHERE appointment_id = :id";
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->bindParam(':id', $id);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_OBJ);
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

    public function countAppointmentsByDateTime($appointmentDate, $appointmentTime)
    {
        $sqlQuery = "SELECT COUNT(*) as total FROM appointments WHERE appointment_date = :appointment_date AND appointment_time = :appointment_time";
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->bindParam(':appointment_date', $appointmentDate);
        $statement->bindParam(':appointment_time', $appointmentTime);
        $statement->execute();

        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
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
        $sqlQuery = "INSERT INTO appointments (customer_name, car_number, car_type, appointment_date, appointment_time, phone_number, email, remark, created_at)
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
        if ($statement->execute()) {
            // Get the last inserted appointment ID
            $appointmentId = $this->_dbHandle->lastInsertId();

            // Insert into ServiceStatuses table
            $statusStatement = $this->_dbHandle->prepare("INSERT INTO serviceStatus (status, appointment_id) VALUES (:status, :appointment_id)");
            $defaultStatus = 'Pending'; // Default status
            $statusStatement->bindParam(':status', $defaultStatus, PDO::PARAM_STR);
            $statusStatement->bindParam(':appointment_id', $appointmentId, PDO::PARAM_INT);

            return $statusStatement->execute(); // Return true if successful
        }

        return false;
    }
    public function updateAppointmentStatus($appointmentId, $status){
        $statement = $this->_dbHandle->prepare("UPDATE serviceStatus SET status = :status WHERE appointment_id = :appointment_id");
        $statement->bindParam(':status', $status, PDO::PARAM_STR);
        $statement->bindParam(':appointment_id', $appointmentId, PDO::PARAM_INT);
        return $statement->execute();
    }
    public function getLatestAppointmentByCarNumber($carNumber) {
        $query = "SELECT * FROM appointments 
              WHERE car_number = :carNumber 
              ORDER BY created_at DESC 
              LIMIT 1";
        $stmt = $this->_dbHandle->prepare($query);
        $stmt->bindParam(':carNumber', $carNumber, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //Delete appointment
    public function deleteAppointmentById($id) {
        // Begin transaction
        $this->_dbHandle->beginTransaction();

        // Delete from serviceStatus
        $sqlQueryServiceStatus = "DELETE FROM serviceStatus WHERE appointment_id = :id";
        $statementServiceStatus = $this->_dbHandle->prepare($sqlQueryServiceStatus);
        $resultServiceStatus = $statementServiceStatus->execute([':id' => $id]);

        // Delete from appointments
        $sqlQueryAppointments = "DELETE FROM appointments WHERE appointment_id = :id";
        $statementAppointments = $this->_dbHandle->prepare($sqlQueryAppointments);
        $resultAppointments = $statementAppointments->execute([':id' => $id]);

        // Check results before committing
        if ($resultServiceStatus && $resultAppointments) {
            $this->_dbHandle->commit();
            return true;
        }

        // Rollback if either operation fails
        $this->_dbHandle->rollBack();
        return false;
    }

}