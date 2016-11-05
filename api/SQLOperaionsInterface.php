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
    public function getAllOrders($selectionCols, $userID = "");

    public function getOrder($id, $selectionCols);

    public function addOrder($buyerId, $cost, $dueDate, $status = "Pending");

    public function deleteOrder($id);

    public function updateOrder($id, $buyerId, $cost, $date, $status);

    public function getOrderItems($orderID, $buyerID);

    public function getDeliveryRequests($deliveryManID);
}
