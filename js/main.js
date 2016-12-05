// ==========================================================================================================
/*	Constants	 */
// ==========================================================================================================

const TOP_CATEGOIRES_COUNT = 4; // The number of top categories to be shown in the main page

// ==========================================================================================================
/*	Data Models	 */
// ==========================================================================================================

function categoryModel(category) {
    var self = this;
    self[CATEGORIES_FLD_ID] = category[CATEGORIES_FLD_ID];
    self[CATEGORIES_FLD_NAME] = category[CATEGORIES_FLD_NAME];
    if (category.products) {
        self.products = category.products;
    }
    self.selected = ko.observable(false);
}

function productSpecModel(productSpec) {
    this[PRODUCT_SPEC_FLD_ID] = ko.observable(productSpec[PRODUCT_SPEC_PSID]);
    this[CATEGORIES_SPEC_FLD_NAME] = ko.observable(productSpec[PRODUCT_SPEC_CSNAME]);
    this[PRODUCT_SPEC_FLD_VALUE] = ko.observable(productSpec[PRODUCT_SPEC_PSVALUE]);
}

function productModel(product) {
    var self = this;
    self[PRODUCTS_FLD_ID] = product[PRODUCTS_FLD_ID];
    self[PRODUCTS_FLD_NAME] = ko.observable(product[PRODUCTS_FLD_NAME]);
    self[PRODUCTS_FLD_PRICE] = ko.observable(product[PRODUCTS_FLD_PRICE]);
    self[PRODUCTS_FLD_RATE] = product[PRODUCTS_FLD_RATE];
    self[PRODUCTS_FLD_IMAGE] = ko.observable(product[PRODUCTS_FLD_IMAGE]);
    self[PRODUCTS_FLD_AVA_QUANTITY] = ko.observable(product[PRODUCTS_FLD_AVA_QUANTITY]);
    self[PRODUCTS_FLD_EARNINGS] = ko.observable(product[PRODUCTS_FLD_EARNINGS]);
    self[PRODUCTS_FLD_SOLDITEMS] = ko.observable(product[PRODUCTS_FLD_SOLDITEMS]);
    self[PRODUCTS_FLD_SIZE] = ko.observable(product[PRODUCTS_FLD_SIZE]);
    self[PRODUCTS_FLD_WEIGHT] = ko.observable(product[PRODUCTS_FLD_WEIGHT]);
    self[PRODUCTS_FLD_AVAILABILITY_ID] = ko.observable(product[PRODUCTS_FLD_AVAILABILITY_ID]);
    self[PRODUCTS_FLD_ORIGIN] = ko.observable(product[PRODUCTS_FLD_ORIGIN]);
    self[PRODUCTS_FLD_PROVIDER] = ko.observable(product[PRODUCTS_FLD_PROVIDER]);
    self[PRODUCTS_FLD_SELLER_ID] = ko.observable(product[PRODUCTS_FLD_SELLER_ID]);
    self[PRODUCTS_FLD_CATEGORY_ID] = ko.observable(product[PRODUCTS_FLD_CATEGORY_ID]);
    self[PRODUCTS_FLD_DESCRIPTION] = ko.observable(product[PRODUCTS_FLD_DESCRIPTION]);
    self[PRODUCT_SELLER_NAME] = ko.observable(product[PRODUCT_SELLER_NAME]);
    self[PRODUCT_CATEGORY_NAME] = ko.observable(product[PRODUCT_CATEGORY_NAME]);
    self[PRODUCT_AVAILABILITY_STATUS] = ko.observable(product[PRODUCT_AVAILABILITY_STATUS]);

    self.userRate = ko.observable(((product.userrate) ? parseFloat(product.userrate) : 0));
    self.quantity = ko.observable(((product.quantity) ? parseInt(product.quantity) : 0));

    self.more = ko.observableArray();
    product.more.forEach(function (more) {
        self.more.push(new productSpecModel(more));
    });
    // Add less important details to more array
    var extraData = [PRODUCTS_FLD_ORIGIN, PRODUCTS_FLD_PROVIDER, PRODUCTS_FLD_SIZE, PRODUCTS_FLD_WEIGHT, PRODUCTS_FLD_AVA_QUANTITY, PRODUCTS_FLD_SOLDITEMS, PRODUCTS_FLD_DESCRIPTION];
    if (checkUserRole() == USER_SELLER) {
        extraData.unshift(PRODUCTS_FLD_EARNINGS);
    }
    for (var data in extraData) {
        var moreData = {};

        if (extraData[data] == 'available_quantity')
            moreData[PRODUCT_SPEC_CSNAME] = 'Available Quantity';
        else if (extraData[data] == 'solditems')
            moreData[PRODUCT_SPEC_CSNAME] = 'Sold Items';
        else if (extraData[data] == 'earnings')
            continue;
        else
            moreData[PRODUCT_SPEC_CSNAME] = extraData[data];
        moreData[PRODUCT_SPEC_PSVALUE] = self[extraData[data]]();
        self.more.push(new productSpecModel(moreData));
    }

    self.isMoreDivVisible = ko.observable(false);
}

