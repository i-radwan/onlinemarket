<?php

/**
 * This class will be containing the result JSON object
 *
 * @author ibrahimradwan
 */
class Response implements \JsonSerializable {

    private $statusCode, $result, $jwt;

    function __construct($statusCode, $result, $jwt) {
        $this->statusCode = $statusCode;
        $this->result = $result;
        $this->jwt = $jwt;
    }

    function getJwt() {
        return $this->jwt;
    }

    function setJwt($jwt) {
        $this->jwt = $jwt;
    }

    function getStatusCode() {
        return $this->statusCode;
    }

    function getResult() {
        return $this->result;
    }

    function setStatusCode($statusCode) {
        $this->statusCode = $statusCode;
    }

    function setResult($result) {
        $this->result = $result;
    }

    public function jsonSerialize() {
        $vars = get_object_vars($this);

        return $vars;
    }

}
