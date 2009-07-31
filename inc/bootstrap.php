<?php

/**
 * This file does some basic stuff that's project specific and must
 * be replaced if you want to reuse our tests.
 */

// Make sure we have the necessary config
$necessaryConfigValues = array('jcr.url', 'jcr.user', 'jcr.pass', 'jcr.workspace', 'jcr.transport');
foreach ($necessaryConfigValues as $val) {
    if (empty($GLOBALS[$val])) {
        die('Please set '.$val.' in your phpunit.xml.' . "\n");
    }
}

//Autoloader used for jackalope
function autoload($class) {
    $incFile = dirname(__FILE__) . '/../lib/' . str_replace("_", DIRECTORY_SEPARATOR, $class).".php";
    if (@fopen($incFile, "r", TRUE)) {
        include($incFile);
        return $incFile;
    }
    return FALSE;
}
spl_autoload_register('autoload');

//Helper function which returns the repository necessary for some tests
function getRepository($config) {
    if (empty($config['url']) || empty($config['transport'])) {
        return false;
    }
    return jr_cr::lookup($config['url'], $config['transport']);
}

function getSimpleCredentials($user, $password) {
    return new jr_cr_simplecredentials($user, $password);
}

//Main method which returns a session to the tests
function getJCRSession($config, $credentials = null) {
    $repository = getRepository($config);
    if (isset($config['pass']) || isset($credentials)) {
        if (empty($config['workspace'])) {
            $config['workspace'] = null;
        }
        if (empty($credentials)) {
            $credentials = getSimpleCredentials($config['user'], $config['pass']);
        }
        return $repository->login($credentials, $config['workspace']);
    } elseif (isset($config['workspace'])) {
        return $repository->login(null, $config['workspace']);
    } else {
        return $repository->login(null, null);
    }
}
define('SPEC_VERSION_DESC', 'jcr.specification.version');
define('SPEC_NAME_DESC', 'jcr.specification.name');
define('REP_VENDOR_DESC', 'jcr.repository.vendor');
define('REP_VENDOR_URL_DESC', 'jcr.repository.vendor.url');
define('REP_NAME_DESC', 'jcr.repository.name');
define('REP_VERSION_DESC', 'jcr.repository.version');
define('LEVEL_1_SUPPORTED', 'level.1.supported');
define('LEVEL_2_SUPPORTED', 'level.2.supported');
define('OPTION_TRANSACTIONS_SUPPORTED', 'option.transactions.supported');
define('OPTION_VERSIONING_SUPPORTED', 'option.versioning.supported');
define('OPTION_OBSERVATION_SUPPORTED', 'option.observation.supported');
define('OPTION_LOCKING_SUPPORTED', 'option.locking.supported');
define('OPTION_QUERY_SQL_SUPPORTED', 'option.query.sql.supported');
define('QUERY_XPATH_POS_INDEX', 'query.xpath.pos.index');
define('QUERY_XPATH_DOC_ORDER', 'query.xpath.doc.order');