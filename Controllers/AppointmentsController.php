<?php

require_once 'Model/AppointmentsDataSetModel.php';

// Set the timezone to Malaysia
date_default_timezone_set('Asia/Kuala_Lumpur');

// Get the current date in the desired format
$adminCurrentDate = date("d F Y");


class AppointmentsController
{
    protected $model;

    public function __construct()
    {
        $this->model = new AppointmentsDataSetModel();
    }

    public function getCarBrands()
    {
        return $this->model->getCarBrands();
    }

    public function getCarModels($brand_id)
    {
        return $this->model->getCarModels($brand_id);
    }

    public function getAppointments()
    {
        $appointments = $this->model->getAppointments();
        $appointmentsCount = $this->model->getAppointmentsCount();
        return ['appointments' => $appointments, 'appointmentsCount' => $appointmentsCount];
    }

    public function insertAppointment()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullName = isset($_POST['fullName']) ? $_POST['fullName'] : null;
            $carNumber = isset($_POST['carNumber']) ? $_POST['carNumber'] : null;
            $carBrand = isset($_POST['carBrand']) ? $_POST['carBrand'] : null;
            $carModel = isset($_POST['carModel']) ? $_POST['carModel'] : null;
            $appointmentDate = isset($_POST['appointmentDate']) ? $_POST['appointmentDate'] : null;
            $appointmentTime = isset($_POST['appointmentTime']) ? $_POST['appointmentTime'] : null;
            $phoneNumber = isset($_POST['phoneNumber']) ? $_POST['phoneNumber'] : null;
            $email = isset($_POST['email']) ? $_POST['email'] : null;
            $remark = isset($_POST['remark']) ? $_POST['remark'] : null;

            if ($fullName && $carNumber && $carBrand && $carModel && $appointmentDate && $appointmentTime && $phoneNumber) {
                $appointmentCount = $this->model->countAppointmentsByDateTime($appointmentDate, $appointmentTime);
                if ($appointmentCount >= 2) {
                    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var resultModal = new bootstrap.Modal(document.getElementById("resultModal"));
            document.getElementById("resultModalLabel").innerText = "Appointment Failed";
            document.getElementById("resultModalBody").innerHTML = "The selected time is not available. Please choose another time.";
            resultModal.show();
        });
    </script>';
                    return;
                }

                $success = $this->model->insertAppointment($fullName, $carNumber, $carBrand, $carModel, $appointmentDate, $appointmentTime, $phoneNumber, $email, $remark);

                if ($success) {
                    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var resultModal = new bootstrap.Modal(document.getElementById("resultModal"));
            document.getElementById("resultModalLabel").innerText = "Appointment Booked";
            document.getElementById("resultModalBody").innerHTML = "Your appointment has been successfully booked!";
            resultModal.show();
        });
    </script>';
                } else {
                    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var resultModal = new bootstrap.Modal(document.getElementById("resultModal"));
            document.getElementById("resultModalLabel").innerText = "Appointment Failed";
            document.getElementById("resultModalBody").innerHTML = "Failed to book the appointment. Please try again.";
            resultModal.show();
        });
    </script>';
                }
            }
        }
        require_once('Views/home.phtml');
    }

    public function updateAppointment($appointmentId, $newStatus)
    {
        return $this->model->updateAppointmentStatus($appointmentId, $newStatus);
    }
}

// Instantiate the controller
$controller = new AppointmentsController();

// Fetch all car brands
$carBrands = $controller->getCarBrands();

// Initialize an array to store all car models
$allCarModels = [];

// Loop through each car brand and fetch its models
foreach ($carBrands as $brand) {
    $allCarModels[$brand['brand_id']] = $controller->getCarModels($brand['brand_id']);
}


