#!/usr/bin/env php
<?php
use IntlRandString\Charset;
use IntlRandString\InvalidCharsetFlags;

exit((new class () {
   
   const DEFAULT_LENGTH = 12;
   const FALLBACK_CHARSET_NAME = 'English';
   
   const VENDOR_AUTOLOAD = __DIR__.'/../vendor/autoload.php';
   const BIN_VENDOR_AUTOLOAD = __DIR__.'/../../../autoload.php';
   
   const USER_SETTING_DIR_BASENAME = '.rand-string';
   
   const ROOT_NAMESPACE = 'IntlRandString';
   const CHARSET_PARENT_SHORTNAME = 'Charset';
   const CHARSET_PARENT_CLASS = self::ROOT_NAMESPACE.'/'.self::CHARSET_PARENT_SHORTNAME;
   const CHARSET_NAMESPACE = self::ROOT_NAMESPACE.'/Charset';
   const CHARSET_CLASS_ROOT = __DIR__.'/../src/'.self::CHARSET_NAMESPACE;
   
   const ME_DESC = 'random string generator';
   const ME_NAME = 'rand-string';
   const ME_USAGE = "[-hl|<setting command>] | [--charset=][<char flags...>][<LEN>]";
   const ME_HELP =<<<ME_HELP
mode flags:
  -h,--help 
    Print a help message and exit.
  -l,--list
    Print each available charset and exit.

setting commands:
  --set-default-charset=<CHARSET-NAME>
    Set the default charset for the current user and exit.
  --print-default-charset
    Print the default charset for the current user and exit.

random string options:
  --charset=<CHARSET-NAME>
    Optionally specify random string charset.

  char flags:
    --no-upper-letters
      Random string will not include upper-case characters.
    --no-lower-letters
      Random string will not include lower-case letter characters.
    --no-digits
      Random string will not include digit numeral characters.
    --only-upper-letters
      Random string will only include upper-case characters.
      Cannot be used with any other char flag.
    --only-lower-letters
      Random string will only include lower-case characters.
      Cannot be used with any other char flag.
    --only-digits
      Random string will only include digit numerical characters.
      Cannot be used with any other char flag.
    
arguments:
  <LEN>
    Optionally specify random string length.
    Default: %DEFAULT_LENGTH%
ME_HELP;
   
   const ME_COPYRIGHT = 'Copyright (c) 2012-2018 Doug Bird. All Rights Reserved.';
   
   private static function printHelp() : void {
      static::printLine(static::ME_DESC);
      static::printLine(static::ME_COPYRIGHT);
      static::printLine("");
      static::printUsage();
      static::printLine("");
      $help = static::ME_HELP;
      $help = str_replace("%DEFAULT_LENGTH%", static::DEFAULT_LENGTH, $help);
      echo str_replace("\n",PHP_EOL,$help).PHP_EOL;
   }
   
   private static function printUsage() : void {
      echo "usage:".PHP_EOL;
      echo static::ME_NAME." ".static::ME_USAGE.PHP_EOL;
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
   private static function validateCharsetName(string $charsetName,bool $printErrors=false) : int {
      if (!ctype_alpha(substr($charsetName,0,1))) {
         $printErrors && static::printError("invalid <CHARSET-NAME>; must start with a letter");
         return 1;
      } else if (!ctype_alnum(str_replace(['-','_'],'',$charsetName))) {
         $printErrors && static::printError("invalid <CHARSET-NAME>; may only include letters, numbers, and the dash '-' and underscore '_' chars");
         return 1;
      } else {
         $charsetName = ucfirst($charsetName);
         $charsetClass = str_replace("/","\\",static::CHARSET_NAMESPACE."/$charsetName");
         if (!class_exists($charsetClass)) {
            $printErrors && static::printError("unrecognized <CHARSET-NAME> '$charsetName'");
            return 1;
         }
      }
      return 0;
   }
   
   
   private static function validateUserSettingDir(bool $printErrors=false) : int {
      if (!isset($_SERVER) || !isset($_SERVER['HOME'])) {
         $printErrors && static::printError("cannot determine home directory for user setting dir");
         return 1;
      }
      if (!is_dir($_SERVER['HOME'])) {
         $printErrors && static::printError("home directory '{$_SERVER['HOME']}' not found for user setting dir");
         return 1;
      }
      $userSettingRoot = "{$_SERVER['HOME']}/".static::USER_SETTING_DIR_BASENAME;
      if (!is_dir($userSettingRoot)) {
         if (!mkdir($userSettingRoot,0770,true)) {
            $printErrors && static::printError("failed to create '$userSettingRoot' user setting dir");
            return 1;
         }
      }
      return 0;
   }
   private static function getUserSettingFile(string $setting_name) : string {
      $homeDir = "";
      if (isset($_SERVER) && isset($_SERVER['HOME'])) {
         $homeDir = $_SERVER['HOME'];
      }
      return "$homeDir/".static::USER_SETTING_DIR_BASENAME."/.$setting_name";
   }
   private static function saveUserSetting(string $setting_name,string $setting_value) : int {
      if (0!==($isValid = static::validateUserSettingDir(true))) {
         return $isValid;
      }
      $userSettingFile = static::getUserSettingFile($setting_name);
      if (false===file_put_contents($userSettingFile, "$setting_value\n")) {
         static::printError("failed to save '$userSettingFile' user setting file");
         return 1;
      }
      return 0;
   }
   private static function readUserSetting(string $setting_name, string &$setting_value, bool $printErrors=false) : int {
      $setting_value = "";
      if (0!==($isValid = static::validateUserSettingDir($printErrors))) {
         return $isValid;
      }
      $userSettingFile = static::getUserSettingFile($setting_name);
      if (!$printErrors) {
         $oldReporting = error_reporting(0);
      }
      $status = 0;
      if (false===($setting_value=file_get_contents($userSettingFile))) {
         $setting_value = "";
         $status = 1;
         $printErrors && static::printError("failed to read '$userSettingFile' user setting file");
      }
      if (!$printErrors) {
         error_reporting($oldReporting);
      }
      $setting_value = trim($setting_value);
      return $status;
   }

   private static function getDefaultCharset() : string {
      $defaultCharset = "";
      if (0===static::readUserSetting('default-charset',$defaultCharset,false)) {
         if (0===static::validateCharsetName($defaultCharset,false)) {
            return $defaultCharset;
         }
      }
      return static::FALLBACK_CHARSET_NAME;
   }
   
   private static function enumCharsetClassNames() : array {
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
      
      
      
      /*
       * check for unrecognized options
       */
      $unrecognizedOption = false;
      if (!empty($_SERVER) && !empty($_SERVER['argv'])) {
         $allowedShortOpt = "hual";
         $allowedLongOpt = [
            'help','usage','about',
            'list','list-charsets','list-charset',
            'charset',
            'no-upper-letters','no-lower-letters','no-digits',
            'only-upper-letters','only-lower-letters','only-digits',
            'set-default-charset','print-default-charset'
         ];
         $allowedValueOpt = $requiredValueOpt = ['charset','set-default-charset'];
         $foundOptVal = [];
         foreach($requiredValueOpt as $longOptName) {
            $optf = getopt("",[$longOptName]);
            $longOptInd = 0;
            $optv = getopt("",["$longOptName:"],$longOptInd);
            if (isset($optv[$longOptName])) {
               $foundOptVal[]=$longOptName;
               if ($longOptInd>$optind) {
                  $optind=$longOptInd;
               }
            } else {
               if (isset($optf[$longOptName])) {
                  static::printError("option --$longOptName must have a value");
                  $unrecognizedOption = true;
               }
            }
         }
         unset($longOptName);
         
         
         foreach($_SERVER['argv'] as $a=>$v) {
            $longOptName = null;
            if ((substr($v,0,2)=='--') && (false!==strpos($v,'=')) ) {
               $o = explode("=",$v);
               if (empty($o[1])) {
                  $unrecognizedOption = true;
                  static::printError("unrecognized option: $v");
               } else {
                  $longOptName = substr($o[0],2);
                  if (!in_array($longOptName,$allowedLongOpt)) {
                     $unrecognizedOption = true;
                     static::printError("unrecognized option: $o[0]");
                  }
               }
            } else {
               if ((substr($v,0,2)=='--')) {
                  $longOptName = substr($v,2);
                  if (!in_array($longOptName,$allowedLongOpt)) {
                     $unrecognizedOption = true;
                     static::printError("unrecognized option: $v");
                  }
               } else {
                  if ((substr($v,0,1)=='-')) {

                     $optCheck = substr($v,1);
                     $optLen = strlen($optCheck);
                     for( $i = 0; $i < $optLen; $i++ ) {
                        $o = substr($optCheck,$i,1);
                        if ($o==='=') {
                           break 1;
                        }
                        if (false===strpos($allowedShortOpt,$o)) {
                           $unrecognizedOption = true;
                           static::printError("unrecognized option: -$o");
                        }
                     }
                     
                  }
               }
            }
         }
         unset($a);
         unset($v);
         
      }
      
      
      if ($unrecognizedOption) {
         return $this->exitStatus = 2;
      }
      
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
       * apply "help" mode
       *   if any of the options
       *   "-h,-u,-a,-v,--help,--usage,--about,--version" 
       *   are indicated
       *      OR
       *   the first argument is any of
       *   "help,usage,about,version"
       */
      if (
            (in_array($arg1,['help','usage','about','version'])) ||
            (false!==($opt = getopt("huav",['help','usage','about','version'])) && count($opt))
            )
      {
         static::printHelp();
         return;
      }
      
      /*
       * apply "list" mode
       *   if any of the options 
       *   "-l,--list,--list-charset,--list-charsets"
       *   are indicated
       *      OR
       *   the first argument is any of
       *   "list,list-charset,list-charsets"
       */
      if (
            (in_array($arg1,['list','list-charset','list-charsets'])) ||
            (false!==($opt = getopt("l",['list','list-charset','list-charsets'])) && count($opt))
            )
      {
         $charsetNameList = static::enumCharsetClassNames();
         foreach($charsetNameList as $className) {
            $charsetName = pathinfo(str_replace("\\","/",$className),PATHINFO_FILENAME);
            
            static::printLine($charsetName);
         }
         unset($className);
         return;
      }
      
      /*
       * apply "set-default-charset" mode
       *   if option "--set-default-charset" is indicated 
       */
      if (false!==($opt = getopt("",['set-default-charset:'])) && isset($opt['set-default-charset'])) {
         $defaultCharsetName = $opt['set-default-charset'];
         if (!is_string($defaultCharsetName)) {
            static::printError("may only specify one --set-default-charset option");
            return $this->exitStatus = 2;
         }
         if (0!==static::validateCharsetName($defaultCharsetName,true)) {
            return $this->exitStatus = 2;
         }
         if (0!==($saveStatus=static::saveUserSetting('default-charset',$defaultCharsetName))) {
            return $this->exitStatus = $saveStatus;
         }
         static::printLine("default-charset is now '$defaultCharsetName'",static::PRINT_FLAG_NAME_PREFIX);
         return;
      }
      
      /*
       * get the default charset name
       */
      $defaultCharsetName = static::getDefaultCharset();
      
      /*
       * apply "print-default-charset" mode
       *   if option "--print-default-charset" is indicated
       */
      if (false!==($opt = getopt("",['print-default-charset'])) && count($opt)) {
         static::printLine($defaultCharsetName);
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
         $charsetClass = str_replace("/","\\",static::CHARSET_NAMESPACE.'/'.$defaultCharsetName);
      } else {
         if (static::validateCharsetName($charsetName,'--charset')!==0) {
            $invalidArg = true;
         }
         $charsetClass = str_replace("/","\\",static::CHARSET_NAMESPACE.'/'.$charsetName);
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