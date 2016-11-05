// ==========================================================================================================
/*	Constants	 */
// ==========================================================================================================

const TOP_CATEGOIRES_COUNT = 4; // The number of top categories to be shown in the main page

// ==========================================================================================================
/*	Data Models	 */
// ==========================================================================================================

function categoryModel(category) {
	var self = this;
	self.id = category.id;
	self.name = category.name;
	if (category.products) {
		self.products = category.products;
	}
	self.selected = ko.observable(false);
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

function orderModel(order) {
	var self = this;
	self.id = order.id;
	self.duedate = order.duedate;
	self.cost = order.cost;
	self.status = order.status;
	self.products = order.products;
	self.isMoreDivVisible = ko.observable(false);
}

// ==========================================================================================================
/*	View Models	 */
// ==========================================================================================================

var onlineMarketMVVM;


function leftMenuViewModel(params) {
	var self = this;

	self.categoriesArray = [];
	/**
		This function initializes the categoriesArray
	*/
	self.init = function () {
		// Get all categories from API and add them to the array
		getCategoriesArray().forEach(function (category) {
			self.categoriesArray.push(new categoryModel(category));
		});
	}();
	/**
	 * This function handles category item click
	 * @param {object} item  clicked category
	 * @param {object} event click event
	 */
	self.categoryClick = function (item, event) {
		sammyApp.setLocation('#/' + item.id);
	};

	shouter.subscribe(function (newCategoryID) {
		if (newCategoryID == 0) {
			self.selectOneCategory(null);
		} else {
			self.categoriesArray.forEach(function (category, index) {
				if (category.id == newCategoryID) {

					self.selectOneCategory(category);
					params.categoryID(category.id);
				}
			});
		}
	}, self, "changedCategoryID");

	self.selectOneCategory = function (item) {
		//Reset all categories to not selected
		self.categoriesArray.forEach(function (category, index) {
			category.selected(false);
		});
		if (item != null)
			item.selected(true);
	}
}

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

function searchProductsViewModel(params) {
	var self = this;
	self.params = params.value;
	self.params.searchWord = params.searchWord;
	self.searchProductsArray = ko.observableArray();
	self.init = function () {
		var products = getSearchProducts(self.params.searchWord());
		products.forEach(function (product) {
			self.searchProductsArray.push(new productModel(product));
		});
	}();
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
		order.duedate = "2016-10-28";
		order.cost = onlineMarketMVVM.cartAmount();
		order.status = "Pending";
		order.products = products;
		self.ordersArray.push(new orderModel(order));
		alert("Order added successfully!");
		sammyApp.setLocation("#/profile");
	}, self, "addOrder");
}

function orderViewModel(params) {
	var self = this;
	self.params = params.value;
	self.params.cart = params.cart;
	self.params.productsArray = [];
	self.params.totalItemsCount = 0;

	self.loadMoreClick = function (item, event) {
		self.params.isMoreDivVisible(!self.params.isMoreDivVisible());
	}
	self.init = function () {
		var products = self.params.products;
		products.forEach(function (product) {
			self.params.totalItemsCount += product.quantity;
			self.params.productsArray.push(new productModel(product));
		});
	}();
}

function headerViewModel(params) {
	var self = this;

	self.cartAmount = params.cartAmount;

	self.cartClicked = function () {
		sammyApp.setLocation('#/cart');
	}

	self.profileClicked = function () {
		sammyApp.setLocation('#/profile');
	}

	self.logoClicked = function () {
		sammyApp.setLocation('#/');
	}
}

