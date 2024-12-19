<?php

require_once('Controllers/AppointmentsController.php');
require_once('Controllers/LoginController.php');

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

if ($page === 'home') {
    $controller = new AppointmentsController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->insertAppointment();
    }
    $carBrands = $controller->getCarBrands();
    include 'Views/home.phtml';
} else if ($page === 'admin') {
    // Always process POST for login
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller = new LoginController();
        $loginSuccess = $controller->handleLogin($_POST); // Handle login attempt and get success/failure

        if ($loginSuccess) {
            // If login is successful, redirect to the admin page
            header('Location: index.php?page=admin-dashboard');
        } else {
            // If login fails, stay on the home page and show error message
            header('Location: index.php'); // Redirection
            exit; // Stop further code execution
        }
    }
} else if ($page === 'logout') {
    // Logout logic
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    header('Location: index.php'); // Redirect to home after logout
    exit;
} elseif ($page === 'admin-dashboard') { // Admin dashboard route
    $controller = new AppointmentsController();
    $data = $controller->getAppointments();
    $appointments = $data['appointments'];
    $appointmentsCount = $data['appointmentsCount'];
    include 'Views/admin-dashboard.phtml';
} else {
    echo "Page not found.";
}

