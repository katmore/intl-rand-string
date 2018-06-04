<?php
require __DIR__.'/../vendor/autoload.php';

$randString = new IntlRandString\Charset\Italian();
echo $randString->randomString(12);

echo PHP_EOL;