function orderModel(order) {
    var self = this;
    self[ORDERS_ID] = order[ORDERS_ID];
    self[ORDERS_ISSUEDATE] = order[ORDERS_ISSUEDATE];
    self[ORDERS_COST] = order[ORDERS_COST];
    self[ORDERS_STATUS_ID] = order[ORDERS_STATUS_ID];
    self.products = order.products;
    self.isMoreDivVisible = ko.observable(false);
    self.textStatus = ko.computed(function () {
        if (self[ORDERS_STATUS_ID] == ORDER_STATUS_PENDING)
            return "Pending";
        if (self[ORDERS_STATUS_ID] == ORDER_STATUS_PICKED)
            return "Picked";
        if (self[ORDERS_STATUS_ID] == ORDER_STATUS_SHIPPED)
            return "Shipped";
        if (self[ORDERS_STATUS_ID] == ORDER_STATUS_DELIVERED)
            return "Delivered";
        else
            return "Error";
    });
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
        console.log(item);
        sammyApp.setLocation('#/' + item[CATEGORIES_FLD_ID]);
    };

    shouter.subscribe(function (newCategoryID) {
        if (newCategoryID == 0) {
            self.selectOneCategory(null);
        } else {
            self.categoriesArray.forEach(function (category, index) {
                if (category[CATEGORIES_FLD_ID] == newCategoryID) {
                    self.selectOneCategory(category);
                    params.categoryID(category[CATEGORIES_FLD_ID]);
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
    self.categoriesArray = [];
    self.topCategoriesArray = ko.observableArray();

    self.changeProductsViewContent = function (categoryID) {
        self.topCategoriesArray.removeAll();

        if (categoryID == 0) {
            var topCategories = getTopCategories();
            topCategories.forEach(function (topCategory) {
                topCategory['products'].forEach(function (product, index) {
                    topCategory.products[index] = new productModel(product);
                });
                self.topCategoriesArray.push(new categoryModel(topCategory));
            });
        } else {
            var category = {};
            category[CATEGORIES_FLD_ID] = categoryID;
            self.categoriesArray.forEach(function (cate) {
                if (cate[CATEGORIES_FLD_ID] == categoryID)
                    category[CATEGORIES_FLD_NAME] = cate[CATEGORIES_FLD_NAME];
            });
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
        getCategoriesArray().forEach(function (category) {
            self.categoriesArray.push(new categoryModel(category));
        });
        self.changeProductsViewContent(params.categoryID());
        // handle categoryID changed from the leftpanel module
        shouter.subscribe(function (newCategoryID) {
            var found = false;
            self.categoriesArray.forEach(function (category) {
                if (category[CATEGORIES_FLD_ID] == newCategoryID) {
                    found = true;
                }
            });
            if (found)
                params.categoryID(newCategoryID);
            else
                params.categoryID(0);
            self.changeProductsViewContent(params.categoryID());
        }, self, "changedCategoryID");
    }();

    self.expandCategory = function (item) {
        sammyApp.setLocation('#/' + item[CATEGORIES_FLD_ID]);
    }

}

function searchProductsViewModel(params) {
    var self = this;
    self.params = params.value;
    self.params.searchWord = params.searchWord;
    self.searchProductsArray = ko.observableArray();
    self.search = function (word) {
        if (word.length <= 0)
            return;
        self.searchProductsArray.removeAll();
        var products = getSearchProducts(word);
        products.forEach(function (product) {
            self.searchProductsArray.push(new productModel(product));
        });
    };

    shouter.subscribe(function (newSearchWord) {
        self.search(newSearchWord);
    }, self, "newSearchWord");

    self.init = function () {
        self.search(self.params.searchWord());
    }();

}

function cartProductsViewModel(params) {
    var self = this;
    self.cartProductsArray = ko.observableArray();
    self.init = function () {
        var cartProducts = getCartProducts();
        var cartTotalAmount = 0;
        cartProducts.forEach(function (product) {
            cartTotalAmount += product[PRODUCTS_FLD_PRICE] * product.quantity;
            self.cartProductsArray.push(new productModel(product));
        });
        onlineMarketMVVM.increaseCartAmount(cartTotalAmount);
    }();

    self.cancelProduct = function (product) {
        if (confirm("Do you want to remove this product for sure ?")) {
            if (cancelProductInCart(product.params[PRODUCTS_FLD_ID])) {
                self.cartProductsArray.remove(product.params);
                return true;
            }
        }
    }

    self.checkoutOrder = function () {
        if (confirm("Are you sure, the amount will be withdrawed from your CC ?")) {
            var newOrder = addOrder();
            if (newOrder && newOrder[ORDERS_ID] > 0) {
                var cartProducts = [];
                ko.utils.arrayForEach(self.cartProductsArray(), function (product, index) {
                    cartProducts.push(product);
                });
                newOrder.products = cartProducts;

                shouter.notifySubscribers("", "refreshOrdersList");
                //				window.location = WEBSITE_LINK;
                return true;
            }
        }
        return false;
    }

    shouter.subscribe(function (newProduct) {
        var added = false;
        ko.utils.arrayForEach(self.cartProductsArray(), function (product, index) {
            if (newProduct[CATEGORIES_FLD_ID] == product[CATEGORIES_FLD_ID]) {
                product.quantity(product.quantity() + 1);
                added = true;
            }
        });
        if (!added) {
            newProduct.quantity(1);
            self.cartProductsArray.push(newProduct);
        }
    }, self, "addProductToCart");
}

function productViewModel(params) {
    var self = this;
    self.params = params.value;
    self.params.cart = params.cart;
    self.params.order = params.order;

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
        var cartItemId = addProductToCart(product.params[PRODUCTS_FLD_ID]);
        if (cartItemId != -1) {
            onlineMarketMVVM.increaseCartAmount(product.params[PRODUCTS_FLD_PRICE]());
            product.params.cartItemID = cartItemId;
            shouter.notifySubscribers(product.params, "addProductToCart");
        }
    }

    self.increaseQuantity = function () {
        var status = {
            added: false
        };
        addProductToCart(self.params[PRODUCTS_FLD_ID], status)
        if (status.added) {
            self.params.quantity(self.params.quantity() + 1);
            return true;
        }
    }
    self.decreaseQuantity = function () {
        if (self.params.quantity() > 1) {
            if (decreaseProductInCart(self.params[PRODUCTS_FLD_ID])) {
                self.params.quantity(self.params.quantity() - 1);
                return true;
            }
        }
    }

    self.increaseRate = function () {
        if (self.params.userRate() + 0.5 <= 5 && updateRate(self.params[PRODUCTS_FLD_ID], self.params.userRate() + 0.5)) {
            self.params.userRate(self.params.userRate() + 0.5);
            // notify other orders to update existing same products
            var newData = {};
            newData[PRODUCTS_FLD_ID] = self.params[PRODUCTS_FLD_ID];
            newData[RATE_FLD_RATE] = self.params.userRate();
            shouter.notifySubscribers(newData, "rateChanged");

            return true;
        }
        return false;
    }
    self.decreaseRate = function () {
        if (self.params.userRate() - 0.5 >= 0 && updateRate(self.params[PRODUCTS_FLD_ID], self.params.userRate() - 0.5)) {
            self.params.userRate(self.params.userRate() - 0.5);
            // notify other orders to update existing same products
            var newData = {};
            newData[PRODUCTS_FLD_ID] = self.params[PRODUCTS_FLD_ID];
            newData[RATE_FLD_RATE] = self.params.userRate();
            shouter.notifySubscribers(newData, "rateChanged");

            return true;
        }
        return false;
    }
}

function profileViewModel(params) {
    var self = this;
    self.ordersArray = ko.observableArray();
    self.userModel = getUserModel();

    shouter.subscribe(function (newData) {
        ko.utils.arrayForEach(self.ordersArray(), function (order, index) {
            order.productsArray.forEach(function (product) {
                if (product[PRODUCTS_FLD_ID] == newData[PRODUCTS_FLD_ID]) {
                    product.userRate(newData[RATE_FLD_RATE]);
                }
            });
        });
    }, self, "rateChanged");


    self.init = function () {
        var orders = getUserOrders();
        orders.forEach(function (order) {
            self.ordersArray.push(new orderModel(order));
        });
    };
    self.init();

    shouter.subscribe(function (dummy) {
        self.ordersArray.removeAll();
        self.init();
        sammyApp.setLocation('#/profile');
    }, self, "refreshOrdersList");



    self.deleteOrder = function (order) {
        console.log(order.params);
        self.ordersArray.remove(order.params);
    }
    self.saveProfile = function () {
        var data = {};
        data[USERS_FLD_NAME] = self.userModel[USERS_FLD_NAME]();
        data[USERS_FLD_TEL] = self.userModel[USERS_FLD_TEL]();
        data[USERS_FLD_PASS1] = self.userModel.currentPass();
        data[USERS_FLD_PASS2] = self.userModel.newPass();
        data[BUYERS_FLD_ADDRESS] = self.userModel[BUYERS_FLD_ADDRESS]();
        data[BUYERS_FLD_CCNUMBER] = self.userModel[BUYERS_FLD_CCNUMBER]();
        data[BUYERS_FLD_CC_CCV] = self.userModel[BUYERS_FLD_CC_CCV]();
        data[BUYERS_FLD_CC_MONTH] = self.userModel[BUYERS_FLD_CC_MONTH]();
        data[BUYERS_FLD_CC_YEAR] = self.userModel[BUYERS_FLD_CC_YEAR]();

        $.ajax({
            url: API_LINK + USER_ENDPOINT,
            type: 'PUT',
            data: data,
            async: false,
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
            },
            success: function (result) {
                var returnedData = JSON.parse(result);
                if (returnedData.statusCode == USER_EDIT_ACCOUNT_SUCCESSFUL) {
                    alert(returnedData.result);
                    localStorage.setItem(OMARKET_PREFIX + USERS_FLD_NAME, data[USERS_FLD_NAME]);
                    localStorage.setItem(OMARKET_PREFIX + USERS_FLD_TEL, data[USERS_FLD_TEL]);
                    localStorage.setItem(OMARKET_PREFIX + BUYERS_FLD_ADDRESS, data[BUYERS_FLD_ADDRESS]);
                    localStorage.setItem(OMARKET_PREFIX + BUYERS_FLD_CCNUMBER, data[BUYERS_FLD_CCNUMBER]);
                    localStorage.setItem(OMARKET_PREFIX + BUYERS_FLD_CC_CCV, data[BUYERS_FLD_CC_CCV]);
                    localStorage.setItem(OMARKET_PREFIX + BUYERS_FLD_CC_YEAR, data[BUYERS_FLD_CC_YEAR]);
                    localStorage.setItem(OMARKET_PREFIX + BUYERS_FLD_CC_MONTH, data[BUYERS_FLD_CC_MONTH]);

                } else {
                    alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                }
            },
            fail: function (result) {
                alert("Please try again later!");
            }
        });
        return localStorage.getItem(OMARKET_PREFIX + USERS_FLD_NAME);
    }
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
        console.log("ORDER", self.params);
        var products = self.params.products;
        console.log(self.params);
        products.forEach(function (product) {
            self.params.totalItemsCount += 1;
            self.params.productsArray.push(new productModel(product));
        });
    }();
    self.cancel = function () {
        if (confirm("Are you sure?"))
            return deleteOrder(self.params[ORDERS_ID]);
    }
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

    self.searchWord.subscribe(function (newSearchWord) {
        shouter.notifySubscribers(newSearchWord, "newSearchWord");
    });

    // Utils functions
    self.increaseCartAmount = function (price) {
        self.cartAmount(self.cartAmount() + parseFloat(price));
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
            if (checkIfSignedIn() && checkUserRole() == USER_BUYER)
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
    var ret = [];
    $.ajax({
        url: API_LINK + PRODUCTS_ENDPOINT + "/search/" + searchWord,
        type: 'GET',
        async: false,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
        },
        success: function (result) {
            console.log(result);
            var returnedData = JSON.parse(result);
            if (returnedData.statusCode == PRODUCT_GET_FROM_KEY_SUCCESS) {
                ret = returnedData.result;
            } else {
                alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
            }
        },
        fail: function (result) {
            alert("Please try again later!");
        }
    });
    return ret;
}

/**
 * This function returns the user model
 * @returns {object} user model
 */
function getUserModel() {
    var user = {};
    user[USERS_FLD_NAME] = ko.observable(localStorage.getItem(OMARKET_PREFIX + USERS_FLD_NAME));
    user[USERS_FLD_EMAIL] = ko.observable(localStorage.getItem(OMARKET_PREFIX + USERS_FLD_EMAIL));
    user[USERS_FLD_USER_TYPE] = ko.observable(localStorage.getItem(OMARKET_PREFIX + USERS_FLD_USER_TYPE));
    user[USERS_FLD_TEL] = ko.observable(localStorage.getItem(OMARKET_PREFIX + USERS_FLD_TEL));
    user[USERS_FLD_STATUS] = ko.observable(localStorage.getItem(OMARKET_PREFIX + USERS_FLD_STATUS));

    if (user[USERS_FLD_USER_TYPE]() == USER_BUYER) {
        user[BUYERS_FLD_ADDRESS] = ko.observable(localStorage.getItem(OMARKET_PREFIX + BUYERS_FLD_ADDRESS));
        user[BUYERS_FLD_CCNUMBER] = ko.observable(localStorage.getItem(OMARKET_PREFIX + BUYERS_FLD_CCNUMBER));
        user[BUYERS_FLD_CC_CCV] = ko.observable(localStorage.getItem(OMARKET_PREFIX + BUYERS_FLD_CC_CCV));
        user[BUYERS_FLD_CC_MONTH] = ko.observable(localStorage.getItem(OMARKET_PREFIX + BUYERS_FLD_CC_MONTH));
        user[BUYERS_FLD_CC_YEAR] = ko.observable(localStorage.getItem(OMARKET_PREFIX + BUYERS_FLD_CC_YEAR));
    } else if (user[USERS_FLD_USER_TYPE]() == USER_SELLER) {
        user[SELLERS_FLD_ADDRESS] = ko.observable(localStorage.getItem(OMARKET_PREFIX + SELLERS_FLD_ADDRESS));
        user[SELLERS_FLD_BANK_ACCOUNT] = ko.observable(localStorage.getItem(OMARKET_PREFIX + SELLERS_FLD_BANK_ACCOUNT));
    }

    // Observables to control forms
    user.currentPass = ko.observable("");
    user.newPass = ko.observable("");
    user.changePass = ko.observable(false);
    user.isBuyer = ko.observable(true);
    return user;
}

/**
 * This function returns user orders
 * @returns {Array} user orders
 */
function getUserOrders() {
    var ret = [];
    $.ajax({
        url: API_LINK + ORDERS_ENDPOINT,
        type: 'GET',
        async: false,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
        },
        success: function (result) {
            console.log("DATA", result);

            var returnedData = JSON.parse(result);

            if (returnedData.statusCode == ORDERS_GET_SUCCESSFUL) {
                ret = returnedData.result;
            } else {
                alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
            }
        },
        fail: function (result) {
            alert("Please try again later!");
        }
    });
    return ret;
}

/**
 * This function retrieves all the the categories from the server
 * @returns {Array} Categories array -> contains categories objects
 */
function getCategoriesArray() {
    var ret = [];
    $.ajax({
        url: API_LINK + CATEGORY_ENDPOINT,
        type: 'GET',
        async: false,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
        },
        success: function (result) {
            console.log(result);
            var returnedData = JSON.parse(result);
            if (returnedData.statusCode == CATEGORY_GET_ALL_CATEGORIES_SUCCESS) {
                ret = returnedData.result;
            } else {
                alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
            }
        },
        fail: function (result) {
            alert("Please try again later!");
        }
    });
    return ret;
}
/**
 * This function gets the top categories from the server
 * @returns {Array}  top categories array
 */
function getTopCategories() {
    var ret = [];
    $.ajax({
        url: API_LINK + PRODUCTS_ENDPOINT + "/top",
        type: 'GET',
        async: false,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
        },
        success: function (result) {
            console.log(result);
            var returnedData = JSON.parse(result);
            if (returnedData.statusCode == PRODUCT_GET_TOP_3_IN_4_CAT_SUCCESS) {
                ret = returnedData.result;
            } else {
                alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
            }
        },
        fail: function (result) {
            alert("Please try again later!");
        }
    });
    return ret;
}
/**
 * This function gets top products of the required id
 * @param   {number} categoryID required category id to get its top products
 * @returns {Array}  [[Description]]
 */
