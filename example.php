<?php

require "src/GoogleSearch.php";

use GoogleSearch\GoogleSearch;


$query = "es teh";

$st = new GoogleSearch($query);
$out = $st->exec();

print_r($out);