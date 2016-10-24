<?php

/**
 * This file contains all the functions that will deal with the DB
 * @author ibrahimradwan
 */
include_once 'utilities/Constants.php';
require_once './SQLOperaionsInterface.php';

class SQLOperations implements SQLOperationsInterface {

    function __construct() {
        $this->db_link = new mysqli("localhost", "root", "20061996", "online_market");
        if ($this->db_link->connect_errno > 0) {
            die('Unable to connect to database [' . $this->db_link->connect_error . ']');
        }
    }

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

    /**
     * This function retrieves all the categories
     * @return json string
     */
    public function getCategories() {
        if (!$result = $this->db_link->query("SELECT * FROM `categories`")) {
            die('There was an error running the query [' . $this->db_link->error . ']');
        }
        while ($row = $result->fetch_assoc()) {
            echo $row['_id'] . ' ' .$row['name']  . '<br />';
        }
      
        return $categories;
    }

    function __destruct() {
        $this->db_link->close();
    }

}