function getCategoryProducts(categoryID, limit) {
    if (categoryID != 0) {
        var ret = [];
        $.ajax({
            url: API_LINK + PRODUCTS_ENDPOINT + "/" + categoryID,
            type: 'GET',
            async: false,
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
            },
            success: function (result) {
                console.log(result);
                var returnedData = JSON.parse(result);
                if (returnedData.statusCode == PRODUCTS_GET_ALL_PRODUCTS_SUCCESS) {
                    ret = returnedData.result;
                } else {
                    alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                }
            },
            fail: function (result) {
                alert("Please try again later!");
            }
        });
        return ret;
    }
}

/**
 * This function returns the user current cart products
 * @returns {Array} cart products
 */
function getCartProducts() {
    var ret = [];
    $.ajax({
        url: API_LINK + USER_CART_ENDPOINT,
        type: 'GET',
        async: false,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
        },
        success: function (result) {
            console.log(result);
            var returnedData = JSON.parse(result);
            if (returnedData.statusCode == CART_GET_ITEMS_SUCCESSFUL) {
                ret = returnedData.result;
            } else {
                alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
            }
        },
        fail: function (result) {
            alert("Please try again later!");
        }
    });
    return ret;
}

/**
 * This function adds product to cart items
 * @param {int} productID the required product ID
 * @returns {object} response which contains status and cartItem ID
 */
