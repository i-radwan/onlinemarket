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
    static public function checkValidEmail(string $email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
           return true;
        }
        return false;
    }

    /**
     * This function makes the input form data safe for SQL
     * @param string $data : input data
     * @return string : output safe data
     */
    static public function makeInputSafe(string $data) {
        $data = htmlspecialchars(stripslashes(trim($data)));
        return $data;
    }

}
