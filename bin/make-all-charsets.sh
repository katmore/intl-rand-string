#!/bin/sh
# generates all Charset class files
# - uses 'make-charset.php'
#
# @author D. Bird <retran@gmail.com> 
#
set -e

#
# @link https://www.jasan.tk/posix/2017/05/11/posix_shell_dirname_replacement
#
a="/$0"; a=${a%/*}; a=${a:-.}; a=${a#/}/; ME_DIR=$(cd "$a"; pwd)

MAKE_CHARSET_BIN=$ME_DIR/make-charset.php

$MAKE_CHARSET_BIN cyrillic 0x0410 0x0450
