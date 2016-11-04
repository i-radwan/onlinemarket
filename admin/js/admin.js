// ==========================================================================================================
/*	Constants	 */
// ==========================================================================================================

// ==========================================================================================================
/*	Data Models	 */
// ==========================================================================================================

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

function productModel(product) {
	var self = this;
	self.id = product.id;
	self.name = product.name;
	self.price = product.price;
	self.rate = product.rate;
	self.image = product.image;
	self.more = product.more || [];
	self.quantity = ko.observable(product.quantity || 1);
	self.isMoreDivVisible = ko.observable(false);
}

// ==========================================================================================================
/*	View Models	 */
// ==========================================================================================================

var controlPanelViewModel;
/*ToDo remove unused*/
function productsCollectionViewModel(params) {
	var self = this;
	self.topCategoriesArray = ko.observableArray(); // make observable

	self.changeProductsViewContent = function (categoryID) {
		self.topCategoriesArray.removeAll();

		if (categoryID == 0) {
			// Get top categories and top produdcts in every category from the API and add them to array
			var topCategories = getTopCategories(TOP_CATEGOIRES_COUNT);

			topCategories.forEach(function (topCategory) {
				var topProducts = getCategoryProducts(0, 3);
				topProducts.forEach(function (topProduct) {
					topCategory.products.push(new productModel(topProduct));
				});
				self.topCategoriesArray.push(new categoryModel(topCategory));
			});
		} else {
			var category = {};
			category.id = categoryID;
			category.name = getCategoryName(categoryID);
			category.products = [];
			var products = getCategoryProducts(categoryID, 0);
			products.forEach(function (product) {
				category.products.push(new productModel(product));
			});
			self.topCategoriesArray.push(new categoryModel(category));
		}
	}

	/**
		This function initializes the categoriesArray
	*/
	self.init = function () {
		self.changeProductsViewContent(params.categoryID());
		// handle categoryID changed from the leftpanel module
		shouter.subscribe(function (newCategoryID) {
			// ToDo: if categoryID not found-> back to zero
			params.categoryID(newCategoryID);
			self.changeProductsViewContent(params.categoryID());
		}, self, "changedCategoryID");
	}();

	self.expandCategory = function (item) {
		sammyApp.setLocation('#/' + item.id);
	}

}

function cartProductsViewModel(params) {
	var self = this;
	self.cartProductsArray = ko.observableArray(); // make observable
	/**
		This function initializes the categoriesArray
	*/
	self.init = function () {
		var cartProducts = getCartProducts();
		cartProducts.forEach(function (product) {
			product.quantity = 1;
			self.cartProductsArray.push(new productModel(product));
		});
	}();

	self.cancelProduct = function (product) {
		if (confirm("Do you want to remove this product for sure ?")) {
			self.cartProductsArray.remove(product.params);
			return true;
		}
	}

	self.checkoutOrder = function () {
		if (confirm("Are you sure, the amount will be withdrawed from your CC ?")) {
			var cartProducts = [];
			ko.utils.arrayForEach(self.cartProductsArray(), function (product, index) {
				product.quantity = product.quantity();
				cartProducts.push(product);
			});
			shouter.notifySubscribers(cartProducts, "addOrder");
			self.cartProductsArray.removeAll();
			return true;
		}
		return false;
	}

	shouter.subscribe(function (newProduct) {
		var added = false;
		ko.utils.arrayForEach(self.cartProductsArray(), function (product, index) {
			if (newProduct.id == product.id) {
				product.quantity(product.quantity() + 1);
				added = true;
			}
		});
		if (!added) {
			self.cartProductsArray.push(newProduct);
		}
	}, self, "addProductToCart");
}

function productViewModel(params) {
	var self = this;
	self.params = params.value;
	self.params.cart = params.cart;
	self.params.order = params.order;
	self.params.userRate = ko.observable(getUserRate(self.params.id));

	self.params.formattedRate = ko.computed(function () {
		return (self.params.userRate()).toFixed(1);
	});
	/**
	 * This function handles product load more click
	 * @param {object} item  clicked category
	 * @param {object} event click event
	 */
	self.loadMoreClick = function (item, event) {
		self.params.isMoreDivVisible(true);
	}

	/**
	 * This function handles product add to cart click
	 * @param {object} item  clicked category
	 * @param {object} event click event
	 */
	self.addToCart = function (product) {
		onlineMarketMVVM.increaseCartAmount(product.params.price);
		shouter.notifySubscribers(product.params, "addProductToCart");
	}

	self.increaseQuantity = function () {
		// ToDo check if available
		self.params.quantity(self.params.quantity() + 1);
		return true;

	}
	self.decreaseQuantity = function () {
		if (self.params.quantity() > 1) {
			self.params.quantity(self.params.quantity() - 1);
			return true;
		}
	}

	self.increaseRate = function () {
		if (self.params.userRate() + 0.5 <= 5) {
			self.params.userRate(self.params.userRate() + 0.5);
			return true;
		}
		return false;
	}
	self.decreaseRate = function () {
		if (self.params.userRate() - 0.5 >= 0) {
			self.params.userRate(self.params.userRate() - 0.5);
			return true;
		}
		return false;
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
	/**
		This function initializes the categoriesArray
	*/
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
	/**
		This function initializes the categoriesArray
	*/
	self.init = function () {
		// Get all categories from API and add them to the array
		getAllSellersAndBuyers().forEach(function (user) {
			self.usersArray.push(new userModel(user));
		});
	}();

	self.blockUser  = function (item, event) {
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