function addProductToCart(productID, status = {}) {
    var newCartID = -1;
    var data = {};
    data[PRODUCTS_FLD_ID] = productID;
    $.ajax({
        url: API_LINK + USER_CART_ENDPOINT,
        type: 'POST',
        async: false,
        data: data,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
        },
        success: function (result) {
            console.log(result);
            var returnedData = JSON.parse(result);
            if (returnedData.statusCode == CART_ADD_ITEM_SUCCESSFUL) {
                newCartID = returnedData.result;
                status.added = true;
            } else if (returnedData.statusCode == CART_ADD_ITEM_USER_BANNED) {
                alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                logOut();
            } else {
                alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
            }
        },
        fail: function (result) {
            alert("Please try again later!");
        },
        statusCode: {
            401: function () {
                alert("Authentication required!");
                logOut();
            },
            400: function () {
                alert("Bad request!");
                logOut();
            }
        }
    });
    return newCartID;
}


/**
 * This function decreases product in cart
 * @param {int} productID the required product ID
 * @returns {boolean} true if decreased
 */
function decreaseProductInCart(productID) {
    var decreased = false;
    var data = {};
    data[PRODUCTS_FLD_ID] = productID;
    $.ajax({
        url: API_LINK + USER_CART_ENDPOINT,
        type: 'PUT',
        async: false,
        data: data,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
        },
        success: function (result) {
            console.log(result);
            var returnedData = JSON.parse(result);
            if (returnedData.statusCode == CART_DECREASE_ITEM_SUCCESSFUL) {
                decreased = true;
            } else if (returnedData.statusCode == USER_STATUS_BANNED) {
                alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                logOut();
            } else {
                alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
            }
        },
        fail: function (result) {
            alert("Please try again later!");
        },
        statusCode: {
            401: function () {
                alert("Authentication required!");
                logOut();
            },
            400: function () {
                alert("Bad request!");
                logOut();
            }
        }
    });
    return decreased;
}