function onlineMarketViewModel() {
	var self = this;
	self.cartAmount = ko.observable(0.0);
	self.categoryID = ko.observable(0); // when zero, get top products, else get specific category products
	self.isMainContentVisible = ko.observable(true);
	self.isCartVisible = ko.observable(false);
	self.isProfileVisible = ko.observable(false);
	self.isSearchVisible = ko.observable(false);
	self.searchWord = ko.observable("");
	self.tmpSearchWord = ko.observable("");

	self.userModel = getUserModel();
	// Register main-view components
	ko.components.register('left-menu', {
		template: {
			element: 'left-menu'
		},
		viewModel: leftMenuViewModel
	});

	ko.components.register('header', {
		template: {
			element: 'header'
		},
		viewModel: headerViewModel
	});

	ko.components.register('content', {
		template: {
			element: 'main-page-content'
		}
	});

	ko.components.register('product-container', {
		template: {
			element: 'single-product-view'
		},
		viewModel: productViewModel
	});

	ko.components.register('products', {
		template: {
			element: 'products'
		},
		viewModel: productsCollectionViewModel
	});
	// Register cart components

	ko.components.register('cart', {
		template: {
			element: 'cart-page-content'
		},
		viewModel: cartProductsViewModel
	});
	// Register profile components

	ko.components.register('profile', {
		template: {
			element: 'profile-page-content'
		},
		viewModel: profileViewModel
	});

	ko.components.register('order-container', {
		template: {
			element: 'single-order-view'
		},
		viewModel: orderViewModel
	});

	// Register search components

	ko.components.register('search', {
		template: {
			element: 'search-page-content'
		},
		viewModel: searchProductsViewModel
	});

	// Utils functions
	self.increaseCartAmount = function (price) {
		self.cartAmount(self.cartAmount() + price);
	}

	self.changeContentVisibility = function (isCartVisible, isMainContentVisible, isProfileVisible, isSearchVisible) {
		onlineMarketMVVM.isCartVisible(isCartVisible);
		onlineMarketMVVM.isMainContentVisible(isMainContentVisible);
		onlineMarketMVVM.isProfileVisible(isProfileVisible);
		onlineMarketMVVM.isSearchVisible(isSearchVisible);
	}
}

// ==========================================================================================================
/*	App  Logic	 */
// ==========================================================================================================
var shouter = new ko.subscribable();


onlineMarketMVVM = new onlineMarketViewModel();
ko.applyBindings(onlineMarketMVVM);

// Check for login
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
// Routing
var sammyApp;
(function ($) {

	sammyApp = $.sammy('#content', function () {
		this.get('#/', function (context) {
			onlineMarketMVVM.changeContentVisibility(false, true, false, false);
			shouter.notifySubscribers('0', "changedCategoryID");
		});
		this.get('#/cart', function (context) {
			if (checkIfSignedIn()&& checkUserRole() == USER_BUYER)
				onlineMarketMVVM.changeContentVisibility(true, false, false, false);
		});
		this.get('#/profile', function (context) {
			if (checkIfSignedIn() && checkUserRole() == USER_BUYER)
				onlineMarketMVVM.changeContentVisibility(false, false, true, false);
		});
		this.get('#/search', function (context) {
			onlineMarketMVVM.changeContentVisibility(false, false, false, true);
		});
		this.get('#/:categoryID', function (context) {
			onlineMarketMVVM.changeContentVisibility(false, true, false, false);

			var self = this;
			setTimeout(function () {
				shouter.notifySubscribers(self.params['categoryID'], "changedCategoryID");
			}, 50);
		});

	});


	$(function () {
		if (sammyApp.getLocation().search("signup.html") == -1 && sammyApp.getLocation().search("login.html") == -1) {
			sammyApp.run('#/');
		}
	});


})(jQuery);

// ==========================================================================================================
/*	API	Requests */
// ==========================================================================================================
/**
 * This function logs the user off the system
 */
function logOut() {
	localStorage.setItem(OMARKET_JWT, "");
	localStorage.setItem(OMARKET_PREFIX + USERS_FLD_NAME, "");
	localStorage.setItem(OMARKET_PREFIX + USERS_FLD_EMAIL, "");
	localStorage.setItem(OMARKET_PREFIX + USERS_FLD_TEL, "");
	localStorage.setItem(OMARKET_PREFIX + USERS_FLD_USER_TYPE, "");
	localStorage.setItem(OMARKET_PREFIX + USERS_FLD_STATUS, "");
	localStorage.setItem(OMARKET_PREFIX + BUYERS_FLD_ADDRESS, "");
	localStorage.setItem(OMARKET_PREFIX + BUYERS_FLD_CCNUMBER, "");
	localStorage.setItem(OMARKET_PREFIX + BUYERS_FLD_CC_CCV, "");
	localStorage.setItem(OMARKET_PREFIX + BUYERS_FLD_CC_YEAR, "");
	localStorage.setItem(OMARKET_PREFIX + BUYERS_FLD_CC_MONTH, "");
	window.location = WEBSITE_LINK;
}
/**
 * This function returns the products that match the search word
 * @param   {string} searchWord search text word
 * @returns {Array}  products that match the search word
 */
