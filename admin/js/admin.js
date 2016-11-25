// ==========================================================================================================
/*	Constants	 */
// ==========================================================================================================

// ==========================================================================================================
/*	Data Models	 */
// ==========================================================================================================
function productSpecModel(productSpec) {
	this.name = ko.observable(productSpec.name);
	this.value = ko.observable(productSpec.value);
}

function productModel(product) {
	var self = this;
	self.id = product.id;
	self.name = ko.observable(product.name);
	self.price = ko.observable(product.price);
	self.rate = product.rate;
	self.image = ko.observable(product.image);
	self.quantity = ko.observable(product.quantity);
	self.earnings = ko.observable(product.earnings);
	self.solditems = ko.observable(product.solditems);
	self.more = ko.observableArray();
	product.more.forEach(function (more) {
		self.more.push(new productSpecModel(more));
	});

	self.tmpName = ko.observable(product.name);
	self.tmpPrice = ko.observable(product.price);
	self.tmpImage = ko.observable(product.image);
	self.tmpQuantity = ko.observable(product.quantity);
	self.tmpMore = ko.observableArray();
	product.more.forEach(function (more) {
		self.tmpMore.push(new productSpecModel(more));
	});

	self.isMoreDivVisible = ko.observable(false);
	self.editMode = ko.observable(false);
}

function categoryModel(category) {
	var self = this;
	self.id = category.id;
	self.name = ko.observable(category.name);
	self.tmpName = ko.observable(category.name);
	self.editMode = ko.observable(false);
}

function categorySpecModel(categoySpec) {
	this.id = ko.observable(categoySpec.id);
	this.name = ko.observable(categoySpec.name);
	this.value = ko.observable("");
}

function employeeModel(employee) {
	this[USERS_FLD_ID] = ko.observable(employee[USERS_FLD_ID]);
	this[USERS_FLD_EMAIL] = ko.observable(employee[USERS_FLD_EMAIL]);
	this[USERS_FLD_TMP_EMAIL] = ko.observable(employee[USERS_FLD_EMAIL]);
	this[USERS_FLD_PASS] = ko.observable("");
	this.editMode = ko.observable(false);
}

function userModel(user) {
	this[USERS_FLD_ID] = user[USERS_FLD_ID];
	this[USERS_FLD_EMAIL] = ko.observable(user[USERS_FLD_EMAIL]);
	this[USERS_FLD_STATUS] = ko.observable(user[USERS_FLD_STATUS]);
}

function orderModel(order) {
	var self = this;
	self[ORDERS_ID] = order[ORDERS_ID];
	self[ORDERS_DATE] = order[ORDERS_DATE];
	self[ORDERS_COST] = order[ORDERS_COST];
	self[ORDERS_STATUS_ID] = ko.observable(order[ORDERS_STATUS_ID]);
	self.textStatus = ko.computed(function () {
		if (self[ORDERS_STATUS_ID]() == ORDER_STATUS_PENDING)
			return "Pending";
		if (self[ORDERS_STATUS_ID]() == ORDER_STATUS_PICKED)
			return "Picked";
		if (self[ORDERS_STATUS_ID]() == ORDER_STATUS_SHIPPED)
			return "Shipped";
		if (self[ORDERS_STATUS_ID]() == ORDER_STATUS_DELIVERED)
			return "Delivered";
		else
			return "Error";
	});
}

function deliveryRequestModel(deliveryRequest) {
	var self = this;
	self[DELIVERYREQUESTS_ID] = deliveryRequest[DELIVERYREQUESTS_ID];
	self[DELIVERYREQUESTS_DUEDATE] = deliveryRequest[DELIVERYREQUESTS_DUEDATE];
	self[ORDERS_COST] = deliveryRequest[ORDERS_COST];
	self[ORDERS_STATUS_ID] = ko.observable(deliveryRequest[DELIVERYREQUESTS_STATUS_ID]);
	self[USERS_FLD_NAME] = deliveryRequest[USERS_FLD_NAME];
	self[USERS_FLD_TEL] = deliveryRequest[USERS_FLD_TEL];
	self[BUYERS_FLD_ADDRESS] = deliveryRequest[BUYERS_FLD_ADDRESS];
	self.textStatus = ko.computed(function () {
		if (self[ORDERS_STATUS_ID]() == ORDER_STATUS_PENDING)
			return "Pending";
		if (self[ORDERS_STATUS_ID]() == ORDER_STATUS_PICKED)
			return "Picked";
		if (self[ORDERS_STATUS_ID]() == ORDER_STATUS_SHIPPED)
			return "Shipped";
		if (self[ORDERS_STATUS_ID]() == ORDER_STATUS_DELIVERED)
			return "Delivered";
		else
			return "Error";
	});
}

