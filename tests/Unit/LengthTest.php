<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use IntlRandString\Charset;

final class LengthTest extends TestCase {
   
   const ROOT_NAMESPACE = 'IntlRandString';
   const CHARSET_NAMESPACE = self::ROOT_NAMESPACE.'/Charset';
   const CHARSET_CLASS_ROOT = __DIR__.'/../../src/'.self::CHARSET_NAMESPACE;
   const CHARSET_PARENT_SHORTNAME = 'Charset';
   const CHARSET_PARENT_CLASS = self::ROOT_NAMESPACE.'/'.self::CHARSET_PARENT_SHORTNAME;
   
   public function enumCharsetClassNames() : array {
      $className = [];
      foreach(scandir(static::CHARSET_CLASS_ROOT) as $filename) {
         
         if (pathinfo($filename,\PATHINFO_EXTENSION)!=='php') {
            continue;
         }
         
         $path = static::CHARSET_CLASS_ROOT."/$filename";
         $oldErrorReporting = error_reporting(0);
         $fp = fopen($path, 'r');
         $class = $buffer = '';
         $i = 0;
         while (!$class) {
            if (feof($fp)) break;
            
            $buffer .= fread($fp, 512);
            $tokens = token_get_all($buffer);
            
            if (strpos($buffer, '{') === false) continue;
            
            for (;$i<count($tokens);$i++) {
               if ($tokens[$i][0] === T_CLASS) {
                  for ($j=$i+1;$j<count($tokens);$j++) {
                     if ($tokens[$j] === '{') {
                        $class = $tokens[$i+2][1];
                     }
                  }
               }
            }
         }
         error_reporting($oldErrorReporting);
         $class = str_replace("/","\\",static::CHARSET_NAMESPACE)."\\$class";
         
         if (class_exists($class)) {
            $r = new \ReflectionClass($class);
            if ($r->isInstantiable() && $r->isSubclassOf(str_replace("/","\\",static::CHARSET_PARENT_CLASS))) {
               $className[]=$class;
            }
         }
      }
      unset($filename);
      return $className;
   }
   
   const LENGTH_DATA_COUNT = 1000;
   
   public function lengthDataProvider() : array {
      $dataSet = [];
      $dataSetCeil = static::LENGTH_DATA_COUNT+1;
      for($i=1;$i<$dataSetCeil;$i++) {
         $dataSet []= [$i];
      }
      return $dataSet;
   }
   
   /**
    * @dataProvider lengthDataProvider
    */
   public function testRandomStringLength(int $length) {

      foreach($this->enumCharsetClassNames() as $className) {
         $charset = new $className;
         $randomString = $charset->randomString($length);
         $this->assertEquals($length, mb_strlen($randomString),"length on charset $className: $randomString");

      }
      unset($charset);
   }
   
}