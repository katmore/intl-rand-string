#!/bin/sh
# Purpose: generates all intl-rand-string Charset class files
# - uses 'make-charset.php'
#
# @author D. Bird <retran@gmail.com> 
#
set -e
#
# script localization
#
ME_NAME='make-all-charsets.sh'
a="/$0"; a=${a%/*}; a=${a:-.}; a=${a#/}/; ME_DIR=$(cd "$a"; pwd)
#
# resolve path to 'make-charset.php'
#
MAKE_CHARSET_BIN=$ME_DIR/make-charset.php
help_mode() {
   echo "generate all intl-rand-string charsets utility"
   echo "Copyright (c) 2012-2018 Doug Bird. All Rights Reserved."
   echo ""
   echo "Purpose: generates all intl-rand-string Charset class files"
   echo ""
   echo "usage:"
   echo "  $ME_NAME [-h][--verbose]";
   echo ""
   echo "options:"
   echo "  -h: Print a help message and exit."
   echo "  --verbose: Print more details."
   exit 0
}
#
# parse options
#
VERBOSE_OPT=
while getopts :?hu-: arg; do { case $arg in
   h|u) help_mode;; 
   -) case $OPTARG in
      verbose) VERBOSE_OPT=--verbose;;
      help|usage) help_mode;;
      *) >&2 echo "$ME_NAME: unrecognized option --$OPTARG"; exit 2;;
   esac ;;
   *) >&2 echo "$ME_NAME: unrecognized option -$OPTARG"; exit 2;;
esac } done
shift $((OPTIND-1)) # remove parsed options and args from $@ list
[ -z "$@" ] || { >&2 echo "$ME_NAME: one or more unrecognized positional arguments"; exit 2; }  
#
# make-charset.php wrapper
#
make_charset() { charset=$1; $MAKE_CHARSET_BIN $VERBOSE_OPT "$@" || { 
   exit_code=$?; >&2 echo "$ME_NAME: 'make-charset.php $charset' failed with exit code $exit_code"; exit 1
} }
#
# latin codepoint ranges
#
LATIN_NUMBERS="U+0030 U+0040"
BASIC_LATIN_LETTERS="U+0041 U+005B U+0061 U+007B"

#
# English Charset
#
make_charset english $LATIN_NUMBERS $BASIC_LATIN_LETTERS

#
# Cyrillic Charset
#
make_charset cyrillic\
   $LATIN_NUMBERS\
   U+0410 U+0450

#
# Spanish Charset
#
CHARSET_LETTERS=
CHARSET_LETTERS="$CHARSET_LETTERS U+00E1 U+00E2 U+00E9 U+00EA U+00ED U+00EE U+00F3 U+00F4 U+00FA U+00FB" #acute a,e,i,o,u
CHARSET_LETTERS="$CHARSET_LETTERS U+00C1 U+00C2 U+00C9 U+00CA U+00CD U+00CE U+00D3 U+00D4 U+00DA U+00DB" #acute A,E,I,O,U
CHARSET_LETTERS="$CHARSET_LETTERS U+00F1 U+00F2 U+00D1 U+00D2" #tilde n,N
make_charset spanish\
   $LATIN_NUMBERS\
   $BASIC_LATIN_LETTERS\
   $CHARSET_LETTERS
   
#
# German Charset
#
CHARSET_LETTERS=
CHARSET_LETTERS="$CHARSET_LETTERS U+00E4 U+00E5 U+00F6 U+00F7 U+00FC U+00FD" #diaresis a,o,u
CHARSET_LETTERS="$CHARSET_LETTERS U+00C4 U+00C5 U+00D6 U+00D7 U+00DC U+00DD" #diaresis A,O,U
CHARSET_LETTERS="$CHARSET_LETTERS U+00DF U+00E0 U+1E9E U+1E9F" #sharp s,S
make_charset german\
   $LATIN_NUMBERS\
   $BASIC_LATIN_LETTERS\
   $CHARSET_LETTERS
 
#
# Italian Charset
#
CHARSET_LETTERS=
CHARSET_LETTERS="$CHARSET_LETTERS U+0061 U+006B U+0041 U+004B" #a-j,A-J
CHARSET_LETTERS="$CHARSET_LETTERS U+006C U+0077 U+004C U+0057" #l-v,L-V
CHARSET_LETTERS="$CHARSET_LETTERS U+007A U+007B U+005A U+005B" #z,Z
make_charset italian\
   $LATIN_NUMBERS\
   $CHARSET_LETTERS































 

 

 

 
