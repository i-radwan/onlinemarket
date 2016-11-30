// ==========================================================================================================
/*	Constants	 */
// ==========================================================================================================

// ==========================================================================================================
/*	Data Models	 */
// ==========================================================================================================
function productSpecModel(productSpec) {
    this[PRODUCT_SPEC_FLD_ID] = ko.observable(productSpec[PRODUCT_SPEC_PSID]);
    this[CATEGORIES_SPEC_FLD_NAME] = ko.observable(productSpec[PRODUCT_SPEC_CSNAME]);
    this[PRODUCT_SPEC_FLD_VALUE] = ko.observable(productSpec[PRODUCT_SPEC_PSVALUE]);
}

function productModel(product, allCategoires) {
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
        else
            moreData[PRODUCT_SPEC_CSNAME] = extraData[data];
        moreData[PRODUCT_SPEC_PSVALUE] = self[extraData[data]]();
        self.more.push(new productSpecModel(moreData));
    }

    // Temp values for product editing
    self[PRODUCTS_FLD_NAME + 'Tmp'] = ko.observable(product[PRODUCTS_FLD_NAME]);
    self[PRODUCTS_FLD_PRICE + 'Tmp'] = ko.observable(product[PRODUCTS_FLD_PRICE]);
    self[PRODUCTS_FLD_RATE + 'Tmp'] = product[PRODUCTS_FLD_RATE];
    self[PRODUCTS_FLD_IMAGE + 'Tmp'] = ko.observable(product[PRODUCTS_FLD_IMAGE]);
    self[PRODUCTS_FLD_AVA_QUANTITY + 'Tmp'] = ko.observable(product[PRODUCTS_FLD_AVA_QUANTITY]);
    self[PRODUCTS_FLD_SIZE + 'Tmp'] = ko.observable(product[PRODUCTS_FLD_SIZE]);
    self[PRODUCTS_FLD_WEIGHT + 'Tmp'] = ko.observable(product[PRODUCTS_FLD_WEIGHT]);
    self[PRODUCTS_FLD_AVAILABILITY_ID + 'Tmp'] = ko.observable(product[PRODUCTS_FLD_AVAILABILITY_ID]);
    self[PRODUCTS_FLD_ORIGIN + 'Tmp'] = ko.observable(product[PRODUCTS_FLD_ORIGIN]);
    self[PRODUCTS_FLD_PROVIDER + 'Tmp'] = ko.observable(product[PRODUCTS_FLD_PROVIDER]);
    self[PRODUCTS_FLD_CATEGORY_ID + 'Tmp'] = ko.observable(product[PRODUCTS_FLD_CATEGORY_ID]);
    self[PRODUCTS_FLD_DESCRIPTION + 'Tmp'] = ko.observable(product[PRODUCTS_FLD_DESCRIPTION]);

    self[CATEGORIES_SPEC + 'Tmp'] = ko.observableArray();
    product.more.forEach(function (more) {
        var newPSM = new productSpecModel(more);
        newPSM.tmpValue = ko.observable(more[PRODUCT_SPEC_PSVALUE]);
        self[CATEGORIES_SPEC + 'Tmp'].push(newPSM);
    });

    self.isMoreDivVisible = ko.observable(false);
    self.editMode = ko.observable(false);
}

function categoryModel(category) {
    var self = this;
    self[CATEGORIES_FLD_ID] = category[CATEGORIES_FLD_ID];
    self[CATEGORIES_FLD_NAME] = ko.observable(category[CATEGORIES_FLD_NAME]);
    self[CATEGORIES_SPEC] = ko.observableArray();
    self.tmpName = ko.observable(category[CATEGORIES_FLD_NAME]);
    self.editMode = ko.observable(false);
    self.expanded = ko.observable(false);
}

function categorySpecModel(categorySpec) {
    var self = this;
    self[CATEGORIES_SPEC_FLD_ID] = categorySpec[CATEGORIES_SPEC_FLD_ID];
    self[CATEGORIES_SPEC_FLD_NAME] = ko.observable(categorySpec[CATEGORIES_SPEC_FLD_NAME]);
    self[CATEGORIES_SPEC_FLD_CATID] = categorySpec[CATEGORIES_SPEC_FLD_CATID];
    self.tmpName = ko.observable(categorySpec[CATEGORIES_SPEC_FLD_NAME]);
    self.editMode = ko.observable(false);
}


