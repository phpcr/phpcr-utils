<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

class jackalope_tests_level1_AccessTest_Repository extends jackalope_baseCase {
    protected $path = 'level1/read';
    
    // 6.1.1 Repository
    public function testRepository() {
        $rep = getRepository($this->sharedFixture['config']);
        $this->assertTrue(is_object($rep));
        $this->assertTrue($rep instanceOf PHPCR_RepositoryInterface);
    }
    
    public function testLoginSession() {
        $ses = $this->assertSession($this->sharedFixture['config']);
        $this->assertEquals($ses->getWorkspace()->getName(), $this->sharedFixture['config']['workspace']);
    }
    
    public function testDefaultWorkspace() {
        $cfg = $this->sharedFixture['config'];
        unset($cfg['workspace']);
        $ses = $this->assertSession($cfg);
        //This will produce a false-positive if your configured workspace is the default one
        $this->assertNotEquals($ses->getWorkspace()->getName(), $this->sharedFixture['config']['workspace']);
    }
    
    public function testNoLogin() {
        $cfg = $this->sharedFixture['config'];
        unset($cfg['user']);
        unset($cfg['pass']);
        $ses = $this->assertSession($cfg);
        $this->assertEquals($ses->getWorkspace()->getName(), $this->sharedFixture['config']['workspace']);
    }
    
    public function testNoLoginAndWorkspace() {
        $cfg = $this->sharedFixture['config'];
        unset($cfg['user']);
        unset($cfg['pass']);
        unset($cfg['workspace']);
        $ses = $this->assertSession($cfg);
        $this->assertNotEquals($ses->getWorkspace()->getName(), $this->sharedFixture['config']['workspace']);
    }
    
    /**
     * @expectedException phpCR_LoginException
     */
    public function testLoginException() {
        $this->markTestSkipped('TODO: Figure how to make a login fail');
        $cfg = $this->sharedFixture['config'];
        $cfg['user'] = 'foo';
        $cfg['pass'] = 'bar';
        $ses = $this->assertSession($cfg);
    }
    
    /**
     * @expectedException phpCR_NoSuchWorkspaceException
     */
    public function testLoginNoSuchWorkspace() {
        $cfg = $this->sharedFixture['config'];
        $cfg['workspace'] = 'foobar';
        $ses = $this->assertSession($cfg);
    }
    
    /**
     * @expectedException phpCR_RepositoryException
     */
    public function testLoginRepositoryException() {
        $cfg = $this->sharedFixture['config'];
        $cfg['workspace'] = '//';
        $ses = $this->assertSession($cfg);
    }
}
