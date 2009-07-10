<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

class jackalope_tests_level1_AccessTest_RepositoryDescriptors extends jackalope_baseCase {
    protected $path = 'level1/read';
    
    //Those constants need to be defined in the bootstrap file
    protected $expectedDescriptors = array(
        SPEC_VERSION_DESC,
        SPEC_NAME_DESC,
        REP_VENDOR_DESC,
        REP_VENDOR_URL_DESC,
        REP_NAME_DESC,
        REP_VERSION_DESC,
        LEVEL_1_SUPPORTED,
        LEVEL_2_SUPPORTED,
        OPTION_TRANSACTIONS_SUPPORTED,
        OPTION_VERSIONING_SUPPORTED,
        OPTION_OBSERVATION_SUPPORTED,
        OPTION_LOCKING_SUPPORTED,
        OPTION_QUERY_SQL_SUPPORTED,
        QUERY_XPATH_POS_INDEX,
        QUERY_XPATH_DOC_ORDER
    );
    
    // 6.1.1.1 Repository Descriptors 
    public function testDescriptorKeys() {
        $rep = getRepository($this->sharedFixture['config']);
        $keys = $rep->getDescriptorKeys();
        $this->assertTrue(is_array($keys));
        $this->assertFalse(empty($keys));
        foreach ($this->expectedDescriptors as $descriptor) {
            $this->assertContains($descriptor, $keys);
        }
    }

    //TODO: Check if the values are compatible to the spec
    public function testDescription() {
        $rep = getRepository($this->sharedFixture['config']);
        foreach ($this->expectedDescriptors as $descriptor) {
            $str = $rep->getDescriptor($descriptor);
            $this->assertTrue(is_string($str));
            $this->assertFalse(empty($str));
        }
    }
}
