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
if (! $phpcr = getenv('phpcr.srcdir')) {
    if (isset($GLOBALS['phpcr.srcdir'])) {
        $phpcr = $GLOBALS['phpcr.srcdir'];
    }
}
if ($phpcr) {
    $classLoader->registerNamespaces(array(
        'PHPCR'         => __DIR__."/$phpcr",
    ));
}
