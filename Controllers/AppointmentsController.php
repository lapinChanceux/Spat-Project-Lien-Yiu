<?php
// AppointmentsControllers.php
require_once 'Model/AppointmentsDataSetModel.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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
        $pendingCount = $this->model->getAppointmentsCountByStatus('Pending');
        $onServiceCount = $this->model->getAppointmentsCountByStatus('On-Service');
        $completedCount = $this->model->getAppointmentsCountByStatus('Completed');
        $pendingCountToday = $this->model->getAppointmentsCountByStatusToday('Pending');
        $onServiceCountToday = $this->model->getAppointmentsCountByStatusToday('On-Service');
        $completedCountToday = $this->model->getAppointmentsCountByStatusToday('Completed');
        return [
            'appointments' => $appointments,
            'appointmentsCount' => $appointmentsCount,
            'pendingCount' => $pendingCount,
            'onServiceCount' => $onServiceCount,
            'completedCount' => $completedCount,
            'pendingCountToday' => $pendingCountToday,
            'onServiceCountToday' => $onServiceCountToday,
            'completedCountToday' => $completedCountToday
        ];
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
                    // Send email after successful appointment booking
                    $this->sendAppointmentEmail($email, $fullName, $appointmentDate, $appointmentTime);
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

    public function deleteAppointment($appointmentId)

    {
        // Debugging: Check if the appointment ID is being passed
        error_log('Controller: Deleting appointment with ID: ' . $appointmentId);
        return $this->model->deleteAppointmentById($appointmentId);
    }

    public function checkBookingAppointment($carNumber)
    {
        $appointment = $this->model->getLatestAppointmentByCarNumber($carNumber);

        if ($appointment) {
            $customerName = htmlspecialchars($appointment['customer_name']);
            $carNum = htmlspecialchars($appointment['car_number']);
            $appointmentDate = htmlspecialchars($appointment['appointment_date']);
            $appointmentTime = htmlspecialchars($appointment['appointment_time']);

            // Inject the modal display logic into the page
            echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                var confirmModal = new bootstrap.Modal(document.getElementById("confirmationModal"));
                confirmModal.show();
                
                // Fill in the modal with appointment details
                document.getElementById("confirmationModalName").textContent = "' . $customerName . '";
                document.getElementById("confirmationModalCarNumber").textContent = "' . $carNum . '";
                document.getElementById("confirmationModalDate").textContent = "' . $appointmentDate . ' ' . $appointmentTime . '";
            });
        </script>';
        } else {
            // No appointment found for the car number
            echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var resultModal = new bootstrap.Modal(document.getElementById("resultModal"));
            document.getElementById("resultModalLabel").innerText = "";
            document.getElementById("resultModalBody").innerHTML = "Failed to book the appointment. Please try again.";
            resultModal.show();
        });
    </script>';
        }
    }

    // Function to send the appointment email
    private function sendAppointmentEmail($email, $fullName, $appointmentDate, $appointmentTime)
    {
        require 'vendor/autoload.php';

        $mail = new PHPMailer(true);

        try {
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'lienyiuappointment@gmail.com'; // Your Gmail address
            $mail->Password = 'leanyiu@123456';  // Your Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Email Details
            $mail->setFrom('lienyiuappointment@gmail.com', 'Lien Yiu Customer Support');
            $mail->addAddress($email); // Customer email
            $mail->Subject = 'Appointment Confirmation - Lien Yiu Battery & Tyre Sdn Bhd';
            $mail->Body = "Dear $fullName,\n\nYour appointment has been successfully booked for $appointmentDate at $appointmentTime.\n\nThank you for choosing us!\n\nBest Regards,\nAdmin\nLien Yiu Battery & Tyre Sdn Bhd";

            // Send Email
            $mail->send();
            echo 'Email sent successfully.';
        } catch (Exception $e) {
            echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
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


