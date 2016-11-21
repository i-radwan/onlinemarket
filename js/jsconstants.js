/**
 *		CONSTANTS
 */

const API_LINK = "http://localhost/onlinemarket/api/api.php/";
const WEBSITE_LINK = "http://localhost/onlinemarket/";
const ADMIN_LINK = "http://localhost/onlinemarket/admin";
const LOGIN_ENDPOINT = "login";
const SIGNUP_ENDPOINT = "signup";
const USER_ENDPOINT = "user";
const CHANGE_USER_STATUS_ENDPOINT = "block";
const USER_EDIT_ENDPOINT = "edit";

const OMARKET_PREFIX = "OMarket_";

const OMARKET_JWT = "OMarket_JWT";

const USERS_FLD_ID = '_id';
const USERS_FLD_EMAIL = 'email';
const USERS_FLD_TMP_EMAIL = 'tmpEmail';
const USERS_FLD_PASS = 'pass';
const USERS_FLD_PASS1 = 'pass1';
const USERS_FLD_PASS2 = 'pass2';
const USERS_FLD_USER_TYPE = 'user_type';
const USERS_FLD_NAME = 'name';
const USERS_FLD_TEL = 'tel';
const USERS_FLD_STATUS = 'user_status';

const BUYERS_FLD_USER_ID = 'user_id';
const BUYERS_FLD_ADDRESS = 'address';
const BUYERS_FLD_CCNUMBER = 'creditcard';
const BUYERS_FLD_CC_CCV = 'cc_ccv';
const BUYERS_FLD_CC_MONTH = 'cc_month';
const BUYERS_FLD_CC_YEAR = 'cc_year';

const SELLERS_FLD_USER_ID = 'user_id';
const SELLERS_FLD_ADDRESS = 'address';
const SELLERS_FLD_BACK_ACCOUNT = 'bankAccount';
const SELLERS_FLD_BACK_ACCOUNT_SMALLCASE = 'bankaccount';

const USER_BUYER = "1";
const USER_SELLER = "2";
const USER_ACCOUNTANT = "3";
const USER_ADMIN = "4";
const USER_DELIVERYMAN = "5";

const USER_ACTIVE = "1";
const USER_BANNED = "2";

const ORDER_STATUS_PENDING = "1";
const ORDER_STATUS_PICKED = "2";
const ORDER_STATUS_SHIPPED = "3";
const ORDER_STATUS_DELIVERED = "4";

// Constants of the auth process response (fields names)
const AUTH_RESPONSE_STATUS_CODE = "statusCode";
const AUTH_RESPONSE_JWT = "jwt";
const AUTH_RESPONSE_RESULT = "result";
const AUTH_RESPONSE_CC_NUMBER = "ccNumber";
const AUTH_RESPONSE_CC_MONTH = "ccMonth";
const AUTH_RESPONSE_CC_YEAR = "ccYear";
const AUTH_RESPONSE_CC_CCV = "ccCCV";
const AUTH_RESPONSE_ERROR_MSG = "errorMsg";

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
 * User operations constants
 */
const USER_EDIT_ACCOUNT_SUCCESSFUL = 800;
const USER_EDIT_ACCOUNT_EMPTY_DATA = 801;
const USER_EDIT_ACCOUNT_FAILED = 802;
const USER_EDIT_ACCOUNT_INVALID_PASS = 803;
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