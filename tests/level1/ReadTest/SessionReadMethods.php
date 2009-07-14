<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

class jackalope_tests_level1_ReadTest_SessionReadMethods extends jackalope_baseCase {
    protected $path = 'level1/read';
    
    //6.2.1 Session Read Methods
    public function testGetRepository() {
        $rep = $this->sharedFixture['session']->getRepository();
        $this->assertTrue(is_object($rep));
        $this->assertTrue($rep instanceOf PHPCR_RepositoryInterface);
    }
    
    public function testGetUserId() {
        $user = $this->sharedFixture['session']->getUserId();
        $this->assertEquals($this->sharedFixture['config']['user'], $user);
    }
    
    public function testGetAttributeNames() {
        $this->markTestSkipped('TODO: Figure why Jackrabbit is not returning the AttributeNames');
        $cr = $this->assertSimpleCredentials($this->sharedFixture['config']['user'], $this->sharedFixture['config']['pass']);
        $cr->setAttribute('foo', 'bar');
        $session = $this->assertSession($this->sharedFixture['config'], $cr);
        $attrs = $session->getAttributeNames();
        $this->assertTrue(is_array($attrs));
        $this->assertContains('foo', $attrs);
    }
    
    public function testGetAttribute() {
        $this->markTestSkipped('TODO: Figure why Jackrabbit is not returning the Attribute');
        $cr = $this->assertSimpleCredentials($this->sharedFixture['config']['user'], $this->sharedFixture['config']['pass']);
        $cr->setAttribute('foo', 'bar');
        $session = $this->assertSession($this->sharedFixture['config'], $cr);
        $val = $session->getAttribute('foo');
        $this->assertTrue(is_string($val));
        $this->assertEquals($val, 'bar');
    }
    
    public function testGetWorkspace() {
        $workspace = $this->sharedFixture['session']->getWorkspace();
        $this->assertTrue($workspace instanceOf PHPCR_WorkspaceInterface);
    }
    
    public function testGetRootNode() {
        $node = $this->sharedFixture['session']->getRootNode();
        $this->assertTrue($node instanceOf PHPCR_NodeInterface);
        $this->assertEquals($node->getPath(), '/');
    }
    
    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testGetRootNodeRepositoryException() {
        $this->markTestIncomplete('TODO: Figure out how to test this');
    }
    
    public function testGetItem() {
        $node = $this->sharedFixture['session']->getItem('tests_level1_access_base');
        $this->assertTrue($node instanceOf PHPCR_NodeInterface);
        $this->assertEquals($node->getName(), 'tests_level1_access_base');
    }
    
    /**
     * @expectedException PHPCR_PathNotFoundException
     */
    public function testGetItemPathNotFound() {
        $this->sharedFixture['session']->getItem('/foobar');
    }
    
    /**
     * @expectedException PHPCR_RepositoryException
     */
     public function testGetItemRepositoryException() {
         $this->sharedFixture['session']->getItem('//');
     }
    
    public function testItemExists() {
        $this->assertTrue($this->sharedFixture['session']->itemExists('/tests_level1_access_base'));
        $this->assertTrue($this->sharedFixture['session']->itemExists('tests_level1_access_base'));
        $this->assertFalse($this->sharedFixture['session']->itemExists('foobar'));
    }
    
    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testItemExistsRepositoryException() {
        $this->sharedFixture['session']->itemExists('//');
    }
    
    public function testGetNodeByUUID() {
        $node1 = $this->sharedFixture['session']->getItem('/tests_level1_access_base/idExample');
        $node2 = $this->sharedFixture['session']->getNodeByUUID('842e61c0-09ab-42a9-87c0-308ccc90e6f4');
        $this->assertTrue($node2 instanceOf PHPCR_NodeInterface);
        $this->assertEquals($node1, $node2);
    }
    
    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testGetNodeByUUIDRepositoryException() {
        $this->sharedFixture['session']->getNodeByUUID('foo');
    }
    
    /**
     * @expectedException PHPCR_ItemNotFoundException
     */
    public function testGetNodeByUUIDItemNotFoundException() {
        $this->sharedFixture['session']->getNodeByUUID(jr_cr_node::uuid());
    }
    
    /**
     * @expectedException JavaException
     */
    public function testImpersonate() {
        //TODO: Check if that's implemented in newer jackrabbit versions.
        //TODO: Write tests for LoginException and RepositoryException
        $cr = $this->assertSimpleCredentials($this->sharedFixture['config']['user'], $this->sharedFixture['config']['pass']);
        $ses = $this->sharedFixture['session']->impersonate($cr);
    }
    
    public function testIsLiveLogout() {
        $ses = $this->assertSession($this->sharedFixture['config']);
        $this->assertTrue($ses->isLive());
        $ses->logout();
        $this->assertTrue($ses instanceOf PHPCR_SessionInterface);
        $this->assertFalse($ses->isLive());
    }
}
