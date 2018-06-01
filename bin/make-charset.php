#!/usr/bin/env php
<?php
exit((new class () {
   
   const CHARSET_NAMESPACE = 'RandString/Charset';
   const CHARSET_CLASS_ROOT = __DIR__.'/../src/'.self::CHARSET_NAMESPACE;
   
   const ME_DESC = 'rand-string charset generator';
   const ME_NAME = 'make-charset.php';
   const ME_USAGE = '[-hl] [CHARSET-NAME] [U-START] [U-LIMIT]';
   const ME_HELP =<<<ME_HELP
Mode Options:
  -h: print a help message and exit
  -l: list each available [ENITITY-TABLE] and exit

Arguments:
  [CHARSET-NAME]
    Specify the new charset name.
    Creates the "Charset" class file in the "src/Randstring/Charset" directory.
  [U-START]
    Specify the first unicode point in the new charset range.
  [U-LIMIT]
    Specify one more than the last unicode unicode point in the new charset range. 

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
      
      $arg1 = null;
      $arg2 = null;
      $arg3 = null;
      if (!empty($_SERVER) && !empty($_SERVER['argv'])) {
         if (!empty($_SERVER['argv'][1])) {
            $arg1 = $_SERVER['argv'][1];
         }
         if (!empty($_SERVER['argv'][2])) {
            $arg2 = $_SERVER['argv'][2];
         }
         if (!empty($_SERVER['argv'][3])) {
            $arg3 = $_SERVER['argv'][3];
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
      $invalidArg = false;
      $charsetName = $arg1;
      
      if (empty($charsetName)) {
         static::printError("missing [CHARSET-NAME]");
         $invalidArg = true;
      } else {
         if (!ctype_alpha(substr($charsetName,0,1))) {
            static::printError("invalid [CHARSET-NAME], must start with a letter");
            $invalidArg = true;
         } else if (!ctype_alnum($charsetName)) {
            static::printError("invalid [CHARSET-NAME], must be alphanumeric");
            $invalidArg = true;
         }
      }
      
      $uStart = $arg2;
      if (empty($uStart)) {
         static::printError("missing [U-START]");
         $invalidArg = true;
      } else {
         if (((substr($uStart,0,2)==='0x') || (substr($uStart,0,2)==='U+')) && (ctype_xdigit(substr($uStart,2)))) {
            $uStart = (int) hexdec(substr($uStart,2));
         } else if (ctype_xdigit($uStart)) {
            $uStart = (int) hexdec($uStart);
         } else if (ctype_digit($uStart)) {
            $uStart = (int) $uStart;
         } else {
            $invalidArg = true;
            static::printError("invalid [U-START], must be integer");
         }
      }
      
      $uLimit = $arg3;
      if (empty($uLimit)) {
         static::printError("missing [U-LIMIT]");
         $invalidArg = true;
      } else {
         if (((substr($uLimit,0,2)==='0x') || (substr($uLimit,0,2)==='U+')) && (ctype_xdigit(substr($uLimit,2)))) {
            $uLimit = (int) hexdec(substr($uLimit,2));
         } else if (ctype_xdigit($uLimit)) {
            $uLimit = (int) hexdec($uLimit);
         } else if (ctype_digit($uLimit)) {
            $uLimit = (int) $uLimit;
         } else {
            $invalidArg = true;
            static::printError("invalid [U-START], must be integer");
         }
      }
      
      if ($invalidArg) {
         static::printUsage();
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
       * enforce that Charset class file does not already exist
       */
      if (is_file($classPath)) {
         static::printError("a corresponding class file already exists for the [CHARSET-NAME] '$charsetName'; hint, try ".'"rm '.$classPath.'" to delete the existing class file and try again');
      }
      
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
      
      
      /*
       * write the 'top' code chunk of the Charset class defintion
       */
      file_put_contents($tmpClassFile,
            "<?php\n//\n//\n// -- GENERATED BY katmore/rand-string/bin/".static::ME_NAME."\n// -- ".date('c'). "\n//\n//\n".
            "namespace ".static::CHARSET_NAMESPACE.";\n".
            "\n".
            "class $tmpClassFile {\n".
            "\n"
      );
      
      $upper = [];
      $lower = [];
      //
      // Cyrillic: 0x0410,0x0450
      //
      IntlChar::enumCharNames($uStart,$uLimit,function($codepoint , $nameChoice , $name ) use (&$upper,&$lower)
      {
         static::printLine( "$codepoint: $name");
         if (IntlChar::isalpha($codepoint)) {
            if (IntlChar::isupper($codepoint)) {
               $upper[] = IntlChar::chr($codepoint);
               static::printLine("--added upper char--");
               return;
            }
            if (IntlChar::islower($codepoint)) {
               $lower[] = IntlChar::chr($codepoint);
               static::printLine("--added lower char--");
               return;
            }
         } else {
            static::printLine("--ignored char--");
         }
      });
      
      if (false===file_put_contents($tmpClassFile,
            "<?php\n//\n//\n// -- GENERATED BY katmore/rand-string/bin/".static::ME_NAME."\n// -- ".date('c'). "\n//\n//\n".
            "namespace ".str_replace("/","\\",static::CHARSET_NAMESPACE).";\n".
            "\n".
            "class $charsetShortName {\n".
            "\n".
            "   const UPPER_LETTERS = [\n      '".implode("',\n      '",$upper)."',\n   ];\n\n".
            "   const LOWER_LETTERS = [\n      '".implode("',\n      '",$lower)."',\n   ];\n\n".
            "   const LETTERS = [\n      '".implode("',\n      '",array_merge($upper,$lower))."',\n   ];\n\n".
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