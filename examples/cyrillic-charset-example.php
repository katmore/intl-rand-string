<?php
require __DIR__.'/../vendor/autoload.php';

$randString = new IntlRandString\Charset\Cyrillic();
echo $randString->randomString(12);

echo PHP_EOL;