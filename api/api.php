<?php

require_once './SQLOperations.php';
require '../vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App();
$app->get('/hello/{name:.*}', function (Request $request, Response $response) {
    $name = $request->getAttribute("name");

    $allGetVars = $request->getQueryParams();
    
    $paramValue = $allGetVars['fields'];
    echo "Hello, $name :: $paramValue";
});
// Add route callbacks
$app->post('/login', function (Request $request, Response $response) {
    $sqlOperations = new SQLOperations();
    $allPostPutVars = $request->getParsedBody();
    $email = $allPostPutVars['email'];
    $pass = $allPostPutVars['pass'];
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

// Add route callbacks
//This GET Request takes One ID only and returns the selected Columns about that Order 
$app->get('/order/{id}', function (Request $request, Response $response, $args = []) {
    $sqlOperations = new SQLOperations();
    $id = $request->getAttribute('id');
    //It takes an array of columns needed to be returned
    $selectionCols = $request->getParam('fields');
    //Return the response with the order object written in it in JSON 
    return $response->withStatus(200)->write($sqlOperations->getOrder($id, $selectionCols));
});
//Get All ORDERS (May be needed for accountant)
// reponds to both `/orders/` and `/orders/123`
// but not to `/orders`
$app->get('/orders/[{id}]', function (Request $request, Response $response, $args = []) {
    $sqlOperations = new SQLOperations();
    $id = $args['id'];
    $selectionCols = $request->getParam('fields');
    if (trim($id) != "" && is_numeric($id) || trim($id) == "") { // certain user
        return $response->withStatus(200)->write($sqlOperations->getAllOrders($selectionCols, trim($id)));
    } else {
        return $response->withStatus(404);
    }
});
//Delete Certain Order by the ID (Semi Finished) (Middleware Authorization)/ WithStatus 401 / Retuen respone 
$app->delete('/order/{id}', function (Request $request, Response $response, $args) {
    //middleware (authorization)
    //with status (401) Error
    $sqlOperations = new SQLOperations();
    $id = $args['id'];
    return $response->withStatus(200)->write($sqlOperations->deleteOrder($id));
});
//Add Order
$app->post('/order/', function (Request $request, Response $response) {
    $sqlOperations = new SQLOperations();
    $postVars = $request->getParsedBody();
    $buyerID = $postVars[Constants::ORDERS_BUYER_ID];
    $cost = $postVars[Constants::ORDERS_COST];
    $date = $postVars[Constants::ORDERS_DATE];
    $status = $postVars[Constants::ORDERS_STATUS_ID];
    return $response->withStatus(200)->write($sqlOperations->addOrder($buyerID, $cost, $date, $status));
});
//Update Order
$app->put('/order/{id}', function (Request $request, Response $response, $args) {
    $sqlOperations = new SQLOperations();
    $id = $args['id'];
    $postVars = $request->getParsedBody();
    $buyerID = $postVars[Constants::ORDERS_BUYER_ID];
    $cost = $postVars[Constants::ORDERS_COST];
    $date = $postVars[Constants::ORDERS_DATE];
    $status = $postVars[Constants::ORDERS_STATUS_ID];
    return $response->withStatus(200)->write($sqlOperations->updateOrder($id, $buyerID, $cost, $date, $status));
});
//Get Order Items
$app->get('/orderitems/{orderid}/{buyerid}', function (Request $request, Response $response, $args) {
    $sqlOperations = new SQLOperations();
    $orderID = $request->getAttribute('orderid');
    $buyerID = $request->getAttribute('buyerid');
    return $response->withStatus(200)->write($sqlOperations->getOrderItems($orderID, $buyerID));
});
//Get Delivery Requests Items
$app->get('/deliveryrequests/{id}', function (Request $request, Response $response) {
    $sqlOperations = new SQLOperations();
    $deliveryManID = $request->getAttribute('id');
    return $response->withStatus(200)->write($sqlOperations->getDeliveryRequests($deliveryManID));
});

// Run application
$app->run();
//echo $sqlOperations->login("i.radwan1996@gmail.com", "2006");
//echo $sqlOperations->signUpUser("i.radwan1996aasa.@hotmail.coam", "2006", "2006", Constants::USER_BUYER, "Ibrahims", "00120120123", [Constants::BUYERS_FLD_ADDRESS => "6B, Pyramids Gardens", Constants::BUYERS_FLD_CCNUMBER => "123141212", Constants::BUYERS_FLD_CC_CCV => "123", Constants::BUYERS_FLD_CC_MONTH => "10", Constants::BUYERS_FLD_CC_YEAR => "2018"]);
//$sqlOperations->secure("eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0Nzc5MDg1ODksImp0aSI6IkFsb3I3Umw2V3hod09uTExWWHo2c0wzVmlCYXFjRTdmbjlyWHlCbjY4azkySzNzQWJGYUNGdHlCZ2pNVjRMZmtrejVRdklKMmJIVGNxbEh6aVpyb01RPT0iLCJpc3MiOiJsb2NhbGhvc3QiLCJuYmYiOjE0Nzc5MDg1OTksImV4cCI6MTQ3NzkwODY1OSwiZGF0YSI6eyJfaWQiOiIxOSIsImVtYWlsIjoiaS5yYWR3YW4xOTk2QGhvdG1haWwuY29hbSIsIm5hbWUiOiJJYnJhaGltcyIsInRlbCI6IjAwMjAxMDk3Nzk5ODU2IiwidXNlcl90eXBlIjoiMSJ9fQ.HNMDLmoa5tWB8FPSf_EYuR2td7w_4qUdbbbjXrGcCJNnqpv5e9fdb_LmI7X3liflyNDgrxUyIVMACYxaAO-v4A");