// ==========================================================================================================
/*	View Models	 */
// ==========================================================================================================

var controlPanelViewModel;
/*ToDo remove unused*/
function productsViewModel(params) {
	var self = this;
	self.params = params.value;
	self.productsArray = ko.observableArray();
	self.allCategories = getCategoriesArray();
	self.categorySpecs = ko.observableArray();
	//new product vars
	self.newProduct = {}
	self.newProduct.category = ko.observable();

	self.newProduct.category.subscribe(function (category) {
		self.categorySpecs.removeAll();
		getCategorySpecs(category.id).forEach(function (categorySpec) {
			self.categorySpecs.push(new categorySpecModel(categorySpec));
		});
	})

	self.init = function () {
		var products = getAllProducts();
		products.forEach(function (product) {
			self.productsArray.push(new productModel(product));
		});
	}();
	// ToDO: seller removes product only, admin removes product and order items that contain this product
	self.deleteProduct = function (item, event) {
		if (confirm("Are you sure?")) {
			self.productsArray.remove(item.params);
		}
	}

	self.addNewProduct = function () {
		alert("ADD");
	}
}

function singleProductViewModel(params) {
	var self = this;
	self.params = params.value;

	/**
	 * This function handles product load more click
	 * @param {object} item  clicked category
	 * @param {object} event click event
	 */
	self.loadMoreClick = function (item, event) {
		self.params.isMoreDivVisible(true);
	}

	self.save = function () {
		if (confirm("Are you sure?")) {
			// Call api first and if true edit these values
			self.params.name(self.params.tmpName());
			self.params.price(self.params.tmpPrice());
			self.params.image(self.params.tmpImage());
			self.params.quantity(self.params.tmpQuantity());

			ko.utils.arrayForEach(self.params.more(), function (spec, index) {
				self.params.more()[index].value(self.params.tmpMore()[index].value());
			});
			self.params.editMode(false);
		}
	}
}

function profileViewModel(params) {
	var self = this;
	self.userModel = getUserModel();
	self.saveProfile = function () {
		var data = {};
		data[USERS_FLD_NAME] = self.userModel.name();
		data[USERS_FLD_TEL] = self.userModel.tel();
		data[USERS_FLD_PASS1] = self.userModel.currentPass();
		data[USERS_FLD_PASS2] = self.userModel.newPass();
		if (self.userModel.type() == USER_SELLER) {
			data[SELLERS_FLD_ADDRESS] = self.userModel.address();
			data[SELLERS_FLD_BACK_ACCOUNT_SMALLCASE] = self.userModel.bankAccount();
		}
		$.ajax({
			url: API_LINK + USER_ENDPOINT,
			type: 'PUT',
			data: data,
			headers: {
				'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
			},
			success: function (result) {
				var returnedData = JSON.parse(result);
				if (returnedData.statusCode == USER_EDIT_ACCOUNT_SUCCESSFUL) {
					alert(returnedData.result);
					localStorage.setItem(OMARKET_PREFIX + USERS_FLD_NAME, data[USERS_FLD_NAME]);
					localStorage.setItem(OMARKET_PREFIX + USERS_FLD_TEL, data[USERS_FLD_TEL]);
					if (self.userModel.type() == USER_SELLER) {
						localStorage.setItem(OMARKET_PREFIX + SELLERS_FLD_ADDRESS, data[SELLERS_FLD_ADDRESS]);
						localStorage.setItem(OMARKET_PREFIX + SELLERS_FLD_BACK_ACCOUNT_SMALLCASE, data[SELLERS_FLD_BACK_ACCOUNT_SMALLCASE]);
					}
					window.location = ADMIN_LINK;
				} else {
					alert(returnedData.errorMsg);
				}
			}
		});
	}
}

