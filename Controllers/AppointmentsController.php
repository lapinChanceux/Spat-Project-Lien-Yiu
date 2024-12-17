<?php

require_once 'Model/AppointmentsDataSetModel.php';

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
                        var failureModal = new bootstrap.Modal(document.getElementById("failureModal"));
                        failureModal.show();
                    });
                </script>';
                    return;
                }

                $success = $this->model->insertAppointment($fullName, $carNumber, $carBrand, $carModel, $appointmentDate, $appointmentTime, $phoneNumber, $email, $remark);

                if ($success) {
                    echo '<script>
                    document.addEventListener("DOMContentLoaded", function() {
                        var successModal = new bootstrap.Modal(document.getElementById("successModal"));
                        successModal.show();
                    });
                </script>';
                } else {
                    echo '<script>
                    document.addEventListener("DOMContentLoaded", function() {
                        var failureModal = new bootstrap.Modal(document.getElementById("failureModal"));
                        failureModal.show();
                    });
                </script>';
                }
            }
        }
        require_once('Views/home.phtml');
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


