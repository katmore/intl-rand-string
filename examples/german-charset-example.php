<?php
require __DIR__.'/../vendor/autoload.php';

$randString = new IntlRandString\Charset\German();
echo $randString->randomString(12);

echo PHP_EOL;