function categoriesViewModel(params) {
	var self = this;
	self.categoriesArray = ko.observableArray();
	self.newCategoryName = ko.observable("");
	/**
	 This function initializes the categoriesArray
	 */
	self.init = function () {
		// Get all categories from API and add them to the array
		getCategoriesArray().forEach(function (category) {
			self.categoriesArray.push(new categoryModel(category));
		});
	}();

	self.removeCategory = function (item, event) {
		if (confirm("Are you sure?")) {
			// ToDo call API to delete category first, and check if it has no products
			self.categoriesArray.remove(item.params);
		}
	}
	self.addNewCategory = function () {
		if (!self.newCategoryName() || self.newCategoryName().trim().length == 0) {
			alert("Please enter non-empty category name!");
			return;
		}
		var isUnique = true;
		ko.utils.arrayForEach(self.categoriesArray(), function (category, index) {
			if (category.name().trim() == self.newCategoryName().trim()) {
				isUnique = false;
			}
		});
		if (isUnique) {
			var newID = addCategory(self.newCategoryName().trim());
			if (newID > 0) {
				// ToDo insert using API, get id, add to array
				self.categoriesArray.push(new categoryModel({
					id: newID,
					name: self.newCategoryName().trim()
				}));
				self.newCategoryName("");
			}
		} else {
			alert("Please choose different unique name!");
		}
	}
}

function singleCategoryViewModel(params) {
	var self = this;
	self.params = params.value;
	self.parent = params.parent;
	self.init = function () {}();
	self.save = function (item, event) {
		if (item.params.tmpName().trim().length > 0) {
			// ToDo: trim, call API to update categoryName
			var isUnique = true;
			ko.utils.arrayForEach(self.parent.categoriesArray(), function (category, index) {
				if (category.name().trim() == item.params.tmpName().trim() && (category != item.params)) {
					isUnique = false;
				}
			});
			if (isUnique) {
				item.params.name(item.params.tmpName().trim());
				item.params.editMode(false);
			} else {
				alert("Please choose different unique name!");
			}
		} else {
			alert("Please enter name!");
		}
	}
}


function employeesViewModel(params) {
	var self = this;
	self.employeesArray = ko.observableArray();
	self.newEmployeeEmail = ko.observable("");
	self.newEmployeePass = ko.observable("");
	self.employeeTypeName = params.employeeTypeName;
	self.employeeType = params.employeeType;
	self.employeeSingleName = params.employeeSingleName;
	self.init = function () {
		// Get all categories from API and add them to the array
		getAllUsers(self.employeeType).forEach(function (employee) {
			self.employeesArray.push(new employeeModel(employee));
		});
	}();

	self.removeEmployee = function (item, event) {
		if (confirm("Are you sure?")) {
			if (deleteEmployee(item.params[USERS_FLD_ID])) {
				self.employeesArray.remove(item.params);
			}
		}
	}
	self.addNewEmployee = function () {

		if (!self.newEmployeeEmail() || !self.newEmployeePass() || self.newEmployeeEmail().trim().length == 0 || self.newEmployeePass().trim().length == 0) {
			alert("Please enter non-empty employee data!");
			return;
		}
		var isUnique = true;
		ko.utils.arrayForEach(self.employeesArray(), function (employee, index) {
			if (employee.email().trim() == self.newEmployeeEmail().trim()) {
				isUnique = false;
			}
		});
		if (isUnique) {
			var newID = addEmployee(self.newEmployeeEmail(), self.newEmployeePass(), self.employeeType);
			if (newID != -1) {
				var newEmp = {};
				newEmp[USERS_FLD_ID] = newID;
				newEmp[USERS_FLD_EMAIL] = self.newEmployeeEmail().trim();
				self.employeesArray.push(new employeeModel(newEmp));
				self.newEmployeeEmail("");
				self.newEmployeePass("");
			}
		} else {
			alert("Please choose different unique email!");
		}
	}
}

function singleEmployeeViewModel(params) {
	var self = this;
	self.params = params.value;
	self.parent = params.parent;
	self.init = function () {}();
	self.save = function (item, event) {
		if (item.params.tmpEmail().trim().length > 0) {
			var isUnique = true;
			ko.utils.arrayForEach(self.parent.employeesArray(), function (employee, index) {
				if (employee.email().trim() == item.params[USERS_FLD_TMP_EMAIL]().trim() && employee != item.params) {
					isUnique = false;
				}
			});
			if (isUnique) {
				if (editEmployee(item.params[USERS_FLD_ID](), item.params[USERS_FLD_TMP_EMAIL](), item.params[USERS_FLD_PASS]())) {
					item.params.email(item.params[USERS_FLD_TMP_EMAIL]().trim());
					item.params.editMode(false);
				}
			} else {
				alert("Please choose different unique email!");
			}
		} else {
			alert("Please enter email and password!");
		}
	}
}

