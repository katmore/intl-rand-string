# IntlRandString
generate internationalized random alpha-numeric strings

The **IntlRandString** package is a PHP project that facilitates generating random strings in multiple internationalized character sets. A typical use-case is generating random passwords in a targeted "language", i.e. using characters familiar to a language rather than simply using only english latin character, as is typical. For similar reasons, it could also prove ideal for generating strings in use-cases, such as: password reset validation codes, coupon or promotional codes, etc.

## Installation
use composer to add **IntlRandString** to your PHP project:
```sh
composer require katmore/intl-rand-string
```

## Character sets
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
$randString = new IntlRandString\Charset\Italian();
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
./vendor/bin/phpunit
```

### make-charset.php dev utility
The **`bin/make-charset.php`** command-line developer utility script creates a character set class defintion PHP source file in the `src/IntlRandString/Charset` directory. After creating a character set, [perform all unit tests](#unit-tests) to ensure conformity.

Specifying the `--help` option will display usage details.
```sh
$ bin/make-charset.php --help
```

### make-all-charsets.sh dev utility
The **`bin/make-all-charsets.sh`** command-line developer utility script contains the Unicode start and end points for all [*Charset* class defintions](#character-sets). Invoking it will (re-)generate all [character set defintion source files](./src/IntlRandString/Charset). 

```sh
$ bin/make-all-charsets.sh
```

The Unicode character ranges are for the charsets are ultimately defined in [bin/make-all-charsets.sh](./bin/make-all-charsets.sh). Therefore, by modifying the [make-all-charsets.sh](./bin/make-all-charsets.sh) source file, character sets can be permanently added or modified. After modifying the source and invoking to (re-)generating character sets, [perform all unit tests](#unit-tests) to ensure conformity.

For example, the ["German" character set](./src/IntlRandString/Charset/German.php) is defined in [*make-all-charsets.sh*](./bin/make-all-charsets.sh#L82-L92) as follows:
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

## Legal
### Copyright
IntlRandString - https://github.com/katmore/intl-rand-string

Copyright (c) 2012-2018 Doug Bird. All Rights Reserved.

### License
IntlRandString is copyrighted free software.
You may redistribute and modify it under either the terms and conditions of the
"The MIT License (MIT)"; or the terms and conditions of the "GPL v3 License".
See [LICENSE](https://github.com/katmore/intl-rand-string/blob/master/LICENSE) and [GPLv3](https://github.com/katmore/intl-rand-string/blob/master/GPLv3).
