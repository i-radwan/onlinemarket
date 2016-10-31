<?php

require_once 'User.php';

/**
 * This class models the system admin - inherts from User class
 *
 * @author ibrahimradwan
 */
class Admin extends User implements \JsonSerializable {

    function __construct($_id, $name, $email, $tel, $user_type) {
        parent::__construct($_id, $name, $email, $tel, $user_type);
    }

    public function jsonSerialize() {
        $vars = get_object_vars($this);
        $parentVars = parent::jsonSerialize();
        $vars = array_merge($parentVars, $vars);
        return $vars;
    }

}
