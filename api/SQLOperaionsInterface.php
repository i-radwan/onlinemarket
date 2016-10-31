<?php

/**
 * This interface contains the functions to be implemented in SQLOperations class
 * @author ibrahimradwan
 */
interface SQLOperationsInterface {

    // =========================================================================================================
    //                                          USERS FUNCTIONS
    // =========================================================================================================

    function signUpUser($email, $pass1, $pass2, $role, $name, $tel, $extraData);

    function login($emial, $pass);
    
}
