#!/usr/bin/env php
<?php
use IntlRandString\Charset;
use IntlRandString\InvalidCharsetFlags;

exit((new class () {
   
   const DEFAULT_LENGTH = 12;
   const DEFAULT_CHARSET_NAME = 'English';
   
   const VENDOR_AUTOLOAD = __DIR__.'/../vendor/autoload.php';
   const BIN_VENDOR_AUTOLOAD = __DIR__.'/../../../autoload.php';
   
   const ROOT_NAMESPACE = 'IntlRandString';
   const CHARSET_PARENT_SHORTNAME = 'Charset';
   const CHARSET_PARENT_CLASS = self::ROOT_NAMESPACE.'/'.self::CHARSET_PARENT_SHORTNAME;
   const CHARSET_NAMESPACE = self::ROOT_NAMESPACE.'/Charset';
   const CHARSET_CLASS_ROOT = __DIR__.'/../../src/'.self::CHARSET_NAMESPACE;
   
   const ME_DESC = 'random string generator';
   const ME_NAME = 'rand-string';
   const ME_USAGE = '[-h]|[--charset(=English)][<charset options...>] <LENGTH>';
   const ME_HELP =<<<ME_HELP
options:
  -h: Print a help message and exit.
  --charset=<CHARSET-NAME>
    Optionally specify the charset name. 
    Default: English

charset options:
  --no-upper-letters
    Do not include upper-case characters.
  --no-lower-letters
    Do not include lower-case letter characters.
  --no-digits
    Do not include digit numeral characters.
  --only-upper-letters
    Only include upper-case characters.
    Cannot be used with any other charset option.
  --only-lower-letters
    Only include lower-case characters.
    Cannot be used with any other charset option.
  --only-digits
    Only include digit numerical characters.
    Cannot be used with any other charset option.
    
arguments:
  <LENGTH>
    Optionally specify length.
    Default: 12
ME_HELP;
   
   const ME_COPYRIGHT = 'Copyright (c) 2012-2018 Doug Bird. All Rights Reserved.';
   
   private static function printHelp() : void {
      static::printLine(static::ME_DESC);
      static::printLine(static::ME_COPYRIGHT);
      static::printLine("");
      static::printUsage();
      static::printLine("");
      echo str_replace("\n",PHP_EOL,static::ME_HELP).PHP_EOL;
   }
   
   private static function printUsage() : void {
      echo "usage:".PHP_EOL;
      echo "  ".static::ME_NAME." ".static::ME_USAGE.PHP_EOL;
   }
   
   const PRINT_FLAG_PLAIN = 0;
   const PRINT_FLAG_NAME_PREFIX = 1;
   const PRINT_FLAG_VERBOSE_ONLY = 2;
   private static function printError(string $message,int $flags=self::PRINT_FLAG_NAME_PREFIX) : void {
      if ($flags & static::PRINT_FLAG_NAME_PREFIX) $message = static::ME_NAME.": $message";
      fwrite(STDERR,$message.PHP_EOL);
   }
   private static function printLine(string $message,int $flags=self::PRINT_FLAG_PLAIN) : void {
      if ($flags & static::PRINT_FLAG_NAME_PREFIX) $message = static::ME_NAME.": $message";
      echo $message.PHP_EOL;
   }

   private $exitStatus = 0;
   public function getExitStatus() : int {
      return $this->exitStatus;
   }
   
   public function __construct() {
      
      if (is_file(static::VENDOR_AUTOLOAD)) {
         require static::VENDOR_AUTOLOAD;
      } else {
         if (is_file(static::BIN_VENDOR_AUTOLOAD)) {
            require static::BIN_VENDOR_AUTOLOAD;
         } else {
            static::printError("missing vendor/autoload.php, hint; have you run composer?");
            return $this->exitStatus = 1;
         }
      }
      
      $optind = 1;
      getopt("",[],$optind);
      $argOffset = $optind-1;
      $argv = [];
      $argc = 0;
      $arg1 = null;
      if (!empty($_SERVER) && !empty($_SERVER['argv'])) {
         $argv = array_slice($_SERVER['argv'], $argOffset);
         $argc = count($argv);
         if (isset($argv[1])) {
            $arg1 = $argv[1];
         }
      }
      
      /*
       * apply help mode
       *   if the "-h, -u, --help, or --usage" option is indicated
       *   if first argument is "usage" or "help"
       */
      if (
            ($arg1=='usage') ||
            ($arg1=='help') ||
            (false!==($opt = getopt("hu",['help','usage'])) && count($opt))
            )
      {
         static::printHelp();
         return;
      }
      
      /*
       * enforce sanity of arguments
       */
      $missingArg = false;
      $invalidArg = false;
      $length = $arg1;
      $charsetName = null;
      $charsetOpt = getopt("",['charset:']);
      if (isset($charsetOpt['charset'])) {
         $charsetName = $charsetOpt['charset'];
      }
      
      if (!empty($_SERVER) && !empty($_SERVER['argv'])) {
         $allowedOpt = [
            '--charset',
            '--no-upper-letters','--no-lower-letters','--no-digits',
            '--only-upper-letters','--only-lower-letters','--only-digits',
         ];
         foreach($_SERVER['argv'] as $a=>$v) {
            if ((substr($v,0,1)=='-') && (false!==strpos($v,'=')) ) {
               $o = explode("=",$v);
               if (!in_array($o[0],$allowedOpt)) {
                  $invalidArg = true;
                  static::printError("unrecognized option: $o");
               }
            } else
            if ((substr($v,0,1)=='-') && !in_array($v,$allowedOpt)) {
               $invalidArg = true;
               static::printError("unrecognized option: $v");
            }
         }
         unset($a);
         unset($v);
      }
      
      if ($length===null) {
         $length = static::DEFAULT_LENGTH;
      } else {
         if (!ctype_digit($length)) {
            $invalidArg = true;
            static::printError("invalid <LENGTH>, must be an integer");
         } else {
            $length = (int) $length;
            if ($length < 1) {
               $invalidArg = true;
               static::printError("invalid <LENGTH>, must be greater than 0");
            }
         }
      }
      
      if ($charsetName===null) {
         $charsetClass = str_replace("/","\\",static::CHARSET_NAMESPACE.'/'.static::DEFAULT_CHARSET_NAME);
      } else {
         if (!ctype_alpha(substr($charsetName,0,1))) {
            static::printError("invalid --charset, must start with a letter");
            $invalidArg = true;
         } else if (!ctype_alnum(str_replace(['-','_'],'',$charsetName))) {
            static::printError("invalid --charset, may only include letters, numbers, and the dash '-' and underscore '_' chars");
            $invalidArg = true;
         } else {
            $charsetName = ucfirst($charsetName);
            $charsetClass = str_replace("/","\\",static::CHARSET_NAMESPACE."/$charsetName");
            if (!class_exists($charsetClass)) {
               $invalidArg = true;
               static::printError("unrecognized --charset '$charsetName'");
            }
         }
      }
      
      if ($invalidArg) {
         $missingArg && static::printUsage();
         return $this->exitStatus = 2;
      }
      
      $charsetFlags = Charset::FLAG_UPPER_LETTERS | Charset::FLAG_LOWER_LETTERS | Charset::FLAG_DIGITS;
      
      $hasOnlyCharsetFlag = null;
      $onlyCharsetFlag = [
         'only-upper-letters'=>Charset::FLAG_UPPER_LETTERS,
         'only-lower-letters'=>Charset::FLAG_LOWER_LETTERS,
         'only-digits'=>Charset::FLAG_DIGITS,
      ];
      $onlyCharsetOpt = getopt("",array_keys($onlyCharsetFlag));
      if (count($onlyCharsetOpt)) {
         foreach($onlyCharsetOpt as $flag=>$v) {
            if ($hasOnlyCharsetFlag!==null) {
               static::printError("cannot use --$flag with --$hasOnlyCharsetFlag");
               $this->exitStatus = 2;
            } else {
               $hasOnlyCharsetFlag = $flag;
               $charsetFlags = $onlyCharsetFlag[$flag];
            }
         }
         unset($flag);
         unset($v);
      }
      
      $noCharsetFlag = [
         'no-upper-letters'=>Charset::FLAG_UPPER_LETTERS,
         'no-lower-letters'=>Charset::FLAG_LOWER_LETTERS,
         'no-digits'=>Charset::FLAG_DIGITS,
      ];
      $noCharsetOpt = getopt("",array_keys($noCharsetFlag));
      if (count($noCharsetOpt)) {
         foreach($noCharsetOpt as $flag=>$v) {
            if ($hasOnlyCharsetFlag!==null) {
               static::printError("cannot use --$flag with --$hasOnlyCharsetFlag");
               $this->exitStatus = 2;
            } else {
               $charsetFlags = $charsetFlags &~$noCharsetFlag[$flag];
            }
         }
         unset($flag);
         unset($v);
      }
      
      if ($this->exitStatus!=0) {
         return;
      }
      
      
      try {
         $charset = new $charsetClass($charsetFlags);
      } catch (InvalidCharsetFlags $e) {
         static::printError("not enough potential characters with given options");
         return $this->exitStatus = 2;
      }
      
      static::printLine($charset->randomString($length));
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
   }
   
})->getExitStatus());