<?php

require "src/GoogleSearch.php";

use GoogleSearch\GoogleSearch;


$query = "phpunit";

$st = new GoogleSearch($query);
$out = $st->exec();

var_dump($out);