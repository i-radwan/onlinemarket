<?php

require_once 'User.php';

/**
 * This class models the system seller - inherts from User class
 *
 * @author ibrahimradwan
 */
class Seller extends User implements \JsonSerializable {

    private $address, $bankAccount;

    function __construct($_id, $name, $email, $tel, $user_type, $user_status, $address, $bankAccount) {
        parent::__construct($_id, $name, $email, $tel, $user_type, $user_status);

        $this->address = $address;
        $this->bankAccount = $bankAccount;
    }

    function getAddress() {
        return $this->address;
    }

    function getBankAccount() {
        return $this->bankAccount;
    }

    function setAddress($address) {
        $this->address = $address;
    }

    function setBankAccount($bankAccount) {
        $this->bankAccount = $bankAccount;
    }

    public function jsonSerialize() {
        $vars = get_object_vars($this);
        $parentVars = parent::jsonSerialize();
        $vars = array_merge($parentVars, $vars);
        return $vars;
    }

}
