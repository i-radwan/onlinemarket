// ==========================================================================================================
/*	Constants	 */
// ==========================================================================================================

// ==========================================================================================================
/*	Data Models	 */
// ==========================================================================================================

// ==========================================================================================================
/*	View Models	 */
// ==========================================================================================================

var authViewModel;

function authViewModel() {
    self.userModel = getUserModel();
    self.errorMsg = ko.observable("");
    self.loading = ko.observable(false);

    self.signup = function () {
        if (checkIfLoggedInAndRedirect(true))
            return;
        var data = {};
        data[USERS_FLD_EMAIL] = userModel[USERS_FLD_EMAIL]();
        data[USERS_FLD_PASS1] = userModel[USERS_FLD_PASS1]();
        data[USERS_FLD_PASS2] = userModel[USERS_FLD_PASS2]();
        data[USERS_FLD_USER_TYPE] = userModel[USERS_FLD_USER_TYPE]();
        data[USERS_FLD_NAME] = userModel[USERS_FLD_NAME]();
        data[USERS_FLD_TEL] = userModel[USERS_FLD_TEL]();
        data[USERS_FLD_STATUS] = USER_ACTIVE;
        if (userModel[USERS_FLD_USER_TYPE]() == USER_BUYER) {
            data[BUYERS_FLD_ADDRESS] = userModel[BUYERS_FLD_ADDRESS]();
            data[BUYERS_FLD_CCNUMBER] = userModel[BUYERS_FLD_CCNUMBER]();
            data[BUYERS_FLD_CC_CCV] = userModel[BUYERS_FLD_CC_CCV]();
            data[BUYERS_FLD_CC_MONTH] = userModel[BUYERS_FLD_CC_MONTH]();
            data[BUYERS_FLD_CC_YEAR] = userModel[BUYERS_FLD_CC_YEAR]();
        } else if (userModel[USERS_FLD_USER_TYPE]() == USER_SELLER) {
            data[SELLERS_FLD_ADDRESS] = userModel[SELLERS_FLD_ADDRESS]();
            data[SELLERS_FLD_BANK_ACCOUNT] = userModel[SELLERS_FLD_BANK_ACCOUNT]();
        }
        self.loading(true);
        $.post(API_LINK + SIGNUP_ENDPOINT, data, function (returnedData) {
            self.storeLocalStorageData(returnedData);
        });
    }

    self.login = function () {
        if (checkIfLoggedInAndRedirect(true))
            return;
        var data = {};
        data[USERS_FLD_EMAIL] = userModel[USERS_FLD_EMAIL]();
        data[USERS_FLD_PASS] = userModel[USERS_FLD_PASS]();
        self.loading(true);
        $.post(API_LINK + LOGIN_ENDPOINT, data, function (returnedData) {
            self.storeLocalStorageData(returnedData);
        });
    }
    self.storeLocalStorageData = function (returnedData) {
		console.log(returnedData);
        returnedData = JSON.parse(returnedData);
        if (returnedData[AUTH_RESPONSE_STATUS_CODE] == LOGIN_SUCCESSFUL_LOGIN) {
            localStorage.setItem(OMARKET_JWT, returnedData[AUTH_RESPONSE_JWT]);
            localStorage.setItem(OMARKET_PREFIX + USERS_FLD_NAME, returnedData[AUTH_RESPONSE_RESULT][USERS_FLD_NAME]);
            localStorage.setItem(OMARKET_PREFIX + USERS_FLD_EMAIL, returnedData[AUTH_RESPONSE_RESULT][USERS_FLD_EMAIL]);
            localStorage.setItem(OMARKET_PREFIX + USERS_FLD_TEL, returnedData[AUTH_RESPONSE_RESULT][USERS_FLD_TEL]);
            localStorage.setItem(OMARKET_PREFIX + USERS_FLD_USER_TYPE, returnedData[AUTH_RESPONSE_RESULT][USERS_FLD_USER_TYPE]);
            localStorage.setItem(OMARKET_PREFIX + USERS_FLD_STATUS, returnedData[AUTH_RESPONSE_RESULT][USERS_FLD_STATUS]);
            if (returnedData[AUTH_RESPONSE_RESULT][USERS_FLD_USER_TYPE] == USER_BUYER) {
                localStorage.setItem(OMARKET_PREFIX + BUYERS_FLD_ADDRESS, returnedData[AUTH_RESPONSE_RESULT][BUYERS_FLD_ADDRESS]);
                localStorage.setItem(OMARKET_PREFIX + BUYERS_FLD_CCNUMBER, returnedData[AUTH_RESPONSE_RESULT][AUTH_RESPONSE_CC_NUMBER]);
                localStorage.setItem(OMARKET_PREFIX + BUYERS_FLD_CC_CCV, returnedData[AUTH_RESPONSE_RESULT][AUTH_RESPONSE_CC_CCV]);
                localStorage.setItem(OMARKET_PREFIX + BUYERS_FLD_CC_YEAR, returnedData[AUTH_RESPONSE_RESULT][AUTH_RESPONSE_CC_YEAR]);
                localStorage.setItem(OMARKET_PREFIX + BUYERS_FLD_CC_MONTH, returnedData[AUTH_RESPONSE_RESULT][AUTH_RESPONSE_CC_MONTH]);
            } else if (returnedData[AUTH_RESPONSE_RESULT][USERS_FLD_USER_TYPE] == USER_SELLER) {
                localStorage.setItem(OMARKET_PREFIX + SELLERS_FLD_ADDRESS, returnedData[AUTH_RESPONSE_RESULT][SELLERS_FLD_ADDRESS]);
                localStorage.setItem(OMARKET_PREFIX + SELLERS_FLD_BANK_ACCOUNT, returnedData[AUTH_RESPONSE_RESULT][SELLERS_FLD_BANK_ACCOUNT]);
            }
            self.errorMsg("Welcome " + returnedData[AUTH_RESPONSE_RESULT][USERS_FLD_NAME] + "!");

            window.setTimeout(function () {
                checkIfLoggedInAndRedirect(true);
            }, 1000);
        } else {
            self.loading(false);
            self.errorMsg(returnedData[AUTH_RESPONSE_ERROR_MSG]);
        }
    }


}
// ==========================================================================================================
/*	App  Logic	 */
// ==========================================================================================================
// Check if user already signed in
$(function () {
    checkIfLoggedInAndRedirect(true);
    $(".main-data-container").hide();
    window.setTimeout(function () {
        $(".auth-container").addClass("auth-container-expanded");
        $(".auth-container--contentarea").addClass("auth-container--contentarea-expanded");
        var h = $(".main-data-container").height();
        $(".main-data-container").height(0);
        $(".main-data-container").animate({
            height: h
        }, 400, function () {
            $(".main-data-container").css('height', 'auto');
        });
        $(".main-data-container").show();

    }, 300);

});

