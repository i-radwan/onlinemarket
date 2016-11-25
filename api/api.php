<?php

require_once './SQLOperations.php';
require '../vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App();

/**
 * This function returns user data extracted from passed user token
 * @param string $token  user token
 * @return array user data array extracted from token
 */
function getTokenData($jwt) {
    $secretKey = base64_decode(jwt_key);
    $token = JWT::decode($jwt, $secretKey, jwt_algorithm);
    return ((array) $token->data);
}

/**
 * This function is used to authenticate user and check its role to match the given array
 * @param array $userType array of allowed user_roles constants
 * @param Request $request
 * @param Response $response
 * @return boolean true if authorized 
 */
function authUsers($userType, Request $request, Response $response) {
    $authHeader = $request->getHeader('Authorization');
    list($jwt) = sscanf($authHeader[0], 'Bearer %s');
    if ($jwt) {
        try {
            $data = getTokenData($jwt);
            if (in_array($data[Constants::USERS_FLD_USER_TYPE], $userType)) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    } else {
        return false;
    }
    return false;
}

// For Testing @ToDo To be removed
$app->get('/hello/{name}', function (Request $request, Response $response) {
    //$name = $request->getAttribute("name");
    //echo "Hello, $name";
    return $response->write('Hello ' . $request->getAttribute("name"));
});

// Add route callbacks
$app->post('/login', function (Request $request, Response $response) {
    $sqlOperations = new SQLOperations();
    $allPostPutVars = $request->getParsedBody();
    $email = $allPostPutVars[Constants::USERS_FLD_EMAIL];
    $pass = $allPostPutVars[Constants::USERS_FLD_PASS];
    return $response->withStatus(200)->write($sqlOperations->login($email, $pass));
});
$app->post('/signup', function (Request $request, Response $response) {
    $sqlOperations = new SQLOperations();
    $allPostPutVars = $request->getParsedBody();
    $email = $allPostPutVars[Constants::USERS_FLD_EMAIL];
    $pass1 = $allPostPutVars[Constants::USERS_FLD_PASS . "1"];
    $pass2 = $allPostPutVars[Constants::USERS_FLD_PASS . "2"];
    $role = $allPostPutVars[Constants::USERS_FLD_USER_TYPE];
    $name = $allPostPutVars[Constants::USERS_FLD_NAME];
    $tel = $allPostPutVars[Constants::USERS_FLD_TEL];
    switch ($role) {
        case Constants::USER_BUYER:
            $extraData = array(
                Constants::BUYERS_FLD_ADDRESS => $allPostPutVars[Constants::BUYERS_FLD_ADDRESS],
                Constants::BUYERS_FLD_CCNUMBER => $allPostPutVars[Constants::BUYERS_FLD_CCNUMBER],
                Constants::BUYERS_FLD_CC_CCV => $allPostPutVars[Constants::BUYERS_FLD_CC_CCV],
                Constants::BUYERS_FLD_CC_MONTH => $allPostPutVars[Constants::BUYERS_FLD_CC_MONTH],
                Constants::BUYERS_FLD_CC_YEAR => $allPostPutVars[Constants::BUYERS_FLD_CC_YEAR]
            );
            break;
        case Constants::USER_SELLER:
            $extraData = array(
                Constants::SELLERS_FLD_ADDRESS => $allPostPutVars[Constants::SELLERS_FLD_ADDRESS],
                Constants::SELLERS_FLD_BACK_ACCOUNT => $allPostPutVars[Constants::SELLERS_FLD_BACK_ACCOUNT]
            );
            break;
    }
    return $response->withStatus(200)->write($sqlOperations->signUpUser($email, $pass1, $pass2, $role, $name, $tel, $extraData));
});
$app->put('/user', function (Request $request, Response $response) {
    if (authUsers(Constants::USER_TYPES, $request, $response)) {
        $sqlOperations = new SQLOperations();
        $authHeader = $request->getHeader('Authorization');
        list($jwt) = sscanf($authHeader[0], 'Bearer %s');
        $data = getTokenData($jwt);
        $userID = $data[Constants::USERS_FLD_ID];
        $userType = $data[Constants::USERS_FLD_USER_TYPE];
        return $response->withStatus(200)->write($sqlOperations->editAccount($userID, $userType, $request->getParsedBody()));
    } else {
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
});
$app->put('/user/edit', function (Request $request, Response $response) {
    if (authUsers([Constants::USER_ADMIN], $request, $response)) {
        $sqlOperations = new SQLOperations();
        return $response->withStatus(200)->write($sqlOperations->editEmpAccount($request->getParsedBody()));
    } else {
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
});
$app->post('/user', function (Request $request, Response $response) {
    if (authUsers([Constants::USER_ADMIN], $request, $response)) {
        $sqlOperations = new SQLOperations();
        return $response->withStatus(200)->write($sqlOperations->addEmployee($request->getParsedBody()));
    } else {
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
});
$app->delete('/user', function (Request $request, Response $response) {
    if (authUsers([Constants::USER_ADMIN], $request, $response)) {
        $sqlOperations = new SQLOperations();
        return $response->withStatus(200)->write($sqlOperations->deleteUser($request->getParsedBody()[Constants::USERS_FLD_ID]));
    } else {
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
});
$app->put('/user/block', function (Request $request, Response $response) {
    if (authUsers([Constants::USER_ADMIN], $request, $response)) {
        $sqlOperations = new SQLOperations();
        return $response->withStatus(200)->write($sqlOperations->changeUserStatus($request->getParsedBody()[Constants::USERS_FLD_ID], $request->getParsedBody()[Constants::USERS_FLD_STATUS]));
    } else {
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
});
$app->get('/user/{userType}', function (Request $request, Response $response) {
    if (authUsers([Constants::USER_ADMIN], $request, $response)) {
        $sqlOperations = new SQLOperations();
        $userType = $request->getAttribute('userType');
        return $response->withStatus(200)->write($sqlOperations->getUsersUsingType($userType));
    } else {
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
});

$app->post('/cart', function (Request $request, Response $response) {
    if (authUsers([Constants::USER_BUYER], $request, $response)) {
        $sqlOperations = new SQLOperations();
        $authHeader = $request->getHeader('Authorization');
        list($jwt) = sscanf($authHeader[0], 'Bearer %s');
        $data = getTokenData($jwt);
        return $response->withStatus(200)->write($sqlOperations->addProductToCart($request->getParsedBody()[Constants::PRODUCTS_FLD_ID], $data[Constants::USERS_FLD_ID]));
    } else {
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
});
$app->put('/cart', function (Request $request, Response $response) {
    if (authUsers([Constants::USER_BUYER], $request, $response)) {
        $sqlOperations = new SQLOperations();
        $authHeader = $request->getHeader('Authorization');
        list($jwt) = sscanf($authHeader[0], 'Bearer %s');
        $data = getTokenData($jwt);
        return $response->withStatus(200)->write($sqlOperations->decreaseProductFromCart($request->getParsedBody()[Constants::PRODUCTS_FLD_ID], $data[Constants::USERS_FLD_ID]));
    } else {
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
});
$app->delete('/cart/{productID}', function (Request $request, Response $response) {
    if (authUsers([Constants::USER_BUYER], $request, $response)) {
        $sqlOperations = new SQLOperations();
        $authHeader = $request->getHeader('Authorization');
        list($jwt) = sscanf($authHeader[0], 'Bearer %s');
        $data = getTokenData($jwt);
        return $response->withStatus(200)->write($sqlOperations->removeProductFromCart($request->getAttribute('productID'), $data[Constants::USERS_FLD_ID]));
    } else {
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
});

$app->post('/category', function (Request $request, Response $response) {
    if (authUsers([Constants::USER_ADMIN], $request, $response)) {
        $sqlOperations = new SQLOperations();
        $postVars = $request->getParsedBody();
        $cateName = $postVars[Constants::CATEGORIES_FLD_NAME];
        return $response->withStatus(200)->write($sqlOperations->addCategory($cateName));
    } else {
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
});
//Get All ORDERS (May be needed for accountant)
// reponds to both `/orders/` and `/orders/123`
// but not to `/orders`
$app->get('/orders/[{id}]', function (Request $request, Response $response, $args = []) {
    if (authUsers([Constants::USER_ACCOUNTANT], $request, $response)) {
        $sqlOperations = new SQLOperations();
        $id = $args['id'];
        $selectionCols = $request->getParam('fields');
        $appliedFilters = $request->getParam('filters');
        if ((trim($id) != "" && is_numeric($id)) || trim($id) == "") { // certain user
            return $response->withStatus(200)->write($sqlOperations->getAllOrders($selectionCols, $appliedFilters, trim($id)));
        } else {
            return $response->withStatus(400);
        }
    } else {
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
});

//This GET Request takes One ID only and returns the selected Columns about that Order 
// @ToDo check and test and if not useful remove it 
$app->get('/order/{id}', function (Request $request, Response $response, $args = []) {
    $sqlOperations = new SQLOperations();
    $id = $request->getAttribute('id');
    //It takes an array of columns needed to be returned
    $selectionCols = $request->getParam('fields');
    //Return the response with the order object written in it in JSON 
    return $response->withStatus(200)->write($sqlOperations->getOrder($id, $selectionCols));
});

//Delete Certain Order by the ID (Semi Finished) (Middleware Authorization)/ WithStatus 401 / Retuen respone
// @ToDo authenticate and let users only delete their only orders
$app->delete('/order/{id}', function (Request $request, Response $response, $args) {
    //middleware (authorization)
    //with status (401) Error
    $sqlOperations = new SQLOperations();
    $id = $args['id'];
    return $response->withStatus(200)->write($sqlOperations->deleteOrder($id));
});
//Add Order
// @ToDo check and test
$app->post('/order/', function (Request $request, Response $response) {
    $sqlOperations = new SQLOperations();
    $postVars = $request->getParsedBody();
    $buyerID = $postVars[Constants::ORDERS_BUYER_ID]; // @IAR @ToDo get id from jwt
    $cost = $postVars[Constants::ORDERS_COST];
    $date = $postVars[Constants::ORDERS_DATE];
    $status = $postVars[Constants::ORDERS_STATUS_ID];
    return $response->withStatus(200)->write($sqlOperations->addOrder($buyerID, $cost, $date, $status));
});
//Update Order
$app->put('/order/{id}', function (Request $request, Response $response, $args) {
    if (authUsers([Constants::USER_DELIVERMAN, Constants::USER_BUYER], $request, $response)) {
        $sqlOperations = new SQLOperations();
        $userID = -1;
        $authHeader = $request->getHeader('Authorization');
        list($jwt) = sscanf($authHeader[0], 'Bearer %s');
        $data = getTokenData($jwt);
        if ($data[Constants::USERS_FLD_USER_TYPE] == Constants::USER_BUYER) {
            $userID = $data[Constants::USERS_FLD_ID];
        }
        $id = $args['id'];
        $postVars = $request->getParsedBody();
        $status = $postVars[Constants::ORDERS_STATUS_ID];
        return $response->withStatus(200)->write($sqlOperations->updateOrder($id, $status, $userID));
    } else {
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
});
//Get Order Items
// @ToDo check if working, get user id from jwt
$app->get('/orderitems/{orderid}/{buyerid}', function (Request $request, Response $response, $args) {
    $sqlOperations = new SQLOperations();
    $orderID = $request->getAttribute('orderid');
    $buyerID = $request->getAttribute('buyerid');
    return $response->withStatus(200)->write($sqlOperations->getOrderItems($orderID, $buyerID));
});
//Get Delivery Requests Items
$app->get('/deliveryrequests', function (Request $request, Response $response) {
    if (authUsers([Constants::USER_DELIVERMAN], $request, $response)) {
        $sqlOperations = new SQLOperations();
        $authHeader = $request->getHeader('Authorization');
        list($jwt) = sscanf($authHeader[0], 'Bearer %s');
        $data = getTokenData($jwt);
        return $response->withStatus(200)->write($sqlOperations->getDeliveryRequests($data[Constants::USERS_FLD_ID]));
    } else {
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
});

// Run application
$app->run();
