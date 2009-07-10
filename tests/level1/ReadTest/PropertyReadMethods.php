<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

// 6.2.4 Property Read Methods

class jackalope_tests_level1_ReadTest_PropertyReadMethods extends jackalope_baseCase {
    protected $path = 'level1/read';
    protected $node;
    protected $property;
    protected $multiProperty;
    
    public function setUp() {
        parent::setUp();
        $this->node = $this->sharedFixture['session']->getRootNode()->getNode('tests_level1_access_base');
        $this->property = $this->node->getProperty('jcr:created');
        $this->multiProperty = $this->node->getNode('multiValueProperty')->getProperty('jcr:mixinTypes');
    }
    
    public function testGetValue() {
        $val = $this->property->getValue();
        $this->assertType('object', $val);
        $this->assertTrue($val instanceOf PHPCR_ValueInterface);
    }
    
    /**
     * @expectedException PHPCR_ValueFormatException
     */
    public function testGetValueValueFormatException() {
        $this->multiProperty->getValue();
    }
    
    /**
     * @expectedException PHPCR_RepostioryException
     */
    public function testGetValueRepositoryException() {
        $this->markTestSkipped('TODO: Figure out how to provoke this error.');
    }
    
    public function testGetValues() {
        $vals = $this->multiProperty->getValues();
        $this->assertType('array', $vals);
        foreach ($vals as $val) {
            $this->assertTrue($val instanceOf PHPCR_ValueInterface);
        }
    }
    
    /**
     * @expectedException PHPCR_ValueFormatException
     */
    public function testGetValuesValueFormatException() {
        $this->property->getValues();
    }
    
    /**
     * @expectedException PHPCR_RepostioryException
     */
    public function testGetValuesRepositoryException() {
        $this->markTestSkipped('TODO: Figure out how to provoke this error.');
    }
    
    public function testGetString() {
        $expectedStr = date('o-m-d\T');
        $str = $this->property->getString();
        $this->assertType('string', $str);
        $this->assertEquals(0, strpos($str, $expectedStr));
    }
    
    public function testGetLong() {
        $prop = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('longNumber');
        $num = $prop->getLong();
        $this->assertType('int', $num);
        $this->assertEquals(999, $num);
    }
        
    /**
     * @expectedException PHPCR_ValueFormatException
     */
    public function testGetLongValueFormatException() {
        $this->multiProperty->getLong();
    }
    
    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testGetLongRepositoryException() {
        $this->markTestSkipped('TODO: Figure out how to provoke this error.');
    }
    
    /**
     * The PHP Implementation requires that getLong and getInt return the same
     */
    public function testGetLongAndIntSame() {
        $long = $this->property->getLong();
        $int = $this->property->getInt();
        $this->assertEquals($long, $int);
    }
    
    public function testGetDouble() {
        $node = $this->node->getNode('numberPropertyNode/jcr:content');
        $node->setProperty('newFloat', 3.9999);
        $prop = $node->getProperty('newFloat');
        $num = $prop->getDouble();
        $this->assertType('float', $num);
        $this->assertEquals(3.9999, $num);
    }
    
    /**
     * @expectedException PHPCR_ValueFormatException
     */
    public function testGetDoubleValueFormatException() {
        $this->multiProperty->getDouble();
    }
    
    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testGetDoubleRepositoryException() {
        $this->markTestSkipped('TODO: Figure out how to provoke this error.');
    }
    
    /**
     * The PHP Implementation requires that getDouble and getFloat return the same
     */
    public function testGetDoubleAndFloatSame() {
        $double = $this->property->getDouble();
        $float = $this->property->getFloat();
        $this->assertEquals($double, $float);
    }
    
    public function testGetDate() {
        $date = $this->property->getDate();
        $this->assertType('object', $date);
        $this->assertTrue($date instanceOf DateTime);
        $this->assertEquals(floor($date->format('U') / 1000), floor(time() / 1000));
    }
    
    /**
     * @expectedException PHPCR_ValueFormatException
     */
    public function testGetDateValueFormatExceptionMulti() {
        $vals = $this->multiProperty->getDate();
    }
    
