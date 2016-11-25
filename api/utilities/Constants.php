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
     * Category operation functions (3x) constants
     */
    const CATEGORY_INSERT_FAILED = 30;
    const CATEGORY_NAME_REPETION = 31;
    const CATEGORY_ADD_SUCCESS = 32;
    const CATEGORY_DELETE_SUCCESS = 33;
    const CATEGORY_EMPTY_DATA = 34;
    const CATEGORY_DELETE_FAILED = 35;
    const CATEGORY_INVALID_ID = 36;
    const CATEGORY_SELECT_FAILED = 37;
    const CATEGORY_UPDATE_FAILED = 38;
    const CATEGORY_SELECT_SUCCESS = 39;
    const CATEGORY_UPDATE_SUCCESS = 301;
    const CATEGORY_DELETE_FAILED_FOREIGNKEY = 302;

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
     * Rates operation functions (7x) constants
     */
    const RATE_INSERT_FAILED = 70;
    const RATE_INSERT_SUCCESS = 71;
    const RATE_AVGERAGE_FAILED = 72;
    const RATE_AVERAGE_SUCCESS = 73;
    const RATE_AVERAGE_INVALID_PRODUCT = 74;
    const RATE_AVERAGE_EMPTY_DATA = 75;
    const RATE_GET_INVALID_PRODUCT = 76;
    const RATE_GET_EMPTY_DATA = 77;
    const RATE_GET_INVALID_BUYER = 78;
    const RATE_GET_SUCCESS = 79;
    const RATE_GET_FAILED = 700;
    const RATE_UPDATE_SUCCESS = 701;

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
     * Cart operations constants
     */
    const CART_ADD_ITEM_SUCCESSFUL = 901;
    const CART_ADD_ITEM_FAILED = 902;
    const CART_ADD_ITEM_EMPTY_DATA = 903;
    const CART_ADD_ITEM_NOT_AVAILABLE = 904;
    const CART_ADD_ITEM_USER_BANNED = 905;
    const CART_ADD_ITEM_LIMIT = 906;
    const CART_DELETE_ITEM_SUCCESSFUL = 910;
    const CART_DELETE_ITEM_FAILED = 911;
    const CART_DELETE_ITEM_EMPTY_DATA = 912;
    const CART_DECREASE_ITEM_SUCCESSFUL = 920;
    const CART_DECREASE_ITEM_FAILED = 921;
    const CART_DECREASE_ITEM_EMPTY_DATA = 922;

    /**
     * Database Constants
     */
    //==================================
    //THE USERS TABLE CONSTANTS
    const TBL_USERS = 'users';
    const USERS_FLD_ID = '_id';
    const USERS_FLD_EMAIL = 'email';
    const USERS_FLD_PASS = 'pass';
    const USERS_FLD_USER_TYPE = 'user_type';
    const USERS_FLD_NAME = 'name';
    const USERS_FLD_TEL = 'tel';
    const USERS_FLD_STATUS = 'user_status';
    //==================================
    //THE BUYERS TABLE CONSTANTS
    const TBL_BUYERS = 'buyers';
    const BUYERS_FLD_USER_ID = 'user_id';
    const BUYERS_FLD_ADDRESS = 'address';
    const BUYERS_FLD_CCNUMBER = 'creditcard';
    const BUYERS_FLD_CC_CCV = 'cc_ccv';
    const BUYERS_FLD_CC_MONTH = 'cc_month';
    const BUYERS_FLD_CC_YEAR = 'cc_year';
    //==================================
    //THE SELLERS TABLE CONSTANTS
    const TBL_SELLERS = 'sellers';
    const SELLERS_FLD_USER_ID = 'user_id';
    const SELLERS_FLD_ADDRESS = 'address';
    const SELLERS_FLD_BACK_ACCOUNT = 'bankaccount';
    //==================================
    //THE ADMINS TABLE CONSTANTS
    const TBL_ADMINS = 'admins';
    const ADMINS_FLD_USER_ID = 'user_id';
    //==================================
    //THE ACCOUNTATNS TABLE CONSTANTS
    const TBL_ACCOUNTANTS = 'accountants';
    const ACCOUNTANTS_FLD_USER_ID = 'user_id';
    //==================================
    //THE DELIVERYMEN TABLE CONSTANTS
    const TBL_DELIVERYMEN = 'deliverymen';
    const DELIVERYMEN_FLD_USER_ID = 'user_id';
    //==================================
    //THE CART ITEMS TABLE CONSTANTS
    const TBL_CART_ITEMS = 'cart_items';
    const CART_ITEMS_USER_ID = 'user_id';
    const CART_ITEMS_PRODUCT_ID = 'product_id';
    const CART_ITEMS_QUANTITY = 'quantity';
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
    //THE PRODUCTS (Check with abdo)
    const TBL_PRODUCTS = "products";
    const PRODUCTS_FLD_ID = "_id"; //const PRODUCTS_FLD_USER_ID = '_id';
    const PRODUCTS_FLD_AVA_QUANTITY = "available_quantity";
    const PRODUCTS_FLD_AVA_STATUS = "availability_id";
    const PRODUCT_AVAILABLE = "1"; // Relative to DB
    const PRODUCT_INAVAILABLE = "2"; // Relative to DB'
    const PRODUCTS_FLD_CATEGORY_ID = 'category_id';
    const PRODUCTS_FLD_RATE = 'rate';
    //==================================
    //THE CATEGORIES TABLE CONSTANTS
    const TBL_CATEGORIES = 'categories';
    const CATEGORIES_FLD_NAME = 'name';
    const CATEGORIES_FLD_USER_ID = '_id';
    //==================================
    //THE RATE TABLE CONSTANTS
    const TBL_RATE = 'rates';
    const RATE_FLD_PRODUCT_ID = 'product_id';
    const RATE_FLD_RATE = 'rate';
    //==================================
    //THE PRODUCT SPEC TABLE CONSTANTS
    const TBL_PRODUCT_SPEC = 'product_spec';
    const PRODUCT_SPEC_FLD_PRODUCT_ID = 'product_id';
    const PRODUCT_SPEC_FLD_CAT_ID = 'categories_spec_id';
    const PRODUCT_SPEC_FLD_VALUE = 'value';
    //==================================
    //THE CATEGORIES SPEC TABLE CONSTANTS
    const TBL_CATEGORIES_SPEC = 'categories_spec';
    const CATEGORIES_SPEC_FLD_NAME = 'name';
    const CATEGORIES_SPEC_FLD_CATID = 'category_id';
    const CATEGORIES_SPEC_FLD_ID = '_id';
    //Status CONSTANTS
    const PENDING = 1;
    const PICKED = 2;
    const SHIPPED = 3;
    const DELIVERED = 4;

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

    /**
     * Order status
     */
    const ORDER_STATUSES = array("1", "2", "3", "4");
    const ORDER_PENDING = "1";
    const ORDER_PICKED = "2";
    const ORDER_SHIPPED= "3";
    const ORDER_DELIVERED = "4";
}
