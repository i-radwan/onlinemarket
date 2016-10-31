<?php
require_once './SQLOperations.php';

$sqlOperations = new SQLOperations();
//echo $sqlOperations->login("i.radwan1996@gmail.com", "2006");
echo $sqlOperations->signUpUser("i.radwan1996aasa@hotmail.coam", "2006", "2006", Constants::USER_BUYER, "Ibrahims", "00120120123", [Constants::BUYERS_FLD_ADDRESS => "6B, Pyramids Gardens", Constants::BUYERS_FLD_CCNUMBER => "123141212", Constants::BUYERS_FLD_CC_CCV => "123", Constants::BUYERS_FLD_CC_MONTH => "10", Constants::BUYERS_FLD_CC_YEAR => "2018"]);
//$sqlOperations->secure("eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0Nzc5MDg1ODksImp0aSI6IkFsb3I3Umw2V3hod09uTExWWHo2c0wzVmlCYXFjRTdmbjlyWHlCbjY4azkySzNzQWJGYUNGdHlCZ2pNVjRMZmtrejVRdklKMmJIVGNxbEh6aVpyb01RPT0iLCJpc3MiOiJsb2NhbGhvc3QiLCJuYmYiOjE0Nzc5MDg1OTksImV4cCI6MTQ3NzkwODY1OSwiZGF0YSI6eyJfaWQiOiIxOSIsImVtYWlsIjoiaS5yYWR3YW4xOTk2QGhvdG1haWwuY29hbSIsIm5hbWUiOiJJYnJhaGltcyIsInRlbCI6IjAwMjAxMDk3Nzk5ODU2IiwidXNlcl90eXBlIjoiMSJ9fQ.HNMDLmoa5tWB8FPSf_EYuR2td7w_4qUdbbbjXrGcCJNnqpv5e9fdb_LmI7X3liflyNDgrxUyIVMACYxaAO-v4A");