function usersViewModel(params) {
	var self = this;
	self.type = params.type;
	self.usersArray = ko.observableArray();
	self.init = function () {
		// Get all categories from API and add them to the array
		if (self.type == "Sellers") {
			getAllUsers(USER_SELLER).forEach(function (user) {
				self.usersArray.push(new userModel(user));
			});
		} else if (self.type == "Buyers") {
			getAllUsers(USER_BUYER).forEach(function (user) {
				self.usersArray.push(new userModel(user));
			});
		}
	}();

	self.blockUser = function (item, event) {
		if (confirm("Are you sure?")) {
			// ToDo call API to delete category first, and check if it has no products
			if (changeUserBanStatus(item.params[USERS_FLD_ID], USER_BANNED)) {
				item.params[USERS_FLD_STATUS](USER_BANNED);
			}
		}
	}
	self.unblockUser = function (item, event) {
		if (confirm("Are you sure?")) {
			// ToDo call API to delete category first, and check if it has no products
			if (changeUserBanStatus(item.params[USERS_FLD_ID], USER_ACTIVE)) {
				item.params[USERS_FLD_STATUS](USER_ACTIVE);
			}
		}
	}
}

function singleUserViewModel(params) {
	var self = this;
	self.params = params.value;
	self.parent = params.parent;
	self.init = function () {}();
}

function ordersViewModel(params) {
	var self = this;
	self.ordersArray = ko.observableArray();
	self.minPrice = 0;
	self.maxPrice = 5000;
	self.dateEnabled = ko.observable(false);
	self.priceEnabled = ko.observable(false);
	self.minDate = ko.observable("");
	self.maxDate = ko.observable("");
	self.pending = ko.observable(true);
	self.picked = ko.observable(true);
	self.shipped = ko.observable(true);
	self.delivered = ko.observable(true);

	self.totalCost = ko.observable(0);


	self.calculateTotal = function () {
		self.totalCost(0);
		ko.utils.arrayForEach(self.ordersArray(), function (order, index) {
			self.totalCost(parseFloat(self.totalCost()) + parseFloat(order.cost));
		});
	}

	self.loadOrders = function () {
		self.ordersArray.removeAll();
		self.userID = getUserID();
		// Get all categories from API and add them to the array
		if (checkUserRole() == USER_ACCOUNTANT) {

			var filters = {};
			filters[ORDER_FILTER_COST] = {};
			filters[ORDER_FILTER_COST][ORDER_FILTER_STATUS] = self.priceEnabled();
			filters[ORDER_FILTER_COST][ORDER_FILTER_MIN] = self.minPrice;
			filters[ORDER_FILTER_COST][ORDER_FILTER_MAX] = self.maxPrice;

			filters[ORDER_FILTER_DATE] = {};
			filters[ORDER_FILTER_DATE][ORDER_FILTER_STATUS] = self.dateEnabled();
			filters[ORDER_FILTER_DATE][ORDER_FILTER_MIN] = self.minDate();
			filters[ORDER_FILTER_DATE][ORDER_FILTER_MAX] = self.maxDate();

			filters[ORDER_FILTER_STATUS] = {};
			filters[ORDER_FILTER_STATUS][ORDER_FILTER_PENDING] = self.pending();
			filters[ORDER_FILTER_STATUS][ORDER_FILTER_PICKED] = self.picked();
			filters[ORDER_FILTER_STATUS][ORDER_FILTER_SHPPED] = self.shipped();
			filters[ORDER_FILTER_STATUS][ORDER_FILTER_DELIVERED] = self.delivered();
			var data = {};
			data['filters'] = filters;
			getOrders(JSON.stringify(data)).forEach(function (order) {
				self.ordersArray.push(new orderModel(order));
			});
		} else if (self.userID != -1 && checkUserRole() == USER_DELIVERYMAN) {
			getDeliverymanOrders().forEach(function (order) {
				self.ordersArray.push(new deliveryRequestModel(order));
			});
		}
		self.calculateTotal();
	}

	self.init = function () {
		// initial the slider
		$(function () {
			$("#slider").slider({
				range: true,
				min: 0,
				max: 10000,
				values: [0, 5000],
				slide: function (event, ui) {
					self.minPrice = ui.values[0];
					self.maxPrice = ui.values[1];
					$(".orders-filters--pricetext").html("$" + ui.values[0] + " - $" + ui.values[1]);
				}
			});
			$("#startdate").datepicker({
				dateFormat: 'yy-mm-dd'
			});
			$("#enddate").datepicker({
				dateFormat: 'yy-mm-dd'
			});
		});
		self.loadOrders();
	}();

	shouter.subscribe(function (deliveredOrderID) {
		ko.utils.arrayForEach(self.ordersArray(), function (order, index) {
			if (order && order.id == deliveredOrderID) {
				self.ordersArray.remove(order);
			}
		});
	}, self, "removeDeliveredOrder");

}