//function categorySpecModel(categoySpec) {
//	this.id = ko.observable(categoySpec.id);
//	this.name = ko.observable(categoySpec.name);
//	this.value = ko.observable("");
//}

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
    self[ORDERS_ISSUEDATE] = order[ORDERS_ISSUEDATE];
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
    //new product vars
    self.newProduct = {};
    self.newProduct[PRODUCTS_FLD_NAME] = ko.observable("");
    self.newProduct[PRODUCTS_FLD_PRICE] = ko.observable();
    self.newProduct[PRODUCTS_FLD_SIZE] = ko.observable("");
    self.newProduct[PRODUCTS_FLD_AVA_QUANTITY] = ko.observable();
    self.newProduct[PRODUCTS_FLD_WEIGHT] = ko.observable();
    self.newProduct[PRODUCTS_FLD_ORIGIN] = ko.observable("");
    self.newProduct[PRODUCTS_FLD_PROVIDER] = ko.observable("");
    self.newProduct[PRODUCTS_FLD_IMAGE] = ko.observable("");
    self.newProduct[PRODUCTS_FLD_DESCRIPTION] = ko.observable("");
    self.newProduct.category = ko.observable();
    self.newProduct.category[CATEGORIES_SPEC] = ko.observableArray();
    self.newProduct.category.subscribe(function (category) {
        self.newProduct.category[CATEGORIES_SPEC].removeAll();
        category[CATEGORIES_SPEC].forEach(function (categorySpec) {
            var newCateSpecModel = new categorySpecModel(categorySpec);
            newCateSpecModel[PRODUCT_SPEC_FLD_VALUE] = ko.observable("");
            self.newProduct.category[CATEGORIES_SPEC].push(newCateSpecModel);
        });
    });

    self.init = function () {
        var products = getAllProducts();
        products.forEach(function (product) {
            self.productsArray.push(new productModel(product, self.allCategories));
        });
    }();
    self.deleteProduct = function (item, event) {
        if (confirm("Are you sure?")) {
            if (deleteProduct(item.params[PRODUCTS_FLD_ID])) {
                self.productsArray.remove(item.params);
            }
        }
    };

    self.addNewProduct = function () {
        var data = {};
        data[PRODUCTS_FLD_NAME] = self.newProduct[PRODUCTS_FLD_NAME]();
        data[PRODUCTS_FLD_PRICE] = self.newProduct[PRODUCTS_FLD_PRICE]();
        data[PRODUCTS_FLD_SIZE] = self.newProduct[PRODUCTS_FLD_SIZE]();
        data[PRODUCTS_FLD_WEIGHT] = self.newProduct[PRODUCTS_FLD_WEIGHT]();
        data[PRODUCTS_FLD_AVA_QUANTITY] = self.newProduct[PRODUCTS_FLD_AVA_QUANTITY]();
        data[PRODUCTS_FLD_ORIGIN] = self.newProduct[PRODUCTS_FLD_ORIGIN]();
        data[PRODUCTS_FLD_PROVIDER] = self.newProduct[PRODUCTS_FLD_PROVIDER]();
        data[PRODUCTS_FLD_IMAGE] = self.newProduct[PRODUCTS_FLD_IMAGE]();
        data[PRODUCTS_FLD_DESCRIPTION] = self.newProduct[PRODUCTS_FLD_DESCRIPTION]();
        if (self.newProduct.category()) {
            data[PRODUCTS_FLD_CATEGORY_ID] = self.newProduct.category()[CATEGORIES_FLD_ID];

            data[CATEGORIES_SPEC] = [];
            self.newProduct.category[CATEGORIES_SPEC]().forEach(function (cateSpec) {
                var productSpec = {};
                productSpec[CATEGORIES_SPEC_FLD_ID] = cateSpec[CATEGORIES_SPEC_FLD_ID];
                productSpec[CATEGORIES_SPEC_FLD_NAME] = cateSpec[CATEGORIES_SPEC_FLD_NAME]();
                productSpec[PRODUCT_SPEC_FLD_VALUE] = cateSpec[PRODUCT_SPEC_FLD_VALUE]();
                data[CATEGORIES_SPEC].push(productSpec);
            });
            var newProductID = addProduct(data);
            if (newProductID > 0) {
                window.location = ADMIN_LINK;
            }
        } else {
            alert('Please select category!');
        }

    };
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
    };

    self.save = function () {
        if (confirm("Are you sure?")) {
            var data = {};
            data[PRODUCTS_FLD_ID] = self.params[PRODUCTS_FLD_ID];
            data[PRODUCTS_FLD_NAME] = self.params[PRODUCTS_FLD_NAME + 'Tmp']();
            data[PRODUCTS_FLD_PRICE] = self.params[PRODUCTS_FLD_PRICE + 'Tmp']();
            data[PRODUCTS_FLD_IMAGE] = self.params[PRODUCTS_FLD_IMAGE + 'Tmp']();
            data[PRODUCTS_FLD_AVA_QUANTITY] = self.params[PRODUCTS_FLD_AVA_QUANTITY + 'Tmp']();
            data[PRODUCTS_FLD_SIZE] = self.params[PRODUCTS_FLD_SIZE + 'Tmp']();
            data[PRODUCTS_FLD_WEIGHT] = self.params[PRODUCTS_FLD_WEIGHT + 'Tmp']();
            data[PRODUCTS_FLD_AVAILABILITY_ID] = self.params[PRODUCTS_FLD_AVAILABILITY_ID + 'Tmp']();
            data[PRODUCTS_FLD_ORIGIN] = self.params[PRODUCTS_FLD_ORIGIN + 'Tmp']();
            data[PRODUCTS_FLD_PROVIDER] = self.params[PRODUCTS_FLD_PROVIDER + 'Tmp']();
            data[PRODUCTS_FLD_DESCRIPTION] = self.params[PRODUCTS_FLD_DESCRIPTION + 'Tmp']();
            data[PRODUCTS_FLD_AVAILABILITY_ID] = self.params[PRODUCTS_FLD_AVAILABILITY_ID + 'Tmp']();
            data['more'] = [];
            ko.utils.arrayForEach(self.params[CATEGORIES_SPEC + 'Tmp'](), function (spec, index) {
                var newPSM = {};
                newPSM[PRODUCT_SPEC_FLD_ID] = spec[PRODUCT_SPEC_FLD_ID]();
                newPSM[CATEGORIES_SPEC_FLD_NAME] = spec[CATEGORIES_SPEC_FLD_NAME]();
                newPSM[PRODUCT_SPEC_FLD_VALUE] = spec.tmpValue();
                data['more'].push(newPSM);
            });

            if (editProduct(data)) {
                window.location = ADMIN_LINK;
            }
        }
    };
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
                try {
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
                        alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                    }
                } catch (e) {
                    alert("Please try again later!");
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
            var newCategoryModel = new categoryModel(category);
            category[CATEGORIES_SPEC].forEach(function (categorySpec) {
                newCategoryModel[CATEGORIES_SPEC].push(new categorySpecModel(categorySpec));
            });
            self.categoriesArray.push(newCategoryModel);
        });
    }();

    self.removeCategory = function (item, event) {
        if (confirm("Are you sure?")) {
            if (deleteCategory(item.params[CATEGORIES_FLD_ID]))
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
            if (category[CATEGORIES_FLD_NAME]().trim() == self.newCategoryName().trim()) {
                isUnique = false;
            }
        });
        if (isUnique) {
            var newID = addCategory(self.newCategoryName().trim());
            if (newID > 0) {
                var categorymodel = {};
                categorymodel[CATEGORIES_FLD_ID] = newID;
                categorymodel[CATEGORIES_FLD_NAME] = self.newCategoryName().trim();

                self.categoriesArray.push(new categoryModel(categorymodel));
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
    self.newCategorySpecName = ko.observable("");
    self.init = function () {}();
    self.save = function (item, event) {
        if (item.params.tmpName().trim().length > 0) {
            var isUnique = true;
            ko.utils.arrayForEach(self.parent.categoriesArray(), function (category, index) {
                if (category[CATEGORIES_FLD_NAME]().trim() == item.params.tmpName().trim() && (category != item.params)) {
                    isUnique = false;
                }
            });
            if (isUnique) {
                if (editCategory(item.params[CATEGORIES_FLD_ID], item.params.tmpName())) {
                    item.params[CATEGORIES_FLD_NAME](item.params.tmpName().trim());
                    item.params.editMode(false);
                }
            } else {
                alert("Please choose different unique name!");
            }
        } else {
            alert("Please enter name!");
        }
    }
    self.removeCategorySpec = function (item) {
        if (confirm("Are you sure?")) {
            if (deleteCategorySpec(item[CATEGORIES_SPEC_FLD_ID]))
                self.params[CATEGORIES_SPEC].remove(item);
        }
    }
    self.addNewCategorySpec = function () {
        if (!self.newCategorySpecName() || self.newCategorySpecName().trim().length == 0) {
            alert("Please enter non-empty category specification name!");
            return;
        }
        var isUnique = true;
        ko.utils.arrayForEach(self.params[CATEGORIES_SPEC](), function (categorySpec, index) {
            if (categorySpec[CATEGORIES_SPEC_FLD_NAME]().trim() == self.newCategorySpecName().trim()) {
                isUnique = false;
            }
        });
        if (isUnique) {
            var newID = addCategorySpec(self.newCategorySpecName().trim(), self.params[CATEGORIES_FLD_ID]);
            if (newID > 0) {
                var categorySpecmodel = {};
                categorySpecmodel[CATEGORIES_SPEC_FLD_ID] = newID;
                categorySpecmodel[CATEGORIES_SPEC_FLD_NAME] = self.newCategorySpecName().trim();
                categorySpecmodel[CATEGORIES_SPEC_FLD_CATID] = self.params[CATEGORIES_FLD_ID];
                self.params[CATEGORIES_SPEC].push(new categorySpecModel(categorySpecmodel));
                self.newCategorySpecName("");
            }
        } else {
            alert("Please choose different unique name!");
        }
    }
    self.saveCategorySpec = function (item) {
        if (item.tmpName().trim().length > 0) {
            var isUnique = true;
            ko.utils.arrayForEach(self.params[CATEGORIES_SPEC](), function (categorySpec, index) {
                if (categorySpec[CATEGORIES_SPEC_FLD_NAME]().trim() == item.tmpName().trim() && (categorySpec != item)) {
                    isUnique = false;
                }
            });
            if (isUnique) {
                if (editCategorySpec(item[CATEGORIES_SPEC_FLD_ID], item[CATEGORIES_SPEC_FLD_CATID], item.tmpName())) {
                    item[CATEGORIES_SPEC_FLD_NAME](item.tmpName().trim());
                    item.editMode(false);
                }
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
    };

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
    };

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
            if (order && order[ORDERS_ID] == deliveredOrderID) {
                console.log("TEST", order, order[ORDERS_ID], deliveredOrderID);
                self.ordersArray.remove(order);
            } else {
                console.log(order);
            }
        });
    }, self, "removeDeliveredOrder");

}

