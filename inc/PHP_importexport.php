<?php
/**
 * Uses the same session mechanism as the tests to so it's necessarry
 * that importing and exporting is working in your implementation
 *
 * This is a temporary solution untill our java solution is properly
 * working.
 */
 class jackalope_importexport {

     protected $fixturePath;
     protected $session;
     protected $configKeys = array('jcr.url', 'jcr.user', 'jcr.pass', 'jcr.workspace', 'jcr.transport');
     

     public function __construct($path) {
         $this->fixturePath = dirname(__FILE__) . '/../fixtures/' . $path . '/';
         if (!is_dir($this->fixturePath)) {
             throw new Exception('Not a valid directory: ' . $this->fixturePath);
         }
         
         $cfg = array();
         foreach ($this->configKeys as $cfgKey) {
             $cfg[substr($cfgKey, 4)] = $GLOBALS[$cfgKey];
         }
         $this->session = getJCRSession($cfg);
         if (! $this->session instanceOf phpCR_Session) {
             throw new Exception('Could not get JCR session');
         }
         
     }

     public function import($fixture) {
         $fixture = $this->fixturePath . $fixture;
         if (!is_readable($fixture)) {
             throw new Exception('Fixture not found at: ' . $fixture);
         }
         
         $this->session->importXML('/', $fixture, 1);
         $this->session->save();
         $this->session->logout();
     }
     
     public function export() {
         $tmp = tmpfile();
         $this->session->exportDocumentView('/', $tmp, 1, 0);
         return readfile($tmp);
     }
 }
 