<?php
namespace IntlRandString;

use RuntimeException;

class InvalidLength extends RuntimeException {
   public function __construct() {
      parent::__construct("length must be greater than 0");
   }
}