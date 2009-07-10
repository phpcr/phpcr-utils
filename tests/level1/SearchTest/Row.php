<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

//6.6.8 Query API
class jackalope_tests_level1_SearchTest_Row extends jackalope_baseCase {
    private $row;

    public function setUp() {
        $query = $this->sharedFixture['qm']->createQuery('//element(*, nt:folder)', 'xpath');
        $qr = $query->execute();
        //sanity check
        $this->assertTrue(is_object($qr));
        $this->assertTrue($qr instanceof phpCR_QueryResult);

        $this->row = $qr->getRows()->nextRow();

        $this->assertTrue(is_object($this->row));
        $this->assertTrue($this->row instanceof phpCR_Row);
    }

    public function testRowGetValues() {
        $ret = $this->row->getValues();
        $this->assertType('array', $ret);

        foreach($ret as $value) {
            $this->assertTrue($value instanceof phpCR_Value);
        }
    }

    public function testRowGetValue() {
        foreach(jackalope_tests_level1_SearchTest_QueryResults::$expect as $propName) {
            $val = $this->row->getValue($propName);
            $this->assertTrue(is_object($val));
            $this->assertTrue($val instanceof phpCR_Value);

            switch($propName) {
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
                    $this->assertEquals('/tests_level1_search_base/emptyExample', $str);
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