/**
 * This function decreases product in cart
 * @param {int} productID the required product ID
 * @returns {boolean} true if decreased
 */
function cancelProductInCart(productID) {
    var deleted = false;
    $.ajax({
        url: API_LINK + USER_CART_ENDPOINT + "/" + productID,
        type: 'DELETE',
        async: false,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
        },
        success: function (result) {
            console.log(result);
            var returnedData = JSON.parse(result);
            if (returnedData.statusCode == CART_DELETE_ITEM_SUCCESSFUL) {
                deleted = true;
            } else if (returnedData.statusCode == USER_STATUS_BANNED) {
                alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                logOut();
            } else {
                alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
            }
        },
        fail: function (result) {
            alert("Please try again later!");
        },
        statusCode: {
            401: function () {
                alert("Authentication required!");
                logOut();
            },
            400: function () {
                alert("Bad request!");
                logOut();
            }
        }
    });
    return deleted;
}

/**
 * This function adds order to db
 * @returns {object} order object
 */
function addOrder() {
    var ret = {};
    $.ajax({
        url: API_LINK + ORDER_ENDPOINT,
        type: 'POST',
        async: false,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
        },
        success: function (result) {
            console.log(result);
            var returnedData = JSON.parse(result);
            if (returnedData.statusCode == ORDERS_ADD_SUCCESS) {
                ret = returnedData.result;
            } else if (returnedData.statusCode == USER_STATUS_BANNED) {
                alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                logOut();
            } else {
                alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
            }
        },
        fail: function (result) {
            alert("Please try again later!");
        },
        statusCode: {
            401: function () {
                alert("Authentication required!");
                logOut();
            },
            400: function () {
                alert("Bad request!");
                logOut();
            }
        }
    });
    return ret;
}


