<?php
namespace IntlRandString;

/*
 * initial targets
 * english, spanish, french, dutch, italian, russian, german, greek
 */

abstract class Charset {
   
   abstract static public function enumUpperLetters() : array;
   abstract static public function enumLowerLetters() : array;
   abstract static public function enumDigits() : array;
   
   /**
    * 
    * @param int $length number of characters in random string
    * 
    * @throws \IntlRandString\InvalidLength
    */
   final public function randomString(int $length) : string {
      if ($length<1) {
         throw new InvalidLength("must be greater than 0");
      }
      $charsetCeil = count($this->charset)-1;
      $string = "";
      for($i=0;$i<$length;$i++) {
         $string .= $this->charset[random_int(0,$charsetCeil)];
      }
      return $string;
   }
   
   const FLAG_UPPER_LETTERS = 1;
   const FLAG_LOWER_LETTERS = 2;
   const FLAG_DIGITS = 4;
   
   /**
    * @var array
    */
   private $charset = [];
   final public function __construct(int $flags=self::FLAG_UPPER_LETTERS | self::FLAG_LOWER_LETTERS | self::FLAG_DIGITS) {
      if ($flags & static::FLAG_UPPER_LETTERS) {
         $this->charset = array_merge($this->charset,static::enumUpperLetters());
      }
      if ($flags & static::FLAG_LOWER_LETTERS) {
         $this->charset = array_merge($this->charset,static::enumLowerLetters());
      }
      if ($flags & static::FLAG_DIGITS) {
         $this->charset = array_merge($this->charset,static::enumDigits());
      }
   }

}