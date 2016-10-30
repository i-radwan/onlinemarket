// ==========================================================================================================
/*	Constants	 */
// ==========================================================================================================

const TOP_CATEGOIRES_COUNT = 4; // The number of top categories to be shown in the main page

// ==========================================================================================================
/*	Data Models	 */
// ==========================================================================================================

// ==========================================================================================================
/*	View Models	 */
// ==========================================================================================================

var signUpViewModel;

function signUpViewModel() {
	self.userModel = getUserModel();
	self.init = function(){
		
	}();
}
// ==========================================================================================================
/*	App  Logic	 */
// ==========================================================================================================
var shouter = new ko.subscribable();


signUpViewModel = new signUpViewModel();
ko.applyBindings(signUpViewModel);

// ==========================================================================================================
/*	API	Requests */
// ==========================================================================================================

/**
 * This function returns the user model
 * @returns {object} user model
 */
function getUserModel() {
	var user = {};
	user.name = ko.observable("Ibrahim");
	user.email = ko.observable("i.radwan1996@gmail.com");
	user.address = ko.observable("Permanent Address Thomas Nolan Kaszas II 5322 Otter Lane Middleberge FL 32068");
	user.ccnumber = ko.observable("512xxxxx241");
	user.ccccv = ko.observable("xxx");
	user.ccmonth = ko.observable(10);
	user.ccyear = ko.observable(2016);
	user.pass1 = ko.observable("••••••••••");
	user.pass2 = ko.observable("••••••••••");
	user.bankAccount = ko.observable("Bank account");
	user.tel = ko.observable("002010997799856");

	// Observables to control forms
	user.type = ko.observable("buyer");
	return user;
}
