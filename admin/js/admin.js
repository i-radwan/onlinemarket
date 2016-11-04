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

function employeeModel(employee) {
	this.id = employee.id;
	this.email = ko.observable(employee.email);
	this.tmpEmail = ko.observable(employee.email);
	this.pass = ko.observable("");
	this.editMode = ko.observable(false);
}

function userModel(user) {
	this[USERS_FLD_ID] = user[USERS_FLD_ID];
	this[USERS_FLD_EMAIL] = ko.observable(user[USERS_FLD_EMAIL]);
	this[USERS_FLD_STATUS] = ko.observable(user[USERS_FLD_STATUS]);
}

function orderModel(order) {
	var self = this;
	self.id = order.id;
	self.issuedate = order.issuedate;
	self.cost = order.cost;
	self.status = order.status;
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
	self.ordersArray = ko.observableArray();
	self.userModel = getUserModel();

	self.init = function () {
		var orders = getUserOrders();
		orders.forEach(function (order) {
			self.ordersArray.push(new orderModel(order));
		});
	}();

	self.saveProfile = function () {
		// ToDo save profile using API
		console.log(self.userModel.address());
		console.log(self.userModel.ccmonth());
		console.log(self.userModel.ccyear());
		console.log(self.userModel.ccnumber());
		console.log(self.userModel.currentPass());
		console.log(self.userModel.newPass());
	}
	shouter.subscribe(function (products) {
		// ToDo to be retrieved from API
		var order = {};
		order.id = 4;
		order.date = "2016-10-28";
		order.cost = onlineMarketMVVM.cartAmount();
		order.status = "Pending";
		order.products = products;
		self.ordersArray.push(new orderModel(order));
		alert("Order added successfully!");
		sammyApp.setLocation("#/profile");
	}, self, "addOrder");
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
			// ToDo insert using API, get id, add to array
			self.categoriesArray.push(new categoryModel({
				id: 20,
				name: self.newCategoryName().trim()
			}));
			self.newCategoryName("");
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
		getEmployeesArray(self.employeeType).forEach(function (employee) {
			self.employeesArray.push(new employeeModel(employee));
		});
	}();

	self.removeEmployee = function (item, event) {
		if (confirm("Are you sure?")) {
			// ToDo call API to delete category first, and check if it has no products
			self.employeesArray.remove(item.params);
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
			// ToDo insert using API, get id, add to array
			self.employeesArray.push(new employeeModel({
				id: 20,
				email: self.newEmployeeEmail().trim()
			}));
			self.newEmployeeEmail("");
			self.newEmployeePass("");
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
			// ToDo: trim, call API to update employee (Check if password is empty update just the email)
			var isUnique = true;
			ko.utils.arrayForEach(self.parent.employeesArray(), function (employee, index) {
				if (employee.email().trim() == item.params.tmpEmail().trim() && employee != item.params) {
					isUnique = false;
				}
			});
			if (isUnique) {
				item.params.email(item.params.tmpEmail().trim());
				item.params.editMode(false);
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
	self.usersArray = ko.observableArray();
	self.init = function () {
		// Get all categories from API and add them to the array
		getAllSellersAndBuyers().forEach(function (user) {
			self.usersArray.push(new userModel(user));
		});
	}();

	self.blockUser = function (item, event) {
		if (confirm("Are you sure?")) {
			// ToDo call API to delete category first, and check if it has no products
			item.params[USERS_FLD_STATUS](USER_BANNED);
		}
	}
	self.unblockUser = function (item, event) {
		if (confirm("Are you sure?")) {
			// ToDo call API to delete category first, and check if it has no products
			item.params[USERS_FLD_STATUS](USER_ACTIVE);
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
	self.totalCost = ko.observable(0);


	self.calculateTotal = function () {
		self.totalCost(0);
		ko.utils.arrayForEach(self.ordersArray(), function (order, index) {
			self.totalCost(self.totalCost() + order.cost);
		});
	}

	self.loadOrders = function () {
		self.ordersArray.removeAll();
		// Get all categories from API and add them to the array
		getOrders(null).forEach(function (order) {
			self.ordersArray.push(new orderModel(order));
		});
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
			$("#startdate").datepicker();
			$("#enddate").datepicker();
		});
		self.loadOrders();
	}();

}

function singleOrderViewModel(params) {
	var self = this;
	self.params = params.value;

	self.init = function () {

	}();
}

function controlPanelViewModel() {
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

	ko.components.register('order-container', {
		template: {
			element: 'single-order-template'
		},
		viewModel: singleOrderViewModel
	});


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
}

// ==========================================================================================================
/*	App  Logic	 */
// ==========================================================================================================

controlPanelViewModel = new controlPanelViewModel();
ko.applyBindings(controlPanelViewModel);

// Check for login
function checkIfSignedIn() {
	// ToDo should check the localstorage and check for user role
	return true;
}

// ==========================================================================================================
/*	API	Requests */
// ==========================================================================================================

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
 * This function returns user orders
 * @param string filters : filters to be applied
 * @returns {Array} user orders
 */
function getOrders(filters) {
	return [{
			id: 1,
			issuedate: "2016-08-10",
			cost: 1200,
			status: "Pending",
			products: [{
					id: 1,
					name: "IPhone 6S",
					price: 500,
					rate: 4.5,
					image: "../img/img.png",
					quantity: 10,
					more: [
						{
							name: "Origin",
							value: "Apple"
					}
				]
			},
				{
					id: 2,
					name: "IPhone 4S",
					price: 200,
					rate: 4.6,
					image: "../img/img.png",
					quantity: 20,
					more: [
						{
							name: "Origin",
							value: "Apple"
					},
						{
							name: "Sold items",
							value: "100"
					}
				]

			},
				{
					id: 3,
					name: "IPhone 3S",
					price: 100,
					rate: 4.5,
					image: "../img/img.png",
					quantity: 30,
					more: [
						{
							name: "Origin",
							value: "Apple"
					}
				]
			}]
	}, {
			id: 2,
			issuedate: "2016-08-12",
			cost: 1000,
			status: "Picked",
			products: [{
					id: 1,
					name: "IPhone 6S",
					price: 500,
					rate: 4.5,
					image: "../img/img.png",
					quantity: 10,
					more: [
						{
							name: "Origin",
							value: "Apple"
					}
				]
			},
				{
					id: 2,
					name: "IPhone 4S",
					price: 200,
					rate: 4.6,
					image: "../img/img.png",
					quantity: 20,
					more: [
						{
							name: "Origin",
							value: "Apple"
					},
						{
							name: "Sold items",
							value: "100"
					}
				]

			},
				{
					id: 3,
					name: "IPhone 3S",
					price: 100,
					rate: 4.5,
					image: "../img/img.png",
					quantity: 30,
					more: [
						{
							name: "Origin",
							value: "Apple"
					}
				]
			}]
	},
		{
			id: 3,
			issuedate: "2016-08-13",
			cost: 1300,
			status: "Delivered",
			products: [{
					id: 1,
					name: "IPhone 6S",
					price: 500,
					rate: 4.5,
					image: "../img/img.png",
					quantity: 10,
					more: [
						{
							name: "Origin",
							value: "Apple"
					}
				]
			},
				{
					id: 2,
					name: "IPhone 4S",
					price: 200,
					rate: 4.6,
					image: "../img/img.png",
					quantity: 20,
					more: [
						{
							name: "Origin",
							value: "Apple"
					},
						{
							name: "Sold items",
							value: "100"
					}
				]

			},
				{
					id: 3,
					name: "IPhone 3S",
					price: 100,
					rate: 4.5,
					image: "../img/img.png",
					quantity: 30,
					more: [
						{
							name: "Origin",
							value: "Apple"
					}
				]
			}]
	}];
}

/**
 * This function returns all the sellers and buyers in the system
 * @returns {Array} users array
 */
function getAllSellersAndBuyers() {
	return [{
		_id: 1,
		email: "asd1@asd.asd",
		user_type: "1",
		user_status: "1"
	}, {
		_id: 2,
		email: "asd2@asd.asd",
		user_type: "1",
		user_status: "2"
	}, {
		_id: 3,
		email: "asd3@asd.asd",
		user_type: "2",
		user_status: "1"
	}, {
		_id: 4,
		email: "asd4@asd.asd",
		user_type: "2",
		user_status: "2"
	}];
}

/**
 * This function retrieves all the the employees from the server
 * @param {int}   type to select the employees type to retrieve from accountants/deliverymen
 * @returns {Array} Employees array -> contains employees objects
 */
function getEmployeesArray(type) {
	if (type == USER_ACCOUNTANT) {
		return [{
			id: 1,
			email: "asd1@asd.asd"
	}, {
			id: 2,
			email: "asd2@asd.asd"
	}, {
			id: 3,
			email: "asd3@asd.asd"
	}, {
			id: 4,
			email: "asd4@asd.asd"
	}];
	} else if (type == USER_DELIVERMAN) {
		return [{
			id: 1,
			email: "asd21@asd.asd"
	}, {
			id: 2,
			email: "asd22@asd.asd"
	}, {
			id: 3,
			email: "asd23@asd.asd"
	}, {
			id: 4,
			email: "asd24@asd.asd"
	}];
	}
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
