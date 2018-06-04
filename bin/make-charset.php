#!/usr/bin/env php
<?php
exit((new class () {
   
   const CHARSET_CLASS_TEMPLATE_VERSION = '0.0.3';
   
   const ROOT_NAMESPACE = 'IntlRandString';
   const CHARSET_PARENT_SHORTNAME = 'Charset';
   const CHARSET_PARENT_CLASS = self::ROOT_NAMESPACE.'/'.self::CHARSET_PARENT_SHORTNAME;
   const CHARSET_NAMESPACE = self::ROOT_NAMESPACE.'/Charset';
   const CHARSET_CLASS_ROOT = __DIR__.'/../src/'.self::CHARSET_NAMESPACE;
   
   const ME_DESC = 'intl-rand-string charset generator';
   const ME_NAME = 'make-charset.php';
   const ME_USAGE = '[-h]|[CHARSET-NAME] [CODEPOINT-START] [CODEPOINT-LIMIT] [...[[CODEPOINT-START] [CODEPOINT-LIMIT]]]';
   const ME_HELP =<<<ME_HELP
options:
  -h: Print a help message and exit.
  --verbose: Print more details.

arguments:
  [CHARSET-NAME]
    Specify the new charset name.
    Creates the "Charset" class file in the "src/IntlRandString/Charset" directory.
  [CODEPOINT-START]
    Specify a starting Unicode code point.
    Typically expressed in Unicode format; i.e. "U+0400".
    May also be expressed in decimal integer format; i.e. "1024".
  [CODEPOINT-LIMIT]
    Specify an ending Unicode code point.
    Typically expressed in Unicode format; i.e. "U+0400".
    May also be expressed in decimal integer format; i.e. "1024".
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
   
   private static $isVerboseOn = false;
   
   const PRINT_FLAG_PLAIN = 0;
   const PRINT_FLAG_NAME_PREFIX = 1;
   const PRINT_FLAG_VERBOSE_ONLY = 2;
   private static function printError(string $message,int $flags=self::PRINT_FLAG_NAME_PREFIX) : void {
      if (($flags & static::PRINT_FLAG_VERBOSE_ONLY) && !static::$isVerboseOn) return;
      if ($flags & static::PRINT_FLAG_NAME_PREFIX) $message = static::ME_NAME.": $message";
      fwrite(STDERR,$message.PHP_EOL);
   }
   private static function printLine(string $message,int $flags=self::PRINT_FLAG_PLAIN) : void {
      if (($flags & static::PRINT_FLAG_VERBOSE_ONLY) && !static::$isVerboseOn) return;
      if ($flags & static::PRINT_FLAG_NAME_PREFIX) $message = static::ME_NAME.": $message";
      echo $message.PHP_EOL;
   }

   private $exitStatus = 0;
   public function getExitStatus() : int {
      return $this->exitStatus;
   }
   
   public function __construct() {
      
      
      $optind = 1;
      
      if (false!==($opt = getopt("",['verbose'],$optind)) && count($opt)) {
         static::$isVerboseOn = true;
      }
      
      $argOffset = $optind-1;
      $argv = [];
      $argc = 0;
      $arg1 = null;
      if (!empty($_SERVER) && !empty($_SERVER['argv'])) {
         $argv = array_slice($_SERVER['argv'], $argOffset);
         $argc = count($argv);
         if (!empty($argv[1])) {
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
      ) {
         static::printHelp();
         return;
      }
      
      /*
       * apply list mode
       *   if the "-l, or --list" option is indicated
       *   if first argument is "list" 
       */
      if (
         ($arg1=='list') ||
         (false!==($opt = getopt("l",['list',])) && count($opt))
      ) {
         if (false===($dirF = scandir(static::ENTITY_TABLE_ROOT))) {
            static::printError('cannot read "entity-tables" directory ('.static::ENTITY_TABLE_ROOT.')');
            return $this->exitStatus = 1;
         }
         static::printError('printing each available [CHARSET-NAME]...',static::PRINT_FLAG_PLAIN);
         static::printError('',static::PRINT_FLAG_PLAIN);
         $i=0;
         foreach($dirF as $f) {
            $path = static::ENTITY_TABLE_ROOT."/$f";
            if (is_file($path)) {
               static::printLine(pathinfo($f,PATHINFO_FILENAME));
               $i++;
            }
         }
         unset($f);
         static::printError('',static::PRINT_FLAG_PLAIN);
         static::printError("$i total [CHARSET-NAME] available",static::PRINT_FLAG_PLAIN);
         return;
      }
      
      /*
       * enforce sanity of arguments
       */
      $missingArg = false;
      $invalidArg = false;
      $charsetName = $arg1;
      
      if (empty($charsetName)) {
         static::printError("missing [CHARSET-NAME]");
         $missingArg = $invalidArg = true;
      } else {
         if (!ctype_alpha(substr($charsetName,0,1))) {
            static::printError("invalid [CHARSET-NAME], must start with a letter");
            $invalidArg = true;
         } else if (!ctype_alnum(str_replace(['-','_'],'',$charsetName))) {
            static::printError("invalid [CHARSET-NAME], may only include letters, numbers, and the dash '-' and underscore '_' chars");
            $invalidArg = true;
         }
      }
      
      $rangeSetInput = [];
      
      $uStart = null;
      for($i=2;$i<$argc;$i++) {
         if ($uStart === null) {
            $uStart = $argv[$i];
            continue;
         }
         if ($uStart !== null) {
            $rangeSetInput[$uStart] = $argv[$i];
            $uStart = null;
         }
      }
      if ($uStart !== null) {
         static::printError("missing [CODEPOINT-LIMIT] for $uStart");
         $missingArg = $invalidArg = true;
      }
      unset($uStart);
      
      $rangeSet = [];
      foreach($rangeSetInput as $uStartInput=>$uEndInput) {
         $uStartInput = trim($uStartInput);
         $uEndInput = trim($uEndInput);
         if (((substr($uStartInput,0,2)==='0x') || (substr($uStartInput,0,2)==='U+')) && (ctype_xdigit(substr($uStartInput,2)))) {
            $uStart =(int) hexdec(substr($uStartInput,2));
         } else if ((substr($uStartInput,0,1)==='x') && (ctype_xdigit(substr($uStartInput,1)))) {
            $uStart =(int) hexdec(substr($uStartInput,1));
         } else if (ctype_xdigit($uStartInput)) {
            $uStart =(int) hexdec($uStartInput);
         } else if (ctype_digit($uStartInput)) {
            $uStart =(int) $uStartInput;
         } else {
            $invalidArg = true;
            static::printError("invalid [CODEPOINT-START], must be Unicode code point expression");
            static::printError("invalid [CODEPOINT-START] input: $uStartInput",static::PRINT_FLAG_VERBOSE_ONLY);
         }
         if (((substr($uEndInput,0,2)==='0x') || (substr($uEndInput,0,2)==='U+')) && (ctype_xdigit(substr($uEndInput,2)))) {
            $uEnd = (int) hexdec(substr($uEndInput,2));
         } else if ((substr($uEndInput,0,1)==='x') && (ctype_xdigit(substr($uEndInput,1)))) {
            $uEnd = (int) hexdec(substr($uEndInput,1));
         } else if (ctype_xdigit($uEndInput)) {
            $uEnd = (int) hexdec($uEndInput);
         } else if (ctype_digit($uEndInput)) {
            $uEnd = (int) $uEndInput;
         } else {
            $invalidArg = true;
            static::printError("invalid [CODEPOINT-LIMIT], must be Unicode code point expression");
            static::printError("invalid [CODEPOINT-LIMIT] input: $uEndInput",static::PRINT_FLAG_VERBOSE_ONLY);
         }
         if (!$invalidArg) {
            $rangeSet[$uStart] = $uEnd;
         }
      }
      unset($uStartInput);
      unset($uEndInput);
      
      //var_dump($rangeSet);
      
      if ($invalidArg) {
         $missingArg && static::printUsage();
         return $this->exitStatus = 2;
      }
      
      /*
       * prepare to concatenate Charset class name
       */
      $charsetSubns = strtolower($charsetName); //start with the ENTITY-TABLE name
      $charsetSubns = preg_replace('!\s+!', '-', $charsetSubns); //change any whitespace chars to dashes
      $charsetSubns = preg_replace('/[\x00-\x1F\x7F]/u', '', $charsetSubns); //remove unprintable chars
      $charsetSubns = str_replace('_','-',$charsetSubns); //change underscore to dash 
      
      /*
       * concatenate Charset class name
       */
      $charsetShortName = "";
      foreach(explode('-',$charsetSubns) as $subns) {
         $charsetShortName .= ucwords($subns);
      }
      unset($subns);
      
      /*
       * resolve the Charset class file path
       */
      $classPath = static::CHARSET_CLASS_ROOT."/$charsetShortName.php";
      
      /*
       * create temp file for writing Charset class defintion
       */
      if (false===($tmpClassFile = tempnam(sys_get_temp_dir(),static::ME_NAME.'-'))) {
         static::printError("failed to create the temporary class file");
         return $this->exitStatus = 1;
      }
      
      /*
       * delete temp file on script shutdown
       */
      register_shutdown_function(function() use(&$tmpClassFile)
      {
         if (is_file($tmpClassFile) && is_writable($tmpClassFile)) {
            unlink($tmpClassFile);
         }
      });
      
      $upper = [];
      $lower = [];
      $digit = [];
      
      $codepointRange = [];
      foreach($rangeSet as $uStart=>$uEnd) {
         $codepointRange []= "U+".sprintf("%04X",$uStart)." to U+".sprintf("%04X",$uEnd-1);
         IntlChar::enumCharNames($uStart,$uEnd,function($codepoint , $nameChoice , $name ) use (&$upper,&$lower,&$digit)
         {
            static::printLine( "$codepoint: $name",static::PRINT_FLAG_VERBOSE_ONLY);
            
            if (IntlChar::isalpha($codepoint) && IntlChar::isupper($codepoint)) {
               $upper[] = IntlChar::chr($codepoint);
               static::printLine("--added upper char--",static::PRINT_FLAG_VERBOSE_ONLY);
               return;
            } 
            
            if (IntlChar::isalpha($codepoint) && IntlChar::islower($codepoint)) {
               $lower[] = IntlChar::chr($codepoint);
               static::printLine("--added lower char--",static::PRINT_FLAG_VERBOSE_ONLY);
               return;
            }
            
            if (IntlChar::isdigit($codepoint)) {
               $digit[] = IntlChar::chr($codepoint);
               static::printLine("--added digit char--",static::PRINT_FLAG_VERBOSE_ONLY);
               return;
            }
            
            static::printLine("--ignored char--",static::PRINT_FLAG_VERBOSE_ONLY);
         });
      }
      unset($uStart);
      unset($uEnd);
      
      if (false===file_put_contents($tmpClassFile,
            "<?php\n".
            "/* $charsetShortName Charset class - Generated by katmore/rand-string/bin/".static::ME_NAME."\n".
            " * - Template: ".static::CHARSET_CLASS_TEMPLATE_VERSION. "\n".
            " * - Codepoints: ".implode(", ",$codepointRange)."\n".
            " * - Upper-Letters etag: ".md5(json_encode($upper))."\n".
            " * - Lower-Letters etag: ".md5(json_encode($lower))."\n".
            " * - Digits etag: ".md5(json_encode($digit))."\n".
            " */\n".
            "namespace ".str_replace("/","\\",static::CHARSET_NAMESPACE).";\n".
            "\n".
            "use ".str_replace("/","\\",static::CHARSET_PARENT_CLASS).";\n".
            "\n".
            "class $charsetShortName extends ".str_replace("/","\\",static::CHARSET_PARENT_SHORTNAME)." {\n".
            "\n".
            "   const UPPER_LETTERS = [\n      '".implode("',\n      '",$upper)."',\n   ];\n\n".
            "   const LOWER_LETTERS = [\n      '".implode("',\n      '",$lower)."',\n   ];\n\n".
            "   const DIGITS = [\n      '".implode("',\n      '",$digit)."',\n   ];\n\n".
            "   public static function enumUpperLetters() : array { return static::UPPER_LETTERS; }\n".
            "   public static function enumLowerLetters() : array { return static::LOWER_LETTERS; }\n".
            "   public static function enumDigits() : array { return static::DIGITS; }\n".
            "}\n"
            )) 
      {
         static::printError("failed to write temporary class file");
         return $this->exitStatus = 1;
      }
      
      if(false===copy($tmpClassFile,$classPath)) {
         static::printError("failed to create Charset class file");
         return $this->exitStatus = 1;
      }
            
      static::printLine("created Charset class file: src/".static::CHARSET_NAMESPACE."/$charsetShortName.php");
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
   }
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   

})->getExitStatus());