function singleOrderViewModel(params) {
    var self = this;
    self.params = params.value;
    self.changeOrderStatus = function (orderStatus) {
        if (changeOrderStatus(self.params[DELIVERYREQUESTS_ORDER_ID], orderStatus)) {
            self.params[ORDERS_STATUS_ID](orderStatus);
            if (orderStatus == ORDER_STATUS_DELIVERED)
                shouter.notifySubscribers(self.params[ORDERS_ID], "removeDeliveredOrder");
        }
    };
}

function controlPanelViewModel() {
    if (checkIfSignedIn() && (checkUserRole() == USER_ADMIN || checkUserRole() == USER_ACCOUNTANT || checkUserRole() == USER_DELIVERYMAN || checkUserRole() == USER_SELLER)) {
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
    var ret = [];
    $.ajax({
        url: API_LINK + PRODUCT_ENDPOINT,
        type: 'GET',
        async: false,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
        },
        success: function (result) {
            try {
                var returnedData = JSON.parse(result);
                if (returnedData.statusCode == PRODUCTS_GET_ALL_PRODUCTS_SUCCESS) {
                    ret = returnedData.result;
                } else {
                    alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                }
            } catch (e) {
                alert("Please try again later!");
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
        url: API_LINK + ORDER_ENDPOINT + "/" + orderID,
        type: 'PUT',
        async: false,
        data: data,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
        },
        success: function (result) {
            console.log("DATA", result);
            try {
                var returnedData = JSON.parse(result);
                if (returnedData.statusCode == ORDERS_UPDATE_SUCCESS) {
                    statusChanged = true;
                } else {
                    alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                }
            } catch (e) {
                alert("Please try again later!");
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
            try {
                var returnedData = JSON.parse(result);
                if (returnedData.statusCode == DELIVERYREQUESTS_GET_SUCCESSFUL) {
                    ret = returnedData.result;
                } else {
                    alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                }
            } catch (e) {
                alert("Please try again later!");
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
 * This function returns total filtered orders
 * @param string filters : filters to be applied
 * @returns {Array} user orders
 */
function getOrders(filters) {
    var ret = [];
    $.ajax({
        url: API_LINK + ORDERS_ENDPOINT + "?filters=" + filters,
        type: 'GET',
        async: false,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
        },
        success: function (result) {
            try {
                var returnedData = JSON.parse(result);
                if (returnedData.statusCode == ORDERS_GET_SUCCESSFUL) {
                    ret = returnedData.result;
                } else {
                    alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                }
            } catch (e) {
                alert("Please try again later!");
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
            try {
                var returnedData = JSON.parse(result);
                if (returnedData.statusCode == USER_GET_USERS_SUCCESSFUL) {
                    ret = returnedData.result;
                } else {
                    alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                }
            } catch (e) {
                alert("Please try again later!");
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
            try {
                var returnedData = JSON.parse(result);
                if (returnedData.statusCode == USER_UPDATE_STATUS_SUCCESSFUL) {
                    statusChanged = true;
                } else {
                    alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                }
            } catch (e) {
                alert("Please try again later!");
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
            try {
                var returnedData = JSON.parse(result);
                if (returnedData.statusCode == USER_DELETE_SUCCESSFUL) {
                    deleted = true;
                } else {
                    alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                }
            } catch (e) {
                alert("Please try again later!");
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
            try {
                var returnedData = JSON.parse(result);
                if (returnedData.statusCode == USER_INSERT_SUCCESSFUL) {
                    newID = returnedData.result;
                } else {
                    alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                }
            } catch (e) {
                alert("Please try again later!");
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
            try {
                var returnedData = JSON.parse(result);
                if (returnedData.statusCode == USER_EDIT_ACCOUNT_SUCCESSFUL) {
                    edited = true;
                    alert(returnedData.result);
                } else {
                    alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                }
            } catch (e) {
                alert("Please try again later!");
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
            try {
                var returnedData = JSON.parse(result);
                if (returnedData.statusCode == CATEGORY_ADD_SUCCESS) {
                    newID = returnedData.result;
                } else {
                    alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                }
            } catch (e) {
                alert("Please try again later!");
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
    return newID;
}

/**
 * This function updates category name
 * @param   {int}  id  category id
 * @param   {string}  name new category name
 * @returns {boolean} true if updated
 */
function editCategory(id, name) {
    var updated = false;
    var data = {};
    data[CATEGORIES_FLD_NAME] = name;
    data[CATEGORIES_FLD_ID] = id;
    $.ajax({
        url: API_LINK + CATEGORY_ENDPOINT,
        type: 'PUT',
        async: false,
        data: data,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
        },
        success: function (result) {
            try {
                var returnedData = JSON.parse(result);
                if (returnedData.statusCode == CATEGORY_UPDATE_SUCCESS) {
                    updated = true;
                } else {
                    alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                }
            } catch (e) {
                alert("Please try again later!");
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


/**
 * This function deletes category with its ID
 * @param   {number}  cateID Category ID
 * @returns {boolean} True if deleted successfully
 */
function deleteCategory(cateID) {
    var deleted = false;
    $.ajax({
        url: API_LINK + CATEGORY_ENDPOINT + "/" + cateID,
        type: 'DELETE',
        async: false,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
        },
        success: function (result) {
            try {
                var returnedData = JSON.parse(result);
                if (returnedData.statusCode == CATEGORY_DELETE_SUCCESS) {
                    deleted = true;
                } else {
                    alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                }
            } catch (e) {
                alert("Please try again later!");
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
 * This function adds category spec to db
 * @param   {string}  name new category spec name
 * @param   {int}  cateID new category spec category id
 * @returns {int} new category spec ID, -1 if operation failed
 */
function addCategorySpec(name, cateID) {
    var newID = -1;
    var data = {};
    data[CATEGORIES_SPEC_FLD_NAME] = name;
    data[CATEGORIES_SPEC_FLD_CATID] = cateID;
    $.ajax({
        url: API_LINK + CATEGORY_SPEC_ENDPOINT,
        type: 'POST',
        async: false,
        data: data,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
        },
        success: function (result) {
            console.log(result);
            try {
                var returnedData = JSON.parse(result);
                if (returnedData.statusCode == CATEGORY_SPECS_ADD_SUCCESS) {
                    newID = returnedData.result;
                } else {
                    alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                }
            } catch (e) {
                alert("Please try again later!");
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
    return newID;
}

/**
 * This function updates category spec name
 * @param   {int}  id  category spec id
 * @param   {int}  catID  category id
 * @param   {string}  name new category spec name
 * @returns {boolean} true if updated
 */
function editCategorySpec(id, cateID, name) {
    var updated = false;
    var data = {};
    data[CATEGORIES_SPEC_FLD_NAME] = name;
    data[CATEGORIES_SPEC_FLD_ID] = id;
    data[CATEGORIES_SPEC_FLD_CATID] = cateID;
    $.ajax({
        url: API_LINK + CATEGORY_SPEC_ENDPOINT,
        type: 'PUT',
        async: false,
        data: data,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
        },
        success: function (result) {
            console.log(result);
            try {
                var returnedData = JSON.parse(result);
                if (returnedData.statusCode == CATEGORY_SPEC_UPDATE_SUCCESS) {
                    updated = true;
                } else {
                    alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                }
            } catch (e) {
                alert("Please try again later!");
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
/**
 * This function deletes category with its ID
 * @param   {number}  specID Specification ID
 * @param   {number}  cateID Category ID
 * @returns {boolean} True if deleted successfully
 */
function deleteCategorySpec(specID) {
    var deleted = false;
    $.ajax({
        url: API_LINK + CATEGORY_SPEC_ENDPOINT + "/" + specID,
        type: 'DELETE',
        async: false,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
        },
        success: function (result) {
            console.log(result);
            try {
                var returnedData = JSON.parse(result);
                if (returnedData.statusCode == CATEGORY_SPEC_DELETE_SUCCESS) {
                    deleted = true;
                } else {
                    alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                }
            } catch (e) {
                alert("Please try again later!");
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
            try {
                var returnedData = JSON.parse(result);
                if (returnedData.statusCode == CATEGORY_GET_ALL_CATEGORIES_SUCCESS) {
                    ret = returnedData.result;
                } else {
                    alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                }
            } catch (e) {
                alert("Please try again later!");
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
 * This function adds product to db
 * @param   {object}   data all product data
 * @returns {int} new product ID, -1 if operation failed
 */
function addProduct(data) {
    var newID = -1;
    $.ajax({
        url: API_LINK + PRODUCT_ENDPOINT,
        type: 'POST',
        async: false,
        data: data,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
        },
        success: function (result) {
            console.log(result);
            try {
                var returnedData = JSON.parse(result);
                if (returnedData.statusCode == PRODUCT_ADD_SUCCESS) {
                    newID = returnedData.result;
                } else if (returnedData.statusCode == USER_STATUS_BANNED) {
                    alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                    logOut();
                } else {
                    alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                }
            } catch (e) {
                alert("Please try again later!");
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
    return newID;
}



/**
 * This function saves edited product to db
 * @param   {object}   data all product data
 * @returns {boolean} true if operation is done
 */
function editProduct(data) {
    var edited = false;
    $.ajax({
        url: API_LINK + PRODUCT_ENDPOINT,
        type: 'PUT',
        async: false,
        data: data,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
        },
        success: function (result) {
            console.log(result);
            try {
                var returnedData = JSON.parse(result);
                if (returnedData.statusCode == PRODUCT_UPDATE_SUCCESS) {
                    edited = true;
                } else if (returnedData.statusCode == USER_STATUS_BANNED) {
                    alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                    logOut();
                } else {
                    alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                }
            } catch (e) {
                alert("Please try again later!");
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
    return edited;
}

/**
 * This function deletes product with its ID
 * @param   {number}  productID product ID
 * @returns {boolean} True if deleted successfully
 */
function deleteProduct(productID) {
    var deleted = false;
    $.ajax({
        url: API_LINK + PRODUCT_ENDPOINT + "/" + productID,
        type: 'DELETE',
        async: false,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem(OMARKET_JWT)
        },
        success: function (result) {
            console.log(result);
            try {
                var returnedData = JSON.parse(result);
                if (returnedData.statusCode == PRODUCT_DELETE_SUCCESS) {
                    deleted = true;
                } else if (returnedData.statusCode == USER_STATUS_BANNED) {
                    alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                    logOut();
                } else {
                    alert(returnedData.errorMsg + "[" + returnedData.statusCode + "]");
                }
            } catch (e) {
                alert("Please try again later!");
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