function getSearchProducts(searchWord) {
	// ToDo get from API
	return [{
			id: 1,
			name: "IPhone 6S",
			price: 500,
			rate: 4.5,
			image: "img/img.png",
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
			image: "img/img.png",
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
			image: "img/img.png",
			quantity: 30,
			more: [
				{
					name: "Origin",
					value: "Apple"
					}
				]
			}];
}

/**
 * This function returns the user model
 * @returns {object} user model
 */
function getUserModel() {
	var user = {};
	//	console.log(localStorage.getItem(OMARKET_PREFIX + USERS_FLD_NAME));
	user.name = ko.observable(localStorage.getItem(OMARKET_PREFIX + USERS_FLD_NAME));
	user.email = ko.observable(localStorage.getItem(OMARKET_PREFIX + USERS_FLD_EMAIL));
	user.type = ko.observable(localStorage.getItem(OMARKET_PREFIX + USERS_FLD_USER_TYPE));
	user.tel = ko.observable(localStorage.getItem(OMARKET_PREFIX + USERS_FLD_TEL));
	user.status = ko.observable(localStorage.getItem(OMARKET_PREFIX + USERS_FLD_STATUS));

	if (user.type() == USER_BUYER) {
		user.address = ko.observable(localStorage.getItem(OMARKET_PREFIX + BUYERS_FLD_ADDRESS));
		user.ccnumber = ko.observable(localStorage.getItem(OMARKET_PREFIX + BUYERS_FLD_CCNUMBER));
		user.ccccv = ko.observable(localStorage.getItem(OMARKET_PREFIX + BUYERS_FLD_CC_CCV));
		user.ccmonth = ko.observable(localStorage.getItem(OMARKET_PREFIX + BUYERS_FLD_CC_MONTH));
		user.ccyear = ko.observable(localStorage.getItem(OMARKET_PREFIX + BUYERS_FLD_CC_YEAR));
	} else if (user.type() == USER_SELLER) {
		user.address = ko.observable(localStorage.getItem(OMARKET_PREFIX + SELLERS_FLD_ADDRESS));
		user.bankAccount = ko.observable(localStorage.getItem(OMARKET_PREFIX + SELLERS_FLD_BACK_ACCOUNT));
	}

	// Observables to control forms
	user.currentPass = ko.observable("");
	user.newPass = ko.observable("");
	user.changePass = ko.observable(false);
	user.isBuyer = ko.observable(true);
	return user;
}
/**
 * This function returns the user rate for specific product
 * @param   {number} productID product_id to search for
 * @returns {number} user rate for this product
 */
function getUserRate(productID) {
	// ToDo fetch from API
	if (true) {
		return 4.5;
	} else {
		//		return avg_rate;
	}
}

/**
 * This function returns user orders
 * @returns {Array} user orders
 */
