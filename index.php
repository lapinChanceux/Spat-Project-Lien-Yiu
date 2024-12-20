<?php

require_once('Controllers/AppointmentsController.php');

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

if ($page === 'home') {
    $controller = new AppointmentsController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->insertAppointment();
    }
    $carBrands = $controller->getCarBrands();
    include 'Views/home.phtml';
} elseif ($page === 'admin-dashboard') { // Admin dashboard route
    $controller = new AppointmentsController();

    // Handle delete appointment request
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointmentId'])) {
        $appointmentId = intval($_POST['appointmentId']);

        // Call the controller to delete the appointment
        $controller->deleteAppointment($appointmentId);
    }

    // Fetch appointments and display them
    $data = $controller->getAppointments();
    $appointments = $data['appointments'];
    $appointmentsCount = $data['appointmentsCount'];

    include 'Views/admin-dashboard.phtml';
} else {
    echo "Page not found.";
}

