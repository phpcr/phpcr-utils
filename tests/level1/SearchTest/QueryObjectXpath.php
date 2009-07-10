<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

/**
 * 6.6.8 Query API
 *
 * Test XPATH queries
 */
class jackalope_tests_level1_SearchTest_QueryObjectXpath extends jackalope_baseCase {

    public function testExecute() {
        $query = $this->sharedFixture['qm']->createQuery('//idExample[jcr:mimeType="text/plain"]', 'xpath');
        $qr = $query->execute();
        $this->assertTrue(is_object($qr));
        $this->assertTrue($qr instanceof phpCR_QueryResult);
        //content of result is tested in QueryResults
    }

    /**
     * @expectedException phpCR_InvalidQueryException
     *
     * the doc claims there would just be a phpCR_RepositoryException
     * it makes sense that there is a InvalidQueryException
     */
    public function testExecuteInvalid() {
        $query = $this->sharedFixture['qm']->createQuery('this is no xpath statement', 'xpath');
        $qr = $query->execute();
    }
    /*
    testGetStatement()
    testGetLanguage()
    testGetStoredQueryPath()
    testGetStoredQueryPathItemNotFound()
    //not yet stored
    testStoreAsNode()
    +diverse exceptions
    testXPATH
    testSQL
    */
}