function singleOrderViewModel(params) {
	var self = this;
	self.params = params.value;
	self.changeOrderStatus = function (orderStatus) {
		if (changeOrderStatus(self.params[DELIVERYREQUESTS_ORDER_ID], orderStatus)) {
			// update via API ToDO
			self.params[ORDERS_STATUS_ID](orderStatus);
			if (orderStatus == ORDER_STATUS_DELIVERED)
				shouter.notifySubscribers(self.params.id, "removeDeliveredOrder");
		}
	};
}

function controlPanelViewModel() {
	if (checkIfSignedIn() && checkIfActiveUser() && (checkUserRole() == USER_ADMIN || checkUserRole() == USER_ACCOUNTANT || checkUserRole() == USER_DELIVERYMAN || checkUserRole() == USER_SELLER)) {
		var self = this;

		// Register categories components

		ko.components.register('categories', {
			template: {
				element: 'categories-template'
			},
			viewModel: categoriesViewModel
		});


		ko.components.register('category-container', {
			template: {
				element: 'single-category-template'
			},
			viewModel: singleCategoryViewModel
		});
		// Register employees components

		ko.components.register('employees', {
			template: {
				element: 'employees-template'
			},
			viewModel: employeesViewModel
		});


		ko.components.register('employee-container', {
			template: {
				element: 'single-employee-template'
			},
			viewModel: singleEmployeeViewModel
		});
		// Register users components

		ko.components.register('users', {
			template: {
				element: 'users-template'
			},
			viewModel: usersViewModel
		});


		ko.components.register('user-container', {
			template: {
				element: 'single-user-template'
			},
			viewModel: singleUserViewModel
		});

		// Register orders components

		ko.components.register('orders', {
			template: {
				element: 'orders-template'
			},
			viewModel: ordersViewModel
		});

		if (checkUserRole() == USER_ACCOUNTANT) {
			ko.components.register('order-container', {
				template: {
					element: 'single-order-template'
				},
				viewModel: singleOrderViewModel
			});
		} else if (checkUserRole() == USER_DELIVERYMAN) {

			ko.components.register('order-container', {
				template: {
					element: 'single-delivery-request-template'
				},
				viewModel: singleOrderViewModel
			});
		}

		// Register products components

		ko.components.register('admin-products', {
			template: {
				element: 'admin-products-template'
			},
			viewModel: productsViewModel
		});

		ko.components.register('product-container', {
			template: {
				element: 'single-product-view'
			},
			viewModel: singleProductViewModel
		});

		// Register profile components

		ko.components.register('profile', {
			template: {
				element: 'profile-page-content'
			},
			viewModel: profileViewModel
		});
	} else {
		window.location = WEBSITE_LINK;
	}
}

// ==========================================================================================================
/*	App  Logic	 */
// ==========================================================================================================
var shouter = new ko.subscribable();

controlPanelViewModel = new controlPanelViewModel();
ko.applyBindings(controlPanelViewModel);

// Check for login
function getUserID() {
	try {
		var decoded = jwt_decode(localStorage.getItem(OMARKET_JWT));
		return (decoded.data[USERS_FLD_ID]);
	} catch (e) {
		return -1;
	}
}

function checkIfSignedIn() {
	try {
		var decoded = jwt_decode(localStorage.getItem(OMARKET_JWT));
		return true;
	} catch (e) {
		return false;
	}
}

function checkIfActiveUser() {
	try {
		var decoded = jwt_decode(localStorage.getItem(OMARKET_JWT));
		return (decoded.data[USERS_FLD_STATUS] == USER_ACTIVE);
	} catch (e) {
		return false;
	}
}

function checkUserRole() {
	try {
		var decoded = jwt_decode(localStorage.getItem(OMARKET_JWT));
		return (decoded.data[USERS_FLD_USER_TYPE]);
	} catch (e) {
		return "-1";
	}
}
/**
 * This function returns the user model
 * @returns {object} user model
 */
