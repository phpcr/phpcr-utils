<?php
require_once(dirname(__FILE__) . '/importexport.php');
require_once 'PHPUnit/Framework.php';

abstract class jackalope_baseSuite extends PHPUnit_Framework_TestSuite {
    protected $ie; // Holds the import export instance
    protected $path = '';
    protected $configKeys = array('jcr.url', 'jcr.user', 'jcr.pass', 'jcr.workspace', 'jcr.transport');
    
    public function setUp() {
        parent::setUp();
        $this->sharedFixture = array();
        foreach ($this->configKeys as $cfgKey) {
            $this->sharedFixture['config'][substr($cfgKey, 4)] = $GLOBALS[$cfgKey];
        }
        
        $this->sharedFixture['ie'] = new jackalope_importexport($this->path);
    }
}
