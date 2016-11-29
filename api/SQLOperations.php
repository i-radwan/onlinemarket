<?php

/**
 * This file contains all the functions that will deal with the DB
 * @author ibrahimradwan
 * @todo Remember me cookies 
 */
require_once 'utilities/Constants.php';
require_once 'utilities/Utilities.php';
require_once 'utilities/jwt_helper.php';
require_once 'utilities/config.php';
require_once 'models/Buyer.php';
require_once 'models/Seller.php';
require_once 'models/Admin.php';
require_once 'models/Order.php';
require_once 'models/Accountant.php';
require_once 'models/Deliveryman.php';
require_once 'models/Error.php';
require_once 'models/Response.php';
require_once 'models/Category.php';
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
     * @param email $email
     * @param string $pass1
     * @param string $pass2
     * @param int $role
     * @param string $name
     * @param string $tel
     * @param object $extraData
     * @return object user details object, or error object in case anything went wrong
     * @checkedByIAR
     */
    public function signUpUser($email, $pass1, $pass2, $role, $name, $tel, $extraData) {
        // 0. Check if non-empty data
        if (strlen(trim($email)) != 0 && strlen(trim($pass1)) != 0 && strlen(trim($pass2)) != 0 && strlen(trim($role)) != 0 && strlen(trim($name)) != 0 && strlen(trim($tel)) != 0) {
            // 1. Check if valid email address
            if (Utilities::checkValidEmail($email)) {
                $email = Utilities::makeInputSafe($email);
                $pass1 = Utilities::makeInputSafe($pass1);
                $pass2 = Utilities::makeInputSafe($pass2);
                $role = Utilities::makeInputSafe($role);
                $name = Utilities::makeInputSafe($name);
                $tel = Utilities::makeInputSafe($tel);
                // 2. Check if passwords match
                if ($pass1 == $pass2) {
                    // 3. Check if user exists
                    if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_USERS . "` WHERE `" . Constants::USERS_FLD_EMAIL . "` = '$email' LIMIT 1")) {
                        return $this->returnError(Constants::SIGNUP_OPERATION_FAILED, "Please try again later!", 0, 0, 0);
                    }
                    if ($result->num_rows == 0) {
                        // Check if valid role
                        if (in_array($role, Constants::USER_TYPES)) {
                            // 3.0. Insert data into users table
                            $hashedPass = Utilities::hashPassword($pass1);
                            if (!$result = $this->db_link->query("INSERT INTO `" . Constants::TBL_USERS . "` SET `" . Constants::USERS_FLD_EMAIL . "` = '$email',  `" . Constants::USERS_FLD_PASS . "` = '$hashedPass',  `" . Constants::USERS_FLD_NAME . "` = '$name',  `" . Constants::USERS_FLD_TEL . "` = '$tel',  `" . Constants::USERS_FLD_USER_TYPE . "` = '$role'")) {
                                return $this->returnError(Constants::SIGNUP_OPERATION_FAILED, "Please try again later!", 0, 0, 0);
                            }
                            // 3.1. Check the role
                            // 4. Insert data into proper tables and don't insert anything if invalid role (Make input text safe from within the extra data object)
                            $_id = $this->db_link->insert_id;

                            switch ($role) {
                                case Constants::USER_BUYER:
                                    $address = Utilities::makeInputSafe($extraData[Constants::BUYERS_FLD_ADDRESS]);
                                    $ccNumber = Utilities::makeInputSafe($extraData[Constants::BUYERS_FLD_CCNUMBER]);
                                    $ccCCV = Utilities::makeInputSafe($extraData[Constants::BUYERS_FLD_CC_CCV]);
                                    $ccMonth = Utilities::makeInputSafe($extraData[Constants::BUYERS_FLD_CC_MONTH]);
                                    $ccYear = Utilities::makeInputSafe($extraData[Constants::BUYERS_FLD_CC_YEAR]);
                                    if (strlen(trim($address)) != 0 && strlen(trim($ccNumber)) != 0 && strlen(trim($ccCCV)) != 0 && strlen(trim($ccMonth)) != 0 && strlen(trim($ccYear)) != 0) {
                                        if (is_numeric($ccNumber)) {
                                            if (is_numeric($ccCCV) && strlen($ccCCV) == 3) {
                                                if (is_numeric($ccMonth) && $ccMonth > 0 && $ccMonth < 13 && is_numeric($ccYear) && $ccYear >= date("Y") && $ccYear < date("Y") + 10) {
                                                    $result = $this->db_link->query("INSERT INTO `" . Constants::TBL_BUYERS . "` SET `" . Constants::BUYERS_FLD_ADDRESS . "` = '$address', `" . Constants::BUYERS_FLD_CCNUMBER . "` = '$ccNumber', `" . Constants::BUYERS_FLD_CC_CCV . "` = '$ccCCV',  `" . Constants::BUYERS_FLD_CC_MONTH . "` = '$ccMonth', `" . Constants::BUYERS_FLD_CC_YEAR . "` = '$ccYear', `" . Constants::BUYERS_FLD_USER_ID . "` = '$_id'");
                                                    if ($result) {
                                                        return $this->login($email, $pass1);
                                                    } else {
                                                        return $this->returnError(Constants::SIGNUP_OPERATION_FAILED, "Please try again later!", $_id, Constants::USERS_FLD_ID, Constants::TBL_USERS);
                                                    }
                                                } else {
                                                    return $this->returnError(Constants::SIGNUP_INVALID_CCDATE, "Invalid credit card expiry date!", $_id, Constants::USERS_FLD_ID, Constants::TBL_USERS);
                                                }
                                            } else {
                                                return $this->returnError(Constants::SIGNUP_INVALID_CCCCV, "Invalid credit card CCV!", $_id, Constants::USERS_FLD_ID, Constants::TBL_USERS);
                                            }
                                        } else {
                                            return $this->returnError(Constants::SIGNUP_INVALID_CCNUMBER, "Invalid credit card number!", $_id, Constants::USERS_FLD_ID, Constants::TBL_USERS);
                                        }
                                    } else {
                                        return $this->returnError(Constants::SIGNUP_EMPTY_DATA, "All fields are required!", $_id, Constants::USERS_FLD_ID, Constants::TBL_USERS);
                                    }
                                    break;
                                case Constants::USER_SELLER:
                                    $address = Utilities::makeInputSafe($extraData[Constants::SELLERS_FLD_ADDRESS]);
                                    $bankAccount = Utilities::makeInputSafe($extraData[Constants::SELLERS_FLD_BACK_ACCOUNT]);
                                    if (strlen(trim($address)) != 0 && strlen(trim($bankAccount)) != 0) {
                                        $result = $this->db_link->query("INSERT INTO `" . Constants::TBL_SELLERS . "` SET `" . Constants::SELLERS_FLD_ADDRESS . "` = '$address', `" . Constants::SELLERS_FLD_BACK_ACCOUNT . "` = '$bankAccount', `" . Constants::SELLERS_FLD_USER_ID . "` = '$_id'");
                                        if ($result) {
                                            return $this->login($email, $pass1);
                                        } else {
                                            return $this->returnError(Constants::SIGNUP_OPERATION_FAILED, "Please try again later!", $_id, Constants::USERS_FLD_ID, Constants::TBL_USERS);
                                        }
                                    } else {
                                        return $this->returnError(Constants::SIGNUP_EMPTY_DATA, "All fields are required!", $_id, Constants::USERS_FLD_ID, Constants::TBL_USERS);
                                    }
                                    break;
                                case Constants::USER_ADMIN:
                                    $result = $this->db_link->query("INSERT INTO `" . Constants::TBL_ADMINS . "` SET `" . Constants::ADMINS_FLD_USER_ID . "` = '$_id'");
                                    if ($result) {
                                        return $this->login($email, $pass1);
                                    } else {
                                        return $this->returnError(Constants::SIGNUP_OPERATION_FAILED, "Please try again later!", $_id, Constants::USERS_FLD_ID, Constants::TBL_USERS);
                                    }
                                    break;
                                case Constants::USER_ACCOUNTANT:
                                    $result = $this->db_link->query("INSERT INTO `" . Constants::TBL_ACCOUNTANTS . "` SET `" . Constants::ADMINS_FLD_USER_ID . "` = '$_id'");
                                    if ($result) {
                                        return $this->login($email, $pass1);
                                    } else {
                                        return $this->returnError(Constants::SIGNUP_OPERATION_FAILED, "Please try again later!", $_id, Constants::USERS_FLD_ID, Constants::TBL_USERS);
                                    }
                                    break;
                                case Constants::USER_DELIVERMAN:
                                    $result = $this->db_link->query("INSERT INTO `" . Constants::TBL_DELIVERYMEN . "` SET `" . Constants::ADMINS_FLD_USER_ID . "` = '$_id'");
                                    if ($result) {
                                        return $this->login($email, $pass1);
                                    } else {
                                        return $this->returnError(Constants::SIGNUP_OPERATION_FAILED, "Please try again later!", $_id, Constants::USERS_FLD_ID, Constants::TBL_USERS);
                                    }
                                    break;
                            }
                            if (!$result) {
                                return $this->returnError(Constants::SIGNUP_OPERATION_FAILED, "Please try again later!", $_id, Constants::USERS_FLD_ID, Constants::TBL_USERS);
                            }
                        } else {
                            return $this->returnError(Constants::SIGNUP_INVALID_ROLE, "Invalid role!", 0, 0, 0);
                        }
                    } else {
                        return $this->returnError(Constants::SIGNUP_EMAIL_EXISTS, "User already exists, please login!", 0, 0, 0);
                    }
                } else {
                    return $this->returnError(Constants::SIGNUP_PASSWORDS_MISMATCH, "Passwords don't match!", 0, 0, 0);
                }
            } else {
                return $this->returnError(Constants::SIGNUP_INVALID_EMAIL, "Please enter valid e-mail address!", 0, 0, 0);
            }
        } else {
            return $this->returnError(Constants::SIGNUP_EMPTY_DATA, "All fields are required!", 0, 0, 0);
        }
    }

    /**
     * This function signs in the user using the provided info
     * @param email $email user's email
     * @param string $pass user's password
     * @return object user details object, or error object in case anything went wrong
     * @checkedByIAR
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
                    return $this->returnError(Constants::LOGIN_OPERATION_FAILED, "Please try again later!", 0, 0, 0);
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
                        return $this->returnError(Constants::LOGIN_INCORRECT_DATA, "Incorrect e-mail or password!", 0, 0, 0);
                    }
                } else {
                    return $this->returnError(Constants::LOGIN_INCORRECT_DATA, "Incorrect e-mail or password!", 0, 0, 0);
                }
            } else {
                return $this->returnError(Constants::LOGIN_INVALID_EMAIL, "Please enter valid e-mail address!", 0, 0, 0);
            }
        } else {
            return $this->returnError(Constants::LOGIN_EMPTY_DATA, "All fields are required!", 0, 0, 0);
        }
    }

    /**
     * This function generates JWT for the user
     * @param array $row the user row from DB
     * @return string jwt
     * @checkedByIAR
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
                Constants::USERS_FLD_ID => $row[Constants::USERS_FLD_ID],
                Constants::USERS_FLD_EMAIL => $row[Constants::USERS_FLD_EMAIL],
                Constants::USERS_FLD_NAME => $row[Constants::USERS_FLD_NAME],
                Constants::USERS_FLD_TEL => $row[Constants::USERS_FLD_TEL],
                Constants::USERS_FLD_STATUS => $row[Constants::USERS_FLD_STATUS],
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
     * @checkedByIAR
     */
    function generateUserModel($row) {
        $userType = $row[Constants::USERS_FLD_USER_TYPE];
        $_id = $row[Constants::USERS_FLD_ID];

        switch ($userType) {
            case Constants::USER_BUYER:
                // Select more data
                if (!$resultDetails = $this->db_link->query("SELECT * FROM `" . Constants::TBL_BUYERS . "` WHERE `" . Constants::BUYERS_FLD_USER_ID . "` = $_id LIMIT 1")) {
                    return $this->returnError(Constants::LOGIN_OPERATION_FAILED, "Please try again later!", 0, 0, 0);
                }
                $rowDetails = $resultDetails->fetch_assoc();
                $userModel = new Buyer($_id, $row[Constants::USERS_FLD_NAME], $row[Constants::USERS_FLD_EMAIL], $row[Constants::USERS_FLD_TEL], $userType, $row[Constants::USERS_FLD_STATUS], $rowDetails[Constants::BUYERS_FLD_ADDRESS], $rowDetails[Constants::BUYERS_FLD_CCNUMBER], $rowDetails[Constants::BUYERS_FLD_CC_CCV], $rowDetails[Constants::BUYERS_FLD_CC_MONTH], $rowDetails[Constants::BUYERS_FLD_CC_YEAR]);
                // return json
                break;
            case Constants::USER_SELLER:
                // Select more data
                if (!$resultDetails = $this->db_link->query("SELECT * FROM `" . Constants::TBL_SELLERS . "` WHERE `" . Constants::SELLERS_FLD_USER_ID . "` = $_id LIMIT 1")) {
                    return $this->returnError(Constants::LOGIN_OPERATION_FAILED, "Please try again later!", 0, 0, 0);
                }
                $rowDetails = $resultDetails->fetch_assoc();

                $userModel = new Seller($_id, $row[Constants::USERS_FLD_NAME], $row[Constants::USERS_FLD_EMAIL], $row[Constants::USERS_FLD_TEL], $userType, $row[Constants::USERS_FLD_STATUS], $rowDetails[Constants::SELLERS_FLD_ADDRESS], $rowDetails[Constants::SELLERS_FLD_BACK_ACCOUNT]);
                break;
            // For the next 3 cases, we currently don't give them extra data, so we will return the basic data only
            case Constants::USER_ACCOUNTANT:
                $userModel = new Accountant($_id, $row[Constants::USERS_FLD_NAME], $row[Constants::USERS_FLD_EMAIL], $row[Constants::USERS_FLD_TEL], $userType, $row[Constants::USERS_FLD_STATUS]);
                break;
            case Constants::USER_DELIVERMAN:
                $userModel = new Deliveryman($_id, $row[Constants::USERS_FLD_NAME], $row[Constants::USERS_FLD_EMAIL], $row[Constants::USERS_FLD_TEL], $userType, $row[Constants::USERS_FLD_STATUS]);
                break;
            case Constants::USER_ADMIN:
                $userModel = new Admin($_id, $row[Constants::USERS_FLD_NAME], $row[Constants::USERS_FLD_EMAIL], $row[Constants::USERS_FLD_TEL], $userType, $row[Constants::USERS_FLD_STATUS]);
                break;
        }
        return $userModel;
    }

    /**
     * This function refreshes the user hash in db on successful login
     * @param array $row user db row
     * @param string $pass user entered password
     * @checkedByIAR
     */
    function refreshUserHash($row, $pass) {
        $_id = $row[Constants::USERS_FLD_ID];
        // Regenerate and insert password
        $newHash = Utilities::hashPassword($pass);
        $this->db_link->query("UPDATE `" . Constants::TBL_USERS . "` SET `" . Constants::USERS_FLD_PASS . "` = '$newHash' WHERE `" . Constants::USERS_FLD_ID . "` = '$_id' LIMIT 1");
    }

    /**
     * This function edits the user account
     * @param type $userID user id to edit its account
     * @param type $userType user type 
     * @param type $userNewData user new data object from request
     * @return object the result
     * @checkedByIAR
     */
    function editAccount($userID, $userType, $userNewData) {

        $name = Utilities::makeInputSafe($userNewData[Constants::USERS_FLD_NAME]);
        $tel = Utilities::makeInputSafe($userNewData[Constants::USERS_FLD_TEL]);
        $pass1 = Utilities::makeInputSafe($userNewData[Constants::USERS_FLD_PASS . "1"]);
        $pass2 = Utilities::makeInputSafe($userNewData[Constants::USERS_FLD_PASS . "2"]);

        if ($userType == Constants::USER_SELLER) {
            $address = Utilities::makeInputSafe($userNewData[Constants::SELLERS_FLD_ADDRESS]);
            $bankAccount = Utilities::makeInputSafe($userNewData[Constants::SELLERS_FLD_BACK_ACCOUNT]);
            if (strlen($address) <= 0 || strlen($bankAccount) <= 0) {
                return $this->returnError(Constants::USER_EDIT_ACCOUNT_EMPTY_DATA, "All data are required!", 0, 0, 0);
            }
        }
        if ($userType == Constants::USER_BUYER) {
            $address = Utilities::makeInputSafe($userNewData[Constants::BUYERS_FLD_ADDRESS]);
            $ccNumber = Utilities::makeInputSafe($userNewData[Constants::BUYERS_FLD_CCNUMBER]);
            $ccCCV = Utilities::makeInputSafe($userNewData[Constants::BUYERS_FLD_CC_CCV]);
            $ccMonth = Utilities::makeInputSafe($userNewData[Constants::BUYERS_FLD_CC_MONTH]);
            $ccYear = Utilities::makeInputSafe($userNewData[Constants::BUYERS_FLD_CC_YEAR]);
            if (strlen($address) <= 0 || strlen($ccNumber) <= 0 || strlen($ccCCV) <= 0 || strlen($ccMonth) <= 0 || strlen($ccYear) <= 0) {
                return $this->returnError(Constants::USER_EDIT_ACCOUNT_EMPTY_DATA, "All data are required!", 0, 0, 0);
            }
        }
        if (strlen($name) > 0 && strlen($tel) > 0 && strlen($pass1) > 0) {
            if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_USERS . "` WHERE `" . Constants::USERS_FLD_ID . "` = $userID LIMIT 1")) {
                return $this->returnError(Constants::USER_EDIT_ACCOUNT_FAILED, "Please try again later!", 0, 0, 0);
            }
            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                // Check if password hash match
                $password = $row[Constants::USERS_FLD_PASS];
                if (Utilities::checkPassword($pass1, $password) == true) {
                    if (strlen($pass2) > 0) {
                        $hashPass = Utilities::hashPassword($pass2);
                        $query = "UPDATE `" . Constants::TBL_USERS . "` SET `" . Constants::USERS_FLD_NAME . "` = '$name', `" . Constants::USERS_FLD_TEL . "` = '$tel', `" . Constants::USERS_FLD_PASS . "` = '$hashPass' WHERE `" . Constants::USERS_FLD_ID . "` = $userID LIMIT 1";
                    } else {
                        $query = "UPDATE `" . Constants::TBL_USERS . "` SET `" . Constants::USERS_FLD_NAME . "` = '$name', `" . Constants::USERS_FLD_TEL . "` = '$tel' WHERE `" . Constants::USERS_FLD_ID . "` = $userID LIMIT 1";
                    }
                    if (!$result = $this->db_link->query($query)) {
                        return $this->returnError(Constants::USER_EDIT_ACCOUNT_FAILED, "Please try again later!", 0, 0, 0);
                    }
                    if ($userType == Constants::USER_SELLER) {
                        $query = "UPDATE `" . Constants::TBL_SELLERS . "` SET `" . Constants::SELLERS_FLD_ADDRESS . "` = '$address', `" . Constants::SELLERS_FLD_BACK_ACCOUNT . "` = '$bankAccount' WHERE `" . Constants::SELLERS_FLD_USER_ID . "` = $userID LIMIT 1";
                        if (!$result = $this->db_link->query($query)) {
                            return $this->returnError(Constants::USER_EDIT_ACCOUNT_FAILED, "Please try again later!", 0, 0, 0);
                        }
                    }
                    if ($userType == Constants::USER_BUYER) {
                        $query = "UPDATE `" . Constants::TBL_BUYERS . "` SET `" . Constants::BUYERS_FLD_ADDRESS . "` = '$address', `" . Constants::BUYERS_FLD_CCNUMBER . "` = '$ccNumber', `" . Constants::BUYERS_FLD_CC_CCV . "` = '$ccCCV', `" . Constants::BUYERS_FLD_CC_MONTH . "` = '$ccMonth', `" . Constants::BUYERS_FLD_CC_YEAR . "` = '$ccYear' WHERE `" . Constants::SELLERS_FLD_USER_ID . "` = $userID LIMIT 1";
                        if (!$result = $this->db_link->query($query)) {
                            return $this->returnError(Constants::USER_EDIT_ACCOUNT_FAILED, "Please try again later!", 0, 0, 0);
                        }
                    }
                    $response = new Response(Constants::USER_EDIT_ACCOUNT_SUCCESSFUL, "Account edited successfully", "");
                    return json_encode($response);
                } else {
                    return $this->returnError(Constants::USER_EDIT_ACCOUNT_INVALID_PASS, "Incorrect password!", 0, 0, 0);
                }
            } else {
                return $this->returnError(Constants::USER_EDIT_ACCOUNT_FAILED, "Please try again later!", 0, 0, 0);
            }
        } else {
            return $this->returnError(Constants::USER_EDIT_ACCOUNT_EMPTY_DATA, "All data are required!", 0, 0, 0);
        }
    }

    /**
     * This function checks if a certain user is not banned by admins
     * @param type $userID
     * @return boolean
     * @checkedByIAR
     */
    function checkIfActiveUser($userID) {
        $userID = Utilities::makeInputSafe($userID);
        if (!$result = $this->db_link->query("SELECT `" . Constants::USERS_FLD_STATUS . "` FROM `" . Constants::TBL_USERS . "` WHERE `" . Constants::USERS_FLD_ID . "` = '$userID' LIMIT 1")) {
            return $this->returnError(Constants::USER_EDIT_ACCOUNT_FAILED, "Please try again later!", 0, 0, 0);
        }
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row[Constants::USERS_FLD_STATUS] == Constants::USER_ACTIVE) {
                return true;
            }
        }
        return false;
    }

    /**
     * This function edits the specified user data
     * @param type $data new user's data object
     * @return type response object
     * @checkedByIAR
     */
    function editEmpAccount($data) {
        $email = Utilities::makeInputSafe($data[Constants::USERS_FLD_EMAIL]);
        $pass1 = Utilities::makeInputSafe($data[Constants::USERS_FLD_PASS]);
        $userID = Utilities::makeInputSafe($data[Constants::USERS_FLD_ID]);
        if (strlen($email) > 0 && strlen($userID) > 0) {
            if (Utilities::checkValidEmail($email)) {
                if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_USERS . "` WHERE `" . Constants::USERS_FLD_EMAIL . "` = '$email' AND `" . Constants::USERS_FLD_ID . "` != '$userID'  LIMIT 1")) {
                    return $this->returnError(Constants::USER_EDIT_ACCOUNT_FAILED, "Please try again later!", 0, 0, 0);
                }
                if ($result->num_rows == 0) {
                    if (strlen($pass1) > 0) {
                        $hashPass = Utilities::hashPassword($pass1);
                        if (!$result = $this->db_link->query("UPDATE `" . Constants::TBL_USERS . "` SET `" . Constants::USERS_FLD_EMAIL . "` = '$email', `" . Constants::USERS_FLD_PASS . "` = '$hashPass' WHERE `" . Constants::USERS_FLD_ID . "` = '$userID'")) {
                            return $this->returnError(Constants::USER_EDIT_ACCOUNT_FAILED, "Please try again later!", 0, 0, 0);
                        }
                    } else {
                        if (!$result = $this->db_link->query("UPDATE `" . Constants::TBL_USERS . "` SET `" . Constants::USERS_FLD_EMAIL . "` = '$email' WHERE `" . Constants::USERS_FLD_ID . "` = '$userID'")) {
                            return $this->returnError(Constants::USER_EDIT_ACCOUNT_FAILED, "Please try again later!", 0, 0, 0);
                        }
                    }
                    if ($this->db_link->affected_rows == 1) {
                        $theResponse = new Response(Constants::USER_EDIT_ACCOUNT_SUCCESSFUL, "User edited successfully!", "");
                        return(json_encode($theResponse));
                    } else {
                        return $this->returnError(Constants::USER_EDIT_ACCOUNT_INVALID_ACCOUNT, "Invalid employee account!", 0, 0, 0);
                    }
                } else {
                    return $this->returnError(Constants::USER_EDIT_ACCOUNT_EMAIL_EXISTS, "Email already exists!", 0, 0, 0);
                }
            } else {
                return $this->returnError(Constants::USER_EDIT_ACCOUNT_INVALID_EMAIL, "Invalid employee email!", 0, 0, 0);
            }
        } else {
            return $this->returnError(Constants::USER_EDIT_ACCOUNT_EMPTY_DATA, "All fields are required!", 0, 0, 0);
        }
    }

    /**
     * This function returns all the users with a specific type
     * @param int $userType user type to retrieve
     * @return array array of users
     * @checkedByIAR
     */
    function getUsersUsingType($userType) {
        $userType = Utilities::makeInputSafe($userType);
        $query = "SELECT * FROM `" . Constants::TBL_USERS . "` WHERE `" . Constants::USERS_FLD_USER_TYPE . "` = '$userType'";
        $allUsers = array();
        $result = $this->db_link->query($query);
        if (!$result) {
            return $this->returnError(Constants::USER_GET_USERS_FAILED, "Please try again later!", 0, 0, 0);
        }
        $ret = array();
        while ($row = $result->fetch_assoc()) {
            array_push($ret, $row);
        }
        $theResponse = new Response(Constants::USER_GET_USERS_SUCCESSFUL, $ret, "");
        return(json_encode($theResponse));
    }

    /**
     * This function changes specific user active/ban status
     * @param int $userID
     * @param int $newStatus user new status
     * @return object status code, msg
     * @checkedByIAR
     */
    function changeUserStatus($userID, $newStatus) {
        $userID = Utilities::makeInputSafe($userID);
        $newStatus = Utilities::makeInputSafe($newStatus);
        if ($newStatus == Constants::USER_ACTIVE || $newStatus == Constants::USER_BANNED) {
            $result = $this->db_link->query("UPDATE `" . Constants::TBL_USERS . "` SET `" . Constants::USERS_FLD_STATUS . "` = '$newStatus' WHERE `" . Constants::USERS_FLD_ID . "` = '$userID' LIMIT 1");
            if (!$result) {
                return $this->returnError(Constants::USER_UPDATE_STATUS_FAILED, "Please try again later!", 0, 0, 0);
            }
            $theResponse = new Response(Constants::USER_UPDATE_STATUS_SUCCESSFUL, "", "");
            return(json_encode($theResponse));
        } else {
            return $this->returnError(Constants::USER_UPDATE_STATUS_INVALID_DATA, "Please use valid status!", 0, 0, 0);
        }
    }

    /**
     * This function deletes specific user
     * @param int $userID
     * @return object status code, result 
     * @checkedByIAR
     */
    function deleteUser($userID) {
        $userID = Utilities::makeInputSafe($userID);
        if (!$result = $this->db_link->query("DELETE FROM `" . Constants::TBL_USERS . "` WHERE `" . Constants::USERS_FLD_ID . "` = '$userID' LIMIT 1")) {
            return $this->returnError(Constants::USER_DELETE_FAILED, "Please try again later!", 0, 0, 0);
        }
        if ($this->db_link->affected_rows == 1) {
            $theResponse = new Response(Constants::USER_DELETE_SUCCESSFUL, "", "");
            return(json_encode($theResponse));
        }
        return $this->returnError(Constants::USER_DELETE_FAILED, "Please try again later!" . $result->affected_rows, 0, 0, 0);
    }

    /**
     * This function allows the admin to insert employees to db
     * @param email $email emp email
     * @param string $pass1 emp password 
     * @param string $pass2 emp password again
     * @param int $empType emp type (Accountant, Deliveryman)
     * @checkedByIAR
     */
    function addEmployee($data) {
        $email = Utilities::makeInputSafe($data[Constants::USERS_FLD_EMAIL]);
        $pass1 = Utilities::makeInputSafe($data[Constants::USERS_FLD_PASS]);
        $empType = Utilities::makeInputSafe($data[Constants::USERS_FLD_USER_TYPE]);
        if (strlen($email) > 0 && strlen($pass1) > 0 && strlen($empType) > 0) {
            if (Utilities::checkValidEmail($email)) {
                if (in_array($empType, Constants::USER_TYPES)) {
                    if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_USERS . "` WHERE `" . Constants::USERS_FLD_EMAIL . "` = '$email' LIMIT 1")) {
                        return $this->returnError(Constants::USER_INSERT_FAILED, "Please try again later!", 0, 0, 0);
                    }
                    if ($result->num_rows == 0) {
                        $hashPass = Utilities::hashPassword($pass1);
                        if (!$result = $this->db_link->query("INSERT INTO `" . Constants::TBL_USERS . "` SET `" . Constants::USERS_FLD_EMAIL . "` = '$email', `" . Constants::USERS_FLD_PASS . "` = '$hashPass', `" . Constants::USERS_FLD_USER_TYPE . "` = '$empType', `" . Constants::USERS_FLD_NAME . "` = 'Emp',  `" . Constants::USERS_FLD_TEL . "` = '0'")) {
                            return $this->returnError(Constants::USER_INSERT_FAILED, "Please try again later!", 0, 0, 0);
                        }
                        $uID = $this->db_link->insert_id;
                        if ($empType == Constants::USER_ACCOUNTANT && !$result = $this->db_link->query("INSERT INTO `" . Constants::TBL_ACCOUNTANTS . "` SET `" . Constants::ACCOUNTANTS_FLD_USER_ID . "` = '" . $uID . "'")) {
                            return $this->returnError(Constants::USER_INSERT_FAILED, "Please try again later!", 0, 0, 0);
                        }
                        if ($empType == Constants::USER_DELIVERMAN && !$result = $this->db_link->query("INSERT INTO `" . Constants::TBL_DELIVERYMEN . "` SET `" . Constants::DELIVERYMEN_FLD_USER_ID . "` = '" . $uID . "'")) {
                            return $this->returnError(Constants::USER_INSERT_FAILED, "Please try again later!", 0, 0, 0);
                        }
                        $theResponse = new Response(Constants::USER_INSERT_SUCCESSFUL, $uID, "");
                        return(json_encode($theResponse));
                    } else {
                        return $this->returnError(Constants::USER_INSERT_EMAIL_EXISTS, "Email already exists!", 0, 0, 0);
                    }
                } else {
                    return $this->returnError(Constants::USER_INSERT_INVALID_DATA, "Invalid employee role!", 0, 0, 0);
                }
            } else {
                return $this->returnError(Constants::USER_INSERT_INVALID_EMAIL, "Invalid employee email!", 0, 0, 0);
            }
        } else {
            return $this->returnError(Constants::USER_INSERT_EMPTY_DATA, "All fields are required!", 0, 0, 0);
        }
    }

    /**
     * This function gets all products in cart items
     * @param int $userID
     * @return array array of products in cart
     */
    function getCartProducts($userID) {
        $userID = Utilities::makeInputSafe($userID);
        $query = "SELECT p.*, ps." . Constants::PRODUCT_SPEC_FLD_ID . " as '" . Constants::PRODUCT_SPEC_PSID . "',  cs." . Constants::CATEGORIES_SPEC_FLD_NAME . " as '" . Constants::PRODUCT_SPEC_CSNAME . "', ps." . Constants::PRODUCT_SPEC_FLD_VALUE . " as '" . Constants::PRODUCT_SPEC_PSVALUE . "' , u." . Constants::USERS_FLD_NAME . " as '" . Constants::PRODUCT_SELLER_NAME . "' , c." . Constants::CATEGORIES_FLD_NAME . " as '" . Constants::PRODUCT_CATEGORY_NAME . "' , a." . Constants::AVAILABILITY_FLD_STATUS . " as '" . Constants::PRODUCT_AVAILABILITY_STATUS . "', ct." . Constants::CART_ITEMS_QUANTITY . " FROM " . Constants::TBL_PRODUCTS . " p LEFT OUTER JOIN " . Constants::TBL_PRODUCT_SPEC . " ps ON ps." . Constants::PRODUCT_SPEC_FLD_PRODUCT_ID . " = p." . Constants::PRODUCTS_FLD_ID . " LEFT OUTER JOIN " . Constants::TBL_CATEGORIES_SPEC . " cs ON cs." . Constants::CATEGORIES_SPEC_FLD_CATID . " = p." . Constants::PRODUCTS_FLD_CATEGORY_ID . " AND ps." . Constants::PRODUCT_SPEC_FLD_CAT_ID . " = cs." . Constants::CATEGORIES_SPEC_FLD_ID . " JOIN " . Constants::TBL_USERS . " u ON u." . Constants::USERS_FLD_ID . " = p." . Constants::PRODUCTS_FLD_SELLER_ID . " JOIN " . Constants::TBL_CATEGORIES . " c ON c." . Constants::CATEGORIES_FLD_ID . " = p." . Constants::PRODUCTS_FLD_CATEGORY_ID . " JOIN " . Constants::TBL_AVAILABILITY_STATUS . " a ON a." . Constants::AVAILABILITY_FLD_ID . " = p." . Constants::PRODUCTS_FLD_AVA_STATUS . " JOIN " . Constants::TBL_CART_ITEMS . " ct ON ct." . Constants::CART_ITEMS_PRODUCT_ID . " = p." . Constants::PRODUCTS_FLD_ID . " ORDER BY ct." . Constants::CART_ITEMS_ID . " DESC, p." . Constants::PRODUCTS_FLD_ID;
        if (!$result = $this->db_link->query($query)) {
            return $this->returnError(Constants::CART_GET_ITEMS_FAILED, "Please try again later!");
        } else {
            $ret = array();
            $lastRow;
            while ($row = $result->fetch_assoc()) {
                // If this product id is already added to list (products are ordered) then add new element to more details array, else add the new product
                if ($row[Constants::PRODUCTS_FLD_ID] == $lastRow[Constants::PRODUCTS_FLD_ID]) {
                    $newMore = array();
                    $newMore[Constants::PRODUCT_SPEC_PSID] = $row[Constants::PRODUCT_SPEC_PSID];
                    $newMore[Constants::PRODUCT_SPEC_CSNAME] = $row[Constants::PRODUCT_SPEC_CSNAME];
                    $newMore[Constants::PRODUCT_SPEC_PSVALUE] = $row[Constants::PRODUCT_SPEC_PSVALUE];
                    array_push($ret[sizeof($ret) - 1]['more'], $newMore);
                } else {
                    $more = array();
                    $more[Constants::PRODUCT_SPEC_PSID] = $row[Constants::PRODUCT_SPEC_PSID];
                    $more[Constants::PRODUCT_SPEC_CSNAME] = $row[Constants::PRODUCT_SPEC_CSNAME];
                    $more[Constants::PRODUCT_SPEC_PSVALUE] = $row[Constants::PRODUCT_SPEC_PSVALUE];
                    unset($row[Constants::PRODUCT_SPEC_PSID]);
                    unset($row[Constants::PRODUCT_SPEC_CSNAME]);
                    unset($row[Constants::PRODUCT_SPEC_PSVALUE]);
                    $row['more'] = array();
                    if ($more['PSID'] != 'null' && is_numeric($more['PSID']))
                        array_push($row['more'], $more);
                    array_push($ret, $row);
                }
                $lastRow = $row;
            }
            $theResponse = new Response(Constants::CART_GET_ITEMS_SUCCESSFUL, $ret, "");
            return(json_encode($theResponse));
        }
    }

    /**
     * This function adds product to cart, if it wasn't there already. If the product is already in the cart items table, this function increases the required quantity 
     * @param type $productId the product id to be added to cart
     * @param type $userID the user id 
     * @return response object with cart item ID
     * @checkedByIAR
     */
    function addProductToCart($productId, $userID) {
        $productId = Utilities::makeInputSafe($productId);
        $userID = Utilities::makeInputSafe($userID);
        if (strlen($productId) > 0 && strlen($userID) > 0) {
            if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_PRODUCTS . "` WHERE `" . Constants::PRODUCTS_FLD_ID . "` = '$productId' AND `" . Constants::PRODUCTS_FLD_AVA_STATUS . "` = '" . Constants::PRODUCT_AVAILABLE . "' AND `" . Constants::PRODUCTS_FLD_AVA_QUANTITY . "` > 0 ")) {
                return $this->returnError(Constants::CART_ADD_ITEM_FAILED, "Please try again later!", 0, 0, 0);
            }
            if ($result->num_rows > 0) {
                $productPrice = $result->fetch_assoc()[Constants::PRODUCTS_FLD_PRICE];
                if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_USERS . "` WHERE `" . Constants::USERS_FLD_ID . "` = '$userID' AND `" . Constants::USERS_FLD_STATUS . "` = '" . Constants::USER_BANNED . "'")) {
                    return $this->returnError(Constants::CART_ADD_ITEM_FAILED, "Please try again later!", 0, 0, 0);
                }
                if ($result->num_rows == 0) {
                    if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_CART_ITEMS . "` WHERE `" . Constants::CART_ITEMS_USER_ID . "` = '$userID' AND `" . Constants::CART_ITEMS_PRODUCT_ID . "` = '$productId'")) {
                        return $this->returnError(Constants::CART_ADD_ITEM_FAILED, "Please try again later!", 0, 0, 0);
                    }
                    if ($result->num_rows == 0) {
                        // product isn't in cart 
                        if (!$result = $this->db_link->query("INSERT INTO `" . Constants::TBL_CART_ITEMS . "` SET `" . Constants::CART_ITEMS_USER_ID . "` = '$userID', `" . Constants::CART_ITEMS_PRODUCT_ID . "` = '$productId', `" . Constants::CART_ITEMS_PRODUCT_TOTAL_PRICE . "` = '$productPrice'")) {
                            return $this->returnError(Constants::CART_ADD_ITEM_FAILED, "Please try again later!", 0, 0, 0);
                        }
                    } else {
                        // product already exists
                        if (!$result = $this->db_link->query("UPDATE `" . Constants::TBL_CART_ITEMS . "` SET `" . Constants::CART_ITEMS_QUANTITY . "` = " . Constants::CART_ITEMS_QUANTITY . " + 1 , `" . Constants::CART_ITEMS_PRODUCT_TOTAL_PRICE . "` = `" . Constants::CART_ITEMS_PRODUCT_TOTAL_PRICE . "` + '$productPrice'  WHERE `" . Constants::CART_ITEMS_PRODUCT_ID . "` = '$productId' AND  `" . Constants::CART_ITEMS_USER_ID . "` = '$userID' AND `" . Constants::CART_ITEMS_QUANTITY . "` <= 9 LIMIT 1")) {
                            return $this->returnError(Constants::CART_ADD_ITEM_FAILED, "Please try again later!", 0, 0, 0);
                        }
                        if ($this->db_link->affected_rows == 0) {
                            return $this->returnError(Constants::CART_ADD_ITEM_LIMIT, "Cart limit exceeded!", 0, 0, 0);
                        }
                    }
                    $cartItemID = $this->db_link->insert_id;
                    // reduce product qunatity 
                    if (!$result = $this->db_link->query("UPDATE `" . Constants::TBL_PRODUCTS . "` SET `" . Constants::PRODUCTS_FLD_AVA_QUANTITY . "` = " . Constants::PRODUCTS_FLD_AVA_QUANTITY . " - 1 WHERE`" . Constants::PRODUCTS_FLD_ID . "` = '$productId' AND `" . Constants::PRODUCTS_FLD_AVA_QUANTITY . "` > 0 LIMIT 1")) {
                        return $this->returnError(Constants::CART_ADD_ITEM_FAILED, "Please try again later!", 0, 0, 0);
                    }
                    if ($this->db_link->affected_rows == 1) {
                        $theResponse = new Response(Constants::CART_ADD_ITEM_SUCCESSFUL, $cartItemID, "");
                        return(json_encode($theResponse));
                    } else {
                        return $this->returnError(Constants::CART_ADD_ITEM_NOT_AVAILABLE, "This product isn't available!", 0, 0, 0);
                    }
                } else {
                    return $this->returnError(Constants::CART_ADD_ITEM_USER_BANNED, "Please contact OMarket administration!", 0, 0, 0);
                }
            } else {
                return $this->returnError(Constants::CART_ADD_ITEM_NOT_AVAILABLE, "This product isn't available!", 0, 0, 0);
            }
        } else {
            return $this->returnError(Constants::CART_ADD_ITEM_EMPTY_DATA, "All fields are required!", 0, 0, 0);
        }
    }

    /**
     * This function removes product from cart
     * @param type $productId the product id to be removed from the cart
     * @param type $userID the user id 
     * @return response object status object
     * @checkedByIAR
     */
    function removeProductFromCart($productID, $userID) {
        $productId = Utilities::makeInputSafe($productID);
        $userID = Utilities::makeInputSafe($userID);
        if (strlen($productId) > 0 && strlen($userID) > 0) {
            if (!$this->checkIfActiveUser($userID)) {
                return $this->returnError(Constants::USER_STATUS_BANNED, "Please contact OMarket administration!", 0, 0, 0);
            }
            if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_CART_ITEMS . "` WHERE `" . Constants::CART_ITEMS_USER_ID . "` = '$userID' AND `" . Constants::CART_ITEMS_PRODUCT_ID . "` = '$productId' LIMIT 1")) {
                return $this->returnError(Constants::CART_DELETE_ITEM_FAILED, "Please try again later!", 0, 0, 0);
            }
            $row = $result->fetch_assoc();
            $quantity = $row[Constants::CART_ITEMS_QUANTITY];
            if (!$result = $this->db_link->query("DELETE FROM `" . Constants::TBL_CART_ITEMS . "` WHERE `" . Constants::CART_ITEMS_USER_ID . "` = '$userID' AND `" . Constants::CART_ITEMS_PRODUCT_ID . "` = '$productId' LIMIT 1")) {
                return $this->returnError(Constants::CART_DELETE_ITEM_FAILED, "Please try again later!", 0, 0, 0);
            }
            if ($this->db_link->affected_rows == 0) {
                return $this->returnError(Constants::CART_DELETE_ITEM_FAILED, "Please try again later!", 0, 0, 0);
            }
            if (!$result = $this->db_link->query("UPDATE `" . Constants::TBL_PRODUCTS . "` SET `" . Constants::PRODUCTS_FLD_AVA_QUANTITY . "` = " . Constants::PRODUCTS_FLD_AVA_QUANTITY . " + $quantity WHERE`" . Constants::PRODUCTS_FLD_ID . "` = '$productId' LIMIT 1")) {
                return $this->returnError(Constants::CART_DELETE_ITEM_FAILED, "Please try again later!", 0, 0, 0);
            }
            $theResponse = new Response(Constants::CART_DELETE_ITEM_SUCCESSFUL, "", "");
            return(json_encode($theResponse));
        } else {
            return $this->returnError(Constants::CART_DELETE_ITEM_EMPTY_DATA, "Missing data!", 0, 0, 0);
        }
    }

    /**
     * This function removes product from cart
     * @param type $productId the product id to be removed from the cart
     * @param type $userID the user id 
     * @return response object status object
     * @checkedByIAR
     */
    function decreaseProductFromCart($productID, $userID) {
        $productId = Utilities::makeInputSafe($productID);
        $userID = Utilities::makeInputSafe($userID);
        if (strlen($productId) > 0 && strlen($userID) > 0) {
            if (!$this->checkIfActiveUser($userID)) {
                return $this->returnError(Constants::USER_STATUS_BANNED, "Please contact OMarket administration!", 0, 0, 0);
            }
            if (!$result = $this->db_link->query("UPDATE `" . Constants::TBL_CART_ITEMS . "` SET `" . Constants::CART_ITEMS_QUANTITY . "` = " . Constants::CART_ITEMS_QUANTITY . " - 1 WHERE `" . Constants::CART_ITEMS_PRODUCT_ID . "` = '$productId' AND `" . Constants::CART_ITEMS_USER_ID . "` = '$userID' AND `" . Constants::CART_ITEMS_QUANTITY . "` > 1 LIMIT 1")) {
                return $this->returnError(Constants::CART_DECREASE_ITEM_FAILED, "Please try again later!", 0, 0, 0);
            }
            if ($this->db_link->affected_rows == 0) {
                return $this->returnError(Constants::CART_DECREASE_ITEM_FAILED, "Please try again later!", 0, 0, 0);
            }
            if (!$result = $this->db_link->query("UPDATE `" . Constants::TBL_PRODUCTS . "` SET `" . Constants::PRODUCTS_FLD_AVA_QUANTITY . "` = " . Constants::PRODUCTS_FLD_AVA_QUANTITY . " + 1 WHERE`" . Constants::PRODUCTS_FLD_ID . "` = '$productId' LIMIT 1")) {
                return $this->returnError(Constants::CART_DECREASE_ITEM_FAILED, "Please try again later!", 0, 0, 0);
            }
            $theResponse = new Response(Constants::CART_DECREASE_ITEM_SUCCESSFUL, "", "");
            return(json_encode($theResponse));
        } else {
            return $this->returnError(Constants::CART_DECREASE_ITEM_EMPTY_DATA, "Missing data!", 0, 0, 0);
        }
    }

    /**
     * This function generates the error json 
     * @param type $code error code (Constants::)
     * @param type $msg error msg
     * @param type $idToDelete id to be deleted
     * @param type $column column to check id against it
     * @param type $tbl table to delete record form it
     * @return string the json encoded error
     * @checkedByIAR
     */
    function returnError($code, $msg, $idToDelete = 0, $column = 0, $tbl = 0) {
        $error = new Error($code, $msg);
        if ($idToDelete > 0) {
            $result = $this->db_link->query("DELETE FROM `" . $tbl . "` WHERE `" . $column . "` = '$idToDelete'");
        }
        return json_encode($error);
    }

    /**
     * This function gets all orders of all user or a certain user
     * @author AhmedSamir
     * @param array $$selectionCols required columns from the orders table
     * @return Response with the order contents
     * @checkedByIAR
     */
    public function getAllOrders($selectionCols, $appliedFilters, $userID = "") {
        $userID = Utilities::makeInputSafe($userID);
        $appliedFilters = json_decode($appliedFilters, true);
        $appliedFilters = $appliedFilters['filters'];
        //the where String
        $whereStringArray = array();
        $stringCost = '';
        $stringDate = '';
        $stringStatus = array();
        if ($appliedFilters['cost']['status']) {
            $stringCost = constants::ORDERS_COST . ' >= ' . Utilities::makeInputSafe($appliedFilters['cost']['min']) . ' and ' . Constants::ORDERS_COST . " <= " . Utilities::makeInputSafe($appliedFilters['cost']['max']);
            array_push($whereStringArray, $stringCost);
        }
        if ($appliedFilters['date']['status']) {
            $stringDate = constants::ORDERS_ISSUEDATE . ' >= ' . "'" . Utilities::makeInputSafe($appliedFilters['date']['min']) . "'" . ' and ' . Constants::ORDERS_ISSUEDATE . ' <= ' . "'" . Utilities::makeInputSafe($appliedFilters['date']['max']) . "'";
            array_push($whereStringArray, $stringDate);
        }
        if ($appliedFilters['status']['pending'])
            array_push($stringStatus, Constants::PENDING);
        if ($appliedFilters['status']['picked'])
            array_push($stringStatus, Constants::PICKED);
        if ($appliedFilters['status']['shipped'])
            array_push($stringStatus, Constants::SHIPPED);
        if ($appliedFilters['status']['delivered'])
            array_push($stringStatus, Constants::DELIVERED);
        if (count($stringStatus) > 0) {
            $stringStatus = Constants::ORDERS_STATUS_ID . ' in ' . '(' . (implode(", ", $stringStatus)) . ')';
            array_push($whereStringArray, $stringStatus);
        }
        if (count($whereStringArray) > 0)
            $theWhereQuery = implode(" and ", $whereStringArray);
        else
            $theWhereQuery = '';

        if ($selectionCols == "") {
            $theString = array(Constants::ORDERS_BUYER_ID, Constants::ORDERS_COST, Constants::ORDERS_ISSUEDATE, Constants::ORDERS_STATUS_ID);
            $selectionCols = ' * ';
        } else
            $theString = explode(",", $selectionCols);
        if ($userID != "") {
            $result = $this->db_link->query('SELECT ' . $selectionCols . ' FROM ' . Constants::TBL_ORDERS . ' WHERE ' . Constants::ORDERS_STATUS_ID . ' != ' . Constants::ORDER_DELETED . ' AND ' . Constants::ORDERS_BUYER_ID . ' = ' . $userID . ' ORDER BY ' . Constants::ORDERS_STATUS_ID . ' ASC');
        } else {
            if (strlen($theWhereQuery) > 0) {
                $result = $this->db_link->query('SELECT ' . $selectionCols . ' FROM ' . Constants::TBL_ORDERS . ' WHERE ' . Constants::ORDERS_STATUS_ID . ' != ' . Constants::ORDER_DELETED . ' AND ' . $theWhereQuery . ' ORDER BY ' . Constants::ORDERS_STATUS_ID . ' ASC');
            } else {
                $result = $this->db_link->query('SELECT ' . $selectionCols . ' FROM ' . Constants::TBL_ORDERS . ' WHERE ' . Constants::ORDERS_STATUS_ID . ' != ' . Constants::ORDER_DELETED  . ' ORDER BY ' . Constants::ORDERS_STATUS_ID . ' ASC');
            }
        }
        $ret = array();
        while ($row = $result->fetch_assoc()) {
            $theOrder = new Order(); //Creating new object
            $theOrder->setId($row[Constants::ORDERS_ID]);
            $theOrder->setAttributes($row, $theString); //Assigning the Other Attributes according to the sent columns

            if ($userID != "") {
                // Get products of this order 
                $theOrder->setProducts(json_decode($this->getOrderItems($row[Constants::ORDERS_ID], $userID), true)['result']);
            }
            array_push($ret, $theOrder);
        }
        $response = new Response(Constants::ORDERS_GET_SUCCESSFUL, $ret, "");
        return json_encode($response);
    }

    /**
     * This function gets one order with it's id
     * @author AhmedSamir
     * @param array $selectionCols required columns from the orders table
     * @param int $id order id
     * @return Response with the order contents 
     * @checkedByIAR
     */
    public function getOrder($id, $selectionCols) {
        $selectionCols = Utilities::makeInputSafe($selectionCols); // @IAR
        if ($selectionCols == "") { //Select all columns
            $result = $this->db_link->query('SELECT * FROM ' . Constants::TBL_ORDERS . ' WHERE ' . Constants::ORDERS_ID . ' = ' . $id . ' LIMIT 1');
            $theString = array(Constants::ORDERS_BUYER_ID, Constants::ORDERS_COST, Constants::ORDERS_ISSUEDATE, Constants::ORDERS_STATUS_ID, Constants::ORDERS_ISSUEDATE);
        } else { //Select the sent ones only
            $result = $this->db_link->query('SELECT ' . $selectionCols . ' FROM ' . Constants::TBL_ORDERS . ' WHERE ' . Constants::ORDERS_ID . ' = ' . $id . ' LIMIT 1');
            $theString = explode(",", $selectionCols);
        }

        if (!$result)
            return $this->returnError(Constants::ORDERS_GET_FAILED, "Please try again later!" . $this->db_link->error);
        if ($row = $result->fetch_assoc()) { //Fetching the Result 
            $theOrder = new Order(); //Creating new object
            $theOrder->setId($id); //Setting the Order ID
            $theOrder->setAttributes($row, $theString); //Assigning the Other Attributes according to the sent columns
            $theResponse = new Response(Constants::ORDERS_GET_SUCCESSFUL, $theOrder, "");
            return(json_encode($theResponse));
        }
    }

    /**
     * This function Adds one order By taking its attributes 
     * @author AhmedSamir
     * $dueDate, $status = "Pending"
     * @param int $buyerId the buyer id
     * @param float $cost the cost
     * @param date $dueDate the date of the order
     * @param string @status the status of the order (Initially it's pending) 
     * @return Response with the order contents
     * @checkedByIAR
     */
    public function addOrder($buyerId) {
        //Make text safe
        $buyerId = Utilities::makeInputSafe($buyerId);
        if (!$this->checkIfActiveUser($buyerId)) {
            return $this->returnError(Constants::USER_STATUS_BANNED, "Please contact OMarket administration!", 0, 0, 0);
        }
        // @IAR removed status because it will be pending by default when adding the order for the first time
        //Check for non-valid fields 
        if (!is_numeric($buyerId))
            return $this->returnError(Constants::ORDERS_ADD_FAILED, "Invalid buyer!");

        // Use this method to clear results after procedure call
        if ($this->db_link->multi_query("CALL addOrder($buyerId);")) {
            do {
                /* store first result set */
                if ($result = $this->db_link->store_result()) {
                    while ($row = $result->fetch_assoc()) {
                        $orderID = $row['(orderID)'];
                    }
                    $result->free();
                }
            } while ($this->db_link->next_result());
        } else {
            return $this->returnError(Constants::ORDERS_ADD_FAILED, "Please try again later!". $this->db_link->error);
        }
        $getOrder = $this->getOrder($orderID, "");
        $getOrderDecoded = json_decode($getOrder, true);
        if ($getOrderDecoded['statusCode'] == Constants::ORDERS_GET_SUCCESSFUL) {
            $getOrderDecoded['statusCode'] = Constants::ORDERS_ADD_SUCCESS;
        }

        return json_encode($getOrderDecoded);
    }

    /**
     * This function Delete one order By taking its ID 
     * @author AhmedSamir
     * @param int $orderID the order id
     * @param int $userID the user id
     * @return Response with the operation code
     * @checkedByIARButNotTestedYet
     */
    public function deleteOrder($orderID, $userID) {
        $userID = Utilities::makeInputSafe($userID);
        $orderID = Utilities::makeInputSafe($orderID);
        if (!$this->checkIfActiveUser($userID)) {
            return $this->returnError(Constants::USER_STATUS_BANNED, "Please contact OMarket administration!", 0, 0, 0);
        }
        $result = $this->db_link->query('SELECT * FROM ' . Constants::TBL_ORDERS . ' where ' . Constants::ORDERS_ID . ' = ' . $orderID . ' AND ' . Constants::ORDERS_BUYER_ID . ' = ' . $userID . ' LIMIT 1');
        $row = $result->fetch_assoc();
        if ($row != "") {
            if (!$mainResut = $this->db_link->query('SELECT * FROM ' . Constants::TBL_ORDERITEMS . ' where ' . Constants::ORDERITEMS_ORDERID . ' = ' . $orderID)) {
                return $this->returnError(Constants::ORDERS_DELETE_FAILED, "Please try again later!", 0, 0, 0);
            }
            while ($row = $mainResut->fetch_assoc()) {
                $productId = $row[Constants::ORDERITEMS_PRODUCTID];
                $quantity = $row[Constants::ORDERITEMS_QUANTITY];
                $totalproductprice = $row[Constants::ORDERITEMS_QUANTITY_TOTAL_PRODUCT_COST];

                if (!$result = $this->db_link->query("UPDATE `" . Constants::TBL_PRODUCTS . "` SET `" . Constants::PRODUCTS_FLD_AVA_QUANTITY . "` = " . Constants::PRODUCTS_FLD_AVA_QUANTITY . " + $quantity, `" . Constants::PRODUCTS_FLD_EARNINGS . "` = " . Constants::PRODUCTS_FLD_EARNINGS . " - $totalproductprice WHERE`" . Constants::PRODUCTS_FLD_ID . "` = '$productId' LIMIT 1")) {
                    return $this->returnError(Constants::ORDERS_DELETE_FAILED, "Please try again later!", 0, 0, 0);
                }
                if (!$this->db_link->query('UPDATE ' . Constants::TBL_ORDERS . ' SET ' . Constants::ORDERS_STATUS_ID . ' = ' . Constants::ORDER_DELETED . ' WHERE ' . Constants::ORDERS_ID . ' = ' . $orderID)) {
                    return $this->returnError(Constants::ORDERS_DELETE_FAILED, "Please try again later!", 0, 0, 0);
                }
            }

            // @ToDo  (Don't return the deleted orders in the get functions)
            $theResponse = new Response(Constants::ORDERS_DELETE_SUCCESS, "", "");
            return json_encode($theResponse);
        } else {
            return $this->returnError(Constants::ORDERS_DELETE_FAILED, "Please try again later!");
        }
    }

    /**
     * This function Updates one order By taking its attributes 
     * @author AhmedSamir
     * @param int $id the order id
     * @param int @status the status of the order. 
     * @return Response with the order contents
     * @checkedByIAR
     */
    //public function updateOrder($id, $buyerId, $cost, $dueDate, $status) {
    public function updateOrder($id, $status) {
        //Make text safe
        $id = Utilities::makeInputSafe($id);
        //$buyerId = Utilities::makeInputSafe($buyerId);
        //$cost = Utilities::makeInputSafe($cost);
        //$dueDate = Utilities::makeInputSafe($dueDate);
        $status = Utilities::makeInputSafe($status);
        //Check if Order is Found

        $result = $this->db_link->query('SELECT * FROM ' . Constants::TBL_ORDERS . ' where ' . Constants::ORDERS_ID . ' = ' . $id . ' LIMIT 1');

        if ($result->num_rows == 0) {
            return $this->returnError(Constants::ORDERS_UPDATE_FAILED, "Please try again later!" . $this->db_link->error);
        }
        //Check for empty fields 
        //if (!(is_numeric($status)) || !(is_numeric($buyerId)) || !(is_numeric($cost)) || $dueDate == "") {
        if (!(is_numeric($status))) {
            return $this->returnError(Constants::ORDERS_UPDATE_FAILED, "Please try again later!");
        }
        //Check for any non-logical parameter values
        //if ($buyerId <= 0 || $cost <= 0 || $status < 1 && $status > 5 || !Utilities::validateDate($dueDate)) {
        if ($status < Constants::PENDING || $status > Constants::DELIVERED) {
            return $this->returnError(Constants::ORDERS_UPDATE_FAILED, "Please try again later!");
        }
        //Update the Order
        //$this->db_link->query('UPDATE ' . Constants::TBL_ORDERS . ' set ' . Constants::ORDERS_BUYER_ID . ' = ' . $buyerId . ' , ' . Constants::ORDERS_COST . ' = ' . $cost . ' , ' . Constants::ORDERS_DATE . " = '" . $dueDate . "' , " . Constants::ORDERS_STATUS_ID . ' = ' . $status . ' where ' . Constants::ORDERS_ID . ' = ' . $id);
        $this->db_link->query('UPDATE ' . Constants::TBL_ORDERS . ' set ' . Constants::ORDERS_STATUS_ID . ' = ' . $status . ' where ' . Constants::ORDERS_ID . ' = ' . $id);

        if ($status == Constants::ORDER_DELIVERED) {

            //Insert avg rate into DB
            $mainResult = $this->db_link->query('SELECT ' . Constants::ORDERITEMS_PRODUCTID . ' FROM ' . Constants::TBL_ORDERITEMS . ' WHERE ' . Constants::ORDERITEMS_ORDERID . ' = ' . $id);

            while ($row = $mainResult->fetch_assoc()) {
                $productID = $row[Constants::ORDERITEMS_PRODUCTID];
                $result = $this->db_link->query('SELECT AVG(' . Constants::RATE_FLD_RATE . ') as `rate`, ' . Constants::ORDERS_BUYER_ID . ' FROM ' . Constants::TBL_RATE . ', ' . Constants::TBL_ORDERS . ' WHERE ' . Constants::RATE_FLD_PRODUCT_ID . ' = ' . $productID . ' AND ' . Constants::RATE_FLD_USER_ID . ' != ' . Constants::ORDERS_BUYER_ID . ' AND ' . Constants::TBL_ORDERS . "." . Constants::ORDERS_ID . ' = ' . $id);
                if (!$result)
                    return $this->returnError(Constants::ORDERS_UPDATE_FAILED, "Please try again later!" . $this->db_link->error);
                $row = $result->fetch_assoc();
                $buyer_id = $row[Constants::ORDERS_BUYER_ID];
                $avgRate = $row[Constants::RATE_FLD_RATE];
                if (!$avgRate || $avgRate == null)
                    $avgRate = 0;
                $deletePreviousResult = $this->db_link->query('DELETE FROM ' . Constants::TBL_RATE . ' WHERE ' . Constants::RATE_FLD_PRODUCT_ID . " = '$productID' AND  " . Constants::RATE_FLD_USER_ID . " = '$buyer_id'");
                $result = $this->db_link->query("INSERT INTO " . Constants::TBL_RATE . " SET " . Constants::RATE_FLD_PRODUCT_ID . " = '$productID', " . Constants::RATE_FLD_USER_ID . " = '$buyer_id', " . Constants::RATE_FLD_RATE . " = '$avgRate'");
                if (!$result)
                    return $this->returnError(Constants::ORDERS_UPDATE_FAILED, "Please try again later!" . $this->db_link->error);
            }
            $result = $this->db_link->query('DELETE FROM ' . Constants::TBL_DELIVERYREQUESTS . ' WHERE ' . Constants::DELIVERYREQUESTS_ORDERID . " = '$id' LIMIT 1");
            if (!$result)
                return $this->returnError(Constants::ORDERS_UPDATE_FAILED, "Please try again later!" . $this->db_link->error);
        }
        $theResponse = new Response(Constants::ORDERS_UPDATE_SUCCESS, "", "");
        return (json_encode($theResponse));
    }

    /**
     * This function gets all order items of a certain order
     * @author AhmedSamir
     * @param int $buyerID the buyer ID
     * @return Response with the order items
     * @checkedByIARNotTested
     */
    public function getOrderItems($orderID, $buyerID) {
        //Make Input Safe
        $orderID = Utilities::makeInputSafe($orderID);
        $buyerID = Utilities::makeInputSafe($buyerID);
        //Check if Order is in the Order Items and of the same buyer
        $result = $this->db_link->query('SELECT * FROM ' . Constants::TBL_ORDERS . ' where ' . Constants::ORDERS_ID . ' = ' . $orderID . ' and ' . Constants::ORDERS_BUYER_ID . ' = ' . $buyerID . ' LIMIT 1');
        if ($result->fetch_assoc()) { // @ToDo check with $result->num_rows if 0 no rows exist with given ID
            //Order is found and is associated with this buyer , Therefore go and get its items.
            $query = "SELECT p.*, ps." . Constants::PRODUCT_SPEC_FLD_ID . " as '" . Constants::PRODUCT_SPEC_PSID . "',  cs." . Constants::CATEGORIES_SPEC_FLD_NAME . " as '" . Constants::PRODUCT_SPEC_CSNAME . "', ps." . Constants::PRODUCT_SPEC_FLD_VALUE . " as '" . Constants::PRODUCT_SPEC_PSVALUE . "' , u." . Constants::USERS_FLD_NAME . " as '" . Constants::PRODUCT_SELLER_NAME . "' , c." . Constants::CATEGORIES_FLD_NAME . " as '" . Constants::PRODUCT_CATEGORY_NAME . "' , a." . Constants::AVAILABILITY_FLD_STATUS . " as '" . Constants::PRODUCT_AVAILABILITY_STATUS . "', ot." . Constants::ORDERITEMS_QUANTITY . " as 'quantity', r." . Constants::RATE_FLD_RATE . " as 'userrate' FROM " . Constants::TBL_PRODUCTS . " p LEFT OUTER JOIN " . Constants::TBL_PRODUCT_SPEC . " ps ON ps." . Constants::PRODUCT_SPEC_FLD_PRODUCT_ID . " = p." . Constants::PRODUCTS_FLD_ID . " LEFT OUTER JOIN " . Constants::TBL_CATEGORIES_SPEC . " cs ON (cs." . Constants::CATEGORIES_SPEC_FLD_CATID . " = p." . Constants::PRODUCTS_FLD_CATEGORY_ID . " AND ps." . Constants::PRODUCT_SPEC_FLD_CAT_ID . " = cs." . Constants::CATEGORIES_SPEC_FLD_ID . ") JOIN " . Constants::TBL_USERS . " u ON u." . Constants::USERS_FLD_ID . " = p." . Constants::PRODUCTS_FLD_SELLER_ID . " JOIN " . Constants::TBL_CATEGORIES . " c ON c." . Constants::CATEGORIES_FLD_ID . " = p." . Constants::PRODUCTS_FLD_CATEGORY_ID . " JOIN " . Constants::TBL_AVAILABILITY_STATUS . " a ON a." . Constants::AVAILABILITY_FLD_ID . " = p." . Constants::PRODUCTS_FLD_AVA_STATUS . " JOIN " . Constants::TBL_ORDERITEMS . " ot ON ot." . Constants::ORDERITEMS_PRODUCTID . " = p." . Constants::PRODUCTS_FLD_ID . " JOIN " . Constants::TBL_ORDERS . " o ON o." . Constants::ORDERS_ID . " = ot." . Constants::ORDERITEMS_ORDERID . " LEFT OUTER JOIN " . Constants::TBL_RATE . " r ON (r." . Constants::RATE_FLD_PRODUCT_ID . " = p." . Constants::PRODUCTS_FLD_ID . " AND r." . Constants::RATE_FLD_USER_ID . " = o." . Constants::ORDERS_BUYER_ID . ") WHERE ot." . Constants::ORDERITEMS_ORDERID . " = '$orderID'  ORDER BY p." . Constants::PRODUCTS_FLD_ID . " DESC";
            $mainResult = $this->db_link->query($query);
            $ret = array();
            $lastRow;
            while ($row = $mainResult->fetch_assoc()) {
                // If this product id is already added to list (products are ordered) then add new element to more details array, else add the new product
                if ($row[Constants::PRODUCTS_FLD_ID] == $lastRow[Constants::PRODUCTS_FLD_ID]) {
                    $newMore = array();
                    $newMore[Constants::PRODUCT_SPEC_PSID] = $row[Constants::PRODUCT_SPEC_PSID];
                    $newMore[Constants::PRODUCT_SPEC_CSNAME] = $row[Constants::PRODUCT_SPEC_CSNAME];
                    $newMore[Constants::PRODUCT_SPEC_PSVALUE] = $row[Constants::PRODUCT_SPEC_PSVALUE];
                    array_push($ret[sizeof($ret) - 1]['more'], $newMore);
                } else {
                    $more = array();
                    $more[Constants::PRODUCT_SPEC_PSID] = $row[Constants::PRODUCT_SPEC_PSID];
                    $more[Constants::PRODUCT_SPEC_CSNAME] = $row[Constants::PRODUCT_SPEC_CSNAME];
                    $more[Constants::PRODUCT_SPEC_PSVALUE] = $row[Constants::PRODUCT_SPEC_PSVALUE];
                    unset($row[Constants::PRODUCT_SPEC_PSID]);
                    unset($row[Constants::PRODUCT_SPEC_CSNAME]);
                    unset($row[Constants::PRODUCT_SPEC_PSVALUE]);
                    $row['more'] = array();
                    if ($more['PSID'] != 'null' && is_numeric($more['PSID']))
                        array_push($row['more'], $more);
                    array_push($ret, $row);
                }
                $lastRow = $row;
            }
            $theResponse = new Response(Constants::ORDERITEMS_GET_SUCCESSFUL, $ret, "");
            return(json_encode($theResponse));
        } else {
            return $this->returnError(Constants::ORDERITEMS_GET_FAILED, "Please try again later!");
        }
    }

    /**
     * This function gets all delivery requests of a certain delivery man
     * @author AhmedSamir
     * @param int $deliveryManID the delivery Man ID
     * @return Response with the delivery requests
     * @checkedByIAR
     */
    public function getDeliveryRequests($deliveryManID) {
        $deliveryManID = Utilities::makeInputSafe($deliveryManID);
        $result = $this->db_link->query("SELECT o." . Constants::ORDERS_ID . ", d." . Constants::DELIVERYREQUESTS_DUEDATE . ", o." . Constants::ORDERS_COST . ", o." . Constants::ORDERS_STATUS_ID . ", u." . Constants::USERS_FLD_NAME . ", u." . Constants::USERS_FLD_TEL . ", b." . Constants::BUYERS_FLD_ADDRESS . " FROM " . Constants::TBL_USERS . " u, " . Constants::TBL_BUYERS . " b, " . Constants::TBL_DELIVERYREQUESTS . " d, " . Constants::TBL_ORDERS . " o WHERE d." . Constants::DELIVERYREQUESTS_ORDERID . "=o." . Constants::ORDERS_ID . " AND u." . Constants::USERS_FLD_ID . " = o." . Constants::ORDERS_BUYER_ID . " AND u." . Constants::USERS_FLD_ID . " = b." . Constants::BUYERS_FLD_USER_ID . " AND d." . Constants::DELIVERYREQUESTS_DELIVERYMANID . " = " . $deliveryManID . " AND o." . Constants::ORDERS_STATUS_ID . " != " . Constants::ORDER_DELIVERED);
        $ret = array();
        while ($row = $result->fetch_assoc()) {
            array_push($ret, $row);
        }
        $theResponse = new Response(Constants::DELIVERYREQUESTS_GET_SUCCESSFUL, $ret, "");
        return(json_encode($theResponse));
    }

    /**
     * This function Adds category
     * @parm string $name
     * @return response 
     * @checkedByIAR
     */
    public function addCategory($name) {
        $name = Utilities::makeInputSafe($name);
        // Check if not empty data
        if (strlen($name) != 0) {
            //check if there is a cat. with the same name
            if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_CATEGORIES . "` WHERE `" . Constants::CATEGORIES_FLD_NAME . "` = '$name' LIMIT 1")) {
                return $this->returnError(Constants::CATEGORY_INSERT_FAILED, "Please try again later!");
            }
            if ($result->num_rows == 1) {
                return $this->returnError(Constants::CATEGORY_NAME_REPETION, "Category's name already exists!");
            } else {
                if (!$result = $this->db_link->query("INSERT INTO `" . Constants::TBL_CATEGORIES . "` SET `" . Constants::CATEGORIES_FLD_NAME . "` = '$name' ")) {
                    return $this->returnError(Constants::CATEGORY_INSERT_FAILED, "Please try again later!");
                } else {
                    $theResponse = new Response(Constants::CATEGORY_ADD_SUCCESS, $this->db_link->insert_id, "");
                    return(json_encode($theResponse));
                }
            }
        } else {
            return $this->returnError(Constants::CATEGORY_EMPTY_DATA, "All fields are required!");
        }
    }

    /**
     * This function deletes category
     * @parm integer $id
     * @return response success if deleted
     * @checkedByIAR
     */
    public function deleteCategory($id) {
        $id = Utilities::makeInputSafe($id);
        if (strlen($id) != 0) {
            //checking if there is a prodcut using that category's id
            $check = $this->db_link->query("SELECT * FROM `" . Constants::TBL_PRODUCTS . "` WHERE " . Constants::PRODUCTS_FLD_CATEGORY_ID . " ='$id'");
            if ($check->num_rows == 0) {
                if (!$result = $this->db_link->query("DELETE FROM `" . Constants::TBL_CATEGORIES . "` WHERE `" . Constants::CATEGORIES_FLD_ID . "` = '$id' LIMIT 1")) {
                    return $this->returnError(Constants::CATEGORY_DELETE_FAILED, "Please try again later!");
                } else {
                    // successul response
                    $theResponse = new Response(Constants::CATEGORY_DELETE_SUCCESS, "DELETE FROM `" . Constants::TBL_CATEGORIES . "` WHERE `" . Constants::CATEGORIES_FLD_ID . "` = '$id' LIMIT 1", "");
                    return(json_encode($theResponse));
                }
            } else {
                return $this->returnError(Constants::CATEGORY_DELETE_FAILED_FOREIGNKEY, "Can't delete category.");
            }
        } else {
            return $this->returnError(Constants::CATEGORY_EMPTY_DATA, "All fields are required!");
        }
    }

    /**
     * This function gets name ,id of category
     * @parm integer $id
     * @return response
     * @checkedByIAR
     */
    public function selectCategory($id) {
        if (strlen(trim($id)) != 0) {
            $id = Utilities::makeInputSafe($id);
            if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_CATEGORIES . "` WHERE `" . Constants::CATEGORIES_FLD_ID . "` = '$id' LIMIT 1")) {
                return $this->returnError(Constants::CATEGORY_SELECT_FAILED, "Please try again later!");
            }
            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                $theResponse = new Response(Constants::CATEGORY_SELECT_SUCCESS, $row, "");
                return(json_encode($theResponse));
            } else {

                return $this->returnError(Constants::CATEGORY_INVALID_ID, "Please,Enter a valid category id.");
            }
        } else {
            return $this->returnError(Constants::CATEGORY_EMPTY_DATA, "All fields are required!");
        }
    }

    /**
     * This function updates category
     * @parm integer $id, string $name
     * @return response
     * @checkedByIAR
     */
    public function updateCategory($id, $name) {
        $name = Utilities::makeInputSafe($name);
        $id = Utilities::makeInputSafe($id);

        if ((strlen($id) != 0) && (strlen($name) != 0)) {

            if (!($result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_CATEGORIES . "` WHERE `" . Constants::CATEGORIES_FLD_ID . "`=" . $id))) {
                return $this->returnError(Constants::CATEGORY_UPDATE_FAILED, "Please try again later!");
            }

            if ($result->num_rows == 1) {
                //check if name is repeated
                if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_CATEGORIES . "` WHERE `" . Constants::CATEGORIES_FLD_NAME . "` = '$name' AND  `" . Constants::CATEGORIES_FLD_ID . "` != $id LIMIT 1")) {
                    return $this->returnError(Constants::CATEGORY_INSERT_FAILED, "Please try again later!");
                }
                if ($result->num_rows == 1) {
                    return $this->returnError(Constants::CATEGORY_NAME_REPETION, "Category's name already exists,Please enter another name.");
                } else {
                    $this->db_link->query("UPDATE `" . Constants::TBL_CATEGORIES . "` SET `" . Constants::CATEGORIES_FLD_NAME . "` = '$name' " . "WHERE `" . Constants::TBL_CATEGORIES . "`.`" . Constants::CATEGORIES_FLD_ID . "` = $id");
                    $theResponse = new Response(Constants::CATEGORY_UPDATE_SUCCESS, "UPDATE `" . Constants::TBL_CATEGORIES . "` SET `" . Constants::CATEGORIES_FLD_NAME . "` = '$name' " . "WHERE `" . Constants::TBL_CATEGORIES . "`.`" . Constants::CATEGORIES_FLD_ID . "` = $id", "");
                    return(json_encode($theResponse));
                }
            } else {
                return $this->returnError(Constants::CATEGORY_UPDATE_FAILED, "Please try again later!");
            }
        } else {
            return $this->returnError(Constants::CATEGORY_EMPTY_DATA, "All fields are required!");
        }
    }

    /**
     * This function gets all categories
     * @return response with array 
     * @checkedByIAR
     */
    public function getAllCategories() {
        if (!$result = $this->db_link->query('SELECT * FROM `' . Constants::TBL_CATEGORIES . '`')) {
            return $this->returnError(Constants::CATEGORY_GET_ALL_CATEGORIES_FAILED, "Please try again later!");
        } else {
            $ret = array();
            while ($row = $result->fetch_assoc()) {
                $cateSpec = json_decode($this->getCategorySpec($row[Constants::CATEGORIES_FLD_ID]), true);
                $row[Constants::CATEGORIES_SPEC] = $cateSpec['result'];
                array_push($ret, $row);
            }
            $theResponse = new Response(Constants::CATEGORY_GET_ALL_CATEGORIES_SUCCESS, $ret, ""); //tp do constants
            return(json_encode($theResponse));
        }
    }

    /**
     * This function adds category specs
     * @parm integer $categoryId ,string $name
     * @return response if succeeded
     * @checkedByIAR
     */
    public function addCategorySpec($categoryId, $name) {

        // Check if not empty data
        if ((strlen(trim($name)) != 0) && (strlen(trim($categoryId)) != 0)) {
            $name = Utilities::makeInputSafe($name);
            $categoryId = Utilities::makeInputSafe($categoryId);
            //check if cat id exists 
            if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_CATEGORIES . "` WHERE `" . Constants::CATEGORIES_FLD_ID . "` = '$categoryId' LIMIT 1")) {
                return $this->returnError(Constants::CATEGORY_SPECS_INSERT_FAILED, "Please try again later!");
            }
            //check if name is repeated
            if ($result->num_rows == 1) {
                if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_CATEGORIES_SPEC . "` WHERE `" . Constants::CATEGORIES_SPEC_FLD_NAME . "` = '$name' AND `" . Constants::CATEGORIES_SPEC_FLD_CATID . "` = '$categoryId' LIMIT 1")) {
                    return $this->returnError(Constants::CATEGORY_SPECS_INSERT_FAILED, "Please try again later!");
                }
                //if repeated error,else insert
                if ($result->num_rows == 1) {
                    return $this->returnError(Constants::CATEGORY_SPECS_NAME_REPEATED, "Please choose another unique name.");
                } else {

                    if (!$result = $this->db_link->query("INSERT INTO `" . Constants::TBL_CATEGORIES_SPEC . "` (`" . Constants::CATEGORIES_SPEC_FLD_CATID . "` ,`" . Constants::CATEGORIES_SPEC_FLD_NAME . "`) VALUES ( '$categoryId','$name' )")) {
                        return $this->returnError(Constants::CATEGORY_SPECS_INSERT_FAILED, "Please try again later!");
                    } else {
                        $theResponse = new Response(Constants::CATEGORY_SPECS_ADD_SUCCESS, $this->db_link->insert_id, "");
                        return(json_encode($theResponse));
                    }
                }
            } else {
                return $this->returnError(Constants::CATEGORY_SPECS_INVALID_CAT_ID, "Invalid category id");
            }
        } else {
            return $this->returnError(Constants::CATEGORY_SPECS_EMPTY_DATA, "All fields are required!");
        }
    }

    /////////////////////////////////////////////////////////
    /**
     * This function updates category specs
     * @param integer $id 
     * @param int ,$categoryId
     * @param string $name 
     * @return response if succeeded
     * @checkedByIAR
     */
    public function updateCategorySpec($id, $categoryId, $name) {
        if ((strlen(trim($id)) != 0) && (strlen(trim($categoryId)) != 0) && (strlen(trim($name)) != 0)) {
            $id = Utilities::makeInputSafe($id);
            $name = Utilities::makeInputSafe($name);
            $categoryId = Utilities::makeInputSafe($categoryId);
            //check if id exists
            if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_CATEGORIES_SPEC . "` WHERE `" . Constants::CATEGORIES_SPEC_FLD_ID . "` = '$id' LIMIT 1")) {
                return $this->returnError(Constants::CATEGORY_SPECS_INSERT_FAILED, "Please try again later!");
            }
            if ($result->num_rows == 1) {
                //check if cat id (foreign key) exists
                if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_CATEGORIES . "` WHERE `" . Constants::CATEGORIES_FLD_ID . "` = '$categoryId' LIMIT 1")) {
                    return $this->returnError(Constants::CATEGORY_SPECS_INSERT_FAILED, "Please try again later!");
                }
                if ($result->num_rows == 1) {
                    //if same name and catid repeated error else update
                    if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_CATEGORIES_SPEC . "` WHERE `" . Constants::CATEGORIES_SPEC_FLD_CATID . "` = '$categoryId' AND " . Constants::CATEGORIES_SPEC_FLD_NAME . " = '$name' AND `" . Constants::CATEGORIES_SPEC_FLD_ID . "` != $id LIMIT 1")) {
                        return $this->returnError(Constants::CATEGORY_SPECS_UPDATE_FAILED, "Please try again later!");
                    }

                    if ($result->num_rows == 1) {
                        return $this->returnError(Constants::CATEGORY_SPECS_PRIMARY_KEY, "Category specification already exists");
                    } else { //else update all
                        $this->db_link->query("UPDATE `" . Constants::TBL_CATEGORIES_SPEC . "` SET `" . Constants::CATEGORIES_SPEC_FLD_CATID . "` = '$categoryId' , `" . Constants::CATEGORIES_SPEC_FLD_NAME . "`= '$name'  WHERE " . Constants::CATEGORIES_SPEC_FLD_ID . "= '$id' ");
                        $theResponse = new Response(Constants::CATEGORY_SPEC_UPDATE_SUCCESS, array(), "");
                        return(json_encode($theResponse));
                    }
                } else {
                    return $this->returnError(Constants::CATEGORY_SPECS_INVALID_CAT_ID, "Invalid category id");
                }
            } else {
                return $this->returnError(Constants::CATEGORY_SPECS_INVALID_SPEC_ID, "Invalid category specification's id");
            }
        } else {
            return $this->returnError(Constants::CATEGORY_SPECS_EMPTY_DATA, "All fields are required!");
        }
    }

    /**
     * This function deletes category specs
     * @param integer $id spec ID
     * @return response object
     * @checkedByIAR
     */
    public function deleteCategorySpec($id) {
        if (strlen(trim($id)) != 0) {
            $id = Utilities::makeInputSafe($id);

            if (!$result = $this->db_link->query("DELETE FROM `" . Constants::TBL_CATEGORIES_SPEC . "` WHERE `" . Constants::CATEGORIES_SPEC_FLD_ID . "` = '$id' LIMIT 1")) {
                return $this->returnError(Constants::CATEGORY_SPEC_DELETE_FAILED, "Please try again later!");
            } else {
                // successul response
                $theResponse = new Response(Constants::CATEGORY_SPEC_DELETE_SUCCESS, array(), "");
                return(json_encode($theResponse));
            }
        } else {
            return $this->returnError(Constants::CATEGORY_SPECS_EMPTY_DATA, "All fields are required!");
        }
    }

    /**
     * This function gets category specs
     * @param integer $id category spec's id
     * @return response 
     * @checkedByIAR
     */
    public function getCategorySpec($id) {
        $id = Utilities::makeInputSafe($id);
        if (strlen($id) != 0) {
            if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_CATEGORIES_SPEC . "` WHERE `" . Constants::CATEGORIES_SPEC_FLD_CATID . "` = '$id'")) {
                return $this->returnError(Constants::CATEGORY_SELECT_FAILED, "Please try again later!");
            }
            $ret = array();
            while ($row = $result->fetch_assoc()) {
                array_push($ret, $row);
            }
            $theResponse = new Response(Constants::CATEGORY_SPEC_SELECT_SUCCESS, $ret, "");
            return(json_encode($theResponse));
        } else {
            return $this->returnError(Constants::CATEGORY_SPECS_EMPTY_DATA, "All fields are required!");
        }
    }

    function __destruct() {
        $this->db_link->close();
    }

    /**
     * This function updates rate of product
     * @parm integer $buyerId
     * @param integer $productId
     * @param integer $rate new rate value
     * @return response with response
     * @checkedByIAR
     */
    public function updateRate($buyerId, $productId, $rate) {
        $buyerId = Utilities::makeInputSafe($buyerId);
        $productId = Utilities::makeInputSafe($productId);
        $increase = Utilities::makeInputSafe($increase);

        if ((strlen(trim($rate)) != 0) && (strlen(trim($productId)) != 0) && (strlen(trim($rate)) != 0)) {
            $buyerId = Utilities::makeInputSafe($buyerId);
            $productId = Utilities::makeInputSafe($productId);
            $rate = Utilities::makeInputSafe($rate);

            if (!$this->checkIfActiveUser($buyerId)) {
                return $this->returnError(Constants::USER_STATUS_BANNED, "Please contact OMarket administration!", 0, 0, 0);
            }
            if (($rate > 5) || ($rate <= 0)) {
                return $this->returnError(Constants::INVALID_INPUT, "Please enter a valid rate");
            } else {
                if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_BUYERS . "` WHERE `" . Constants::BUYERS_FLD_USER_ID . "` = '$buyerId' LIMIT 1")) {
                    return $this->returnError(Constants::RATE_INSERT_FAILED, "Please try again later!");
                }

                if ($result->num_rows == 1) {

                    if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_PRODUCTS . "` WHERE `" . Constants::PRODUCTS_FLD_ID . "` = '$productId' LIMIT 1")) {
                        return $this->returnError(Constants::RATE_INSERT_FAILED, "Please try again later!");
                    }
                    if ($result->num_rows == 1) {
                        $this->db_link->query("UPDATE `" . Constants::TBL_RATE . "` SET `" . Constants::RATE_FLD_RATE . "` = '$rate' WHERE " . Constants::RATE_FLD_PRODUCT_ID . "= '$productId' AND " . Constants::BUYERS_FLD_USER_ID . "= '$buyerId'");
                        $theResponse = new Response(Constants::RATE_UPDATE_SUCCESS, array(), "");
                        return(json_encode($theResponse));
                    } else {
                        return $this->returnError(Constants::RATE_PRODUCT_NOT_EXISTS, "Please try again later!");
                    }
                } else {
                    return $this->returnError(Constants::RATE_BUYER_NOT_EXISTS, "Please try again later!");
                }
            }
        } else {

            return $this->returnError(Constants::RATE_EMPTY_DATA, "All fields are required!");
        }
    }

    /**
     * This function get product's average rate + updates it in the product
     * @parm integer $productId
     * @return response with average rate 
     */
    public function getAvgRate($productId) {

        if (strlen(trim($productId) != 0)) {

            $productId = Utilities::makeInputSafe($productId);
            //checking if product exists
            if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_RATE . "` WHERE `" . Constants::RATE_FLD_PRODUCT_ID . "` = '$productId' LIMIT 1")) {
                return $this->returnError(Constants::RATE_AVGERAGE_FAILED, "Please try again later!");
            }
            //calculating average and sending it+updating it in products
            if ($result->num_rows == 1) {

                $Query = $this->db_link->query("UPDATE `" . Constants::TBL_PRODUCTS . "` SET `" . Constants::RATE_FLD_RATE . "` =  ( SELECT AVG ( " . Constants::RATE_FLD_RATE . " ) FROM " . Constants::TBL_RATE . " WHERE " . Constants::RATE_FLD_PRODUCT_ID . " = '$productId ' ) WHERE `" . Constants::TBL_PRODUCTS . "`.`" . Constants::PRODUCTS_FLD_ID . "` = " . $productId);

                $Query1 = $this->db_link->query(" SELECT " . Constants::RATE_FLD_RATE . " FROM " . Constants::TBL_PRODUCTS . "  WHERE `" . Constants::TBL_PRODUCTS . "`.`" . Constants::PRODUCTS_FLD_ID . "` = " . $productId);
                $row = $Query1->fetch_assoc();

                $theResponse = new Response(Constants::RATE_AVERAGE_SUCCESS, $row, "");

                return(json_encode($theResponse));
            } else {
                return $this->returnError(Constants::RATE_AVERAGE_INVALID_PRODUCT, "No such product  id");
            }
        } else {
            return $this->returnError(Constants::RATE_AVERAGE_EMPTY_DATA, "All fields are required!");
        }
    }

    /**
     * This function get product's rate
     * @parm integer $productId, integer $buyerId
     * @return response with rate 
     */
    public function getProductRate($productId, $buyerId) {
        if (strlen(trim($productId) != 0)) {
            $productId = Utilities::makeInputSafe($productId);
            $buyerId = Utilities::makeInputSafe($buyerId);
            //checking if product id exists
            if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_RATE . "` WHERE `" . Constants::RATE_FLD_PRODUCT_ID . "` = '$productId' LIMIT 1")) {
                return $this->returnError(Constants::RATE_GET_FAILED, "Please try again later!");
            }

            if ($result->num_rows == 1) {
                //checking if buyer id exists
                if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_BUYERS . "` WHERE `" . Constants::BUYERS_FLD_USER_ID . "` = '$buyerId' LIMIT 1")) {
                    return $this->returnError(Constants::RATE_GET_FAILED, "Please try again later!");
                }

                if ($result->num_rows == 1) {
                    //selecting the rate

                    $theQuery = $this->db_link->query("SELECT " . Constants::RATE_FLD_RATE . " FROM`" . Constants::TBL_RATE . "` WHERE " . Constants::RATE_FLD_PRODUCT_ID . " = '$productId' AND " . Constants::BUYERS_FLD_USER_ID . " = '$buyerId'");

                    $row = $theQuery->fetch_assoc();

                    $theResponse = new Response(Constants::RATE_GET_SUCCESS, $row, "");


                    return(json_encode($theResponse));
                } else {
                    return $this->returnError(Constants::RATE_GET_INVALID_BUYER, "No such buyer  id");
                }
            } else {
                return $this->returnError(Constants::RATE_GET_INVALID_PRODUCT, "No such product  id");
            }
        } else {
            return $this->returnError(Constants::RATE_GET_EMPTY_DATA, "All fields are required!");
        }
    }

    /**
     * This function gets all products with specifications ,1 spec of a product per row
     * @param int $cateID category id to get its products
     * @param int $sellerID seller id to get his/her products
     * @return response with array 
     * @todo fix to return only non-deleted and non-seller-blocked products to non-admins, non-sellers users
     */
    public function getAllProducts($cateID = -1, $sellerID = -1) {
        /*
         * Query:
         *  SELECT p.*, ps._id as 'PSID',  cs.name as 'CSNAME', ps.value as 'PSVALUE', u.name as 'seller_name', c.name as 'category_name', a.status as 'availability_status' FROM products p
         *  LEFT OUTER JOIN product_spec ps ON ps.product_id = p._id
         *  LEFT OUTER JOIN categories_spec cs ON cs.category_id = p.category_id AND ps.categories_spec_id = cs._id
         *  JOIN users u ON u._id = p.seller_id
         *  JOIN categories c ON c._id = p.category_id
         *  JOIN availability_status a ON a._id = p.availability_id
         *  ORDER BY p._id DESC
         */
        $query = "SELECT p.*, ps." . Constants::PRODUCT_SPEC_FLD_ID . " as '" . Constants::PRODUCT_SPEC_PSID . "',  cs." . Constants::CATEGORIES_SPEC_FLD_NAME . " as '" . Constants::PRODUCT_SPEC_CSNAME . "', ps." . Constants::PRODUCT_SPEC_FLD_VALUE . " as '" . Constants::PRODUCT_SPEC_PSVALUE . "' , u." . Constants::USERS_FLD_NAME . " as '" . Constants::PRODUCT_SELLER_NAME . "' , c." . Constants::CATEGORIES_FLD_NAME . " as '" . Constants::PRODUCT_CATEGORY_NAME . "' , a." . Constants::AVAILABILITY_FLD_STATUS . " as '" . Constants::PRODUCT_AVAILABILITY_STATUS . "' FROM " . Constants::TBL_PRODUCTS . " p LEFT OUTER JOIN " . Constants::TBL_PRODUCT_SPEC . " ps ON ps." . Constants::PRODUCT_SPEC_FLD_PRODUCT_ID . " = p." . Constants::PRODUCTS_FLD_ID . " LEFT OUTER JOIN " . Constants::TBL_CATEGORIES_SPEC . " cs ON cs." . Constants::CATEGORIES_SPEC_FLD_CATID . " = p." . Constants::PRODUCTS_FLD_CATEGORY_ID . " AND ps." . Constants::PRODUCT_SPEC_FLD_CAT_ID . " = cs." . Constants::CATEGORIES_SPEC_FLD_ID . " JOIN " . Constants::TBL_USERS . " u ON u." . Constants::USERS_FLD_ID . " = p." . Constants::PRODUCTS_FLD_SELLER_ID . " JOIN " . Constants::TBL_CATEGORIES . " c ON c." . Constants::CATEGORIES_FLD_ID . " = p." . Constants::PRODUCTS_FLD_CATEGORY_ID . " JOIN " . Constants::TBL_AVAILABILITY_STATUS . " a ON a." . Constants::AVAILABILITY_FLD_ID . " = p." . Constants::PRODUCTS_FLD_AVA_STATUS . " ORDER BY p." . Constants::PRODUCTS_FLD_ID . " DESC";
        if ($sellerID > 0) {
            $query = "SELECT p.*, ps." . Constants::PRODUCT_SPEC_FLD_ID . " as '" . Constants::PRODUCT_SPEC_PSID . "',  cs." . Constants::CATEGORIES_SPEC_FLD_NAME . " as '" . Constants::PRODUCT_SPEC_CSNAME . "', ps." . Constants::PRODUCT_SPEC_FLD_VALUE . " as '" . Constants::PRODUCT_SPEC_PSVALUE . "' , u." . Constants::USERS_FLD_NAME . " as '" . Constants::PRODUCT_SELLER_NAME . "' , c." . Constants::CATEGORIES_FLD_NAME . " as '" . Constants::PRODUCT_CATEGORY_NAME . "' , a." . Constants::AVAILABILITY_FLD_STATUS . " as '" . Constants::PRODUCT_AVAILABILITY_STATUS . "' FROM " . Constants::TBL_PRODUCTS . " p LEFT OUTER JOIN " . Constants::TBL_PRODUCT_SPEC . " ps ON ps." . Constants::PRODUCT_SPEC_FLD_PRODUCT_ID . " = p." . Constants::PRODUCTS_FLD_ID . " LEFT OUTER JOIN " . Constants::TBL_CATEGORIES_SPEC . " cs ON cs." . Constants::CATEGORIES_SPEC_FLD_CATID . " = p." . Constants::PRODUCTS_FLD_CATEGORY_ID . " AND ps." . Constants::PRODUCT_SPEC_FLD_CAT_ID . " = cs." . Constants::CATEGORIES_SPEC_FLD_ID . " JOIN " . Constants::TBL_USERS . " u ON u." . Constants::USERS_FLD_ID . " = p." . Constants::PRODUCTS_FLD_SELLER_ID . " JOIN " . Constants::TBL_CATEGORIES . " c ON c." . Constants::CATEGORIES_FLD_ID . " = p." . Constants::PRODUCTS_FLD_CATEGORY_ID . " JOIN " . Constants::TBL_AVAILABILITY_STATUS . " a ON a." . Constants::AVAILABILITY_FLD_ID . " = p." . Constants::PRODUCTS_FLD_AVA_STATUS . " WHERE p." . Constants::PRODUCTS_FLD_SELLER_ID . " = '$sellerID' AND p." . Constants::PRODUCTS_FLD_AVAILABILITY_ID . " != " . Constants::PRODUCT_DELETED . " ORDER BY p." . Constants::PRODUCTS_FLD_ID . " DESC";
        }
        if ($cateID > 0) {
            $query = "SELECT p.*, ps." . Constants::PRODUCT_SPEC_FLD_ID . " as '" . Constants::PRODUCT_SPEC_PSID . "',  cs." . Constants::CATEGORIES_SPEC_FLD_NAME . " as '" . Constants::PRODUCT_SPEC_CSNAME . "', ps." . Constants::PRODUCT_SPEC_FLD_VALUE . " as '" . Constants::PRODUCT_SPEC_PSVALUE . "' , u." . Constants::USERS_FLD_NAME . " as '" . Constants::PRODUCT_SELLER_NAME . "' , c." . Constants::CATEGORIES_FLD_NAME . " as '" . Constants::PRODUCT_CATEGORY_NAME . "' , a." . Constants::AVAILABILITY_FLD_STATUS . " as '" . Constants::PRODUCT_AVAILABILITY_STATUS . "' FROM " . Constants::TBL_PRODUCTS . " p LEFT OUTER JOIN " . Constants::TBL_PRODUCT_SPEC . " ps ON ps." . Constants::PRODUCT_SPEC_FLD_PRODUCT_ID . " = p." . Constants::PRODUCTS_FLD_ID . " LEFT OUTER JOIN " . Constants::TBL_CATEGORIES_SPEC . " cs ON cs." . Constants::CATEGORIES_SPEC_FLD_CATID . " = p." . Constants::PRODUCTS_FLD_CATEGORY_ID . " AND ps." . Constants::PRODUCT_SPEC_FLD_CAT_ID . " = cs." . Constants::CATEGORIES_SPEC_FLD_ID . " JOIN " . Constants::TBL_USERS . " u ON u." . Constants::USERS_FLD_ID . " = p." . Constants::PRODUCTS_FLD_SELLER_ID . " JOIN " . Constants::TBL_CATEGORIES . " c ON c." . Constants::CATEGORIES_FLD_ID . " = p." . Constants::PRODUCTS_FLD_CATEGORY_ID . " JOIN " . Constants::TBL_AVAILABILITY_STATUS . " a ON a." . Constants::AVAILABILITY_FLD_ID . " = p." . Constants::PRODUCTS_FLD_AVA_STATUS . " WHERE p." . Constants::PRODUCTS_FLD_AVAILABILITY_ID . " != " . Constants::PRODUCT_DELETED . " AND p." . Constants::PRODUCTS_FLD_CATEGORY_ID . " = '$cateID' ORDER BY p." . Constants::PRODUCTS_FLD_ID . " DESC";
        }
        if (!$result = $this->db_link->query($query)) {
            return $this->returnError(Constants::PRODUCTS_GET_ALL_PRODUCTS_FAILED, "Please try again later!");
        } else {
            $ret = array();
            $lastRow;
            while ($row = $result->fetch_assoc()) {
                // If this product id is already added to list (products are ordered) then add new element to more details array, else add the new product
                if ($row[Constants::PRODUCTS_FLD_ID] == $lastRow[Constants::PRODUCTS_FLD_ID]) {
                    $newMore = array();
                    $newMore[Constants::PRODUCT_SPEC_PSID] = $row[Constants::PRODUCT_SPEC_PSID];
                    $newMore[Constants::PRODUCT_SPEC_CSNAME] = $row[Constants::PRODUCT_SPEC_CSNAME];
                    $newMore[Constants::PRODUCT_SPEC_PSVALUE] = $row[Constants::PRODUCT_SPEC_PSVALUE];
                    array_push($ret[sizeof($ret) - 1]['more'], $newMore);
                } else {
                    $more = array();
                    $more[Constants::PRODUCT_SPEC_PSID] = $row[Constants::PRODUCT_SPEC_PSID];
                    $more[Constants::PRODUCT_SPEC_CSNAME] = $row[Constants::PRODUCT_SPEC_CSNAME];
                    $more[Constants::PRODUCT_SPEC_PSVALUE] = $row[Constants::PRODUCT_SPEC_PSVALUE];
                    unset($row[Constants::PRODUCT_SPEC_PSID]);
                    unset($row[Constants::PRODUCT_SPEC_CSNAME]);
                    unset($row[Constants::PRODUCT_SPEC_PSVALUE]);
                    $row['more'] = array();
                    if ($more['PSID'] != 'null' && is_numeric($more['PSID']))
                        array_push($row['more'], $more);
                    array_push($ret, $row);
                }
                $lastRow = $row;
            }
            $theResponse = new Response(Constants::PRODUCTS_GET_ALL_PRODUCTS_SUCCESS, $ret, "");
            return(json_encode($theResponse));
        }
    }

    /**
     * This function gets all categories
     * @param int $productId 
     * @param array $specs contains array of index AKA specName ,value
     * @return response 
     * @checkedByIAR
     */
    public function addProductSpec($productId, $specs) {
        $productId = Utilities::makeInputSafe($productId);

        if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_PRODUCTS . "` WHERE `" . Constants::PRODUCTS_FLD_ID . "` = '$productId' LIMIT 1")) {
            return $this->returnError(Constants::CATEGORY_SPECS_INSERT_FAILED, "Please try again later!");
        }
        if ($result->num_rows == 1) {
            foreach ($specs as $spec) {
                $specvalue = Utilities::makeInputSafe($spec[Constants::PRODUCT_SPEC_FLD_VALUE]);
                $specname = Utilities::makeInputSafe($spec[Constants::CATEGORIES_SPEC_FLD_NAME]);
                $specid = Utilities::makeInputSafe($spec[Constants::CATEGORIES_SPEC_FLD_ID]);
                if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_CATEGORIES_SPEC . "` WHERE `" . Constants::CATEGORIES_SPEC_FLD_ID . "` = '$specid' AND  `" . Constants::CATEGORIES_SPEC_FLD_NAME . "` = '$specname' LIMIT 1")) {
                    return $this->returnError(Constants::CATEGORY_SPECS_INSERT_FAILED, "Please try again later!");
                }
                if ($result->num_rows == 1) {
                    if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_PRODUCT_SPEC . "` WHERE `" . Constants::PRODUCT_SPEC_FLD_PRODUCT_ID . "` = '$productId' AND  `" . Constants::PRODUCT_SPEC_FLD_CAT_ID . "` = '$specid' LIMIT 1")) {
                        return $this->returnError(Constants::CATEGORY_SPECS_INSERT_FAILED, "Please try again later!");
                    }
                    if ($result->num_rows == 0) {
                        if (!$result = $this->db_link->query("INSERT INTO `" . Constants::TBL_PRODUCT_SPEC . "` ( `" . Constants::PRODUCT_SPEC_FLD_PRODUCT_ID . "`,`" . Constants::PRODUCT_SPEC_FLD_CAT_ID . "`,`" . Constants::PRODUCT_SPEC_FLD_VALUE . "`) VALUES ('$productId' , '$specid' ,'$specvalue' )")) {
                            return $this->returnError(Constants::PRODUCT_SPEC_ADD_FAILED, "Please try again later!");
                        }
                    } else {
                        return $this->returnError(Constants::PRODUCT_SPEC_ADD_EXISTS, "Product specification alreay exists!");
                    }
                } else {
                    return $this->returnError(Constants::PRODUCT_SPEC_ADD_FAILED, "Please try again later!");
                }
            }
            $theResponse = new Response(Constants::PRODUCT_SPEC_ADD_SUCCESS, array(), "");
            return(json_encode($theResponse));
        } else {
            return $this->returnError(Constants::PRODUCT_SPEC_INVALID_PRODUCTID, "Please,enter a valid product id");
        }
    }

    /**
     * This function updates product spec.
     * @param integer $id
     * @param string $newSpecValue ,Aka value
     * @return response 
     * @checkedByIAR
     */
    public function updateProductSpec($id, $newSpecValue) {
        if ((strlen(trim($id)) != 0) && (strlen(trim($newSpecValue)) != 0)) {
            $id = Utilities::makeInputSafe($id);
            $newSpecValue = Utilities::makeInputSafe($newSpecValue);
            //check if id exists 
            if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_PRODUCT_SPEC . "` WHERE `" . Constants::PRODUCT_SPEC_FLD_ID . "` = '$id' LIMIT 1")) {
                return $this->returnError(Constants::PRODUCT_SPECS_UPDATE_FAILED, "Please try again later!");
            }
            if ($result->num_rows == 1) {
                $this->db_link->query("UPDATE `" . Constants::TBL_PRODUCT_SPEC . "` SET `" . Constants::PRODUCT_SPEC_FLD_VALUE . "` = '$newSpecValue'  WHERE `" . Constants::PRODUCT_SPEC_FLD_ID . "` = '$id' ");
                $theResponse = new Response(Constants::PRODUCT_SPEC_UPDATE_SUCCESS, "", "");
                return(json_encode($theResponse));
            } else {
                return $this->returnError(Constants::PRODUCT_SPEC_INVALID_ID, "Please enter a valid ID");
            }
        } else {
            return $this->returnError(Constants::PRODUCT_SPEC_EMPTY_DATA, "All fields are required!");
        }
    }

    /**
     * This function deletes all product's specs
     * @param integer $productId
     * @return response
     */
    public function deleteProductSpec($productId) {
        if (strlen(trim($productId)) != 0) {
            $productId = Utilities::makeInputSafe($productId);
            if (!$result = $this->db_link->query("DELETE FROM `" . Constants::TBL_PRODUCT_SPEC . "` WHERE `" . Constants::PRODUCT_SPEC_FLD_PRODUCT_ID . "` = '$productId' ")) {
                return $this->returnError(Constants::PRODUCT_SPEC_DELETE_FAILED, "Please try again later!");
            } else {
                // successul response
                $theResponse = new Response(Constants::PRODUCT_SPEC_DELETE_SUCCESS, array(), "");
                return(json_encode($theResponse));
            }
        } else {
            return $this->returnError(Constants::PRODUCT_SPEC_EMPTY_DATA, "All fields are required!");
        }
    }

    /**
     * This function gets all product's specs
     * @param integer $productId
     * @return response with array
     */
    public function getProductSpec($productId) {
        if (strlen(trim($productId)) != 0) {
            $productId = Utilities::makeInputSafe($productId);
            if (!$result = $this->db_link->query('SELECT * FROM `' . Constants::TBL_PRODUCT_SPEC . "` WHERE `" . Constants::PRODUCT_SPEC_FLD_PRODUCT_ID . "` = '$productId' ")) {
                return $this->returnError(Constants::PRODUCT_SPEC_GET_PRODUCTS_FAILED, "Please try again later!");
            } else {
                $ret = array();
                while ($row = $result->fetch_assoc()) {
                    array_push($ret, $row);
                }
                $theResponse = new Response(Constants::PRODUCT_SPEC_GET_PRODUCTS_SUCCESS, $ret, "");
                return(json_encode($theResponse));
            }
        } else {
            return $this->returnError(Constants::PRODUCT_SPEC_EMPTY_DATA, "All fields are required!");
        }
    }

    /**
     * This function adds product 
     * @param string $name
     * @param int $price
     * @param string $size
     * @param decimal $weight
     * @param integer $available_quantity
     * @param string $origin
     * @param string $provider
     * @param string $image
     * @param integer $seller_id
     * @param integer $category_id
     * @param array $specs
     * @param string $description
     * @return response 
     * @checkedByIAR
     */
    public function addProduct($name, $price, $size, $weight, $available_quantity, $origin, $provider, $image, $seller_id, $category_id, $specs, $description) {

        //make input safe 
        $name = Utilities::makeInputSafe($name);
        $price = Utilities::makeInputSafe($price);
        $size = Utilities::makeInputSafe($size);
        $weight = Utilities::makeInputSafe($weight);
        $available_quantity = Utilities::makeInputSafe($available_quantity);
        $origin = Utilities::makeInputSafe($origin);
        $provider = Utilities::makeInputSafe($provider);
        $image = Utilities::makeInputSafe($image);
        $seller_id = Utilities::makeInputSafe($seller_id);
        $category_id = Utilities::makeInputSafe($category_id);
        if (!$this->checkIfActiveUser($seller_id)) {
            return $this->returnError(Constants::USER_STATUS_BANNED, "Please contact OMarket administration!", 0, 0, 0);
        }
        // @IAR @todo check for valid input data (non-empty, is_numric, +ve values for price, quantity etc)
        //check if seller's  id (foreign key) exists
        if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_SELLERS . "` WHERE `" . Constants::SELLERS_FLD_USER_ID . "` = '$seller_id' LIMIT 1")) {
            return $this->returnError(Constants::PRODUCT_ADD_FAILED, "Please try again late1r!");
        }
        if ($result->num_rows == 1) {
            //check if cat id (foreign key) exists
            if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_CATEGORIES . "` WHERE `" . Constants::CATEGORIES_FLD_ID . "` = '$category_id' LIMIT 1")) {
                return $this->returnError(Constants::PRODUCT_ADD_FAILED, "Please try again later!");
            }
            if ($result->num_rows == 1) {
                //inserting               
                if (!$result = $this->db_link->query("INSERT INTO `" . Constants::TBL_PRODUCTS . "` ( `" . Constants::PRODUCTS_FLD_NAME . "`,`" . Constants::PRODUCTS_FLD_PRICE . "`,`" . Constants::PRODUCTS_FLD_SIZE . "`,`" . Constants::PRODUCTS_FLD_WEIGHT . "`,`" . Constants::PRODUCTS_FLD_AVA_QUANTITY . "`,`" . Constants::PRODUCTS_FLD_ORIGIN . "`,`" . Constants::PRODUCTS_FLD_PROVIDER . "`,`" . Constants::PRODUCTS_FLD_IMAGE . "`,`" . Constants::PRODUCTS_FLD_SELLER_ID . "`,`" . Constants::PRODUCTS_FLD_CATEGORY_ID . "`,`" . Constants::PRODUCTS_FLD_DESCRIPTION . "`) VALUES ('$name' , '$price', '$size' , '$weight' , '$available_quantity' , '$origin' , '$provider' , '$image' , '$seller_id' , '$category_id', '$description' )")) {
                    return $this->returnError(Constants::PRODUCT_ADD_FAILED, "Please try again later!");
                } else {
                    $productID = $this->db_link->insert_id;
                    $addSpecResult = $this->addProductSpec($productID, $specs);
                    $addSpecResult = json_decode($addSpecResult, true);
                    if ($addSpecResult['statusCode'] == Constants::PRODUCT_SPEC_ADD_SUCCESS) {
                        $theResponse = new Response(Constants::PRODUCT_ADD_SUCCESS, $this->db_link->insert_id, "");
                        return(json_encode($theResponse));
                    } else {
                        if (!$result = $this->db_link->query("DELETE FROM `" . Constants::TBL_PRODUCTS . "` WHERE `" . Constants::PRODUCTS_FLD_ID . "` = '$productID' LIMIT 1")) {
                            return $this->returnError(Constants::PRODUCT_ADD_FAILED, "Please try again later!");
                        }
                        return $this->returnError(Constants::PRODUCT_ADD_FAILED, "Please try again later!");
                    }
                }
            } else {//cat's id invalid} 
                return $this->returnError(Constants::PRODUCT_INVALID_CATEGORY, "Please try again later!");
            }
        } else {//seller's invalid
            return $this->returnError(Constants::PRODUCT_INVALID_SELLER, "Please try again later!");
        }
    }

    /**
     * This function deletes product
     * @param integer $productId
     * @param boolean $isAdmin if is ADmin is true : Before deleting the product we will reduce all orders that have this product then delete it, else the product will be marked as deleted only
     * @param int $sellerID seller ID who wants to delete this product to verify that this is his product (-1 means admin)
     * @return response 
     * @checkedByIAR
     */
    public function deleteProduct($productId, $isAdmin = false, $sellerID = -1) {
        //input safe
        if (strlen(trim($productId)) != 0) {
            $productId = Utilities::makeInputSafe($productId);
            if (!$isAdmin && $sellerID > 0) {
                if (!$this->checkIfActiveUser($sellerID)) {
                    return $this->returnError(Constants::USER_STATUS_BANNED, "Please contact OMarket administration!", 0, 0, 0);
                }
                if (!$result = $this->db_link->query("UPDATE `" . Constants::TBL_PRODUCTS . "` SET `" . Constants::PRODUCTS_FLD_AVAILABILITY_ID . "` = " . Constants::PRODUCT_DELETED . "  WHERE `" . Constants::PRODUCTS_FLD_ID . "` = '$productId' AND `" . Constants::PRODUCTS_FLD_SELLER_ID . "` = '$sellerID'")) {
                    return $this->returnError(Constants::PRODUCT_DELETE_FAILED, "Please try again later!");
                } else {
                    $theResponse = new Response(Constants::PRODUCT_DELETE_SUCCESS, "", "");
                    return(json_encode($theResponse));
                }
            } else {
                // Delete product but after reducing total amount of orders which were containing this product
                if (!$result = $this->db_link->query("UPDATE " . Constants::TBL_ORDERS . " o JOIN " . Constants::TBL_ORDERITEMS . " ot ON ot." . Constants::ORDERITEMS_ORDERID . " = o." . Constants::ORDERS_ID . " JOIN " . Constants::TBL_PRODUCTS . " p ON ot." . Constants::ORDERITEMS_PRODUCTID . " = p." . Constants::PRODUCTS_FLD_ID . " SET o." . Constants::ORDERS_COST . " = o." . Constants::ORDERS_COST . " - (p." . Constants::PRODUCTS_FLD_PRICE . " * ot." . Constants::ORDERITEMS_QUANTITY . ") WHERE p." . Constants::PRODUCTS_FLD_ID . " = '$productId'")) {
                    return $this->returnError(Constants::PRODUCT_DELETE_FAILED, "Please try again later!");
                }
                if (!$result = $this->db_link->query("DELETE FROM `" . Constants::TBL_PRODUCTS . "` WHERE `" . Constants::PRODUCTS_FLD_ID . "` = '$productId'")) {
                    return $this->returnError(Constants::PRODUCT_DELETE_FAILED, "Please try again later!");
                } else {
                    $theResponse = new Response(Constants::PRODUCT_DELETE_SUCCESS, "", "");
                    return(json_encode($theResponse));
                }
            }
        } else {
            return $this->returnError(Constants::PRODUCT_DELETE_EMPTY_DATA, "All fields are required!");
        }
    }

    /**
     * This function updates product based on seller id and product's id
     * @param integer $id
     * @param string $name
     * @param int $price
     * @param float $rate
     * @param string $size
     * @param decimal $weight
     * @param integer $availability_id
     * @param integer $available_quantity
     * @param string $origin
     * @param string $provider
     * @param string $image
     * @param integer $seller_id
     * @param integer $category_id
     * @param integer $solditems
     * @param array $more more product specs to edit product specs table
     * @return response 
     * @checkedByIAR
     */
    public function updateProduct($id, $name, $price, $size, $weight, $availability_id, $available_quantity, $origin, $provider, $image, $seller_id, $desc, $more) {
        //make input safe
        $id = Utilities::makeInputSafe($id);
        $name = Utilities::makeInputSafe($name);
        $price = Utilities::makeInputSafe($price);
        $desc = Utilities::makeInputSafe($desc);
        $size = Utilities::makeInputSafe($size);
        $weight = Utilities::makeInputSafe($weight);
        $availability_id = Utilities::makeInputSafe($availability_id);
        $available_quantity = Utilities::makeInputSafe($available_quantity);
        $origin = Utilities::makeInputSafe($origin);
        $provider = Utilities::makeInputSafe($provider);
        $image = Utilities::makeInputSafe($image);
        $seller_id = Utilities::makeInputSafe($seller_id);
        if (!$this->checkIfActiveUser($seller_id)) {
            return $this->returnError(Constants::USER_STATUS_BANNED, "Please contact OMarket administration!", 0, 0, 0);
        }
        // @todo Check if valid input (non-empty, numric, within symantic range)
        //check if availability ,seller ,cat id exists 
        if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_AVAILABILITY_STATUS . "` WHERE `" . Constants::AVAILABILITY_FLD_ID . "` = '$availability_id' LIMIT 1")) {
            return $this->returnError(Constants::PRODUCT_UPDATE_FAILED, "Please try again later!");
        }
        if ($result->num_rows == 1) {
            //check if seller's  id (foreign key) exists
            if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_SELLERS . "` WHERE `" . Constants::SELLERS_FLD_USER_ID . "` = '$seller_id' LIMIT 1")) {
                return $this->returnError(Constants::PRODUCT_UPDATE_FAILED, "Please try again later!");
            }
            if ($result->num_rows == 1) {
                //check if cat id (foreign key) exists
                if (!$result = $this->db_link->query("UPDATE " . Constants::TBL_PRODUCTS . " SET " . Constants::PRODUCTS_FLD_NAME . " = '$name' ," . Constants::PRODUCTS_FLD_PRICE . " = '$price' ," . Constants::PRODUCTS_FLD_SIZE . " = '$size' ," . Constants::PRODUCTS_FLD_WEIGHT . " = '$weight' ," . Constants::PRODUCTS_FLD_AVAILABILITY_ID . " = '$availability_id' ," . Constants::PRODUCTS_FLD_AVA_QUANTITY . " = '$available_quantity' ," . Constants::PRODUCTS_FLD_ORIGIN . " = '$origin' ," . Constants::PRODUCTS_FLD_PROVIDER . " = '$provider' ," . Constants::PRODUCTS_FLD_IMAGE . " = '$image', " . Constants::PRODUCTS_FLD_DESCRIPTION . " = '$desc' WHERE `" . Constants::PRODUCTS_FLD_SELLER_ID . "` = '$seller_id' AND " . Constants::PRODUCTS_FLD_ID . " = '$id'")) {
                    return $this->returnError(Constants::PRODUCT_UPDATE_FAILED, "Please try again later!");
                } else {
                    foreach ($more as $spec) {
                        $specvalue = Utilities::makeInputSafe($spec[Constants::PRODUCT_SPEC_FLD_VALUE]);
                        $specid = Utilities::makeInputSafe($spec[Constants::PRODUCT_SPEC_FLD_ID]);
                        $updatepecResult = json_decode($this->updateProductSpec($specid, $specvalue), true);
                        if ($updatepecResult['statusCode'] != Constants::PRODUCT_SPEC_UPDATE_SUCCESS) {
                            return $this->returnError(Constants::PRODUCT_UPDATE_FAILED, "Please try again later!");
                        }
                    }
                    $theResponse = new Response(Constants::PRODUCT_UPDATE_SUCCESS, "", "");
                    return(json_encode($theResponse));
                }
            } else {//seller's invalid
                return $this->returnError(Constants::PRODUCT_INVALID_SELLER, "Please try again later!");
            }
        } else {//availbility invalid
            return $this->returnError(Constants::PRODUCT_INVALID_AVAILABILITY, "Please try again later!");
        }
    }

    /**
     * This function gets total earnings of a product
     * @param integer $productId
     * @param integer $sellerId
     * @return response with total earnings if succeeded
     */
    public function getTotalEarnings($productId, $sellerId) {

        //input safe
        if ((strlen(trim($productId)) != 0) && (strlen(trim($sellerId)) != 0)) {
            //
            $price = Utilities::makeInputSafe($price);
            $productId = Utilities::makeInputSafe($productId);
            //if  seller exists
            if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_SELLERS . "` WHERE `" . Constants::SELLERS_FLD_USER_ID . "` ='$sellerId' LIMIT 1")) {

                return $this->returnError(Constants::PRODUCT_GET_EARNINGS_FAILED, "Please try again later!");
            }
            if ($result->num_rows == 1) {
                //check if product id (foreign key) exists
                if (!$result = $this->db_link->query("SELECT * FROM `" . Constants::TBL_PRODUCTS . "` WHERE `" . Constants::PRODUCTS_FLD_ID . "` = '$productId' LIMIT 1")) {
                    return $this->returnError(Constants::PRODUCT_GET_EARNINGS_FAILED, "Please try again later!");
                }
                if ($result->num_rows == 1) {
                    //sold items*price
                    if (!$result = $this->db_link->query("SELECT " . Constants::PRODUCTS_FLD_PRICE . " , " . Constants::PRODUCTS_FLD_SOLDITEMS . " FROM `" . Constants::TBL_PRODUCTS . "` WHERE `" . Constants::PRODUCTS_FLD_ID . "` = '$productId' AND `" . Constants::PRODUCTS_FLD_SELLER_ID . "` = '$sellerId' LIMIT 1")) {
                        return $this->returnError(Constants::PRODUCT_GET_EARNINGS_FAILED, "Please try again later!");
                    }
                    if ($result->num_rows == 1) {
                        //success sending total earnings
                        $row = $result->fetch_assoc();
                        $totalEarnings = $row["price"] * $row["solditems"];
                        $theResponse = new Response(Constants::PRODUCT_TOTAL_EARNINGS_SUCCESS, $totalEarnings, "");
                        return(json_encode($theResponse));
                    }
                } else {
                    //cat does not exist
                    return $this->returnError(Constants::PRODUCT_INVALID_CATEGORY, "Please, enter a valid category");
                }
            } else { //seller does not exist
                return $this->returnError(Constants::PRODUCT_INVALID_SELLER, "Please,enter a valid seller");
            }
        } else {
            return $this->returnError(Constants::PRODUCT_TOTAL_EARNINGS_EMPTY_DATA, "All fields are required!");
        }
    }

    /**
     * This function gets all the products by given seller
     * @param integer $sellerId
     * @return response with array of products
     */
    public function getProductBySeller($sellerId) {

        if (strlen(trim($sellerId)) != 0) {
            $sellerId = Utilities::makeInputSafe($sellerId);
            if (!$result = $this->db_link->query('SELECT * FROM `' . Constants::TBL_PRODUCTS . "` WHERE `" . Constants::PRODUCTS_FLD_SELLER_ID . "` = '$sellerId' ")) {
                return $this->returnError(Constants::PRODUCT_GET_FROM_SELLER_FAILED, "Please try again later!");
            } else {
                $ret = array();
                while ($row = $result->fetch_assoc()) {
                    array_push($ret, $row);
                }
                $theResponse = new Response(Constants::PRODUCT_GET_FROM_SELLER_SUCCESS, $ret, "");
                return(json_encode($theResponse));
            }
        } else {
            return $this->returnError(Constants::PRODUCT_GET_FROM_SELLER_EMPTY_DATA, "All fields are required!");
        }
    }

    /**
     * This function gets all the products by given category 
     * @param integer $catId
     * @return response with array of products
     */
    public function getProductByCategory($catId) {
        if (strlen(trim($catId)) != 0) {
            $catId = Utilities::makeInputSafe($catId);
            if (!$result = $this->db_link->query('SELECT * FROM `' . Constants::TBL_PRODUCTS . "` WHERE `" . Constants::PRODUCTS_FLD_CATEGORY_ID . "` = '$catId' ")) {
                return $this->returnError(Constants::PRODUCT_GET_FROM_CAT_FAILED, "Please try again later!");
            } else {
                $ret = array();

                while ($row = $result->fetch_assoc()) {
                    array_push($ret, $row);
                }
                $theResponse = new Response(Constants::PRODUCT_GET_FROM_CAT_SUCCESS, $ret, "");
                return(json_encode($theResponse));
            }
        } else {
            return $this->returnError(Constants::PRODUCT_GET_FROM_CAT_EMPTY_DATA, "All fields are required!");
        }
    }

    /**
     * This function gets all the products by part of word or a close word
     * @param integer $keyword
     * @return response with array of products
     */
    public function getProductByKey($keyword) {
        if (strlen(trim($keyword)) != 0) {
            $keyword = Utilities::makeInputSafe($keyword);
            // SELECT * FROM products WHERE name LIKE '%xp%'  OR Description LIKE '%xp%'
            $query = "SELECT p.*, ps." . Constants::PRODUCT_SPEC_FLD_ID . " as '" . Constants::PRODUCT_SPEC_PSID . "',  cs." . Constants::CATEGORIES_SPEC_FLD_NAME . " as '" . Constants::PRODUCT_SPEC_CSNAME . "', ps." . Constants::PRODUCT_SPEC_FLD_VALUE . " as '" . Constants::PRODUCT_SPEC_PSVALUE . "' , u." . Constants::USERS_FLD_NAME . " as '" . Constants::PRODUCT_SELLER_NAME . "' , c." . Constants::CATEGORIES_FLD_NAME . " as '" . Constants::PRODUCT_CATEGORY_NAME . "' , a." . Constants::AVAILABILITY_FLD_STATUS . " as '" . Constants::PRODUCT_AVAILABILITY_STATUS . "' FROM " . Constants::TBL_PRODUCTS . " p LEFT OUTER JOIN " . Constants::TBL_PRODUCT_SPEC . " ps ON ps." . Constants::PRODUCT_SPEC_FLD_PRODUCT_ID . " = p." . Constants::PRODUCTS_FLD_ID . " LEFT OUTER JOIN " . Constants::TBL_CATEGORIES_SPEC . " cs ON cs." . Constants::CATEGORIES_SPEC_FLD_CATID . " = p." . Constants::PRODUCTS_FLD_CATEGORY_ID . " AND ps." . Constants::PRODUCT_SPEC_FLD_CAT_ID . " = cs." . Constants::CATEGORIES_SPEC_FLD_ID . " JOIN " . Constants::TBL_USERS . " u ON u." . Constants::USERS_FLD_ID . " = p." . Constants::PRODUCTS_FLD_SELLER_ID . " JOIN " . Constants::TBL_CATEGORIES . " c ON c." . Constants::CATEGORIES_FLD_ID . " = p." . Constants::PRODUCTS_FLD_CATEGORY_ID . " JOIN " . Constants::TBL_AVAILABILITY_STATUS . " a ON a." . Constants::AVAILABILITY_FLD_ID . " = p." . Constants::PRODUCTS_FLD_AVA_STATUS . "  WHERE p." . Constants::PRODUCTS_FLD_NAME . " LIKE '%$keyword%' OR p." . Constants::PRODUCTS_FLD_DESCRIPTION . " LIKE '%$keyword%' ORDER BY " . Constants::PRODUCTS_FLD_SOLDITEMS . " DESC, p." . Constants::PRODUCTS_FLD_ID;

            if (!$result = $this->db_link->query($query)) {
                return $this->returnError(Constants::PRODUCT_GET_FROM_KEY_FAILED, "Please try again later!" . $this->db_link->error);
            } else {
                $ret = array();
                $lastRow;
                while ($row = $result->fetch_assoc()) {
                    // If this product id is already added to list (products are ordered) then add new element to more details array, else add the new product
                    if ($row[Constants::PRODUCTS_FLD_ID] == $lastRow[Constants::PRODUCTS_FLD_ID]) {
                        $newMore = array();
                        $newMore[Constants::PRODUCT_SPEC_PSID] = $row[Constants::PRODUCT_SPEC_PSID];
                        $newMore[Constants::PRODUCT_SPEC_CSNAME] = $row[Constants::PRODUCT_SPEC_CSNAME];
                        $newMore[Constants::PRODUCT_SPEC_PSVALUE] = $row[Constants::PRODUCT_SPEC_PSVALUE];
                        array_push($ret[sizeof($ret) - 1]['more'], $newMore);
                    } else {
                        $more = array();
                        $more[Constants::PRODUCT_SPEC_PSID] = $row[Constants::PRODUCT_SPEC_PSID];
                        $more[Constants::PRODUCT_SPEC_CSNAME] = $row[Constants::PRODUCT_SPEC_CSNAME];
                        $more[Constants::PRODUCT_SPEC_PSVALUE] = $row[Constants::PRODUCT_SPEC_PSVALUE];
                        unset($row[Constants::PRODUCT_SPEC_PSID]);
                        unset($row[Constants::PRODUCT_SPEC_CSNAME]);
                        unset($row[Constants::PRODUCT_SPEC_PSVALUE]);
                        $row['more'] = array();
                        if ($more['PSID'] != 'null' && is_numeric($more['PSID']))
                            array_push($row['more'], $more);
                        array_push($ret, $row);
                    }
                    $lastRow = $row;
                }
                $theResponse = new Response(Constants::PRODUCT_GET_FROM_KEY_SUCCESS, $ret, "");
                return(json_encode($theResponse));
            }
        } else {
            return $this->returnError(Constants::PRODUCT_GET_FROM_KEY_EMPTY, "All fields are required!");
        }
    }

    /**
     * This function gets Top 3 products in top 4 categories
     * @return response with array of products
     * @checkedByIAR
     */
    public function getTop3In4Cat() {
        //select category_id from products group by category_id order by sum(solditems) DESC limit 4
        if (!$result = $this->db_link->query('SELECT ' . Constants::PRODUCTS_FLD_CATEGORY_ID . " from " . Constants::TBL_PRODUCTS . " group by " . Constants::PRODUCTS_FLD_CATEGORY_ID . " order by sum(" . Constants::PRODUCTS_FLD_SOLDITEMS . ") DESC limit 4")) {
            return $this->returnError(Constants::PRODUCT_GET_TOP_3_IN_4_CAT_FAILED, "Please try again later!");
        } else {
            $categoriesIdArray = array();
            while ($row = $result->fetch_assoc()) {
                array_push($categoriesIdArray, $row[Constants::PRODUCTS_FLD_CATEGORY_ID]);
            }

            $Cats4 = array(); //array carrys 4 arrays of top porducts in top 4 cat
            foreach ($categoriesIdArray as $cateID) {
                $query = "SELECT p.*, ps." . Constants::PRODUCT_SPEC_FLD_ID . " as '" . Constants::PRODUCT_SPEC_PSID . "',  cs." . Constants::CATEGORIES_SPEC_FLD_NAME . " as '" . Constants::PRODUCT_SPEC_CSNAME . "', ps." . Constants::PRODUCT_SPEC_FLD_VALUE . " as '" . Constants::PRODUCT_SPEC_PSVALUE . "' , u." . Constants::USERS_FLD_NAME . " as '" . Constants::PRODUCT_SELLER_NAME . "' , c." . Constants::CATEGORIES_FLD_NAME . " as '" . Constants::PRODUCT_CATEGORY_NAME . "' , a." . Constants::AVAILABILITY_FLD_STATUS . " as '" . Constants::PRODUCT_AVAILABILITY_STATUS . "' FROM " . Constants::TBL_PRODUCTS . " p LEFT OUTER JOIN " . Constants::TBL_PRODUCT_SPEC . " ps ON ps." . Constants::PRODUCT_SPEC_FLD_PRODUCT_ID . " = p." . Constants::PRODUCTS_FLD_ID . " LEFT OUTER JOIN " . Constants::TBL_CATEGORIES_SPEC . " cs ON cs." . Constants::CATEGORIES_SPEC_FLD_CATID . " = p." . Constants::PRODUCTS_FLD_CATEGORY_ID . " AND ps." . Constants::PRODUCT_SPEC_FLD_CAT_ID . " = cs." . Constants::CATEGORIES_SPEC_FLD_ID . " JOIN " . Constants::TBL_USERS . " u ON u." . Constants::USERS_FLD_ID . " = p." . Constants::PRODUCTS_FLD_SELLER_ID . " JOIN " . Constants::TBL_CATEGORIES . " c ON c." . Constants::CATEGORIES_FLD_ID . " = p." . Constants::PRODUCTS_FLD_CATEGORY_ID . " JOIN " . Constants::TBL_AVAILABILITY_STATUS . " a ON a." . Constants::AVAILABILITY_FLD_ID . " = p." . Constants::PRODUCTS_FLD_AVA_STATUS . " WHERE p." . Constants::PRODUCTS_FLD_CATEGORY_ID . " = '$cateID'  ORDER BY " . Constants::PRODUCTS_FLD_SOLDITEMS . " DESC , p." . Constants::PRODUCTS_FLD_ID;
                if (!$result = $this->db_link->query($query)) {
                    return $this->returnError(Constants::PRODUCT_GET_TOP_3_IN_4_CAT_FAILED, "Please try again later!");
                } else {
                    $ret = array();
                    $lastRow;
                    $actualProductCount = 0;
                    while ($row = $result->fetch_assoc()) {
                        if ($actualProductCount >= 3)
                            break;
                        // If this product id is already added to list (products are ordered) then add new element to more details array, else add the new product
                        if ($row[Constants::PRODUCTS_FLD_ID] == $lastRow[Constants::PRODUCTS_FLD_ID]) {
                            $newMore = array();
                            $newMore[Constants::PRODUCT_SPEC_PSID] = $row[Constants::PRODUCT_SPEC_PSID];
                            $newMore[Constants::PRODUCT_SPEC_CSNAME] = $row[Constants::PRODUCT_SPEC_CSNAME];
                            $newMore[Constants::PRODUCT_SPEC_PSVALUE] = $row[Constants::PRODUCT_SPEC_PSVALUE];
                            array_push($ret[sizeof($ret) - 1]['more'], $newMore);
                        } else {
                            $more = array();
                            $more[Constants::PRODUCT_SPEC_PSID] = $row[Constants::PRODUCT_SPEC_PSID];
                            $more[Constants::PRODUCT_SPEC_CSNAME] = $row[Constants::PRODUCT_SPEC_CSNAME];
                            $more[Constants::PRODUCT_SPEC_PSVALUE] = $row[Constants::PRODUCT_SPEC_PSVALUE];
                            unset($row[Constants::PRODUCT_SPEC_PSID]);
                            unset($row[Constants::PRODUCT_SPEC_CSNAME]);
                            unset($row[Constants::PRODUCT_SPEC_PSVALUE]);
                            $row['more'] = array();
                            if ($more['PSID'] != 'null' && is_numeric($more['PSID']))
                                array_push($row['more'], $more);
                            array_push($ret, $row);
                            $actualProductCount++;
                        }
                        $lastRow = $row;
                    }
                    $cate = array();
                    $cate[Constants::CATEGORIES_FLD_ID] = $cateID;
                    $cate[Constants::CATEGORIES_FLD_NAME] = json_decode($this->selectCategory($cateID), true)['result'][Constants::CATEGORIES_FLD_NAME];
                    $cate['products'] = $ret;

                    array_push($Cats4, $cate);
                }
            }
            $theResponse = new Response(Constants::PRODUCT_GET_TOP_3_IN_4_CAT_SUCCESS, $Cats4, "");
            return(json_encode($theResponse));
        }
    }

}
