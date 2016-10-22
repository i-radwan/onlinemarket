<?php

/**
 * This file contains all the functions that will deal with the DB
 * @author ibrahimradwan
 */
include_once '../utilities/Constants.php';
class SQLOperations implements SQLOperationsInterface {

    // =========================================================================================================
    //                                          USERS FUNCTIONS
    // =========================================================================================================
    
    /**
     * This function checks for the passed parameters and if everything is okay, it adds the new user to DB
     * @param string $email
     * @param string $pass1
     * @param string $pass2
     * @param integer $role
     * @return integer : sign up status code
     */
    public function signUpUser($email, $pass1, $pass2, $role) {
        
        return Constants::SIGNUP_FAILED;
    }
}