function getUserModel() {
	var user = {};
	user.name = ko.observable(localStorage.getItem(OMARKET_PREFIX + USERS_FLD_NAME));
	user.email = ko.observable(localStorage.getItem(OMARKET_PREFIX + USERS_FLD_EMAIL));
	user.type = ko.observable(localStorage.getItem(OMARKET_PREFIX + USERS_FLD_USER_TYPE));
	user.tel = ko.observable(localStorage.getItem(OMARKET_PREFIX + USERS_FLD_TEL));
	user.status = ko.observable(localStorage.getItem(OMARKET_PREFIX + USERS_FLD_STATUS));

	if (user.type() == USER_SELLER) {
		user.address = ko.observable(localStorage.getItem(OMARKET_PREFIX + SELLERS_FLD_ADDRESS));
		user.bankAccount = ko.observable(localStorage.getItem(OMARKET_PREFIX + SELLERS_FLD_BACK_ACCOUNT));
	}

	// Observables to control forms
	user.currentPass = ko.observable("");
	user.newPass = ko.observable("");
	user.changePass = ko.observable(false);
	return user;
}

// ==========================================================================================================
/*	API	Requests */
// ==========================================================================================================
/**
 * This function logs the user off the system
 */
function logOut() {
	if (localStorage.getItem(OMARKET_PREFIX + USERS_FLD_USER_TYPE) == USER_SELLER) {
		localStorage.setItem(OMARKET_PREFIX + SELLERS_FLD_ADDRESS, "");
		localStorage.setItem(OMARKET_PREFIX + SELLERS_FLD_BACK_ACCOUNT, "");
	}
	localStorage.setItem(OMARKET_JWT, "");
	localStorage.setItem(OMARKET_PREFIX + USERS_FLD_NAME, "");
	localStorage.setItem(OMARKET_PREFIX + USERS_FLD_EMAIL, "");
	localStorage.setItem(OMARKET_PREFIX + USERS_FLD_TEL, "");
	localStorage.setItem(OMARKET_PREFIX + USERS_FLD_USER_TYPE, "");
	localStorage.setItem(OMARKET_PREFIX + USERS_FLD_STATUS, "");

	window.location = WEBSITE_LINK;
}
/**
 * This function returns all the products on the server
 * @returns {Array} all products
 */
function getAllProducts() {
	return [
		{
			id: 0,
			name: "IPhone 6S",
			price: 500,
			rate: 4.5,
			image: "../img/img.png",
			earnings: "20000$",
			solditems: "100",
			quantity: 12,
			more: [
				{
					name: "Origin",
					value: "Apple"
                }
            ]
        },
		{
			id: 0,
			name: "IPhone 6S",
			price: 200,
			rate: 4.6,
			image: "../img/img.png",
			earnings: "20000$",
			solditems: "100",
			quantity: 12,
			more: [
				{
					name: "Origin",
					value: "Apple"
                }
            ]

        },
		{
			id: 0,
			name: "IPhone 6S",
			price: 500,
			rate: 4.5,
			image: "../img/img.png",
			earnings: "20000$",
			solditems: "100",
			quantity: 12,
			more: [
				{
					name: "Origin",
					value: "Apple"
                }
            ]
        },
		{
			id: 0,
			name: "IPhone 6S",
			price: 500,
			rate: 4.5,
			image: "../img/img.png",
			earnings: "20000$",
			solditems: "100",
			quantity: 12,
			more: [
				{
					name: "Origin",
					value: "Apple"
                }
            ]
        },
		{
			id: 0,
			name: "IPhone 6S",
			price: 500,
			rate: 4.5,
			image: "../img/img.png",
			earnings: "20000$",
			solditems: "100",
			quantity: 12,
			more: [
				{
					name: "Origin",
					value: "Apple"
                }
            ]
        },
		{
			id: 0,
			name: "IPhone 6S",
			price: 500,
			rate: 4.5,
			image: "../img/img.png",
			earnings: "20000$",
			solditems: "100",
			quantity: 12,
			more: [
				{
					name: "Origin",
					value: "Apple"
                }
            ]
        },
		{
			id: 0,
			name: "IPhone 6S",
			price: 500,
			rate: 4.5,
			image: "../img/img.png",
			earnings: "20000$",
			solditems: "100",
			quantity: 12,
			more: [
				{
					name: "Origin",
					value: "Apple"
                }
            ]
        },
		{
			id: 0,
			name: "IPhone 6S",
			price: 500,
			rate: 4.5,
			image: "../img/img.png",
			earnings: "20000$",
			solditems: "100",
			quantity: 12,
			more: [
				{
					name: "Origin",
					value: "Apple"
                }
            ]
        }
    ];
}
/**
 * This function changes the status of a order 
 * @param {int} orderID order id
 * @param {int} newStatus order new status (as constant)
 * @returns {Boolean}
 */
