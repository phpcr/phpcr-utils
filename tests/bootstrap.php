<?php
/**
 * This is a bootstrap for phpUnit unit tests.
 *
 * @author Nacho Martín <nitram.ohcan@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License 2.0
 *
 * @see http://phpcr.github.io/
 */

// $file2 for run tests if phpcr-utils lib inside of vendor directory.
$file = __DIR__.'/../vendor/autoload.php';
$file2 = __DIR__.'/../../../autoload.php';
if (file_exists($file)) {
    $autoload = require_once $file;
} elseif (file_exists($file2)) {
    $autoload = require_once $file2;
} else {
    throw new RuntimeException('Install dependencies to run test suite.');
}
