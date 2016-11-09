<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OrderItems
 *
 * @author Samir
 */
class OrderItems {

    private $orderID, $productID, $sellerID, $quantity;
    
    function __construct() {
        
    }

    function getOrderID() {
        return $this->orderID;
    }

    function getProductID() {
        return $this->productID;
    }

    function getSellerID() {
        return $this->sellerID;
    }

    function getQuantity() {
        return $this->quantity;
    }

    function setOrderID($orderID) {
        $this->orderID = $orderID;
    }

    function setProductID($productID) {
        $this->productID = $productID;
    }

    function setSellerID($sellerID) {
        $this->sellerID = $sellerID;
    }

    function setQuantity($quantity) {
        $this->quantity = $quantity;
    }

}
