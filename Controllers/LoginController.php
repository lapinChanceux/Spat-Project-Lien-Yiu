<?php
require_once 'Model/LoginModel.php';

class LoginController {
    /**
     * Handle the login process.
     *
     * @param array $requestData The data from the login form (e.g., $_POST).
     * @return bool Returns true if login is successful, false otherwise.
     */
    public function handleLogin($requestData) {
        $loginModel = new LoginModel();

        // Extract data from the request
        $username = trim($requestData['username']);
        $password = $requestData['password'];

        // Validate input
        if (empty($username) || empty($password)) {
            return false; // Return false if any field is empty
        }

        // Authenticate user
        $user = $loginModel->login($username, $password);

        if ($user) {
            // Login successful: Store user data in session
            $_SESSION['user'] = $user; // Store user details in session

            return true;
        } else {
            // Authentication failed
            return false;
        }
    }
}
?>
