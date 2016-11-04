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
// Run application
$app->run();
//echo $sqlOperations->login("i.radwan1996@gmail.com", "2006");
//echo $sqlOperations->signUpUser("i.radwan1996aasa.@hotmail.coam", "2006", "2006", Constants::USER_BUYER, "Ibrahims", "00120120123", [Constants::BUYERS_FLD_ADDRESS => "6B, Pyramids Gardens", Constants::BUYERS_FLD_CCNUMBER => "123141212", Constants::BUYERS_FLD_CC_CCV => "123", Constants::BUYERS_FLD_CC_MONTH => "10", Constants::BUYERS_FLD_CC_YEAR => "2018"]);
//$sqlOperations->secure("eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0Nzc5MDg1ODksImp0aSI6IkFsb3I3Umw2V3hod09uTExWWHo2c0wzVmlCYXFjRTdmbjlyWHlCbjY4azkySzNzQWJGYUNGdHlCZ2pNVjRMZmtrejVRdklKMmJIVGNxbEh6aVpyb01RPT0iLCJpc3MiOiJsb2NhbGhvc3QiLCJuYmYiOjE0Nzc5MDg1OTksImV4cCI6MTQ3NzkwODY1OSwiZGF0YSI6eyJfaWQiOiIxOSIsImVtYWlsIjoiaS5yYWR3YW4xOTk2QGhvdG1haWwuY29hbSIsIm5hbWUiOiJJYnJhaGltcyIsInRlbCI6IjAwMjAxMDk3Nzk5ODU2IiwidXNlcl90eXBlIjoiMSJ9fQ.HNMDLmoa5tWB8FPSf_EYuR2td7w_4qUdbbbjXrGcCJNnqpv5e9fdb_LmI7X3liflyNDgrxUyIVMACYxaAO-v4A");
