<?php

require "src/GoogleSearch.php";

use GoogleSearch\GoogleSearch;


$query = "es teh";

$st = new GoogleSearch($query);
$out = $st->exec();

echo $out;
echo "\n";