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
} else {
    echo "Page not found.";
}

