<?php
namespace IntlRandString;

/*
 * initial targets
 * english, spanish, french, dutch, italian, russian, german, greek
 */

abstract class Charset {
   /**
    * Enumerates the charset's upper-case letter characters.
    * @return string[] array containing each lower-case letter character
    */
   abstract static public function enumUpperLetters() : array;
   /**
    * Enumerates the charset's lower-case letter characters.
    * @return string[] array containing each lower-case letter character
    */
   abstract static public function enumLowerLetters() : array;
   /**
    * Enumerates the charset's digit numeral characters.
    * @return string[] array containing each digit numeral character
    */
   abstract static public function enumDigits() : array;
   
   /**
    * Generates a random string
    * 
    * @param int $length number of characters in random string
    * 
    * @throws \IntlRandString\InvalidLength length must be greater than 0
    */
   final public function randomString(int $length) : string {
      if ($length<1) {
         throw new InvalidLength;
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
   /**
    * @param int $charset_flags:  Specify potential characters that are included in generated random strings. 
    * Upper-case letters, lower-case letters, and digit numeral characters are included by default. 
    * <ul>
    *    <li><b>\IntlRandString\Charset::FLAG_UPPER_LETTERS</b> include upper-case letter characters</li>
    *    <li><b>\IntlRandString\Charset::FLAG_UPPER_LETTERS</b> include lower-case letter characters</li>
    *    <li><b>\IntlRandString\Charset::FLAG_UPPER_LETTERS</b> include digit numeral characters</li>
    * </ul>
    */
   final public function __construct(int $charset_flags=self::FLAG_UPPER_LETTERS | self::FLAG_LOWER_LETTERS | self::FLAG_DIGITS) {
      if ($charset_flags & static::FLAG_UPPER_LETTERS) {
         $this->charset = array_merge($this->charset,static::enumUpperLetters());
      }
      if ($charset_flags & static::FLAG_LOWER_LETTERS) {
         $this->charset = array_merge($this->charset,static::enumLowerLetters());
      }
      if ($charset_flags & static::FLAG_DIGITS) {
         $this->charset = array_merge($this->charset,static::enumDigits());
      }
      if (!count($this->charset)) {
         throw new InvalidCharsetFlags;
      }
   }

}