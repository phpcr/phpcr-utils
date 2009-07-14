<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

class jackalope_tests_level1_ReadTest_NodeReadMethods extends jackalope_baseCase {
    protected $path = 'level1/read';
    protected $rootNode;
    protected $node;
    
    // 6.2.3 Node Read Methods
    
    function setUp() {
        parent::setUp();
        $this->rootNode = $this->sharedFixture['session']->getRootNode();
        $this->node = $this->rootNode->getNode('tests_level1_access_base');
    }
    
    public function testGetNodeAbsolutePath() {
        $node = $this->rootNode->getNode('/tests_level1_access_base');
        $this->assertTrue(is_object($node));
        $this->assertTrue($node instanceOf PHPCR_NodeInterface);
        $this->assertEquals('tests_level1_access_base', $node->getName());
    }

    public function testGetNodeRelativePath() {
        $node = $this->rootNode->getNode('tests_level1_access_base');
        $this->assertTrue(is_object($node));
        $this->assertTrue($node instanceOf PHPCR_NodeInterface);
        $this->assertEquals('tests_level1_access_base', $node->getName());
    }
    
    /**
     * @expectedException PHPCR_PathNotFoundException
     */
    public function testGetNodePathNotFoundException() {
        $this->rootNode->getNode('/foobar');
    }
    
    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testGetNodeRepositoryException() {
        $this->rootNode->getNode('//');
    }
    
    public function testGetNodes() {
        $node1 = $this->rootNode->getNode('tests_level1_access_base');
        $iterator = $this->rootNode->getNodes();
        $this->assertTrue(is_object($iterator));
        $this->assertTrue($iterator instanceOf PHPCR_NodeIteratorInterface);
    }
    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testGetNodesRepositoryException() {
        $this->markTestIncomplete('TODO: Figure how to produce this exception');
    }
    
    public function testGetNodesPattern() {
        $iterator = $this->node->getNodes("idExample");
        $this->nodes = array();
        foreach ($iterator as $n) {
            array_push($this->nodes, $n->getName());
        }
        $this->assertContains('idExample', $this->nodes);
        $this->assertNotContains('index.txt', $this->nodes);
    }
    
    public function testGetNodesPatternAdvanced() {
        $this->markTestSkipped('TODO: Figure why this is not working');
        $this->node = $this->rootNode->getNode('tests_level1_access_base');
        $iterator = $this->node->getNodes("test:* | idExample");
        $this->nodes = array();
        foreach ($iterator as $n) {
            array_push($this->nodes, $n->getName());
        }
        $this->assertContains('idExample', $this->nodes);
        $this->assertContains('test:namespacedNode', $this->nodes);
        $this->assertNotContains('index.txt', $this->nodes);
    }
    
    public function testGetProperty() {
        $prop = $this->node->getProperty('jcr:created');
        $this->assertTrue(is_object($prop));
        $this->assertTrue($prop instanceOf PHPCR_PropertyInterface);
    }
    
    /**
     * @expectedException PHPCR_PathNotFoundException
     */
    public function testGetPropertyPathNotFoundException() {
        $this->node->getProperty('foobar');
    }
    
    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testGetPropertyRepositoryException() {
        $this->node->getProperty('//');
    }
    
    public function testGetProperties() {
        $iterator = $this->node->getProperties();
        $this->assertTrue(is_object($iterator));
        $this->assertTrue($iterator instanceOf PHPCR_PropertyIteratorInterface);
        $props = array();
        foreach ($iterator as $prop) {
            array_push($props, $prop->getName());
        }
        $this->assertContains('jcr:created', $props);
    }

    public function testGetPropertiesPattern() {
        $iterator = $this->node->getProperties('jcr:cr*');
        $this->assertTrue(is_object($iterator));
        $this->assertTrue($iterator instanceOf PHPCR_PropertyIteratorInterface);
        $props = array();
        foreach ($iterator as $prop) {
            array_push($props, $prop->getName());
        }
        $this->assertContains('jcr:created', $props);
        $this->assertNotContains('jcr:primaryType', $props);
    }
    
    /**
     * @expectedException PHPCR_RepositoryError
     */
    public function testGetPropertiesRepositoryError() {
        $this->markTestIncomplete('TODO: Figure how to produce this error');
    }
    
    public function testGetPrimaryItem() {
        $node = $this->node->getNode('index.txt')->getPrimaryItem();
        $this->assertTrue(is_object($node));
        $this->assertTrue($node instanceOf PHPCR_NodeInterface);
        $this->assertEquals('/tests_level1_access_base/index.txt/jcr:content', $node->getPath());
    }
    
    /**
     * @expectedException PHPCR_ItemNotFoundException
     */
    public function testGetPrimaryItemItemNotFound() {
        $this->rootNode->getPrimaryItem();
    }
    
    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testGetPrimaryItemRepositoryException() {
        $this->markTestIncomplete('TODO: Figure how to produce this error');
    }
    
    public function testGetUUID() {
        $id = $this->node->getNode('idExample')->getUUID();
        $this->assertEquals('842e61c0-09ab-42a9-87c0-308ccc90e6f4', $id);
    }
    
    /**
     * @expectedException PHPCR_UnsupportedRepositoryOperationException
     */
    public function testGetUUIDUnsupportedRepositoryOperationException() {
        $this->node->getUUID();
    }
    
    public function testGetIndex() {
        //TODO: Improve this test to test actual multiple nodes
        $index = $this->node->getIndex();
        $this->assertTrue(is_numeric($index));
        $this->assertEquals(1, $index);
    }
    
    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testGetIndexRepositoryException() {
        $this->markTestIncomplete('TODO: Figure how to produce this error');
    }
    
    public function testGetReferences() {
        $iterator = $this->node->getReferences();
        $this->assertTrue(is_object($iterator));
        $this->assertTrue($iterator instanceOf jr_cr_propertyIterator);
    }
    
    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testGetReferencesRepositoryException() {
        $this->markTestIncomplete('TODO: Figure how to produce this error');
    }
    
    public function testHasNodeTrue() {
        $this->assertTrue($this->node->hasNode('index.txt'));
    }
    
    public function testHasNodeFalse() {
        $this->assertFalse($this->node->hasNode('foobar'));
    }
    
    public function testHasNodesTrue() {
        $this->assertTrue($this->node->hasNodes());
    }
    
    public function testHasNodesFalse() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $this->assertFalse($node->hasNodes());
    }
    
    public function testHasPropertyTrue() {
        $this->assertTrue($this->node->hasProperty('jcr:created'));
    }
    
    public function testHasPropertyFalse() {
        $this->assertFalse($this->node->hasProperty('foobar'));
    }
    
    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testHasPropertyRepositoryException() {
        $this->assertTrue($this->node->hasProperty('/foobar'));
    }
    
    public function testHasPropertiesTrue() {
        $this->assertTrue($this->node->hasProperties('index.txt'));
    }
    
    public function testHasPropertiesFalse() {
        $this->markTestIncomplete('TODO: Figure how to create a node even without jcr:primaryType');
    }
}