function getUserOrders() {
	return [{
			id: 1,
			duedate: "2016-08-10",
			cost: 1200,
			status: "Pending",
			products: [{
					id: 1,
					name: "IPhone 6S",
					price: 500,
					rate: 4.5,
					image: "img/img.png",
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
					image: "img/img.png",
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
					image: "img/img.png",
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
			duedate: "2016-08-12",
			cost: 1000,
			status: "Picked",
			products: [{
					id: 1,
					name: "IPhone 6S",
					price: 500,
					rate: 4.5,
					image: "img/img.png",
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
					image: "img/img.png",
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
					image: "img/img.png",
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
			duedate: "2016-08-13",
			cost: 1300,
			status: "Delivered",
			products: [{
					id: 1,
					name: "IPhone 6S",
					price: 500,
					rate: 4.5,
					image: "img/img.png",
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
					image: "img/img.png",
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
					image: "img/img.png",
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
/**
 * This function gets the top categories from the server
 * @param {number} count number of required top categories
 * @returns {Array}  top categories array
 */
function getTopCategories(count) {
	//dummy data till the API is ready
	return [
		{
			id: 1,
			name: "Computers, IT & Networking",
			products: []
			},
		{
			id: 2,
			name: "Mobile Phones, Tablets & Accessories",
			products: []
			},
		{
			id: 3,
			name: "Books",
			products: []
			},
		{
			id: 10,
			name: "Furniture",
			products: []
			}
		];
}

/**
 * This function returns the category name using its ID (from the array of categories)
 * @param   {number} categoryID required category 
 * @returns {string} category name
 */
function getCategoryName(categoryID) {
	return "Computers, IT & Networking2";
}
/**
 * This function gets top products of the required id
 * @param   {number} categoryID required category id to get its top products
 * @returns {Array}  [[Description]]
 */
function getCategoryProducts(categoryID, limit) {
	//dummy data till the API is ready
	// return top items by categoryID and satisfy limits
	if (categoryID == 0) {

		return [
			{
				id: 1,
				name: "IPhone 6S",
				price: 500,
				rate: 4.5,
				image: "img/img.png",
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
				image: "img/img.png",
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
				image: "img/img.png",
				more: [
					{
						name: "Origin",
						value: "Apple"
					}
				]
			}
		];
	} else {
		return [
			{
				id: 0,
				name: "IPhone 6S",
				price: 500,
				rate: 4.5,
				image: "img/img.png",
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
				image: "img/img.png",
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
				id: 0,
				name: "IPhone 6S",
				price: 500,
				rate: 4.5,
				image: "img/img.png",
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
				image: "img/img.png",
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
				image: "img/img.png",
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
				image: "img/img.png",
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
				image: "img/img.png",
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
				image: "img/img.png",
				more: [
					{
						name: "Origin",
						value: "Apple"
				}
			]
		}
	];
	}
}

/**
 * This function returns the user current cart products
 * @returns {Array} cart products
 */
function getCartProducts() {
	return [];
	return [
		{
			id: 0,
			name: "IPhone 1",
			price: 500,
			rate: 4.5,
			image: "img/img.png",
			more: [
				{
					name: "Origin",
					value: "Apple"
					}
				]
			},
		{
			id: 0,
			name: "IPhone 1S",
			price: 200,
			rate: 4.6,
			image: "img/img.png",
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
			id: 0,
			name: "IPhone 2",
			price: 500,
			rate: 4.5,
			image: "img/img.png",
			more: [
				{
					name: "Origin",
					value: "Apple"
					}
				]
			},
		{
			id: 0,
			name: "IPhone 2S",
			price: 500,
			rate: 4.5,
			image: "img/img.png",
			more: [
				{
					name: "Origin",
					value: "Apple"
					}
				]
			},
		{
			id: 0,
			name: "IPhone 3",
			price: 200,
			rate: 4.6,
			image: "img/img.png",
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
			id: 0,
			name: "IPhone 3S",
			price: 500,
			rate: 4.5,
			image: "img/img.png",
			more: [
				{
					name: "Origin",
					value: "Apple"
					}
				]
			},
		{
			id: 0,
			name: "IPhone 4",
			price: 500,
			rate: 4.5,
			image: "img/img.png",
			more: [
				{
					name: "Origin",
					value: "Apple"
					}
				]
			},
		{
			id: 0,
			name: "IPhone 4S",
			price: 200,
			rate: 4.6,
			image: "img/img.png",
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
			id: 0,
			name: "IPhone 5",
			price: 500,
			rate: 4.5,
			image: "img/img.png",
			more: [
				{
					name: "Origin",
					value: "Apple"
					}
				]
			}
		];
}
