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
class Order implements \JsonSerializable {

    private $id, $date, $statusId, $cost, $buyerId;

    //Constructor
    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getDate() {
        return $this->date;
    }

    function getStatusId() {
        return $this->statusId;
    }

    function getCost() {
        return $this->cost;
    }

    function getBuyerId() {
        return $this->buyerId;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setDate($date) {
        $this->date = $date;
    }

    function setStatusId($statusId) {
        $this->statusId = $statusId;
    }

    function setCost($cost) {
        $this->cost = $cost;
    }

    function setBuyerId($buyerId) {
        $this->buyerId = $buyerId;
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
                case Constants::ORDERS_DATE:
                    $this->setDate($row[$value]);
                    break;
                case Constants::ORDERS_ID:
                    $this->setId($row[$value]);
                    break;
                case Constants::ORDERS_STATUS_ID:
                    $this->setStatusId($row[$value]);
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
