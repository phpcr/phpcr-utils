<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

class jackalope_tests_level1_ReadTest_WorkspaceReadMethods extends jackalope_baseCase {
    protected $path = 'level1/read';
    protected $workspace;
    
    //6.2.2 Workspace Read Methods
    
    function setUp() {
        parent::setUp();
        $this->workspace = $this->sharedFixture['session']->getWorkspace();
    }
    
    public function testGetSession() {
        $this->assertEquals($this->sharedFixture['session'], $this->workspace->getSession());
    }
    
    public function testGetName() {
        $this->assertEquals($this->sharedFixture['config']['workspace'], $this->workspace->getName());
    }
    
    public function testGetQueryManager() {
        $qm = $this->workspace->getQueryManager();
        $this->assertTrue(is_object($qm));
        $this->assertTrue($qm instanceOf PHPCR_Query_QueryManagerInterface);
    }
    
    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testGetQueryManagerRepositoryException() {
        $this->markTestSkipped('TODO: Figure how to produce this exception.');
    }
    
    public function testGetNamespaceRegistry() {
        $nr = $this->workspace->getNamespaceRegistry();
        $this->assertTrue(is_object($nr));
        $this->assertTrue($nr instanceOf PHPCR_NamespaceRegistryInterface);
    }
    
    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testGetNamespaceRegistryRepositoryException() {
        $this->markTestSkipped('TODO: Figure how to produce this exception.');
    }
    
    public function testGetNodeTypeManager() {
        $ntm = $this->workspace->getNodeTypeManager();
        $this->assertTrue(is_object($ntm));
        $this->assertTrue($ntm instanceOf PHPCR_NodeType_NodeTypeManagerInterface);
    }
    
    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testGetNodeTypeManagerRepositoryException() {
        $this->markTestSkipped('TODO: Figure how to produce this exception.');
    }
    
    public function testGetAccessibleWorkspaceNames() {
        $names = $this->workspace->getAccessibleWorkspaceNames();
        $this->assertTrue(is_array($names));
        $this->assertContains($this->sharedFixture['config']['workspace'], $names);
    }
    
    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testGetAccessibleWorkspaceNamesRepositoryException() {
        $this->markTestSkipped('TODO: Figure how to produce this exception.');
    }
}
