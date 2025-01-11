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
        $sqlQuery = "SELECT appointment_id, customer_name, email, appointment_date, appointment_time FROM appointments
              WHERE appointment_id = :id";
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

    public function insertReceipt($appointmentId, $serviceInfo, $price)
    {
        $statement = $this->_dbHandle->prepare("
        INSERT INTO receipt (appointment_id, service_info, price) 
        VALUES (:appointment_id, :service_info, :price)
    ");
        $statement->bindParam(':appointment_id', $appointmentId, PDO::PARAM_INT);
        $statement->bindParam(':service_info', $serviceInfo, PDO::PARAM_STR);
        $statement->bindParam(':price', $price, PDO::PARAM_INT);
        return $statement->execute();
    }

    public function getLatestAppointmentByCarNumber($carNumber,$fullName) {
        $query = "SELECT * FROM appointments 
              WHERE car_number = :carNumber 
              AND customer_name = :name
              ORDER BY created_at DESC 
              LIMIT 1";
        $stmt = $this->_dbHandle->prepare($query);
        $stmt->bindParam(':carNumber', $carNumber, PDO::PARAM_STR);
        $stmt->bindParam(':name', $fullName, PDO::PARAM_STR);
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

    public function getAppointmentById($appointmentId)
    {
        $query = "
        SELECT 
            a.appointment_id, 
            a.customer_name, 
            a.car_number, 
            a.car_type, 
            a.appointment_date, 
            a.appointment_time, 
            r.receipt_id,
            r.service_info,
            r.price
        FROM 
            appointments a
        LEFT JOIN 
            receipt r ON a.appointment_id = r.appointment_id
        WHERE 
            a.appointment_id = :appointment_id
    ";

        $statement = $this->_dbHandle->prepare($query);
        $statement->bindParam(':appointment_id', $appointmentId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function createPDF($appointmentDetails)
    {
        // Include the mPDF library
        require_once 'vendor/autoload.php';

        $mpdf = new \Mpdf\Mpdf();

        // Create the PDF content
        $html = "
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .receipt-container {
            padding: 20px;
            max-width: 900px;
            margin: 0 auto;
            background-color: #fff;
        }
        .receipt-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
        }
        .company-info {
            text-align: left;
        }
        .company-info h4 {
            margin-bottom: 10px;
        }
        .company-info p {
            margin: 0;
            font-size: 14px;
            color: #555;
        }
        .logo img {
            max-width: 100px;
            height: auto;
        }
        .receipt-subheader {
            text-align: center;
            padding-top: 10px;
            padding-bottom: 5px;
            letter-spacing: 2px;
        }
        .customer-info p{
            margin-bottom: 5px;
            margin-top: 5px;
            font-size: 16px;
        }

        .receipt-details {
            margin: 45px 0 10px;
        }

        .receipt-details h3 {
            letter-spacing: 2px;
            margin-bottom: 5px;
        }
        .receipt-details p {
            margin: 5px 0;
            font-size: 16px;
        }
        .service-info {
            min-height: 200px;
            padding: 10px 0;
            border-radius: 5px;
            font-size: 16px;
        }
        .price-details {
            text-align: right;
        }
        .price {
            display: inline-block;
            background-color: #f9f9f9; 
            padding: 5px 10px;
            border-bottom: 3px solid #000; 
            color: #333; 
            margin: 0px;
        }
        .price strong {
            font-weight: bold;
            margin-right: 25px;
        }
        .price span {
            font-weight: normal; 
        }
        .footer {
            text-align: center;
            font-size: 14px;
            margin-top: 60px;
            color: #777;
        }
    </style>
        <div class='receipt-container'>
        <div class='receipt-header'>
            <div class='logo'>
                <img style='max-width: 200px' src='Views/images/continental_logo.png' alt='Company Logo'>
            </div>        
            <div class='company-info'>
                <h4>Continental Lien Yiu Battery & Tyre Sdn Bhd</h4>
                <p>598 & 598-A, Jalan Jelutong, Jelutong,</p>
                <p>11600 Jelutong, Pulau Pinang</p>
            </div>

        </div>
        <div class='receipt-subheader'>
            <h1>SERVICE RECEIPT</h1>
        </div>
        
        <div class='receipt-customer-details'>
            <table style='width: 100%; border-spacing: 0;'>
                <tr>
                    <td style='width: 60%; vertical-align: top;'>
                        <p><strong>Billed To:</strong> {$appointmentDetails['customer_name']}</p>
                        <p style='margin-top: 20px'>{$appointmentDetails['car_type']}</p>
                        <p>{$appointmentDetails['car_number']}</p>
                    </td>
                    <td style='width: 40%; vertical-align: top;'>
                        <p><strong>Receipt Id:</strong> {$appointmentDetails['receipt_id']}</p>
                        <p><strong>Appointment Date:</strong> {$appointmentDetails['appointment_date']}</p>
                    </td>
                </tr>
            </table>
        </div>
        <div class='receipt-details'>
            <h3>SERVICE INFO</h3><hr>
            <div class='service-info'>
                <p>{$appointmentDetails['service_info']}</p>
            </div>
            <hr>
        </div>
        <div class='price-details'>
            <p class='price'>
                <strong>Total Price (RM)</strong> <span>{$appointmentDetails['price']}</span>
            </p>
        </div>
        <div class='footer'>
            Thank you for your appointment!
        </div>
    </div>
    ";

        $mpdf->WriteHTML($html);

        // Output the PDF as a download
        $mpdf->Output("Service_Receipt_{$appointmentDetails['receipt_id']}.pdf", 'D');
    }

}