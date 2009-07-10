<?php
require_once(dirname(__FILE__) . '/../../inc/baseSuite.php');
//6.6.8 getQueryManager is tested in 6.2.2 Workspace Read Methods
require_once(dirname(__FILE__) . '/SearchTest/QueryManager.php'); //6.6.9
require_once(dirname(__FILE__) . '/SearchTest/QueryObjectXpath.php'); //6.6.10
//6.6.11 storeAsNode is about level2, not relevant here
require_once(dirname(__FILE__) . '/SearchTest/QueryResults.php'); //6.6.12
require_once(dirname(__FILE__) . '/SearchTest/Row.php'); //6.6.12
//TODO verify that permission restrictions are respected... (6.6.13)

class jackalope_tests_level1_SearchTest extends jackalope_baseSuite {
    protected $path = 'level1/search';

    public function setUp() {
        parent::setUp();
        $this->sharedFixture['ie']->import('base.xml');
        $this->sharedFixture['session'] = getJCRSession($this->sharedFixture['config']);
        $this->sharedFixture['qm'] = $this->sharedFixture['session']->getWorkspace()->getQueryManager();
    }

    public function tearDown() {
        parent::tearDown();
        $this->sharedFixture['session']->logout();
        $this->sharedFixture = null;
    }

    public static function suite() {
        $suite = new jackalope_tests_level1_SearchTest('Level1: Search');
        $suite->addTestSuite('jackalope_tests_level1_SearchTest_QueryManager');
        $suite->addTestSuite('jackalope_tests_level1_SearchTest_QueryObjectXpath');
        $suite->addTestSuite('jackalope_tests_level1_SearchTest_QueryResults');
        $suite->addTestSuite('jackalope_tests_level1_SearchTest_Row');
        return $suite;
    }

}
