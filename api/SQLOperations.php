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

    //Test function this function needs verification to work
    function secure($jwt) {
        //list($jwt) = sscanf($authHeader->toString(), 'Authorization: Bearer %s');
        if ($jwt) {
            try {
                $secretKey = base64_decode(jwt_key);

                $token = JWT::decode($jwt, $secretKey, jwt_algorithm);
                echo $token->data->email;
            } catch (Exception $e) {
                
            }
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
     */
    function returnError($code, $msg, $idToDelete = 0, $column = 0, $tbl = 0) {
        $error = new Error($code, $msg);
        if ($idToDelete > 0) {
            $result = $this->db_link->query("DELETE FROM `" . $tbl . "` WHERE `" . $column . "` = '$idToDelete'");
        }
        return json_encode($error);
    }

    function __destruct() {
        $this->db_link->close();
    }

    /**
     * This function gets all orders of all user or a certain user
     * @author AhmedSamir
     * @param array $$selectionCols required columns from the orders table
     * @return Response with the order contents
     */
    public function getAllOrders($selectionCols, $userID = "", $appliedFilters) {
        $appliedFilters = json_decode($appliedFilters, true);
        //the where String
        $whereStringArray = array();
        $stringCost = '';
        $stringDate = '';
        $stringStatus = array();
        if ($appliedFilters['cost']['status']) {
            $stringCost = constants::ORDERS_COST . ' >= ' . $appliedFilters['cost']['min'] . ' and ' . Constants::ORDERS_COST . " <= " . $appliedFilters['cost']['max'];
            array_push($whereStringArray, $stringCost);
        }
        if ($appliedFilters['date']['status']) {
            $stringDate = constants::ORDERS_DATE . ' >= ' . "'" . $appliedFilters['date']['min'] . "'" . ' and ' . Constants::ORDERS_DATE . ' <= ' . "'" . $appliedFilters['date']['max'] . "'";
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
            $theString = array(Constants::ORDERS_BUYER_ID, Constants::ORDERS_COST, Constants::ORDERS_DATE, Constants::ORDERS_STATUS_ID);
            $selectionCols = ' * ';
        } else
            $theString = explode(",", $selectionCols);
        if ($userID != "") {
            $result = $this->db_link->query('SELECT ' . $selectionCols . ' FROM ' . Constants::TBL_ORDERS . ' WHERE ' . Constants::ORDERS_BUYER_ID . ' = ' . $userID . ' and ' . $theWhereQuery);
        } else {
            if (strlen($theWhereQuery) > 0){
                $result = $this->db_link->query('SELECT ' . $selectionCols . ' FROM ' . Constants::TBL_ORDERS . ' WHERE ' . $theWhereQuery);
            }
            else {
                $result = $this->db_link->query('SELECT ' . $selectionCols . ' FROM ' . Constants::TBL_ORDERS);
            }
        }
        $ret = array();
        while ($row = $result->fetch_assoc()) {
            $theOrder = new Order(); //Creating new object
            $theOrder->setId($row[Constants::ORDERS_ID]);
            $theOrder->setAttributes($row, $theString); //Assigning the Other Attributes according to the sent columns
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
     */
    public function getOrder($id, $selectionCols) {
        if ($selectionCols == "") { //Select all columns
            $result = $this->db_link->query('SELECT * FROM ' . Constants::TBL_ORDERS . ' WHERE ' . Constants::ORDERS_ID . ' = ' . $id . ' LIMIT 1');
            $theString = array(Constants::ORDERS_BUYER_ID, Constants::ORDERS_COST, Constants::ORDERS_DATE, Constants::ORDERS_STATUS_ID);
        } else { //Select the sent ones only
            $result = $this->db_link->query('SELECT ' . $selectionCols . ' FROM ' . Constants::TBL_ORDERS . ' WHERE ' . Constants::ORDERS_ID . ' = ' . $id . ' LIMIT 1');
            $theString = explode(",", $selectionCols);
        }
        if (!result)
            return returnError(Constants::ORDERS_GET_FAILED, "Please try again later!");
        $theArray = array();
        if ($row = $result->fetch_assoc()) { //Fetching the Result 
            $theOrder = new Order(); //Creating new object
            $theOrder->setId($id); //Setting the Order ID
            $theOrder->setAttributes($row, $theString); //Assigning the Other Attributes according to the sent columns
            array_push($theArray, $theOrder);
        }
        $theResponse = new Response(Constants::ORDERS_GET_SUCCESSFUL, $theArray, "");
        return(json_encode($theResponse));
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
     */
    public function addOrder($buyerId, $cost, $dueDate, $status = 1) {
        //Make text safe
        $buyerId = Utilities::makeInputSafe($buyerId);
        $cost = Utilities::makeInputSafe($cost);
        $dueDate = Utilities::makeInputSafe($dueDate);
        $status = Utilities::makeInputSafe($status);
        //Check for empty fields 
        if ($status == "" || !is_numeric($buyerId) || !is_numeric($cost) || $dueDate == "")
            return new Response(Constants::ORDERS_ADD_FAILED, json_encode(array()), $jwt);
        //Check for any non-logical parameter values
        if ($buyerId <= 0 || $cost <= 0 || $status < 1 && $status > 5 || !Utilities::validateDate($dueDate))
            return new Response(Constants::ORDERS_ADD_FAILED, json_encode(array()), $jwt);
        //Insert Sql Statment
        $this->db_link->query('INSERT INTO ' . Constants::TBL_ORDERS . ' (' . Constants::ORDERS_BUYER_ID . ',' . Constants::ORDERS_COST . ',' . Constants::ORDERS_DATE . ',' . Constants::ORDERS_STATUS_ID . ')' . ' VALUE (' . $buyerId . ',' . $cost . ",'" . $dueDate . "'," . $status . ')');
        echo 'INSERT INTO ' . Constants::TBL_ORDERS . ' (' . Constants::ORDERS_BUYER_ID . ',' . Constants::ORDERS_COST . ',' . Constants::ORDERS_DATE . ',' . Constants::ORDERS_STATUS_ID . ')' . ' VALUE (' . $buyerId . ',' . $cost . ",'" . $dueDate . "'," . $status . ')';
        //Return the created order by the getOrder function
        return $this->getOrder($this->db_link->insert_id);
    }

    /**
     * This function Delete one order By taking its ID 
     * @author AhmedSamir
     * @param int $orderID the order id
     * @return Response with the operation code
     */
    public function deleteOrder($orderID) {
        $result = $this->db_link->query('SELECT * FROM ' . Constants::TBL_ORDERS . ' where ' . Constants::ORDERS_ID . ' = ' . $orderID . ' LIMIT 1');
        if ($result->fetch_assoc() != "") {
            //Delete First the order items
            $this->db_link->query('DELETE FROM ' . Constants::TBL_ORDERITEMS . ' WHERE ' . Constants::ORDERITEMS_ORDERID . ' = ' . $orderID);
            //Then delete the order
            $this->db_link->query('DELETE FROM ' . Constants::TBL_ORDERS . ' WHERE ' . Constants::ORDERS_ID . ' = ' . $orderID);
            $theResponse = new Response(Constants::ORDERS_DELETE_SUCCESS, array(), "");
            return json_encode($theResponse);
        } else {
            $theResponse = new Response(Constants::ORDERS_DELETE_FAILED, array(), "");
            return json_encode($theResponse);
        }
    }

    /**
     * This function Updates one order By taking its attributes 
     * @author AhmedSamir
     * @param int $id the order id
     * @param int $buyerId the buyer id
     * @param float $cost the cost
     * @param date $dueDate the date of the order
     * @param string @status the status of the order. 
     * @return Response with the order contents
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
        if (!$result->fetch_assoc()) {
            $theResponse = new Response(Constants::ORDERS_UPDATE_FAILED, array(), "");
            return json_encode($theResponse);
        }
        //Check for empty fields 
        //if (!(is_numeric($status)) || !(is_numeric($buyerId)) || !(is_numeric($cost)) || $dueDate == "") {
        if (!(is_numeric($status))) {
            $theResponse = new Response(Constants::ORDERS_UPDATE_FAILED, array(), "");
            return json_encode($theResponse);
        }
        //Check for any non-logical parameter values
        //if ($buyerId <= 0 || $cost <= 0 || $status < 1 && $status > 5 || !Utilities::validateDate($dueDate)) {
        if ($status < Constants::PENDING && $status > Constants::DELIVERED) {
            $theResponse = new Response(Constants::ORDERS_UPDATE_FAILED, array(), "");
            return json_encode($theResponse);
        }
        //Update the Order
        //$this->db_link->query('UPDATE ' . Constants::TBL_ORDERS . ' set ' . Constants::ORDERS_BUYER_ID . ' = ' . $buyerId . ' , ' . Constants::ORDERS_COST . ' = ' . $cost . ' , ' . Constants::ORDERS_DATE . " = '" . $dueDate . "' , " . Constants::ORDERS_STATUS_ID . ' = ' . $status . ' where ' . Constants::ORDERS_ID . ' = ' . $id);
        $this->db_link->query('UPDATE ' . Constants::TBL_ORDERS . ' set ' . Constants::ORDERS_STATUS_ID . ' = ' . $status . ' where ' . Constants::ORDERS_ID . ' = ' . $id);
        $theResponse = $this->getOrder($this->db_link->insert_id);
        return $theResponse;
    }

    /**
     * This function gets all order items of a certain order
     * @author AhmedSamir
     * @param int $orderID the order ID
     * @param int $buyerID the buyer ID
     * @return Response with the order items
     */
    public function getOrderItems($orderID, $buyerID) {
        //Make Input Safe
        $orderID = Utilities::makeInputSafe($orderID);
        $buyerID = Utilities::makeInputSafe($buyerID);
        //Check if Order is in the Order Items and of the same buyer
        $result = $this->db_link->query('SELECT * FROM ' . Constants::TBL_ORDERS . ' where ' . Constants::ORDERS_ID . ' = ' . $orderID . ' and ' . Constants::ORDERS_BUYER_ID . ' = ' . $buyerID . ' LIMIT 1');
        if ($result->fetch_assoc()) {
            //Order is found and is associated with this buyer , Therefore go and get its items.
            $ret = array();
            $query = $this->db_link->query('SELECT * FROM ' . Constants::TBL_ORDERITEMS . ' where ' . Constants::ORDERITEMS_ORDERID . ' = ' . $orderID);
            while ($item = $query->fetch_assoc()) {
                array_push($ret, $item);
            }
            $theResponse = new Response(Constants::ORDERITEMS_GET_SUCCESSFUL, $ret, "");
            return(json_encode($theResponse));
        } else {
            $theResponse = new Response(Constants::ORDERITEMS_GET_FAILED, array(), "");
            return(json_encode($theResponse));
        }
    }

    /**
     * This function gets all delivery requests of a certain delivery man
     * @author AhmedSamir
     * @param int $deliveryManID the delivery Man ID
     * @return Response with the delivery requests
     */
    public function getDeliveryRequests($deliveryManID) {
        $deliveryManID = Utilities::makeInputSafe($deliveryManID);
        $deliveryManID = Utilities::makeInputSafe($deliveryManID);
        $result = $this->db_link->query('SELECT * FROM ' . Constants::TBL_DELIVERYREQUESTS . ' where ' . Constants::DELIVERYREQUESTS_DELIVERYMANID . ' = ' . $deliveryManID);
        $ret = array();
        while ($row = $result->fetch_assoc()) {
            $deliveryRequest = array();
            $deliveryRequest['delivery_request'] = $row;
            //Get the Order (Buyer_id and Staus_id) through order_id
            $orderID = $row[Constants::DELIVERYREQUESTS_ORDERID];
            $orderRow = $this->db_link->query('SELECT ' . Constants::ORDERS_BUYER_ID . ' , ' . Constants::ORDERS_STATUS_ID . ' FROM ' . Constants::TBL_ORDERS . ' where ' . Constants::ORDERS_ID . ' = ' . $orderID);
            //Generate Order Model "object"
            $theOrder = $orderRow->fetch_assoc();
            $theString = array(Constants::ORDERS_BUYER_ID, Constants::ORDERS_STATUS_ID);
            $orderModel = new Order();
            $orderModel->setAttributes($theOrder, $theString);
            $deliveryRequest['delivery_request']['order'] = $orderModel;
            //Get the Buyer through his ID
            $buyerID = $theOrder[Constants::ORDERS_BUYER_ID];
            $buyerRow = $this->db_link->query('SELECT  * FROM ' . Constants::TBL_BUYERS . ' WHERE ' . Constants::BUYERS_FLD_USER_ID . ' = ' . $buyerID);
            $theBuyer = $buyerRow->fetch_assoc();
            //print_r( $theBuyer);
            $deliveryRequest['delivery_request']['buyer'] = $theBuyer;
            array_push($ret, $deliveryRequest);
        }
        $theResponse = new Response(Constants::DELIVERYREQUESTS_GET_SUCCESSFUL, $ret, "");
        return(json_encode($theResponse));
    }

}