function changeOrderStatus(orderID, newStatus) {
	var statusChanged = false;
	var data = {};
	data[DELIVERYREQUESTS_STATUS_ID] = newStatus;
	$.ajax({
		url: API_LINK + CATEGORY_ENDPOINT + "/" + orderID,
		type: 'PUT',
		async: false,
		data: data,
		headers: {
			'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
		},
		success: function (result) {
			console.log(result);
			var returnedData = JSON.parse(result);
			if (returnedData.statusCode == ORDERS_UPDATE_SUCCESS) {
				statusChanged = true;
			} else {
				alert(returnedData.errorMsg);
			}
		}
	});
	return statusChanged;
}

/**
 * This function returns deliverman orders
 * @param string filters : filters to be applied
 * @returns {Array} user orders
 */
function getDeliverymanOrders() {
	var ret = [];
	$.ajax({
		url: API_LINK + DELIVERYREQUESTS_ENDPOINT,
		type: 'GET',
		async: false,
		headers: {
			'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
		},
		success: function (result) {
			console.log(result);
			var returnedData = JSON.parse(result);
			if (returnedData.statusCode == DELIVERYREQUESTS_GET_SUCCESSFUL) {
				ret = returnedData.result;
			} else {
				alert(returnedData.errorMsg);
			}
		}
	});
	return ret;
}
/**
 * This function returns total filtered orders
 * @param string filters : filters to be applied
 * @returns {Array} user orders
 */
function getOrders(filters) {
	var ret = [];
	$.ajax({
		url: API_LINK + ORDERS_ENDPOINT + "/?filters=" + filters,
		type: 'GET',
		async: false,
		headers: {
			'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
		},
		success: function (result) {
			var returnedData = JSON.parse(result);
			if (returnedData.statusCode == ORDERS_GET_SUCCESSFUL) {
				ret = returnedData.result;
			} else {
				alert(returnedData.errorMsg);
			}
		}
	});
	return ret;
}

/**
 * This function returns all the users in the system for specific type
 * @param {int} user type
 * @returns {Array} users array
 */
function getAllUsers(userType) {
	var ret = [];
	$.ajax({
		url: API_LINK + USER_ENDPOINT + '/' + userType,
		type: 'GET',
		async: false,
		headers: {
			'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
		},
		success: function (result) {
			var returnedData = JSON.parse(result);
			if (returnedData.statusCode == USER_GET_USERS_SUCCESSFUL) {
				ret = returnedData.result;
			} else {
				alert(returnedData.errorMsg);
			}
		}
	});
	return ret;
}

/**
 * This function changes the status of a selected user
 * @param {int} userID user id
 * @param {int} newStatus user new status (as constant)
 * @returns {Boolean}
 */
function changeUserBanStatus(userID, newStatus) {
	var statusChanged = false;
	var data = {};
	data[USERS_FLD_ID] = userID;
	data[USERS_FLD_STATUS] = newStatus;
	$.ajax({
		url: API_LINK + USER_ENDPOINT + "/" + CHANGE_USER_STATUS_ENDPOINT,
		type: 'PUT',
		async: false,
		data: data,
		headers: {
			'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
		},
		success: function (result) {
			var returnedData = JSON.parse(result);
			if (returnedData.statusCode == USER_UPDATE_STATUS_SUCCESSFUL) {
				statusChanged = true;
			} else {
				alert(returnedData.errorMsg);
			}
		}
	});
	return statusChanged;
}

/**
 * This function deletes user with its ID
 * @param   {number}  empID User ID
 * @returns {boolean} True if deleted successfully
 */
function deleteEmployee(empID) {
	var deleted = false;
	var data = {};
	data[USERS_FLD_ID] = empID;
	$.ajax({
		url: API_LINK + USER_ENDPOINT,
		type: 'DELETE',
		async: false,
		data: data,
		headers: {
			'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
		},
		success: function (result) {
			var returnedData = JSON.parse(result);
			if (returnedData.statusCode == USER_DELETE_SUCCESSFUL) {
				deleted = true;
			} else {
				alert(returnedData.errorMsg);
			}
		}
	});
	return deleted;
}

