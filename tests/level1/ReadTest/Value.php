<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

// According to PHPCR_ValueInterface

class jackalope_tests_level1_ReadTest_Value extends jackalope_baseCase {
    protected $node;
    protected $value;
    
    public function setUp() {
        parent::setUp();
        $this->node = $this->sharedFixture['session']->getRootNode()->getNode('tests_level1_access_base/numberPropertyNode/jcr:content');
        $this->value = $this->node->getProperty('foo')->getValue();
        $this->assertTrue($this->value instanceOf PHPCR_ValueInterface);
    }
    
    public function testGetString() {
        $str = $this->value->getString();
        $this->assertType('string', $str);
        $this->assertEquals('bar', $str);
    }
    
    public function testGetBinary() {
        $bin = $this->value->getBinary();
        $str = $this->value->getString();
        echo $str;
        echo pack('nvc*', extract(explode('', $str)));
        $this->markTestIncomplete('Figure how to represent this in PHP');
    }
}
