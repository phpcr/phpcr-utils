<?php
/**
 * Handles basic importing and exporting of fixtures trough
 * the java binary
 */
class jackalope_importexport {

    protected $fixturePath;
    protected $jar;

    public function __construct($path) {
        $this->fixturePath = dirname(__FILE__) . '/../fixtures/' . $path . '/';
        if (!is_dir($this->fixturePath)) {
            throw new Exception('Not a valid directory: ' . $this->fixturePath);
        }

        $this->jar = dirname(__FILE__) . '/../bin/jack.jar';
        if (!file_exists($this->jar)) {
            throw new Exception('jack.jar not found at: ' . $this->jar);
        }
    }

    public function import($fixture) {
        $fixture = $this->fixturePath . $fixture;
        if (!is_readable($fixture)) {
            throw new Exception('Fixture not found at: ' . $fixture);
        }

        //TODO fix the stderr redirect which doesn't work properly
        exec('java -jar ' . $this->jar . ' import ' . $fixture . " 2>&1",$output,  $ret);
        if ($ret !== 0){
            $msg = '';
            foreach ($output as $line) {
                $msg .= $line . "\n";
            }
            throw new Exception($msg);
        }
        return true;
    }

    public function exportdocument($file) {
        $fixture = $this->fixturePath . $file;
        if (is_readable($fixture)) {
            throw new Exception('File existing: ' . $fixture);
        }

        //TODO fix the stderr redirect which doesn't work properly
        exec('java -jar ' . $this->jar . ' exportdocument ' . $fixture . " 2>&1",$output,  $ret);
        if ($ret !== 0){
            $msg = '';
            foreach ($output as $line) {
                $msg .= $line . "\n";
            }
            throw new Exception($msg);
        }
        return true;
    }}
