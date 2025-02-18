<?php

require_once('Controllers/AppointmentsController.php');
require_once('Controllers/LoginController.php');
require_once('Controllers/VisitorController.php');
require_once('Controllers/DisplayServiceController.php');

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Handle PDF preview request
if (isset($_GET['page']) && $_GET['page'] === 'preview-pdf' && isset($_GET['appointment_id'])) {
    $controller = new AppointmentsController();
    $appointmentId = $_GET['appointment_id'];
    $controller->previewPDF($appointmentId); // Generate the PDF preview
    exit;  // Stop further execution to prevent loading any view after PDF preview
}

// Handle download PDF request
if (isset($_GET['page']) && $_GET['page'] === 'download-pdf' && isset($_GET['appointment_id'])) {
    $controller = new AppointmentsController();
    $appointmentId = $_GET['appointment_id'];
    $controller->generatePDF($appointmentId); // Generate the PDF for the appointment
    exit;  // Stop further execution to prevent loading any view after PDF download
}

// Routing for home page
if ($page === 'home') {
    $controller = new AppointmentsController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['appointment'])) {
            $controller->insertAppointment();
        } elseif (isset($_POST['checkBooking'])) {
            $carNumber = $_POST['carNumber'];
            $fullName = $_POST['fullName'];
            $controller->checkBookingAppointment($carNumber, $fullName);
        } elseif (isset($_POST['cancelAppointment'])) {
            $appointmentId = intval($_POST['appointmentId']);
            $controller->cancelAppointment($appointmentId);

            // Pass status via redirect query parameters
            $status = $controller->deleteAppointment($appointmentId) ? 'success' : 'failure';
            header("Location: index.php?page=home&status=$status");
            exit;
        } elseif (isset($_POST['login'])) {
            $controller = new LoginController();
            $loginSuccess = $controller->handleLogin($_POST);
            if ($loginSuccess) {
                header('Location: index.php?page=admin-dashboard');
                exit;
            } else {
                $controller->loginFail();
                include 'Views/home.phtml';
                exit;
            }
        }
    }

    // Handle feedback notifications
    if (isset($_GET['status'])) {
        $status = $_GET['status'];
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var resultModal = new bootstrap.Modal(document.getElementById("resultModal"));
            if ("' . $status . '" === "success") {
                document.getElementById("resultModalLabel").innerText = "Successfully Canceled";
                document.getElementById("resultModalBody").innerHTML = "Your appointment has been successfully canceled.";
            } else if ("' . $status . '" === "failure") {
                document.getElementById("resultModalLabel").innerText = "Cancellation Failed";
                document.getElementById("resultModalBody").innerHTML = "Failed to cancel the appointment. Please try again.";
            }
            resultModal.show();
        });
        </script>';
    }

    $carBrands = $controller->getCarBrands();
    include 'Views/home.phtml';

// Routing for admin dashboard
} elseif ($page === 'admin-dashboard') {
    $controller = new AppointmentsController();

    $data = $controller->getAppointments();
    $appointments = $data['appointments'];
    $appointmentsCount = $data['appointmentsCount'];
    $pendingCount = $data['pendingCount'];
    $onServiceCount = $data['onServiceCount'];
    $completedCount = $data['completedCount'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['updateStatus'])) {
            $appointmentId = $_POST['appointmentId'];
            $status = $_POST['status'];
            $serviceInfo = $_POST['serviceInfo'];
            $price = $_POST['price'];

            $controller->updateAppointment($appointmentId, $status, $serviceInfo, $price);
            header('Location: index.php?page=admin-dashboard');
            exit;
        }
        if (isset($_POST['appointmentId']) && !isset($_POST['updateStatus'])) {
            $appointmentId = intval($_POST['appointmentId']);
            $controller->deleteAppointment($appointmentId);
            header('Location: index.php?page=admin-dashboard');
            exit;
        }
        header('Location: index.php');
        exit;
    }

    include 'Views/admin-dashboard.phtml';

// Routing for service progress page
} elseif ($page === 'service-progress') {
    $controller = new DisplayServiceController();
    $serviceData = $controller->getServiceData();

    if (isset($_GET['fetch']) && $_GET['fetch'] === 'data') {
        header('Content-Type: application/json');
        echo json_encode($serviceData);
        exit;
    }
    include 'Views/service-progress.phtml';

// Routing for logout
} elseif ($page === 'logout') {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;

// Default 404 page not found
} else {
    echo "Page not found.";
}

?>
