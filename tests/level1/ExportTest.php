<?php
require_once(dirname(__FILE__) . '/../../inc/baseSuite.php');
require_once(dirname(__FILE__) . '/ExportTest/ExportRepositoryContent.php'); //6.5
require_once(dirname(__FILE__) . '/ExportTest/ImportRepositoryContent.php'); //6.5

class jackalope_tests_level1_ExportTest extends jackalope_baseSuite {
    protected $path = 'level1/export';

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
        $suite = new jackalope_tests_level1_ExportTest('Level1: Export');
        $suite->addTestSuite('jackalope_tests_level1_ExportTest_ExportRepositoryContent');
        $suite->addTestSuite('jackalope_tests_level1_ExportTest_ImportRepositoryContent');
        return $suite;
    }

}
