<?php
require_once(dirname(__FILE__) . '/../../inc/baseSuite.php');
require_once(dirname(__FILE__) . '/NamespacesTest/NamespaceRegistry.php'); //6.3.1
//6.3.2 is explanation only
require_once(dirname(__FILE__) . '/NamespacesTest/SessionNamespaceRemapping.php'); //6.3.3
//6.3.4 is explanation only

class jackalope_tests_level1_NamespacesTest extends jackalope_baseSuite {
    public function setUp() {
        parent::setUp();
        $this->sharedFixture['session'] = getJCRSession($this->sharedFixture['config']);
    }

    public function tearDown() {
        parent::tearDown();
        $this->sharedFixture['session']->logout();
        $this->sharedFixture = null;
    }

    public static function suite() {
        $suite = new jackalope_tests_level1_NamespacesTest('Level1: Namespaces');
        $suite->addTestSuite('jackalope_tests_level1_NamespacesTest_NamespaceRegistry');
        $suite->addTestSuite('jackalope_tests_level1_NamespacesTest_SessionNamespaceRemapping');
        return $suite;
    }
}
