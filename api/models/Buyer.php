<?php

require_once 'User.php';

/**
 * This class models the system buyer - inherts from User class
 *
 * @author ibrahimradwan
 */
class Buyer extends User implements \JsonSerializable {

    private $address, $ccNumber, $ccCCV, $ccmonth, $ccYear;

    function __construct($_id, $name, $email, $tel, $user_type, $address, $ccNumber, $ccCCV, $ccmonth, $ccYear) {
        parent::__construct($_id, $name, $email, $tel, $user_type);

        $this->address = $address;
        $this->ccNumber = $ccNumber;
        $this->ccCCV = $ccCCV;
        $this->ccmonth = $ccmonth;
        $this->ccYear = $ccYear;
    }

    function getAddress() {
        return $this->address;
    }

    function getCcNumber() {
        return $this->ccNumber;
    }

    function getCcCCV() {
        return $this->ccCCV;
    }

    function getCcmonth() {
        return $this->ccmonth;
    }

    function getCcYear() {
        return $this->ccYear;
    }

    function setAddress($address) {
        $this->address = $address;
    }

    function setCcNumber($ccNumber) {
        $this->ccNumber = $ccNumber;
    }

    function setCcCCV($ccCCV) {
        $this->ccCCV = $ccCCV;
    }

    function setCcmonth($ccmonth) {
        $this->ccmonth = $ccmonth;
    }

    function setCcYear($ccYear) {
        $this->ccYear = $ccYear;
    }

    public function jsonSerialize() {
        $vars = get_object_vars($this);
        $parentVars = parent::jsonSerialize();
        $vars = array_merge($parentVars, $vars);
        return $vars;
    }

}
