<?php

/**
 * This class contains all the utilities functions
 *
 * @author ibrahimradwan
 */
class Utilities {

    /**
     * This function checks if the passed email is valid
     * @param string $email : email to be validated
     * @return boolean : true if email is valid, false otherwise
     */
    static public function checkValidEmail($email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
        return false;
    }

    /**
     * This function makes the input form data safe for SQL
     * @param string $data : input data
     * @return string output safe data
     */
    static public function makeInputSafe($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    /**
     * This function generates the hash of given password
     * @param string $pass the password to be hashed
     * @return string hashed password
     */
    static public function hashPassword($pass) {
        $options = [
            'cost' => 11
        ];
        return password_hash($pass, PASSWORD_BCRYPT, $options);
    }
    
    /**
     * This function checks if the hash of given password matches the one stored in db
     * @param string $password the password entered by user
     * @param string $hashedPassword the hashed password from DB
     * @return boolean true if matches, else otherwise 
     */
    static public function checkPassword($password, $hashedPassword) {
        return password_verify($password, $hashedPassword);
    }
    
    

}
