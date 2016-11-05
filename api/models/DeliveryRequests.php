<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DeliveryRequests
 *
 * @author Samir
 */
class DeliveryRequests {

    private $id, $deliveryManID, $orderID, $dueDate;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getDeliveryManID() {
        return $this->deliveryManID;
    }

    function getOrderID() {
        return $this->orderID;
    }

    function getDueDate() {
        return $this->dueDate;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setDeliveryManID($deliveryManID) {
        $this->deliveryManID = $deliveryManID;
    }

    function setOrderID($orderID) {
        $this->orderID = $orderID;
    }

    function setDueDate($dueDate) {
        $this->dueDate = $dueDate;
    }

}
