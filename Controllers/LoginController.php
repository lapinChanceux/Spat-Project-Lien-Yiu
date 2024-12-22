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
        $rememberMe = isset($requestData['rememberMe']) && $requestData['rememberMe'];

        // Validate input
        if (empty($username) || empty($password)) {
            return false; // Return false if any field is empty
        }

        // Authenticate user
        $user = $loginModel->login($username, $password);

        if ($user) {
            if ($rememberMe) {
                setcookie("username", $username, time() + 3600, "/");
                setcookie("password", $password, time() + 3600, "/");
            }
            return true;
        } else {
            // Authentication failed
            return false;
        }
    }
    public function loginFail()
    {
        echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        var resultModal = new bootstrap.Modal(document.getElementById("loginFailedModal"));
        document.getElementById("loginFailedModalLabel").innerText = "Login Failed";
        document.getElementById("loginFailedModalBody").innerHTML = "Wrong username or password. Please try again.";
        resultModal.show();
        });
    </script>';
    }
}
?>
