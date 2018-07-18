<?php

require "src/GoogleSearch.php";

use GoogleSearch\GoogleSearch;


$query = "hello world";

$st = new GoogleSearch($query);
$out = $st->exec();

print_r($out);