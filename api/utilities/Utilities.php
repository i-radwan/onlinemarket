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
     * This function checks if the assigned date format matches DB format
     * @param string $date required date to be checked
     * @return boolean
     */
    static public function checkDate($date) {
        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date)) {
            return true;
        } else {
            return false;
        }
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
     /**
     * This function checks if the sent date if valid or not (Used in Adding Orders)
     * @author AhmedSamir
     * @param string $date the input date string
     * @param string $format the input format (it's now YYYY-MM-DD)
     * @return boolean true if it's valid.
     */
    static public function validateDate($date, $format = 'Y-m-d') {
        $theDate = strtotime($date);
        $today =  date($format);
        $today = strtotime($today);
        return $theDate >= $today;
    }
    

}
