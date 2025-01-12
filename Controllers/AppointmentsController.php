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
        $onServiceCount = $this->model->getAppointmentsCountByStatus('On Service');
        $completedCount = $this->model->getAppointmentsCountByStatus('Completed');

        return [
            'appointments' => $appointments,
            'appointmentsCount' => $appointmentsCount,
            'pendingCount' => $pendingCount,
            'onServiceCount' => $onServiceCount,
            'completedCount' => $completedCount,

        ];
    }

    public function insertAppointment()
    {
        session_start();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['submitted']) && $_POST['submitted'] == '1') {
                return;
            }
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

                $_POST['submitted'] = '1';
                $success = $this->model->insertAppointment($fullName, $carNumber, $carBrand, $carModel, $appointmentDate, $appointmentTime, $phoneNumber, $email, $remark);

                if ($success) {
                    // Send email after successful appointment booking
                    $this->sendAppointmentEmail($email, $fullName, $appointmentDate, $appointmentTime,'insert');
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

    public function updateAppointment($appointmentId, $newStatus, $serviceInfo = null, $price = null)
    {
        $result = $this->model->updateAppointmentStatus($appointmentId, $newStatus);

        // If status is 'Completed', insert into the receipt table
        if ($newStatus === 'Completed' && $serviceInfo !== null && $price !== null) {
            $this->model->insertReceipt($appointmentId, $serviceInfo, $price);
        }

        return $result;
    }


    public function deleteAppointment($appointmentId)

    {
        // Debugging: Check if the appointment ID is being passed
        error_log('Controller: Deleting appointment with ID: ' . $appointmentId);
        return $this->model->deleteAppointmentById($appointmentId);
    }

        public function checkBookingAppointment($carNumber,$fullName){
            $appointment = $this->model->getLatestAppointmentByCarNumber($carNumber,$fullName);

        if ($appointment) {
            $appointmentId = htmlspecialchars($appointment['appointment_id']);
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
                document.getElementById("confirmationModalId").textContent = "' . $appointmentId . '";
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
            document.getElementById("resultModalBody").innerHTML = "No appointment found for the car number.";
            resultModal.show();
        });
    </script>';
        }
    }

    public function cancelAppointment()
    {
        // Check if the request method is POST and appointmentId is set
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointmentId'])) {
            // Sanitize the appointment ID to prevent injection
            $appointmentId = htmlspecialchars($_POST['appointmentId']);
            // Fetch appointment details using the appointment ID
            $appointmentDetails = $this->model->getAppointmentId($appointmentId);
            if ($appointmentDetails) {
                // Extract details
                $email = $appointmentDetails->email;
                $fullName = $appointmentDetails->customer_name;
                $appointmentDate = $appointmentDetails->appointment_date;
                $appointmentTime = $appointmentDetails->appointment_time;
                // Debugging: Log the appointment ID
                error_log('Form submitted with appointment ID: ' . $appointmentId);

                // Call the deleteAppointment method
                $result = $this->deleteAppointment($appointmentId);

                // Redirect or show feedback based on the result
                if ($result) {
                    $this->sendAppointmentEmail($email, $fullName, $appointmentDate, $appointmentTime,'cancel');
                    header('Location: index.php?page=home&status=success');
                    exit;
                } else {
                    header('Location: index.php?page=home&status=failure');
                    exit;
                }
            }
        }
    }

    // Function to send the appointment email
    private function sendAppointmentEmail($email, $fullName, $appointmentDate, $appointmentTime, $action)
    {
        require 'vendor/autoload.php';

        $mail = new PHPMailer(true);

        try {
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'lienyiuappointment@gmail.com'; // Your Gmail address
            $mail->Password = 'jmsczrvpnosbctrg';  // Your Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Email Details
            $mail->setFrom('lienyiuappointment@gmail.com', 'Lien Yiu Customer Support');
            $mail->addAddress($email); // Customer email
            // Customize subject and body based on action
            if ($action === 'insert') {
                $mail->Subject = 'Appointment Confirmation - Lien Yiu Battery & Tyre Sdn Bhd';
                $mail->Body = "Dear $fullName,\n\nYour appointment has been successfully booked for $appointmentDate at $appointmentTime.\n\nThank you for choosing us!\n\nBest Regards,\nAdmin\nLien Yiu Battery & Tyre Sdn Bhd";
            } elseif ($action === 'cancel') {
                $mail->Subject = 'Appointment Cancellation - Lien Yiu Battery & Tyre Sdn Bhd';
                $mail->Body = "Dear $fullName,\n\nYour appointment scheduled for $appointmentDate at $appointmentTime has been successfully canceled.\n\nIf you have any questions, feel free to contact us.\n\nBest Regards,\nAdmin\nLien Yiu Battery & Tyre Sdn Bhd";
            } else {
                $mail->Subject = 'Notification - Lien Yiu Battery & Tyre Sdn Bhd';
                $mail->Body = "Dear $fullName,\n\nThis is a notification regarding your appointment.\n\nThank you for choosing us!\n\nBest Regards,\nAdmin\nLien Yiu Battery & Tyre Sdn Bhd";
            }
            // Send Email
            $mail->send();
            echo 'Email sent successfully.';
        } catch (Exception $e) {
            echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
    }

    public function generatePDF()
    {
        if (isset($_GET['appointment_id']) && is_numeric($_GET['appointment_id'])) {
            $appointmentId = intval($_GET['appointment_id']);

            // Fetch appointment details from the model
            $appointmentDetails = $this->model->getAppointmentById($appointmentId);

            // Generate the PDF
            $this->model->createPDF($appointmentDetails);
        } else {
            echo "Invalid request.";
        }
    }

    public function previewPDF($appointmentId) {
        require_once 'vendor/autoload.php';
        require_once 'Model/AppointmentsDataSetModel.php';

        $appointmentsModel = new AppointmentsDataSetModel();

// Call the method on the instance
        $appointmentDetails = $appointmentsModel->getAppointmentById($appointmentId);
        if (!$appointmentDetails) {
            echo "Invalid appointment ID.";
            return;
        }

        // Start output buffering
        ob_start();
        ?>

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
                    <td style='width: 70%; vertical-align: top; font-size: 12px;'>
                        <p style='margin: 1px; padding: 1px;'><strong>Billed To:</strong> <?= htmlspecialchars($appointmentDetails['customer_name']) ?></p>
                        <p style='margin: 1px; padding: 1px;'><?= htmlspecialchars($appointmentDetails['car_type']) ?></p>
                        <p style='margin: 1px; padding: 1px;'><?= htmlspecialchars($appointmentDetails['car_number']) ?></p>

                    </td>
                    <td style='width: 30%; vertical-align: top; font-size: 12px;'>
                        <p style='margin: 1px; padding: 1px;'><strong>Receipt Id:</strong> <?= htmlspecialchars($appointmentDetails['receipt_id']) ?></p>
                        <p style='margin: 1px; padding: 1px;'><strong>Appointment Date:</strong> <?= htmlspecialchars($appointmentDetails['appointment_date']) ?></p>
                    </td>
                </tr>
            </table>
            </div>

            <div class='receipt-details'>
                <h3>SERVICE INFO</h3><hr style="margin: 10px 0;">
                <div class='service-info'>
                    <p><?= nl2br(htmlspecialchars($appointmentDetails['service_info'])) ?></p>
                </div>
                <hr style="margin: 10px 0;">
            </div>


            <div class='price-details'>
                <p class='price'>
                    <strong>Total Price (RM)</strong> <span><?= htmlspecialchars($appointmentDetails['price']) ?></span>
                </p>
            </div>
            <div class='footer'>
                Thank you for your appointment!
            </div>
        </div>

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
                padding: 10px 0;
            }
            .company-info {
                text-align: left;
            }
            .company-info h4 {
                font-size: 14px;
                margin-bottom: 10px;
            }
            .company-info p {
                margin: 0;
                font-size: 12px;
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

            .receipt-subheader h1{
                font-size: 20px;
            }

            .customer-info p{
                margin-bottom: 5px;
                margin-top: 5px;
                font-size: 12px;
            }

            .receipt-details {
                margin: 45px 0 10px;
            }

            .receipt-details h3 {
                font-size: 14px;
                letter-spacing: 2px;
                margin-bottom: 5px;
            }
            .receipt-details p {
                margin: 5px 0;
                font-size: 12px;
            }
            .service-info {

                padding: 10px 0;
                border-radius: 5px;
                font-size: 16px;
            }
            .price-details {
                text-align: right;
            }
            .price {
                font-size: 12px;
                margin: 20px 0;
                background-color: #f9f9f9;
                padding: 5px 10px;
                border-bottom: 3px solid #000;
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
                font-size: 12px;
                margin-top: 40px;
                color: #777;
            }
        </style>

        <?php
        // Capture the buffer content and return it
        $html = ob_get_clean();

        echo $html;
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


