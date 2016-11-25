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
const USER_CART_ENDPOINT = "cart";
const ORDERS_ENDPOINT = "orders";
const ORDER_ENDPOINT = "order";
const CATEGORY_ENDPOINT = "category";
const DELIVERYREQUESTS_ENDPOINT = "deliveryrequests";

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


const AUTH_RESPONSE_STATUS_CODE = "statusCode";
const AUTH_RESPONSE_JWT = "jwt";
const AUTH_RESPONSE_RESULT = "result";
const AUTH_RESPONSE_CC_NUMBER = "ccNumber";
const AUTH_RESPONSE_CC_MONTH = "ccMonth";
const AUTH_RESPONSE_CC_YEAR = "ccYear";
const AUTH_RESPONSE_CC_CCV = "ccCCV";
const AUTH_RESPONSE_ERROR_MSG = "errorMsg";


const ORDERS_ID = 'id';
const ORDERS_DATE = 'date';
const ORDERS_COST = 'cost';
const ORDERS_BUYER_ID = 'buyer_id';
const ORDERS_STATUS_ID = 'statusId';

const ORDER_STATUS_PENDING = "1";
const ORDER_STATUS_PICKED = "2";
const ORDER_STATUS_SHIPPED = "3";
const ORDER_STATUS_DELIVERED = "4";

//THE CART ITEMS TABLE CONSTANTS
const TBL_CART_ITEMS = 'cart_items';
const CART_ITEMS_USER_ID = 'user_id';
const CART_ITEMS_PRODUCT_ID = 'product_id';
const CART_ITEMS_QUANTITY = 'quantity';

// ORDERS FILTERS CONSTANTS
const ORDER_FILTER_COST = 'cost';
const ORDER_FILTER_DATE = 'date';
const ORDER_FILTER_STATUS = 'status';
const ORDER_FILTER_MIN = 'min';
const ORDER_FILTER_MAX = 'max';
const ORDER_FILTER_PENDING = 'pending';
const ORDER_FILTER_PICKED = 'picked';
const ORDER_FILTER_SHPPED = 'shipped';
const ORDER_FILTER_DELIVERED = 'delivered';

const DELIVERYREQUESTS_ID = '_id';
const DELIVERYREQUESTS_ORDERID = 'order_id';
const DELIVERYREQUESTS_DELIVERYMANID = 'deliveryman_id';
const DELIVERYREQUESTS_DUEDATE = 'duedate';
const DELIVERYREQUESTS_STATUS_ID = 'status_id';
const DELIVERYREQUESTS_ORDER_ID = '_id';

const CATEGORIES_FLD_USER_ID = '_id';
const CATEGORIES_FLD_NAME = 'name';
//THE PRODUCT TABLE CONSTANTS (check with abdo)
const PRODUCT_FLD_ID = '_id';
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
 * DeliveryRequests operation functions (6x) constants
 */
const DELIVERYREQUESTS_GET_SUCCESSFUL = 60;
const DELIVERYREQUESTS_GET_FAILED = 61;

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
