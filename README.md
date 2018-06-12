# IntlRandString
generate internationalized random alpha-numeric strings

 * [Usage in a PHP Project](#usage-in-a-php-project)
 * [Standalone Utility](#rand-string-standalone-installation)
 * [Development](#development)

## About
The **IntlRandString** package facilitates generating random strings in multiple internationalized character sets. A typical use-case is generating random passwords in a targeted "language", i.e. using characters familiar to a language rather than simply using only english latin characters, as is typical. For similar reasons, it could also prove ideal in other use-cases, such as: password reset validation codes, coupon or promotional codes, etc.

## Usage in a PHP Project
use composer to add **IntlRandString** to your PHP project:
```sh
composer require katmore/intl-rand-string
```

A random string can be generated with the following character sets:
 * [Cyrillic](#cyrillic-charset)
 * [English](#english-charset)
 * [German](#german-charset)
 * [Italian](#italian-charset)
 * [Spanish](#spanish-charset)

### Cyrillic Charset
Example, using the **Cyrillic** Charset to generate a random string:
```php
$randString = new IntlRandString\Charset\Cyrillic();
echo $randString->randomString(12);
```
The above example should output a random string that includes only cyrillic characters and latin digits, such as follows:
```txt
МхЫЖЬкхЛДхЦЗ
```

### English Charset
Example, using the **English** Charset to generate a random string:
```php
$randString = new IntlRandString\Charset\English();
echo $randString->randomString(12);
```
The above example should output a random string that includes only latin characters and latin digits as used in English, such as follows:
```txt
fMomhRErXWa8
```

### German Charset
Example, using the **German** Charset to generate a random string:
```php
$randString = new IntlRandString\Charset\German();
echo $randString->randomString(12);
```
The above example should output a random string that includes only latin characters and latin digits as used in German, such as follows:
```txt
0ZNuXÄGksyse
```

### Italian Charset
Example, using the **Italian** Charset to generate a random string:
```php
$randString = new IntlRandString\Charset\Italian();
echo $randString->randomString(12);
```
The above example should output a random string that includes only latin characters and latin digits as used in Italian, such as follows:
```txt
DMFPZNusSJTO
```

### Spanish Charset
Example, using the **Spanish** Charset to generate a random string:
```php
$randString = new IntlRandString\Charset\Spanish();
echo $randString->randomString(12);
```
The above example should output a random string that includes only latin characters and latin digits as used in Spanish, such as follows:
```txt
Uí64DSYjWóQr
```

## Development
The following utility scripts facilitate development of character sets:
 * [make-charset.php](#make-charsetphp-dev-utility)
 * [make-all-charsets.sh](#make-all-charsetssh-dev-utility)
 
### unit tests
The unit tests specified by [`phpunit.xml`](./phpunit.xml) check the basic sanity and entropy of generated random strings for each character set.
```sh
$ vendor/bin/phpunit
```

### make-charset.php dev utility
The **`bin/devel/make-charset.php`** command-line developer utility script creates a character set class defintion PHP source file in the `src/IntlRandString/Charset` directory. After creating a character set, [perform all unit tests](#unit-tests) to ensure conformity.

Specifying the `--help` option will display usage details.
```sh
$ bin/devel/make-charset.php --help
```

### make-all-charsets.sh dev utility
The **`bin/devel/make-all-charsets.sh`** command-line developer utility script contains the Unicode start and end points for all [*Charset* class defintions](#character-sets). Invoking it will (re-)generate all [character set defintion source files](./src/IntlRandString/Charset). 

```sh
$ bin/devel/make-all-charsets.sh
```

The Unicode character ranges for the *Charsets* are ultimately defined in [bin/devel/make-all-charsets.sh](./bin/devel/make-all-charsets.sh#L52-L10000). Therefore, by modifying the [make-all-charsets.sh](./bin/devel/make-all-charsets.sh#L52-L10000) source file, character sets can be permanently added or modified. After modifying the source and invoking to (re-)generate character sets, [perform all unit tests](#unit-tests) to ensure conformity.

For example, the ["German" character set](./src/IntlRandString/Charset/German.php) is defined in [*make-all-charsets.sh*](./bin/devel/make-all-charsets.sh#L82-L92) as follows:
```sh
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
```

## rand-string utility
A standalone utility is provided by the [`bin/rand-string.php`](./bin/rand-string.php) script. Details regarding the usage of this utility and instructions for an optional global installation are included in this section.

### rand-string standalone installation
These installation instructions rely on the `make-phar.sh` installer script. See the [*make-phar.sh utility*](#make-pharsh-utility) section for more in-depth details and troubleshooting.

Installation instructions:
 * Download **intl-rand-string** project using git (or similar), and enter the project directory.
   ```sh
   $ git clone git@github.com:katmore/intl-rand-string.git
   $ cd intl-rand-string
   ```
 * Use the `bin/install/make-phar.sh` utility with the `--install` flag to create and install the phar package on your system.
   ```sh
   $ bin/install/make-phar.sh --install
   ```

### rand-string utility examples
The `rand-string` (or `bin/rand-string.php`) command line utility generates random strings.

Example #1, using default charset and length.
 * the following command
   ```sh
   $ rand-string
   ```
 * should produce output similar to the following
   ```txt
   195tTXDob0ol
   ```

The first positional argument specifies the length of the random string.

Example #2, using default charset and specifying length:
 * the following command
   ```sh
   $ rand-string 20
   ```
 * should produce output similar to the following
   ```txt
   3QCBSV3YC3Dow62Jib5C
   ```

A charset may be specified for one-time use with the `--charset=<CHARSET-NAME>` flag.

Example #3, using `cyrillic` charset:
 * the following command
   ```sh
   $ rand-string --charset=cyrillic
   ```
 * should produce output similar to the following
   ```txt
   ЯИкМСзГД8уя9
   ```

The `English` charset is the global default, though this may be changed [(see usage)](#rand-string-utility-usage).

Example #3, setting the `german` as default:
 * the following command
   ```sh
   $ rand-string --set-default-charset=german
   ```
 * should produce output similar to the following
   ```txt
   $ rand-string: default-charset is now 'german'
   ```
 * subsequent executions should produce random strings using the `german` charset, the following command
   ```sh
   $ rand-string
   ```
 * should produce output similar to the following
   ```txt
   öt7ß1vCQwtNE
   ```

Any Charset available in [`IntlRandString\Charset`](./src/IntlRandString/Charset) may be used.

Example #4, getting a list of available charsets:
 * the following command
   ```sh
   $ rand-string --list
   ```
 * should produce output similar to the following
   ```txt
   Cyrillic
   English
   German
   Italian
   Spanish
   ```
 * and thus, the following command
   ```sh
   $ rand-string --charset=Spanish
   ```
 * should produce output similar to the following
   ```txt
   rñQ0m1úDkáMV
   ```

### rand-string utility usage
```txt
usage:
rand-string [-hl|<setting command>] | [--charset=][<char flags...>][<LEN>]

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
    Default: 12
```

### make-phar.sh utility
The [`bin/install/make-phar.sh`](./bin/install/make-phar.sh) utility creates a standalone `rand-string.phar` phar package file using `bin/rand-string.php` as the entrypoint. Optionally, it will copy the phar package file to an installation path.

**Prerequisites**
 * composer
 * php command line binary

**Usage**
```txt
make-phar.sh [-h] | [--install [--install-path=<PATH>]] [<bin path options>]
options:
  -h,--help: Print a help message and exit.
  --install: Optionally install as a global system command.
  --install-path=<PATH>
    Optionally specify global system command installation path.
    Default: /usr/local/bin/rand-string

bin path options:
  --composer-bin=<COMPOSER-PATH>
    Optionally specify path to composer.
  --php-bin=<PHP-PATH>
    Optionally specify path to php binary.
```


## Legal
### Copyright
IntlRandString - https://github.com/katmore/intl-rand-string

Copyright (c) 2012-2018 Doug Bird. All Rights Reserved.

### License
IntlRandString is copyrighted free software.
You may redistribute and modify it under either the terms and conditions of the
"The MIT License (MIT)"; or the terms and conditions of the "GPL v3 License".
See [LICENSE](https://github.com/katmore/intl-rand-string/blob/master/LICENSE) and [GPLv3](https://github.com/katmore/intl-rand-string/blob/master/GPLv3).
