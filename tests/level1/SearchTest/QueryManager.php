<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

//6.6.8 Query API
class jackalope_tests_level1_SearchTest_QueryManager extends jackalope_baseCase {
    public function testCreateQuery() {
        $ret = $this->sharedFixture['qm']->createQuery('/jcr:root', 'xpath');
        $this->assertTrue(is_object($ret));
        $this->assertTrue($ret instanceof PHPCR_Query_QueryInterface);
    }

    public function testGetQuery() {
        $this->sharedFixture['ie']->import('query.xml');
        try {
            $qnode = $this->sharedFixture['session']->getRootNode()->getNode('queryNode');
            $this->assertTrue(is_object($qnode));
            $this->assertTrue($qnode instanceof PHPCR_NodeInterface);

            $query = $this->sharedFixture['qm']->getQuery($qnode);
            $this->assertTrue(is_object($qnode));
            $this->assertTrue($query instanceof PHPCR_Query_QueryInterface);
        } catch(exception $e) {
            //FIXME: finally?
            $this->sharedFixture['ie']->import('base.xml');
            throw $e;
        }
        $this->sharedFixture['ie']->import('base.xml');
    }
    /**
     * @expectedException PHPCR_Query_InvalidQueryException
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
