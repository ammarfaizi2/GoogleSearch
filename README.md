# Google Search


## Install via Composer :
```
composer require ammarfaizi2/googletranslate
```



## Example : 
```php
<?php

require "src/GoogleSearch.php";

use GoogleSearch\GoogleSearch;


$query = "phpunit";

$st = new GoogleSearch($query);
$out = $st->exec();

print_r($out);
```



## Result :

```
Array
(
    [0] => Array
        (
            [url] => https://phpunit.de/
            [heading] => PHPUnit
            [description] => PHPUnit is a programmer-oriented testing framework for PHP. It is an instance of the xUnit architecture ...
        )

    [1] => Array
        (
            [url] => https://github.com/sebastianbergmann/phpunit
            [heading] => GitHub - sebastianbergmann/phpunit: The PHP Unit Testing ...
            [description] => The PHP Unit Testing framework. Contribute to phpunit development by creating an account on GitHub.
        )

    [2] => Array
        (
            [url] => https://jtreminio.com/2013/03/unit-testing-tutorial-introduction-to-phpunit/
            [heading] => Unit Testing Tutorial Part I: Introduction to PHPUnit — Juan ...
            [description] => To run PHPUnit, you simply do $ ./vendor/bin/phpunit . This will print all options available to you.
        )

    [3] => Array
        (
            [url] => https://en.m.wikipedia.org/wiki/PHPUnit
            [heading] => PHPUnit - Wikipedia
            [description] => PHPUnit is a unit testing framework for the PHP programming language. It is an instance of the xUnit ...
        )

    [4] => Array
        (
            [url] => https://www.drupal.org/docs/8/phpunit
            [heading] => PHPUnit in Drupal 8 | Drupal.org
            [description] => The testing framework PHPUnit was added to Drupal 8. SimpleTest is still supported but is deprecated.
        )

    [5] => Array
        (
            [url] => https://www.jetbrains.com/help/phpstorm/testing-with-phpunit.html
            [heading] => Testing with PHPUnit - Help | PhpStorm - JetBrains
            [description] => PhpStorm supports unit testing of PHP applications through integration with the PHPUnit tool.
        )

    [6] => Array
        (
            [url] => https://samsonasik.wordpress.com/2010/06/20/phpunit-unit-testing-for-php/
            [heading] => PHPUnit : Unit Testing For PHP | Welcome to Abdul Malik ...
            [description] => 20 Jun 2010 · PHPUnit adalah Unit Testing Framework untuk bahasa pemrograman PHP. Pertama ...
        )

)
```