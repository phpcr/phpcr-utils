<?php

$vendorDir = __DIR__.'/../lib/vendor';
require_once $vendorDir.'/Symfony/Component/ClassLoader/UniversalClassLoader.php';

$classLoader = new \Symfony\Component\ClassLoader\UniversalClassLoader();
$classLoader->register();

$classLoader->registerNamespaces(array(
    'PHPCR\\Util'   => __DIR__.'/../src',
    'Symfony\Component\Console' => __DIR__.'/../lib/vendor',
    'Symfony\Component\ClassLoader' => __DIR__.'/../lib/vendor',
));

/* for phpunit.xml and travis */
if (isset($GLOBALS['phpcr_srcdir'])) {
    $phpcr = $GLOBALS['phpcr_srcdir'];
} else if (file_exists(__DIR__.'/../lib/vendor/phpcr/src')) {
    $phpcr = __DIR__.'/../lib/vendor/phpcr/src';
} else {
    $phpcr = false;
}

if ($phpcr) {
    if (! file_exists("$phpcr/PHPCR")) {
        die("Invalid phpcr directory specified: $phpcr");
    }
    $classLoader->registerNamespaces(array(
        'PHPCR'         => $phpcr,
    ));
}
