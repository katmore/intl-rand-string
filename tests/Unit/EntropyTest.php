<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class EntropyTest extends TestCase {
   
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
   
   public static function calcEntropy($string,int $scale) :string {
      $h='0';
      $size = strlen($string);
      foreach (count_chars($string, 1) as $v) {
         $p = bcdiv((string) $v, (string) $size,$scale);
         $h = bcsub($h,bcdiv(bcmul($p,(string) log((float)$p),$scale),(string)log(2),$scale),$scale);
      }
      return  $h;
   }
   
   const RANDOM_STRING_DATA_COUNT = 1000;
   public function minEntropyDataProvider() : array {
      return [
         [2,'0.97'],
         [10,'2.57'],
         [15,'2.98'],
         [20,'3.10'],
         [100,'4.10']
      ];
   }
   
   /**
    * @dataProvider minEntropyDataProvider
    */
   public function testRandomStringEntropy(int $length, string $minimumEntropy) {
      foreach($this->enumCharsetClassNames() as $className) {
         $charset = new $className;
         
         $totalEntropy = '0';
         for($i=1;$i<static::RANDOM_STRING_DATA_COUNT;$i++) {
            $randomString = $charset->randomString($length);
            $totalEntropy = bcadd(static::calcEntropy($randomString,4),$totalEntropy,4);
         }
         $averageEntropy = bcdiv($totalEntropy,(string)static::RANDOM_STRING_DATA_COUNT,4);
         $comp = bccomp($minimumEntropy, $averageEntropy);
         $this->assertNotEquals(1, $comp,"average entropy $averageEntropy on charset $className");
      }
      unset($className);
   }
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
}