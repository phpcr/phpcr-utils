<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

//6.5 Export Repository Content
class jackalope_tests_level1_ExportTest_ImportRepositoryContent extends jackalope_baseCase {

    public function testImportXML() {
        $this->markTestSkipped('TODO: what kind of stream is ok for input?');
        //$this->sharedFixture['session']->importXML('/', input stream, behaviour flags);
    }
}
