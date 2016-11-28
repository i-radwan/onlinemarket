<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Order
 *
 * @author Samir
 */
class Order implements \JsonSerializable {    function getStatus_id() {
        return $this->status_id;
    }

    function getBuyer_id() {
        return $this->buyer_id;
    }

    function getProducts() {
        return $this->products;
    }

    function setStatus_id($status_id) {
        $this->status_id = $status_id;
    }

    function setBuyer_id($buyer_id) {
        $this->buyer_id = $buyer_id;
    }

    function setProducts($products) {
        $this->products = $products;
    }

    
    private $_id, $status_id, $cost, $buyer_id, $issuedate, $products;
    function getIssueDate() {
        return $this->issuedate;
    }

    function setIssueDate($issueDate) {
        $this->issuedate = $issueDate;
    }

        //Constructor
    function __construct() {
        
    }

    function getId() {
        return $this->_id;
    }

    function getStatusId() {
        return $this->status_id;
    }

    function getCost() {
        return $this->cost;
    }

    function getBuyerId() {
        return $this->buyer_id;
    }

    function setId($id) {
        $this->_id = $id;
    }

    function setStatusId($statusId) {
        $this->status_id = $statusId;
    }

    function setCost($cost) {
        $this->cost = $cost;
    }

    function setBuyerId($buyerId) {
        $this->buyer_id = $buyerId;
    }

    function setAttributes($row, $theArray) {

        foreach ($theArray as $value) {
            switch ($value) {
                case Constants::ORDERS_BUYER_ID:
                    $this->setBuyerId($row[$value]);
                    break;
                case Constants::ORDERS_COST:
                    $this->setCost($row[$value]);
                    break;
                case Constants::ORDERS_ID:
                    $this->setId($row[$value]);
                    break;
                case Constants::ORDERS_STATUS_ID:
                    $this->setStatusId($row[$value]);
                    break;
                 case Constants::ORDERS_ISSUE_DATE:
                    $this->setIssueDate($row[$value]);
                    break;
                default:
                    break;
            }
        }
    }

    public function jsonSerialize() {
        $vars = get_object_vars($this);
        return $vars;
    }

}
