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
		var data = {
			email: userModel.email(),
			pass1: userModel.pass1(),
			pass2: userModel.pass2(),
			user_type: userModel.type(),
			name: userModel.name(),
			tel: userModel.tel()

		};
		if (userModel.type() == "1") {
			data.address = userModel.address();
			data.creditcard = userModel.ccnumber();
			data.cc_ccv = userModel.ccccv();
			data.cc_month = userModel.ccmonth();
			data.cc_year = userModel.ccyear();
		} else if (userModel.type() == "2") {
			data.address = userModel.address();
			data.bankaccount = userModel.bankAccount();
		}
		self.loading(true);
		$.post("http://localhost/onlinemarket/api/api.php/signup", data, function (returnedData) {
			self.storeLocalStorageData(returnedData);
		});
	}

	self.login = function () {
		var data = {
			email: userModel.email(),
			pass: userModel.pass1()
		};
		self.loading(true);
		$.post("http://localhost/onlinemarket/api/api.php/login", data, function (returnedData) {
			self.storeLocalStorageData(returnedData);
		});
	}
	self.storeLocalStorageData = function (returnedData) {
		returnedData = JSON.parse(returnedData);
		if (returnedData.statusCode == 20) {
			localStorage.setItem("OMarket_JWT", returnedData.jwt);
			localStorage.setItem("OMarket_name", returnedData.result.name);
			localStorage.setItem("OMarket_email", returnedData.result.email);
			localStorage.setItem("OMarket_tel", returnedData.result.tel);
			localStorage.setItem("OMarket_user_type", returnedData.result.user_type);
			if (returnedData.result.user_type == "1") {
				localStorage.setItem("OMarket_address", returnedData.result.address);
				localStorage.setItem("OMarket_ccNumber", returnedData.result.ccNumber);
				localStorage.setItem("OMarket_ccCCV", returnedData.result.ccCCV);
				localStorage.setItem("OMarket_ccYear", returnedData.result.ccYear);
				localStorage.setItem("OMarket_ccMonth", returnedData.result.ccMonth);
			} else if (returnedData.result.user_type == "2") {
				localStorage.setItem("OMarket_address", returnedData.result.address);
				localStorage.setItem("OMarket_bankaccount", returnedData.result.bankaccount);
			}
			self.errorMsg("Welcome " + returnedData.result.name + "!");
			window.setTimeout(function () {
				window.location = "http://localhost/onlinemarket";
			}, 1000);
		} else {
			self.loading(false);
			self.errorMsg(returnedData.errorMsg);
		}
	}
}
// ==========================================================================================================
/*	App  Logic	 */
// ==========================================================================================================
// Check if user already signed in
$(function () {
	if (localStorage.getItem("OMarket_JWT")) {
//		window.location = "http://localhost/onlinemarket"; ToDo: to be enabled
	}
});


authViewModel = new authViewModel();
ko.applyBindings(authViewModel);

// ==========================================================================================================
/*	API	Requests */
// ==========================================================================================================

/**
 * This function returns the user model
 * @returns {object} user model
 */
function getUserModel() {
	var user = {};
	user.name = ko.observable();
	user.email = ko.observable();
	user.address = ko.observable();
	user.ccnumber = ko.observable();
	user.ccccv = ko.observable();
	user.ccmonth = ko.observable();
	user.ccyear = ko.observable();
	user.pass1 = ko.observable();
	user.pass2 = ko.observable();
	user.bankAccount = ko.observable();
	user.tel = ko.observable();

	// Observables to control forms
	user.type = ko.observable("1");
	return user;
}
