<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

//6.3.1 Namespace Registry
class jackalope_tests_level1_NamespacesTest_NamespaceRegistry extends jackalope_baseCase {
    protected $workspace;
    protected $nr; //the NamespaceRegistry
    protected $nsBuiltIn = array('jcr' => 'http://www.jcp.org/jcr/1.0',
                                 'nt'  => 'http://www.jcp.org/jcr/nt/1.0',
                                 'mix' => 'http://www.jcp.org/jcr/mix/1.0',
                                 'xml' => 'http://www.w3.org/XML/1998/namespace',
                                 ''    => '');

    function setUp() {
        parent::setUp();
        $this->workspace = $this->sharedFixture['session']->getWorkspace();
        $this->nr = $this->workspace->getNamespaceRegistry(); //this function is tested in ReadTest/WorkspaceReadMethods.php::testGetNamespaceRegistry
    }

    public function testGetPrefixes() {
        $ret = $this->nr->getPrefixes();
        $this->assertType('array', $ret);
        $this->assertTrue(count($ret) >= count($this->nsBuiltIn));
    }

    public function testGetURIs() {
        $ret = $this->nr->getURIs();
        $this->assertTrue(is_array($ret));
        $this->assertTrue(count($ret) >= count($this->nsBuiltIn));
        //we test in getURI / getPrefix if the names match
    }

    public function testGetURI() {
        foreach($this->nsBuiltIn as $prefix => $uri) {
            $ret = $this->nr->getURI($prefix);
            $this->assertEquals($uri, $ret);
        }
    }

    /**
     * @expectedException phpCR_NamespaceException
     */
    public function testGetURINamespaceException() {
        $this->nr->getURI('thisshouldnotexist');
    }

    /**
     * @expectedException phpCR_RepositoryException
     */
    public function testGetURIRepositoryException() {
        $this->nr->getURI('in:valid');
    }

    public function testGetPrefix() {
        foreach($this->nsBuiltIn as $prefix => $uri) {
            $ret = $this->nr->getPrefix($uri);
            $this->assertEquals($prefix, $ret);
        }
    }

    /**
     * @expectedException phpCR_NamespaceException
     */
    public function testGetPrefixNamespaceException() {
        $this->nr->getPrefix('http://thisshouldnotexist.org/0.0');
    }
}
