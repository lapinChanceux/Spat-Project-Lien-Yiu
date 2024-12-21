<?php
require_once 'Database.php';

class LoginModel {

    public function login($username, $password)
    {
        $db = Database::getInstance()->getdbConnection();
        $query = "SELECT * FROM Users WHERE username = :username";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username); //Bind the username parameter
        $stmt->execute();

        //Fetch the user record as an associative array
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        //Verify the hashed password
        if ($user && password_verify($password, $user['password'])) {
            //If the password is valid, store user details in the session

            return true; //Authentication successful
        }
        return false; //Authentication failed
    }
}
?>
