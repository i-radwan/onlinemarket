<?php

/**
 * This interface contains the functions to be implemented in SQLOperations class
 * @author ibrahimradwan
 */
interface SQLOperationsInterface {

    // =========================================================================================================
    //                                          USERS FUNCTIONS
    // =========================================================================================================

    function signUpUser($email, $pass1, $pass2, $role, $name, $tel, $extraData);

    function login($emial, $pass);

    function editAccount($userID, $userType, $userNewData);

    function getUsersUsingType($userType);

    function deleteUser($userID);

    function addEmployee($data);

    function changeUserStatus($userID, $newStatus);

    function addProductToCart($productId, $userID);

    function removeProductFromCart($productID, $userID);

    function decreaseProductFromCart($productID, $userID);
    
    public function getAllOrders($selectionCols, $appliedFilters, $userID = "");

    public function getOrder($id, $selectionCols);

    public function addOrder($buyerId, $cost, $dueDate, $status = "Pending");

    public function deleteOrder($id);

    public function updateOrder($id, $status, $userID = -1);

    public function getOrderItems($orderID, $buyerID);

    public function getDeliveryRequests($deliveryManID);
    
    public function addCategory($name);
    
    public function deleteCategory($id);
    
    public function selectCategory($id);
    
    public function updateCategory($id, $name);
    
    public function getAllCategories();
    
    public function addRate($buyerId, $productId, $rate);
    
    public function getAvgRate($productId);
    
    public function getProductRate($productId, $buyerId);
    
    public function addCategorySpec($categoryId, $name);
    
    public function updateCategorySpec($id, $categoryId, $name);

    public function deleteCategorySpec($id);

    public function getCategorySpec($id);

    public function getAllProducts();

    public function getAllCategories();

    public function updateProductSpec($id, $productId, $catId, $spec);

    public function addProductSpec($productId, $specs);

    public function deleteProductSpec($productId);

    public function getProductSpec($productId);

    public function addProduct($name, $price, $rate, $size, $weight, $availability_id, $available_quantity, $origin, $provider, $image, $seller_id, $category_id, $solditems);

    public function updateProduct($id, $name, $price, $rate, $size, $weight, $availability_id, $available_quantity, $origin, $provider, $image, $seller_id, $category_id, $solditems);

    public function deleteProduct($productId, $seller_id);

    public function getTotalEarnings($productId, $sellerId);

    public function getProductBySeller($sellerId);

    public function getProductByCategory($catId);

    public function getProductByKey($key);

    public function getTop3In4Cat();

}
