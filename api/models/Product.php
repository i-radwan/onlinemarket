<?php
/**
 * This class models the real-world products
 *
 * @author ibrahimradwan
 */
class Product  implements \JsonSerializable {
   private $name;
   
   function getName() {
       return $this->name;
   }

   function setName($name) {
       $this->name = $name;
   }
    public function jsonSerialize() {
        $vars = get_object_vars($this);

        return $vars;
    }

}
