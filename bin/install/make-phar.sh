#!/bin/sh
# creates a standalone 'rand-string.phar' from 'bin/rand-string.php'
#
# @author D. Bird <retran@gmail.com>
#

#
# installation dir
#
INSTALL_PATH=/usr/local/bin/rand-string

#
# script localization
#
ME_NAME=$(basename $0)
a="/$0"; a=${a%/*}; a=${a:-.}; a=${a#/}/; ME_DIR=$(cd "$a"; pwd)
APP_ROOT=$ME_DIR/../../
cd $APP_ROOT || {
   >&2 echo "$ME_NAME: app root directory is inaccessable, 'cd $APP_ROOT' failed with exit status $?"
   exit 1
}

#
# help mode
#
help_mode() {
   echo "make-phar utility"
   echo "Copyright (c) 2012-2018 Doug Bird. All Rights Reserved."
   echo ""
   echo "Purpose: creates a standalone 'rand-string.phar' from 'bin/rand-string.php'"
   echo ""
   echo "usage:"
   echo "  $ME_NAME [-h] [--install [--install-path=<PATH>]]";
   echo ""
   echo "options:"
   echo "  -h: Print a help message and exit."
   echo "  --install: Optionally install as a global system command."
   echo "  --install-path=<PATH>"
   echo "    Optionally specify global system command install path."
   echo "    Default: $INSTALL_PATH"
   exit 0
}

#
# parse options
#
OPTION_STATUS=0
INSTALL_MODE=0
while getopts :?hu-: arg; do { case $arg in
   h|u|a|v) help_mode;; 
   -) LONG_OPTARG="${OPTARG#*=}"; case $OPTARG in
      help|usage|version) help_mode;;
      install) INSTALL_MODE=1;;
      install-path) >&2 echo "$ME_NAME: option --$OPTARG must have a value"; OPTION_STATUS=2;;
      install-path=*) INSTALL_PATH=$LONG_OPTARG;;
      *) >&2 echo "$ME_NAME: unrecognized long option --$OPTARG"; OPTION_STATUS=2;;
   esac ;; 
   *) >&2 echo "$ME_NAME: unrecognized option -$OPTARG"; OPTION_STATUS=2;;
esac } done
shift $((OPTIND-1)) # remove parsed options and args from $@ list
[ "$OPTION_STATUS" != "0" ] && { >&2 echo "$ME_NAME: one or more invalid options"; exit 2; }
[ -z "$@" ] || { >&2 echo "$ME_NAME: one or more unrecognized positional arguments"; exit 2; }

#
# enforce composer sanity
#
composer -V > /dev/null 2>&1 || {
   >&2 echo "$ME_NAME: composer is unavailable; have you installed composer?"
   exit 1
}

#
# enforce php sanity
#
php -v > /dev/null 2>&1 || {
   >&2 echo "$ME_NAME: php is unavailable; have you installed php?"
   exit 1
}

#
# enforce install dir sanity
#
INSTALL_DIR=$(dirname $INSTALL_PATH)
[ -d "$INSTALL_DIR" ] || {
   >&2 echo "$ME_NAME: installation directory '$INSTALL_DIR' does not exist"
   exit 1
}

#
# globally install 'phar-builder'
#
PACKAGE=macfja/phar-builder
composer global require $PACKAGE || {
   >&2 echo "$ME_NAME: composer failed to globally install '$PACKAGE' package"
   exit 1
}

#
# global composer vendor dir
#
GLOBAL_VENDOR_DIR=$(composer global config vendor-dir --absolute 2>/dev/null) || {
   >&2 echo "$ME_NAME: unable to get the composer global vendor-dir, 'composer global config vendor-dir' failed with exit status $?"
   exit 1
}

#
# temp build dir
#
TMP_BUILD_DIR=
cleanup_build_dir() {
   [ -z "$TMP_BUILD_DIR" ] && return 0
   [ -d "$TMP_BUILD_DIR" ] && rm -rf "$TMP_BUILD_DIR"
}
trap cleanup_build_dir EXIT
TMP_BUILD_DIR=$(mktemp -d) || {
   >&2 echo "$ME_NAME: failed to create temp build dir, 'mktemp -d' failed with exit status $?"
   exit 1
}

#
# copy app-root to build dir
#
cp -rp ./ $TMP_BUILD_DIR || {
   >&2 echo "$ME_NAME: failed to copy app dir to temp build dir, 'cp -rp' failed with exit status $?"
   exit 1
}
cd $TMP_BUILD_DIR || {
   >&2 echo "$ME_NAME: temp build dir is inaccessable, 'cd' failed with exit status $?"
   exit 1
}

#
# composer update for 'intl-rand-string' package
#
composer update --no-dev || {
   >&2 echo "$ME_NAME: 'composer update --no-dev' failed with exit status $?"
   exit 1
}

#
# entry point
#
ENTRY_POINT=bin/rand-string.php
PHAR_NAME=rand-string.phar
PHAR_PATH=$TMP_BUILD_DIR/$PHAR_NAME

#
# create rand-string.phar
#
php -d phar.readonly=0 "$GLOBAL_VENDOR_DIR/bin/phar-builder" package -n -z --include=vendor --include=src --entry-point="$ENTRY_POINT" --output-dir=$TMP_BUILD_DIR --name="$PHAR_NAME" || {
   >&2 echo "$ME_NAME: phar-builder failed with exit status $?"
   exit 1
}

#
# rand-string.phar exec permission
#
chmod +x $PHAR_PATH || {
   >&2 echo "$ME_NAME: failed to set executable permission for rand-string.phar, 'chmod +x' failed with exit status $?"
   exit 1
}

if [ "$INSTALL_MODE" = "0" ]; then
   mv "$PHAR_PATH" "$APP_ROOT/$PHAR_NAME" || {
      >&2 echo "$ME_NAME: failed to copy rand-string.phar to app root, 'mv' failed with exit status $?"
      exit 1
   }
   echo "$ME_NAME: success, created '$PHAR_NAME'"
   exit 0
fi

#
# copy to installation path
#
mv "$PHAR_PATH" "$INSTALL_PATH" 2>/dev/null || {
   echo "$ME_NAME: using 'sudo' command to copy to installation path"
   sudo -h > /dev/null 2>&1 || {
      >&2 echo "$ME_NAME: failed to copy rand-string.phar to installation path ($INSTALL_PATH) and missing 'sudo' system command"
      exit 1
   }
   sudo mv "$PHAR_PATH" "$INSTALL_PATH" || {
      >&2 echo "$ME_NAME: failed to copy rand-string.phar to installation path ($INSTALL_PATH), 'sudo mv' failed with exit status $?"
      exit 1
   }
}

echo "$ME_NAME: success, installed to '$INSTALL_PATH'"


















