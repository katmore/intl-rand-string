<?php
namespace IntlRandString;

use RuntimeException;

class InvalidCharsetFlags extends RuntimeException {
   /**
    * @return int number of potential characters resulting from specified charset flags
    */
   public function getPotentialChars() : int {
      return $this->potentialChars;
   }
   /**
    * @var int
    */
   private $potentialChars;
   /**
    * @param int $potential_chars number of potential characters resulting from specified charset flags
    */
   public function __construct(int $potential_chars) {
      parent::__construct("charset flags resulted in only $potential_chars potential characters");
   }
}