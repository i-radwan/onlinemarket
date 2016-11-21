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
    const SIGNUP_EMPTY_DATA = 16;
    const SIGNUP_INVALID_CCNUMBER = 17;
    const SIGNUP_INVALID_CCCCV = 18;
    const SIGNUP_INVALID_CCDATE = 19;

    /**
     * Login operation (2x) constants
     */
    const LOGIN_SUCCESSFUL_LOGIN = 20;
    const LOGIN_INVALID_EMAIL = 21;
    const LOGIN_EMPTY_DATA = 22;
    const LOGIN_OPERATION_FAILED = 23;
    const LOGIN_INCORRECT_DATA = 24;

    /**
     * Order operation functions (4x) constants
     */
    const ORDERS_GET_SUCCESSFUL = 40;
    const ORDERS_GET_FAILED = 41;
    const ORDERS_DELETE_SUCCESS = 42;
    const ORDERS_DELETE_FAILED = 43;
    const ORDERS_ADD_SUCCESS = 44;
    const ORDERS_ADD_FAILED = 45;
    const ORDERS_UPDATE_SUCCESS = 46;
    const ORDERS_UPDATE_FAILED = 47;

    /**
     * OrderItems operation functions (5x) constants
     */
    const ORDERITEMS_GET_SUCCESSFUL = 50;
    const ORDERITEMS_GET_FAILED = 51;

    /**
     * DeliveryRequests operation functions (6x) constants
     */
    const DELIVERYREQUESTS_GET_SUCCESSFUL = 60;
    const DELIVERYREQUESTS_GET_FAILED = 61;

    /**
     * User operations constants
     */
    const USER_EDIT_ACCOUNT_SUCCESSFUL = 800;
    const USER_EDIT_ACCOUNT_EMPTY_DATA = 801;
    const USER_EDIT_ACCOUNT_FAILED = 802;
    const USER_EDIT_ACCOUNT_INVALID_PASS = 803;
    const USER_EDIT_ACCOUNT_INVALID_EMAIL = 804;
    const USER_EDIT_ACCOUNT_EMAIL_EXISTS = 805;
    const USER_EDIT_ACCOUNT_INVALID_ACCOUNT = 806;
    const USER_GET_USERS_FAILED = 811;
    const USER_GET_USERS_SUCCESSFUL = 810;
    const USER_UPDATE_STATUS_INVALID_DATA = 821;
    const USER_UPDATE_STATUS_SUCCESSFUL = 820;
    const USER_UPDATE_STATUS_FAILED = 823;
    const USER_DELETE_FAILED = 831;
    const USER_DELETE_SUCCESSFUL = 830;
    const USER_INSERT_FAILED = 841;
    const USER_INSERT_SUCCESSFUL = 840;
    const USER_INSERT_INVALID_DATA = 842;
    const USER_INSERT_INVALID_EMAIL = 843;
    const USER_INSERT_EMPTY_DATA = 844;
    const USER_INSERT_EMAIL_EXISTS = 845;

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
    const USERS_FLD_STATUS = 'user_status';
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
    const TBL_ADMINS = 'admins';
    const ADMINS_FLD_USER_ID = 'user_id';
    const TBL_ACCOUNTANTS = 'accountants';
    const ACCOUNTANTS_FLD_USER_ID = 'user_id';
    const TBL_DELIVERYMEN = 'deliverymen';
    const DELIVERYMEN_FLD_USER_ID = 'user_id';
    //==================================
    //THE ORDER TABLE CONSTANTS
    const TBL_ORDERS = 'orders';
    const ORDERS_ID = '_id';
    const ORDERS_DATE = 'date';
    const ORDERS_COST = 'cost';
    const ORDERS_BUYER_ID = 'buyer_id';
    const ORDERS_STATUS_ID = 'status_id';
    //==================================
    //THE ORDER_ITEMS TABLE CONSTANTS
    const TBL_ORDERITEMS = 'orders';
    const ORDERITEMS_ORDERID = 'order_id';
    const ORDERITEMS_PRODUCTID = 'product_id';
    const ORDERITEMS_SELLERID = 'seller_id';
    const ORDERITEMS_QUANTITY = 'quantity';
    //==================================
    //THE DELIVERY REQUESTS TABLE CONSTANTS
    const TBL_DELIVERYREQUESTS = 'deliveryrequests';
    const DELIVERYREQUESTS_ID = '_id';
    const DELIVERYREQUESTS_ORDERID = 'order_id';
    const DELIVERYREQUESTS_DELIVERYMANID = 'deliveryman_id';
    const DELIVERYREQUESTS_DUEDATE = 'duedate';
    //==================================
    /**
     * User types (related to db)
     * Some checks in the code (signup function) depends on these values
     */
    const USER_TYPES = array("1", "2", "3", "4", "5");
    const USER_BUYER = "1";
    const USER_SELLER = "2";
    const USER_ACCOUNTANT = "3";
    const USER_ADMIN = "4";
    const USER_DELIVERMAN = "5";

    /**
     * User status
     */
    const USER_STATUSES = array("1", "2");
    const USER_ACTIVE = "1";
    const USER_BANNED = "2";

}
