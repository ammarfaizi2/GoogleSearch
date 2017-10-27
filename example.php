<?php

require "src/GoogleSearch.php";

use GoogleSearch\GoogleSearch;


$query = "ammar faizi";

$st = new GoogleSearch($query);
$out = $st->exec();

print_r($out);