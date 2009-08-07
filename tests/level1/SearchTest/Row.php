<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

//6.6.8 Query API
class jackalope_tests_level1_SearchTest_Row extends jackalope_baseCase {
    private $row;

    public function setUp() {
        $query = $this->sharedFixture['qm']->createQuery('/*/element(tests_level1_search_base, nt:folder)', 'xpath');
        $qr = $query->execute();
        //sanity check
        $this->assertTrue(is_object($qr));
        $this->assertTrue($qr instanceof PHPCR_Query_QueryResultInterface);

        $rs = $qr->getRows();
        $rs->rewind();
        $this->row = $rs->current();

        $this->assertTrue(is_object($this->row));
        $this->assertTrue($this->row instanceof PHPCR_Query_RowInterface);
    }

    public function testRowGetValues() {
        $ret = $this->row->getValues();
        $this->assertType('array', $ret);

        foreach($ret as $value) {
            $this->assertTrue($value instanceof PHPCR_ValueInterface);
        }
    }

    public function testRowGetValue() {
        foreach(jackalope_tests_level1_SearchTest_QueryResults::$expect as $propName) {
            $val = $this->row->getValue($propName);
            $this->assertTrue(is_object($val));
            $this->assertTrue($val instanceof PHPCR_ValueInterface);

            switch($propName) {
                case 'jcr:createdBy':
                    $val->getString();
                    //TODO: seems not to be implemented in alpha5 or null for some other reason. whatever
                    break;
                case 'jcr:created':
                    $str = $val->getString();
                    //2009-07-07T14:35:06.955+02:00
                    list($y, $m, $dusw) = split('-',$str);
                    list($d, $usw) = split('T', $dusw);
                    $this->assertTrue($y > 0);
                    $this->assertTrue($m > 0);
                    $this->assertTrue($d > 0);
                    $this->assertTrue(strlen($usw)==18);
                    $d = $val->getDate();
                    $this->assertTrue($d instanceof DateTime);
                    break;
                case 'jcr:primaryType':
                    //nt:folder - depends on the search query
                    $str = $val->getString();
                    $this->assertEquals('nt:folder', $str);
                    break;
                case 'jcr:path':
                    $str = $val->getString();
                    $this->assertEquals('/tests_level1_search_base', $str);
                    break;
                case 'jcr:score':
                    //for me, it was 1788 but i guess that is highly implementation dependent
                    $num = $val->getInt();
                    $this->assertTrue($num > 0);
                    break;
                default:
                    $this->fail("Unknown property $propName");
            }
        }
    }
}
