<?php
/**
 * This class models the real-world products
 *
 * @author ibrahimradwan
 */
class Product {
   private $name;
   
   function getName() {
       return $this->name;
   }

   function setName($name) {
       $this->name = $name;
   }
   
}
