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

    public function updateOrder($id, $status);

    public function getOrderItems($orderID, $buyerID);

    public function getDeliveryRequests($deliveryManID);
    
    public function addCategory($name);
    
    public function deleteCategory($id);
    
    public function selectCategory($id);
    
    public function updateCategory($id, $name);
    
    public function addRate($buyerId, $productId, $rate);
    
    public function getAvgRate($productId);
    
    public function getProductRate($productId, $buyerId);
    
    public function addCategorySpec($categoryId, $name);
}
