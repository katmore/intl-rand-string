<?php
namespace IntlRandString;

use RuntimeException;

class InvalidLength extends RuntimeException {
   public function getReason() {
      return $this->reason;
   }
   /**
    * @var string
    */
   private $reason;
   public function __construct(string $reason) {
      $this->reason = $reason;
      parent::__construct("Invalid length: $reason");
   }
}