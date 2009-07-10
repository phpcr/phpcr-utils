<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

//6.6.8 Query API
class jackalope_tests_level1_SearchTest_QueryManager extends jackalope_baseCase {

    public function testCreateQuery() {
        $ret = $this->sharedFixture['qm']->createQuery('/jcr:root', 'xpath');
        $this->assertTrue(is_object($ret));
        $this->assertTrue($ret instanceof phpCR_Query);
    }

    public function testGetQuery() {
        $this->markTestSkipped('TODO: have a stored query in the fixture.');
/*
        $node = $this->sharedFixture['session']->getRootNode()->getNode('/path/to/query node');
        $this->sharedFixture['qm']->getQuery($node);
*/
    }
    /**
     * @expectedException phpCR_InvalidQueryException
     */
    public function testGetQueryInvalid() {
        $this->sharedFixture['qm']->getQuery($this->sharedFixture['session']->getRootNode());
    }

    public function testGetSupportedQueryLanguages() {
        $ret = $this->sharedFixture['qm']->getSupportedQueryLanguages();
        $this->assertType('array', $ret);
        $this->assertContains('xpath', $ret);
    }
}
