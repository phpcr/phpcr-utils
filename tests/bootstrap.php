<?php
/**
 * This is a bootstrap for phpUnit unit tests
 *
 * @author Nacho Martín <nitram.ohcan@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License 2.0
 * @link http://phpcr.github.com/
 */
if (!class_exists('PHPUnit_Framework_TestCase') ||
    version_compare(PHPUnit_Runner_Version::id(), '3.5') < 0
) {
    die('PHPUnit framework is required, at least 3.5 version');
}

if (!class_exists('PHPUnit_Framework_MockObject_MockBuilder')) {
    die('PHPUnit MockObject plugin is required, at least 1.0.8 version');
}

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