// Animation handler
ko.bindingHandlers.fadeVisible = {
    init: function (element, valueAccessor) {
        var value = !valueAccessor();
        $(element).toggle(ko.unwrap(value));
    },
    update: function (element, valueAccessor) {
        var value = !valueAccessor();
        ko.unwrap(value) ? $(element).hide() : $(element).fadeIn();
    }
};

authViewModel = new authViewModel();
ko.applyBindings(authViewModel);

/**
 * This function checks if the user is already signed in and if so it redirects the user to the proper page
 * @param {boolean} redirect if true, if the user is already signed in-> the page will be redirected to the proper page
 */
function checkIfLoggedInAndRedirect(redirect) {
    if (localStorage.getItem(OMARKET_JWT)) {
        if (redirect)
            if (localStorage.getItem(OMARKET_PREFIX + USERS_FLD_USER_TYPE) == USER_BUYER)
                window.location = WEBSITE_LINK;
            else
                window.location = ADMIN_LINK;

        return true;
    }
    return false;
}
// ==========================================================================================================
/*	API	Requests */
// ==========================================================================================================

/**
 * This function returns the user model
 * @returns {object} user model
 */
function getUserModel() {
    var user = {};
    user[USERS_FLD_NAME] = ko.observable();
    user[USERS_FLD_EMAIL] = ko.observable();
    user[BUYERS_FLD_ADDRESS] = ko.observable();
    user[BUYERS_FLD_CCNUMBER] = ko.observable();
    user[BUYERS_FLD_CC_CCV] = ko.observable();
    user[BUYERS_FLD_CC_MONTH] = ko.observable();
    user[BUYERS_FLD_CC_YEAR] = ko.observable();
    user[USERS_FLD_PASS] = ko.observable();
    user[USERS_FLD_PASS1] = ko.observable();
    user[USERS_FLD_PASS2] = ko.observable();
    user[SELLERS_FLD_BANK_ACCOUNT] = ko.observable();
    user[USERS_FLD_TEL] = ko.observable();
    user[USERS_FLD_STATUS] = ko.observable();

    // Observables to control forms
    user[USERS_FLD_USER_TYPE] = ko.observable(USER_BUYER);
    return user;
}