    /**
     * @expectedException PHPCR_ValueFormatException
     */
    public function testGetDateValueFormatException() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('foo', 'bar');
        $node->getProperty('foo')->getDate();
    }
    
    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testGetDateRepositoryException() {
        $this->markTestSkipped('TODO: Figure out how to provoke this error.');
    }
    
    public function testGetBool() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newBool', true);
        $this->assertTrue($node->getProperty('newBool')->getBoolean());;
    }
    
    /**
     * @expectedException PHPCR_ValueFormatException
     */
    public function testGetBoolValueFormatExceptionMulti() {
        $vals = $this->multiProperty->getBoolean();
    }
    
    /**
     * @expectedException PHPCR_ValueFormatException
     */
    public function testGetBoolValueFormatException() {
        $this->property->getBoolean();
    }
    
    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testGetBoolRepositoryException() {
        $this->markTestSkipped('TODO: Figure out how to provoke this error.');
    }
    
    public function testGetNode() {
        $node = $this->property->getNode();
        $this->assertType('object', $node);
        $this->assertTrue($node instanceOf PHPCR_NodeInterface);
        $this->assertEquals($node, $this->node);
    }
    
    public function testGetLength() {
        $this->assertEquals(29, $this->property->getLength());
    }
    
    public function testGetLengthBinary() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newBinary', 'foobar', PHPCR_PropertyType::BINARY);
        $this->assertEquals(6, $node->getProperty('newBinary')->getLength());
    }
    
    public function testGetLengthUnsuccessfull() {
        $this->markTestSkipped('TODO: This should return -1 but how can I reproduce?');
    }
    
    /**
     * @expectedException PHPCR_ValueFormatException
     */
    public function testGetLengthValueFormatExceptionMulti() {
        $this->multiProperty->getLength();
    }
    
    public function testGetLengths() {
        $this->assertEquals(array(17, 15), $this->multiProperty->getLengths());
    }
    
    public function testGetLengthsBinary() {
        $this->markTestSkipped('TODO: Figure how multivalue binary properties can be set');
    }
    
    public function testGetLengthsUnsuccessfull() {
        $this->markTestSkipped('TODO: This should return -1 but how can I reproduce?');
    }
    
    /**
     * @expectedException PHPCR_ValueFormatException
     */
    public function testGetLengthsValueFormatExceptionMulti() {
        $this->property->getLengths();
    }
    
    public function testGetTypeString() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newString', 'foobar', PHPCR_PropertyType::STRING);
        $this->assertEquals(PHPCR_PropertyType::STRING, $node->getProperty('newString')->getType());
    }
    
    public function testGetTypeBinary() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newBin', 'foobar', PHPCR_PropertyType::BINARY);
        $this->assertEquals(PHPCR_PropertyType::BINARY, $node->getProperty('newBin')->getType());
    }
    
    public function testGetTypeLong() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newLong', 3, PHPCR_PropertyType::LONG);
        $this->assertEquals(PHPCR_PropertyType::LONG, $node->getProperty('newLong')->getType());
    }
    
    public function testGetTypeDouble() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newDouble', 3.5, PHPCR_PropertyType::DOUBLE);
        $this->assertEquals(PHPCR_PropertyType::DOUBLE, $node->getProperty('newDouble')->getType());
    }
    
    public function testGetTypeDate() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newDate', 'foobar', PHPCR_PropertyType::DATE);
        $this->assertEquals(PHPCR_PropertyType::DATE, $node->getProperty('newDate')->getType());
    }
    
    public function testGetTypeBoolean() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newBool', true, PHPCR_PropertyType::BOOLEAN);
        $this->assertEquals(PHPCR_PropertyType::BOOLEAN, $node->getProperty('newBool')->getType());
    }
    
    public function testGetTypeName() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newName', 'foobar', PHPCR_PropertyType::NAME);
        $this->assertEquals(PHPCR_PropertyType::NAME, $node->getProperty('newName')->getType());
    }
    
    public function testGetTypePath() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newPath', 'foobar', PHPCR_PropertyType::PATH);
        $this->assertEquals(PHPCR_PropertyType::PATH, $node->getProperty('newPath')->getType());
    }
    
    public function testGetTypeReference() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newRef', 'foobar', PHPCR_PropertyType::REFERENCE);
        $this->assertEquals(PHPCR_PropertyType::REFERENCE, $node->getProperty('newRef')->getType());
    }
}
