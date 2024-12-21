<?php

require_once('Controllers/AppointmentsController.php');

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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    include 'Views/admin-dashboard.phtml';
} else {
    echo "Page not found.";
}

