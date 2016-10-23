// ==========================================================================================================
/*	Constants	 */
// ==========================================================================================================

const TOP_CATEGOIRES_COUNT = 4; // The number of top categories to be shown in the main page

// ==========================================================================================================
/*	Data Models	 */
// ==========================================================================================================

function categoryModel(category) {
	var self = this;
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
	self.isMoreDivVisible = ko.observable(false);
}

// ==========================================================================================================
/*	View Models	 */
// ==========================================================================================================

var onlineMarketMVVM;


function leftMenuViewModel() {

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
		//Reset all categories to not selected
		categoriesArray.forEach(function (category, index) {
			category.selected(false);
		});
		item.selected(true);
	};
}

function topCategoriesViewModel() {

	self.topCategoriesArray = [];
	/**
		This function initializes the categoriesArray
	*/
	self.init = function () {
		// Get top categories and top produdcts in every category from the API and add them to array
		var topCategories = getTopCategories(TOP_CATEGOIRES_COUNT);

		topCategories.forEach(function (topCategory) {
			var topProducts = getCategoryTopProducts();
			topProducts.forEach(function (topProduct) {
				topCategory.products.push(new productModel(topProduct));
			});
			self.topCategoriesArray.push(new categoryModel(topCategory));
		});
	}();
}

function productViewModel() {

	/**
	 * This function handles product load more click
	 * @param {object} item  clicked category
	 * @param {object} event click event
	 */
	self.loadMoreClick = function (item, event) {
		item.isMoreDivVisible(true);
	}

	/**
	 * This function handles product add to cart click
	 * @param {object} item  clicked category
	 * @param {object} event click event
	 */
	self.addToCart = function (item, event) {
		onlineMarketMVVM.increaseCartAmount(item.price);
	}
}

function headerViewModel(self) {

	self.cartAmount = ko.observable(0.0);

	self.checkIfSignedIn = function () {
		// should check the localstorage
		return true;
	}
}

function onlineMarketViewModel() {
	var self = this;

	self.leftMenuMVVM = new leftMenuViewModel(self);

	self.headerMVVM = new headerViewModel(self);

	self.topCategoriesMVVM = new topCategoriesViewModel(self);

	self.productMVVM = new productViewModel(self);

	self.increaseCartAmount = function (price) {
		self.cartAmount(self.cartAmount() + price);
	}
}

// ==========================================================================================================
/*	App  Logic	 */
// ==========================================================================================================

onlineMarketMVVM = new onlineMarketViewModel();
ko.applyBindings(onlineMarketMVVM);

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
			id: 0,
			name: "Computers, IT & Networking"
		},
		{
			id: 0,
			name: "Mobile Phones, Tablets & Accessories"
		},
		{
			id: 0,
			name: "Car Electronics & Accessories"
		},
		{
			id: 0,
			name: "Books"
		},
		{
			id: 0,
			name: "Gaming"
		},
		{
			id: 0,
			name: "Electronic"
		},
		{
			id: 0,
			name: "Sports & Fitness"
		},
		{
			id: 0,
			name: "Perfumes & Fragrances"
		},
		{
			id: 0,
			name: "Health & Personal Care"
		},
		{
			id: 0,
			name: "Furniture"
		},
		{
			id: 0,
			name: "Apparel, Shoes & Accessories"
		},
		{
			id: 0,
			name: "Appliances"
		},
		{
			id: 0,
			name: "Art, Crafts & Collectables"
		},
		{
			id: 0,
			name: "Baby"
		},
		{
			id: 0,
			name: "Kitchen & Home Supplies"
		},
		{
			id: 0,
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
			id: 0,
			name: "Computers, IT & Networking",
			products: []
		},
		{
			id: 0,
			name: "Mobile Phones, Tablets & Accessories",
			products: []
		},
		{
			id: 0,
			name: "Books",
			products: []
		},
		{
			id: 0,
			name: "Furniture",
			products: []
		}
	];
}

/**
 * This function gets top products of the required id
 * @param   {number} categoryID required category id to get its top products
 * @returns {Array}  [[Description]]
 */
function getCategoryTopProducts(categoryID) {
	//dummy data till the API is ready
	return [
		{
			id: 0,
			name: "IPhone 6S",
			price: 500,
			rate: 4.5,
			image: "https://upload.wikimedia.org/wikipedia/commons/thumb/1/18/IPhone_7_Jet_Black.svg/2000px-IPhone_7_Jet_Black.svg.png",
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
			image: "https://upload.wikimedia.org/wikipedia/commons/thumb/1/18/IPhone_7_Jet_Black.svg/2000px-IPhone_7_Jet_Black.svg.png",
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
			image: "https://upload.wikimedia.org/wikipedia/commons/thumb/1/18/IPhone_7_Jet_Black.svg/2000px-IPhone_7_Jet_Black.svg.png",
			more: [
				{
					name: "Origin",
					value: "Apple"
				}
			]
		}
	];
}
