<?php

require_once('Controllers/AppointmentsController.php');
require_once('Controllers/LoginController.php');

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

if ($page === 'home') {
    $controller = new AppointmentsController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['appointment'])) {
            $controller->insertAppointment();
        } elseif (isset($_POST['checkBooking'])){
            $carNumber = $_POST['carNumber'];
            $controller->checkBookingAppointment($carNumber);
        }
    }
    $carBrands = $controller->getCarBrands();
    include 'Views/home.phtml';
} elseif ($page === 'admin-dashboard') { // Admin dashboard route
    $controller = new AppointmentsController();
    // Fetch appointments and display them
    $data = $controller->getAppointments();
    $appointments = $data['appointments'];
    $appointmentsCount = $data['appointmentsCount'];
    $pendingCount = $data['pendingCount'];
    $onServiceCount = $data['onServiceCount'];
    $completedCount = $data['completedCount'];
    $pendingCountToday = $data['pendingCountToday'];
    $onServiceCountToday = $data['onServiceCountToday'];
    $completedCountToday = $data['completedCountToday'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['login'])) {
            $controller = new LoginController();
            $loginSuccess = $controller->handleLogin($_POST); // Handle login attempt and get success/failure
            if ($loginSuccess) {
                include 'Views/admin-dashboard.phtml';
            } else {
                $controller->loginFail();
                include 'Views/home.phtml';
                exit; // Stop further code execution
            }
        }
        if (isset($_POST['updateStatus'])) {
            $appointmentId = $_POST['appointmentId'];
            $status = $_POST['status'];
            $controller->updateAppointment($appointmentId, $status);
            header('Location: index.php?page=admin-dashboard');
            exit; // Stop further code execution
        }
        // Handle delete appointment request
        if (isset($_POST['appointmentId']) && !isset($_POST['updateStatus'])) {
            $appointmentId = intval($_POST['appointmentId']);
            $controller->deleteAppointment($appointmentId);
            header('Location: index.php?page=admin-dashboard');
            exit; // Make sure to exit after the redirect
        }
        header('Location: index.php');
        exit;
    }

} else if ($page === 'logout') {
    // Logout logic
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    header('Location: index.php'); // Redirect to home after logout
    exit;
} else {
    echo "Page not found.";
}

