<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

/**
 * 6.6.10 Query Object
 *
 */
class jackalope_tests_level1_SearchTest_QueryObjectXpath extends jackalope_baseCase {

    public function testExecute() {
        $query = $this->sharedFixture['qm']->createQuery('//idExample[jcr:mimeType="text/plain"]', 'xpath');
        $qr = $query->execute();
        $this->assertTrue(is_object($qr));
        $this->assertTrue($qr instanceof PHPCR_Query_QueryResultInterface);
        //content of result is tested in QueryResults
    }

    /**
     * @expectedException PHPCR_Query_InvalidQueryException
     *
     * the doc claims there would just be a phpCR_RepositoryException
     * it makes sense that there is a InvalidQueryException
     */
    public function testExecuteInvalid() {
        $query = $this->sharedFixture['qm']->createQuery('this is no xpath statement', 'xpath');
        $qr = $query->execute();
    }

    public function testGetStatement() {
        $qstr = '//idExample[jcr:mimeType="text/plain"]';
        $query = $this->sharedFixture['qm']->createQuery($qstr, 'xpath');
        $this->assertEquals($qstr, $query->getStatement());
    }
    public function testGetLanguage() {
        $qstr = '//idExample[jcr:mimeType="text/plain"]';
        $query = $this->sharedFixture['qm']->createQuery($qstr, 'xpath');
        $this->assertEquals('xpath', $query->getLanguage());
    }
    /**
     * a transient query has no stored query path
     * @expectedException PHPCR_ItemNotFoundException
     */
    public function testGetStoredQueryPathItemNotFound() {
        $qstr = '//idExample[jcr:mimeType="text/plain"]';
        $query = $this->sharedFixture['qm']->createQuery($qstr, 'xpath');
        $query->getStoredQueryPath();
    }
    /* this is level 2 only */
    /*
    public function testStoreAsNode() {
        $qstr = '//idExample[jcr:mimeType="text/plain"]';
        $query = $this->sharedFixture['qm']->createQuery($qstr, 'xpath');
        $query->storeAsNode('/queryNode');
        $this->sharedFixture['session']->save();
    }
    */
    /*
    +diverse exceptions
    */

    /** changes repository state */
    public function testGetStoredQueryPath() {
        $this->sharedFixture['ie']->import('query.xml');
        try {
            $qnode = $this->sharedFixture['session']->getRootNode()->getNode('queryNode');
            $this->assertTrue(is_object($qnode));
            $this->assertTrue($qnode instanceof PHPCR_NodeInterface);

            $query = $this->sharedFixture['qm']->getQuery($qnode);
            $this->assertTrue(is_object($qnode));
            $this->assertTrue($query instanceof PHPCR_Query_QueryInterface);
            //same as QueryManager::testGetQuery

            $p = $query->getStoredQueryPath();
            $this->assertEquals('/queryNode', $p);
        } catch(exception $e) {
            //FIXME: finally?
            $this->sharedFixture['ie']->import('base.xml');
            throw $e;
        }
        $this->sharedFixture['ie']->import('base.xml');
    }

}