/**
 * This function adds employee to db
 * @param   {email}   email new employee email
 * @param   {string}  pass  new employee password
 * @param   {int}  pass  new employee type
 * @returns {int} new emp ID, -1 if operation failed
 */
function addEmployee(email, pass, empType) {
	var newID = -1;
	var data = {};
	data[USERS_FLD_EMAIL] = email;
	data[USERS_FLD_PASS] = pass;
	data[USERS_FLD_USER_TYPE] = empType;
	$.ajax({
		url: API_LINK + USER_ENDPOINT,
		type: 'POST',
		async: false,
		data: data,
		headers: {
			'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
		},
		success: function (result) {
			var returnedData = JSON.parse(result);
			if (returnedData.statusCode == USER_INSERT_SUCCESSFUL) {
				newID = returnedData.result;
			} else {
				alert(returnedData.errorMsg);
			}
		}
	});
	return newID;
}


/**
 * This function adds employee to db
 * @param   {int}   userID employee id
 * @param   {email}   email new employee email
 * @param   {string}  pass  new employee password
 * @returns {boolean} true if operation succeeded
 */
function editEmployee(userID, email, pass) {
	var edited = false;
	var data = {};
	data[USERS_FLD_EMAIL] = email;
	data[USERS_FLD_PASS] = pass;
	data[USERS_FLD_ID] = userID;
	$.ajax({
		url: API_LINK + USER_ENDPOINT + "/" + USER_EDIT_ENDPOINT,
		type: 'PUT',
		async: false,
		data: data,
		headers: {
			'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
		},
		success: function (result) {
			var returnedData = JSON.parse(result);
			if (returnedData.statusCode == USER_EDIT_ACCOUNT_SUCCESSFUL) {
				edited = true;
				alert(returnedData.result);
			} else {
				alert(returnedData.errorMsg);
			}
		}
	});
	return edited;
}

/**
 * This function adds category to db
 * @param   {string}  name new category name
 * @returns {int} new category ID, -1 if operation failed
 */
function addCategory(name) {
	var newID = -1;
	var data = {};
	data[CATEGORIES_FLD_NAME] = name;
	$.ajax({
		url: API_LINK + CATEGORY_ENDPOINT,
		type: 'POST',
		async: false,
		data: data,
		headers: {
			'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
		},
		success: function (result) {
			console.log(result);
			var returnedData = JSON.parse(result);
			if (returnedData.statusCode == CATEGORY_ADD_SUCCESS) {
				newID = returnedData.result;
			} else {
				alert(returnedData.errorMsg);
			}
		}
	});
	return newID;
}


function getCategorySpecs(cateID) {
	return [{
		_id: 1,
		name: "spec1"
        }, {
		_id: 2,
		name: "spec2"
        }, {
		_id: 3,
		name: "spec3"
        }, {
		_id: 4,
		name: "spec4"
        }, {
		_id: 5,
		name: "spec5"
        }];
}
/**
 * This function retrieves all the the categories from the server
 * @returns {Array} Categories array -> contains categories objects
 */
function getCategoriesArray() {
	//dummy data till the API is ready
	return [
		{
			id: 1,
			name: "Computers, IT & Networking"
        },
		{
			id: 2,
			name: "Mobile Phones, Tablets & Accessories"
        },
		{
			id: 3,
			name: "Car Electronics & Accessories"
        },
		{
			id: 4,
			name: "Books"
        },
		{
			id: 5,
			name: "Gaming"
        },
		{
			id: 6,
			name: "Electronic"
        },
		{
			id: 7,
			name: "Sports & Fitness"
        },
		{
			id: 8,
			name: "Perfumes & Fragrances"
        },
		{
			id: 9,
			name: "Health & Personal Care"
        },
		{
			id: 10,
			name: "Furniture"
        },
		{
			id: 11,
			name: "Apparel, Shoes & Accessories"
        },
		{
			id: 12,
			name: "Appliances"
        },
		{
			id: 13,
			name: "Art, Crafts & Collectables"
        },
		{
			id: 14,
			name: "Baby"
        },
		{
			id: 15,
			name: "Kitchen & Home Supplies"
        },
		{
			id: 16,
			name: "Toys"
        }
    ];
}
