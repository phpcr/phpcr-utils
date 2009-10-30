<?php
require_once(dirname(__FILE__) . '/../../inc/baseSuite.php');
require_once(dirname(__FILE__) . '/ReadTest/SessionReadMethods.php');
require_once(dirname(__FILE__) . '/ReadTest/WorkspaceReadMethods.php');
require_once(dirname(__FILE__) . '/ReadTest/NodeReadMethods.php');
require_once(dirname(__FILE__) . '/ReadTest/PropertyReadMethods.php');
require_once(dirname(__FILE__) . '/ReadTest/PropertyTypes.php');
require_once(dirname(__FILE__) . '/ReadTest/Value.php');

class jackalope_tests_level1_ReadTest extends jackalope_baseSuite {
    protected $path = 'level1/read';
    
    public function setUp() {
        parent::setUp();
        $this->sharedFixture['ie']->import('base.xml');
        $this->sharedFixture['session'] = getJCRSession($this->sharedFixture['config']);
    }
    
    public function tearDown() {
        parent::tearDown();
        $this->sharedFixture['session']->logout();
        $this->sharedFixture = null;
    }
    
    public static function suite() {
        $suite = new jackalope_tests_level1_ReadTest('Level1: Read');
        $suite->addTestSuite('jackalope_tests_level1_ReadTest_SessionReadMethods');
        $suite->addTestSuite('jackalope_tests_level1_ReadTest_WorkspaceReadMethods');
        $suite->addTestSuite('jackalope_tests_level1_ReadTest_NodeReadMethods');
        $suite->addTestSuite('jackalope_tests_level1_ReadTest_PropertyReadMethods');
        $suite->addTestSuite('jackalope_tests_level1_ReadTest_PropertyTypes');
        $suite->addTestSuite('jackalope_tests_level1_ReadTest_Value');
        return $suite;
    }
}
