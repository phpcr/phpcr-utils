<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

// 6.2.4 Property Read Methods

class jackalope_tests_level1_ReadTest_PropertyTypes extends jackalope_baseCase {
    protected $types = array(
        'UNDEFINED',
        'STRING',
        'BINARY',
        'LONG',
        'DOUBLE',
        'DATE',
        'BOOLEAN',
        'NAME',
        'PATH',
        'REFERENCE',
        'WEAKREFERENCE',
        'URI',
        'DECIMAL',
    );
    
    protected $typeNames = array(
        'undefined',
        'String',
        'Binary',
        'Long',
        'Double',
        'Date',
        'Boolean',
        'Name',
        'Path',
        'Reference',
        'WeakReference',
        'URI',
        'Decimal',
    );
    
    public function testNameFromValue() {
        for ($x=0;$x < count($this->types);$x++) {
            $this->assertEquals($this->typeNames[$x], PHPCR_PropertyType::nameFromValue($x));
        }
    }
    
    public function valueFromName() {
        for ($x=0;$x < count($this->types);$x++) {
            $this->assertEquals($x, PHPCR_PropertyType::valueFromName($this->types[$x]));
        }
    }
}
