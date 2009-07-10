<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

class jackalope_tests_level1_AccessTest_Credentials extends jackalope_baseCase {
    const CR_USER = 'foo';
    const CR_PASS = 'bar';
    
    // 6.1.2 Credentials
    public function testSimpleCredentials() {
        $cr = $this->assertSimpleCredentials(self::CR_USER, self::CR_PASS);
    }
    
    public function testGetUser() {
        $cr = $this->assertSimpleCredentials(self::CR_USER, self::CR_PASS);
        $user = $cr->getUserId();
        $this->assertEquals($user, self::CR_USER);
    }
    
    //The password gets currently cleared for safety
    public function testGetPassword() {
        $cr = $this->assertSimpleCredentials(self::CR_USER, self::CR_PASS);
        $pass = $cr->getPassword();
        $this->assertTrue(is_string($pass));
        $this->assertEquals($pass, '');
    }
    
    public function testAttributes() {
        $attrName = 'foo';
        $attrValue = 'bar';
        $cr = $this->assertSimpleCredentials(self::CR_USER, self::CR_PASS);
        $cr->setAttribute($attrName, $attrValue);
        $this->assertEquals($attrValue, $cr->getAttribute($attrName));
        $attrs = $cr->getAttributeNames();
        $this->assertTrue(is_array($attrs));
        $this->assertContains($attrName, $attrs);
    }
}
