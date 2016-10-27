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

}

function headerViewModel(params) {
	var self = this;

	self.cartAmount = params.cartAmount;

	self.checkIfSignedIn = function () {
		// should check the localstorage
		return true;
	}

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

	// Utils functions
	self.increaseCartAmount = function (price) {
		self.cartAmount(self.cartAmount() + price);
	}

	self.changeContentVisibility = function (isCartVisible, isMainContentVisible, isProfileVisible) {
		onlineMarketMVVM.isCartVisible(isCartVisible);
		onlineMarketMVVM.isMainContentVisible(isMainContentVisible);
		onlineMarketMVVM.isProfileVisible(isProfileVisible);
	}
}

// ==========================================================================================================
/*	App  Logic	 */
// ==========================================================================================================
var shouter = new ko.subscribable();


onlineMarketMVVM = new onlineMarketViewModel();
ko.applyBindings(onlineMarketMVVM);


// Routing
var sammyApp;
(function ($) {

	sammyApp = $.sammy('#content', function () {
		this.get('#/', function (context) {
			onlineMarketMVVM.changeContentVisibility(false, true, false);
			shouter.notifySubscribers('0', "changedCategoryID");
		});
		this.get('#/cart', function (context) {
			onlineMarketMVVM.changeContentVisibility(true, false, false);
		});
		this.get('#/profile', function (context) {
			onlineMarketMVVM.changeContentVisibility(false, false, true);
		});
		this.get('#/:categoryID', function (context) {
			onlineMarketMVVM.changeContentVisibility(false, true, false);

			var self = this;
			setTimeout(function () {
				shouter.notifySubscribers(self.params['categoryID'], "changedCategoryID");
			}, 50);
		});

	});


	$(function () {
		sammyApp.run('#/');
	});


})(jQuery);

// ==========================================================================================================
/*	API	Requests */
// ==========================================================================================================

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
	//	return [
	//		{
	//			id: 0,
	//			name: "Computers, IT & Networking",
	//			products: []
	//		}
	//	];
}

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
