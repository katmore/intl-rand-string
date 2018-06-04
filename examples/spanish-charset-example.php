<?php
require __DIR__.'/../vendor/autoload.php';

$randString = new IntlRandString\Charset\Spanish();
echo $randString->randomString(12);

echo PHP_EOL;