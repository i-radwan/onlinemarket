<?php

/**
 * This file contains all the functions that will deal with the DB
 * @author ibrahimradwan
 */
require_once 'utilities/Constants.php';
require_once 'utilities/Utilities.php';
require_once 'utilities/jwt_helper.php';
require_once 'utilities/config.php';
require_once 'models/Buyer.php';
require_once 'models/Seller.php';
require_once 'models/Admin.php';
require_once 'models/Accountant.php';
require_once 'models/Deliveryman.php';
require_once 'models/Error.php';
require_once 'models/Response.php';
require_once 'SQLOperaionsInterface.php';

class SQLOperations implements SQLOperationsInterface {

    function __construct() {
        $this->db_link = new mysqli(db_host, db_user, db_pass, db_name);
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
            echo $row['_id'] . ' ' . $row['name'] . '<br />';
        }

        return $categories;
    }

    // ToDo: Access token JWT

    /**
     * This function signs in the user using the provided info
     * @param email $email user's email
     * @param string $pass user's password
     * @return object user details object, or error object in case anything went wrong
     */
    public function login($email, $pass) {
        // Check if not empty data
        if (strlen(trim($email)) != 0 && strlen(trim($pass)) != 0) {
            // Check valid email
            if (Utilities::checkValidEmail($email)) {
                // Make text safe
                $email = Utilities::makeInputSafe($email);
                $pass = Utilities::makeInputSafe($pass);
                // Select from users table
                if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_USERS . "` WHERE `" . Constants::USERS_FLD_EMAIL . "` = '$email' LIMIT 1")) {
                    $error = new Error(Constants::LOGIN_OPERATION_FAILED, "Please try again later!");
                    return json_encode($error);
                }
                //Check if email exists
                if ($result->num_rows == 1) {
                    $row = $result->fetch_assoc();
                    // Check if password hash match
                    $password = $row[Constants::USERS_FLD_PASS];
                    if (Utilities::checkPassword($pass, $password) == true) {
                        $this->refreshUserHash($row, $pass); // refresh user hash in db
                        $userModel = $this->generateUserModel($row); // generate user model
                        $jwt = $this->generateUserToken($row);                        
                        
                        //return json response
                        $response = new Response(Constants::LOGIN_SUCCESSFUL_LOGIN, $userModel, $jwt);
                        return json_encode($response);
                    } else {
                        $error = new Error(Constants::LOGIN_INCORRECT_DATA, "Incorrect e-mail or password!");
                        return json_encode($error);
                    }
                } else {
                    $error = new Error(Constants::LOGIN_INCORRECT_DATA, "Incorrect e-mail or password!");
                    return json_encode($error);
                }
            } else {
                $error = new Error(Constants::LOGIN_INVALID_EMAIL, "Please enter valid e-mail address!");
                return json_encode($error);
            }
        } else {
            $error = new Error(Constants::LOGIN_EMPTY_DATA, "All fields are required!");
            return json_encode($error);
        }
    }

    /**
     * This function generates JWT for the user
     * @param array $row the user row from DB
     * @return string jwt
     */
    function generateUserToken($row) {
        $tokenId = base64_encode(openssl_random_pseudo_bytes(64));
        $issuedAt = time();
        $notBefore = $issuedAt + 10;  //Adding 10 seconds
        $expire = $notBefore + 60; // Adding 60 seconds
        $serverName = db_host;

        $data = [
            'iat' => $issuedAt, // Issued at: time when the token was generated
            'jti' => $tokenId, // Json Token Id: an unique identifier for the token
            'iss' => $serverName, // Issuer
            'nbf' => $notBefore, // Not before
            'exp' => $expire, // Expire
            'data' => [// Data related to the signer user
                Constants::USERS_FLD_ID => $row[Constants::USERS_FLD_ID],
                Constants::USERS_FLD_EMAIL => $row[Constants::USERS_FLD_EMAIL],
                Constants::USERS_FLD_NAME => $row[Constants::USERS_FLD_NAME],
                Constants::USERS_FLD_TEL => $row[Constants::USERS_FLD_TEL],
                Constants::USERS_FLD_USER_TYPE => $row[Constants::USERS_FLD_USER_TYPE]
            ]
        ];
        $secretKey = base64_decode(jwt_key);
        return JWT::encode($data, $secretKey, jwt_algorithm);
    }

    /**
     * This function generates user model given the db row
     * @param array $row user db row array
     * @return object userModel generated from db row
     */
    function generateUserModel($row) {
        $userType = $row[Constants::USERS_FLD_USER_TYPE];
        $_id = $row[Constants::USERS_FLD_ID];

        switch ($userType) {
            case Constants::USER_BUYER:
                // Select more data
                if (!$resultDetails = $this->db_link->query("SELECT * FROM `" . Constants::TBL_BUYERS . "` WHERE `" . Constants::BUYERS_FLD_USER_ID . "` = $_id LIMIT 1")) {
                    $error = new Error(Constants::LOGIN_OPERATION_FAILED, "Please try again later!");
                    return json_encode($error);
                }
                $rowDetails = $resultDetails->fetch_assoc();
                $userModel = new Buyer($_id, $row[Constants::USERS_FLD_NAME], $row[Constants::USERS_FLD_EMAIL], $row[Constants::USERS_FLD_TEL], $userType, $rowDetails[Constants::BUYERS_FLD_ADDRESS], $rowDetails[Constants::BUYERS_FLD_CCNUMBER], $rowDetails[Constants::BUYERS_FLD_CC_CCV], $rowDetails[Constants::BUYERS_FLD_CC_MONTH], $rowDetails[Constants::BUYERS_FLD_CC_YEAR]);
                // return json
                break;
            case Constants::USER_SELLER:
                // Select more data
                if (!$resultDetails = $this->db_link->query("SELECT * FROM `" . Constants::TBL_SELLERS . "` WHERE `" . Constants::SELLERS_FLD_USER_ID . "` = $_id LIMIT 1")) {
                    $error = new Error(Constants::LOGIN_OPERATION_FAILED, "Please try again later!");
                    return json_encode($error);
                }
                $rowDetails = $resultDetails->fetch_assoc();

                $userModel = new Seller($_id, $row[Constants::USERS_FLD_NAME], $row[Constants::USERS_FLD_EMAIL], $row[Constants::USERS_FLD_TEL], $userType, $rowDetails[Constants::SELLERS_FLD_ADDRESS], $rowDetails[Constants::SELLERS_FLD_BACK_ACCOUNT]);
                break;
            // For the next 3 cases, we currently don't give them extra data, so we will return the basic data only
            case Constants::USER_ACCOUNTANT:
                $userModel = new Accountant($_id, $row[Constants::USERS_FLD_NAME], $row[Constants::USERS_FLD_EMAIL], $row[Constants::USERS_FLD_TEL], $userType);
                break;
            case Constants::USER_DELIVERMAN:
                $userModel = new Deliveryman($_id, $row[Constants::USERS_FLD_NAME], $row[Constants::USERS_FLD_EMAIL], $row[Constants::USERS_FLD_TEL], $userType);
                break;
            case Constants::USER_ADMIN:
                $userModel = new Admin($_id, $row[Constants::USERS_FLD_NAME], $row[Constants::USERS_FLD_EMAIL], $row[Constants::USERS_FLD_TEL], $userType);
                break;
        }
        return $userModel;
    }

    /**
     * This function refreshes the user hash in db on succesful login
     * @param array $row user db row
     * @param string $pass user entered password
     */
    function refreshUserHash($row, $pass) {
        $_id = $row[Constants::USERS_FLD_ID];
        // Regenerate and insert password
        $newHash = Utilities::hashPassword($pass);
        $this->db_link->query("UPDATE `" . Constants::TBL_USERS . "` SET `" . Constants::USERS_FLD_PASS . "` = '$newHash' WHERE `" . Constants::USERS_FLD_ID . "` = '$_id' LIMIT 1");
    }

//    //Test function
//    function secure($jwt) {
//        list($jwt) = sscanf($authHeader->toString(), 'Authorization: Bearer %s');
//        if ($jwt) {
//            try {
//                $secretKey = base64_decode(jwt_key);
//
//                $token = JWT::decode($jwt, $secretKey, jwt_algorithm);
//                print_r($token->data->email);
//            } catch (Exception $e) {
//                
//            }
//        }
//    }

    function __destruct() {
        $this->db_link->close();
    }

}