/**
 * This function deletes order
 * @param {int} orderID the required order ID
 * @returns {boolean} true if decreased
 */
function deleteOrder(orderID) {
    var deleted = false;
    $.ajax({
        url: API_LINK + ORDER_ENDPOINT + "/" + orderID,
        type: 'DELETE',
        async: false,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
        },
        success: function (result) {
            console.log(result);
            var returnedData = JSON.parse(result);
            if (returnedData.statusCode == ORDERS_DELETE_SUCCESS) {
                deleted = true;
            } else if (returnedData.statusCode == USER_STATUS_BANNED) {
                alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                logOut();
            } else {
                alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
            }
        },
        fail: function (result) {
            alert("Please try again later!");
        },
        statusCode: {
            401: function () {
                alert("Authentication required!");
                logOut();
            },
            400: function () {
                alert("Bad request!");
                logOut();
            }
        }
    });
    return deleted;
}


/**
 * This function updates product rate in db
 * @param   {integer} productID product id to be updated
 * @param   {float}   rate      new rate value
 * @returns {boolean} true if updated
 */
function updateRate(productID, rate) {
    var updated = false;
    var data = {};
    data[RATE_FLD_PRODUCT_ID] = productID;
    data[RATE_FLD_RATE] = rate;
    $.ajax({
        url: API_LINK + RATE_ENDPOINT,
        type: 'PUT',
        async: false,
        data: data,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
        },
        success: function (result) {
            console.log(result);
            var returnedData = JSON.parse(result);
            if (returnedData.statusCode == RATE_UPDATE_SUCCESS) {
                updated = true;
            } else if (returnedData.statusCode == USER_STATUS_BANNED) {
                alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                logOut();
            } else {
                alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
            }
        },
        fail: function (result) {
            alert("Please try again later!");
        },
        statusCode: {
            401: function () {
                alert("Authentication required!");
                logOut();
            },
            400: function () {
                alert("Bad request!");
                logOut();
            }
        }
    });
    return updated;
}
