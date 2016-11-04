<?php

require_once 'User.php';

/**
 * This class models the system deliveryman - inherts from User class
 *
 * @author ibrahimradwan
 */
class Deliveryman extends User implements \JsonSerializable {

    function __construct($_id, $name, $email, $tel, $user_type, $user_status) {
        parent::__construct($_id, $name, $email, $tel, $user_type, $user_status);
    }

    public function jsonSerialize() {
        $vars = get_object_vars($this);
        $parentVars = parent::jsonSerialize();
        $vars = array_merge($parentVars, $vars);
        return $vars;
    }

}
