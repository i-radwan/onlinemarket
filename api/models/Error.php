<?php

/**
 * This class models any unexpected errors
 *
 * @author ibrahimradwan
 */
class Error implements \JsonSerializable {
    private $statusCode, $errorMsg;
    
    function __construct($statusCode, $errorMsg) {
        $this->statusCode = $statusCode;
        $this->errorMsg = $errorMsg;
    }

    function getStatusCode() {
        return $this->statusCode;
    }

    function getErrorMsg() {
        return $this->errorMsg;
    }

    function setStatusCode($statusCode) {
        $this->statusCode = $statusCode;
    }

    function setErrorMsg($errorMsg) {
        $this->errorMsg = $errorMsg;
    }

    public function jsonSerialize() {
        $vars = get_object_vars($this);

        return $vars;
    }

}
