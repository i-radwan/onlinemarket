<?php
require_once './SQLOperations.php';

$sqlOperations = new SQLOperations();
//echo $sqlOperations->login("i.radwan1996@gmail.com", "2006");
echo $sqlOperations->signUpUser("i.radwan1996@hotmail.coam", "2006", "2006", Constants::USER_BUYER, "Ibrahims", "00201097799856", [Constants::BUYERS_FLD_ADDRESS => "6B, Pyramids Gardens", Constants::BUYERS_FLD_CCNUMBER => "123141212", Constants::BUYERS_FLD_CC_CCV => "123", Constants::BUYERS_FLD_CC_MONTH => "12", Constants::BUYERS_FLD_CC_YEAR => "2019"]);
//$sqlOperations->secure("eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0Nzc4OTM5MjQsImp0aSI6InJcL2RnNUNJYUxUZHZXZytcL3ZXdkEraHpkRU1WMnNWdnhtNGs4eTA3N1BjMFJwcTdYNGR3MmNuYU4wdFkySkNYclwvTDBMV2RhS0xmdVZhOFdZNURkbXlBPT0iLCJpc3MiOiJsb2NhbGhvc3QiLCJuYmYiOjE0Nzc4OTM5MzQsImV4cCI6MTQ3Nzg5Mzk5NCwiZGF0YSI6eyJfaWQiOiIxIn19.Ik-63WH1GXJE_9uazc2kcdWah4Mi_PSEJtiyA2TUDCnNrIkUdxjxuT5mZyAeB_8qCQb0ux_1yVvpcoQ83sDewA");
