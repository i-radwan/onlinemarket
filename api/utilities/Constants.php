<?php

/**
 * This file contains all website constants
 *
 * @author ibrahimradwan
 */
class Constants {

    /**
     * Sign up operation (1x) constants
     */
    
    const SIGNUP_SUCCESSFUL_SIGNUP = 10;
    const SIGNUP_INVALID_EMAIL = 11;
    const SIGNUP_PASSWORDS_MISMATCH = 12;
    const SIGNUP_INVALID_ROLE = 13;
    const SIGNUP_EMAIL_EXISTS = 14;
    const SIGNUP_OPERATION_FAILED = 15;

    /**
     * Login operation (2x) constants
     */
    const LOGIN_SUCCESSFUL_LOGIN = 20;
    const LOGIN_INVALID_EMAIL = 21;
    const LOGIN_EMPTY_DATA = 22;
    const LOGIN_OPERATION_FAILED = 23;
    const LOGIN_INCORRECT_DATA = 24;

    /**
     * Database Constants
     */
    const TBL_USERS = 'users';
    const USERS_FLD_ID = '_id';
    const USERS_FLD_EMAIL = 'email';
    const USERS_FLD_PASS = 'pass';
    const USERS_FLD_USER_TYPE = 'user_type';
    const USERS_FLD_NAME = 'name';
    const USERS_FLD_TEL = 'tel';
    
    const TBL_BUYERS = 'buyers';
    const BUYERS_FLD_USER_ID = 'user_id';
    const BUYERS_FLD_ADDRESS = 'address';
    const BUYERS_FLD_CCNUMBER = 'creditcard';
    const BUYERS_FLD_CC_CCV = 'cc_ccv';
    const BUYERS_FLD_CC_MONTH = 'cc_month';
    const BUYERS_FLD_CC_YEAR = 'cc_year';
    
    const TBL_SELLERS = 'sellers';
    const SELLERS_FLD_USER_ID = 'user_id';
    const SELLERS_FLD_ADDRESS = 'address';
    const SELLERS_FLD_BACK_ACCOUNT = 'bankaccount';

    /**
     * User types
     */
    const USER_BUYER = "1";
    const USER_SELLER = "2";
    const USER_ACCOUNTANT = "3";
    const USER_ADMIN = "4";
    const USER_DELIVERMAN = "5";

}
