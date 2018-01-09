<?php

require "src/GoogleSearch.php";

use GoogleSearch\GoogleSearch;


$query = "php 7.2";

$st = new GoogleSearch($query);
$out = $st->exec();

print_r($out);