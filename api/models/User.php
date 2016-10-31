<?php

/**
 * This class models the system user and gonna be inherited by other sub classes
 *
 * @author ibrahimradwan
 */
class User implements \JsonSerializable {

    private $_id, $name, $email, $tel, $user_type;

    function __construct($_id, $name, $email, $tel, $user_type) {
        $this->_id = $_id;
        $this->name = $name;
        $this->email = $email;
        $this->tel = $tel;
        $this->user_type = $user_type;
    }

    function get_id() {
        return $this->_id;
    }

    function getName() {
        return $this->name;
    }

    function getEmail() {
        return $this->email;
    }

    function getTel() {
        return $this->tel;
    }

    function getUser_type() {
        return $this->user_type;
    }

    function set_id($_id) {
        $this->_id = $_id;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setEmail($email) {
        $this->email = $email;
    }

    function setTel($tel) {
        $this->tel = $tel;
    }

    function setUser_type($user_type) {
        $this->user_type = $user_type;
    }

    public function jsonSerialize() {
        $vars = get_object_vars($this);

        return $vars;
    }

}
