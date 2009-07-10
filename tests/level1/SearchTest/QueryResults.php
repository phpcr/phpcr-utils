<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

//6.6.8 Query API
class jackalope_tests_level1_SearchTest_QueryResults extends jackalope_baseCase {
    public static $expect = array("jcr:created","jcr:primaryType","jcr:path","jcr:score");

    public function setUp() {
        $query = $this->sharedFixture['qm']->createQuery('//element(*, nt:folder)', 'xpath');
        $this->qr = $query->execute();
        //sanity check
        $this->assertTrue(is_object($this->qr));
        $this->assertTrue($this->qr instanceof phpCR_QueryResult);
    }

    public function testGetColumnNames() {
        $ret = $this->qr->getColumnNames();
        $this->assertType('array', $ret);

        //the fields seem to depend on the node type we filtered for. todo: the field names might be implementation specific

        $this->assertEquals(self::$expect, $ret);
    }

    public function testGetRows() {
        $ret = $this->qr->getRows();

        $this->assertTrue(is_object($ret));
        $this->assertTrue($ret instanceof phpCR_RowIterator);

        $exptsize = $ret->getSize();
        $num = 0;
        foreach($ret as $row) {
            $num++;
            $this->assertTrue($row instanceof phpCR_Row);
        }

        $this->assertEquals($exptsize, $num);
        //further tests in Row.php
    }
    /**
     * @expectedException phpCR_NoSuchElementException
     */
    public function testGetRowsNoSuchElement() {
        $ret = $this->qr->getRows();
        while($row = $ret->nextRow()); //just retrieve until after the last
    }

    public function testGetNodes() {
        $ret = $this->qr->getNodes();

        $this->assertTrue(is_object($ret));
        $this->assertTrue($ret instanceof phpCR_NodeIterator);
        $exptsize = $ret->getSize();
        $num = 0;
        foreach($ret as $node) {
            $num++;
            $this->assertTrue($node instanceof phpCR_Node);
        }
        $this->assertEquals($exptsize, $num);
    }

    /**
     * @expectedException phpCR_NoSuchElementException
     */
    public function testGetNodesNoSuchElement() {
        $ret = $this->qr->getNodes();
        while($row = $ret->nextNode()); //just retrieve after the last
    }
}
