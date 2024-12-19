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
    $data = $controller->getAppointments();
    $appointments = $data['appointments'];
    $appointmentsCount = $data['appointmentsCount'];
    include 'Views/admin-dashboard.phtml';
} else {
    echo "Page not found.";
}

