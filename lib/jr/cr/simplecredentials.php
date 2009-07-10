<?php

class jr_cr_simplecredentials implements PHPCR_CredentialsInterface {

    protected $JRcredentials;
    protected $user;
    /**
     *
     */
    function __construct($user,$pass) {
        $this->user = $user;
        $this->JRcredentials = new Java("javax.jcr.SimpleCredentials", $user , str_split($pass));
    }
    
    public function getJRcredentials() {
        return $this->JRcredentials;
    }
    
    public function getUserId() {
        return $this->user;
    }
    
    /**
     * This is kinda a fake since I don't really know how it's supposed to
     * work with references to java.
     */
    public function getPassword() {
        return '';
    }
    
    public function setAttribute($name, $value) {
        $this->JRcredentials->setAttribute($name, $value);
    }
    
    public function getAttribute($name) {
        return $this->JRcredentials->getAttribute($name);
    }
    
    public function removeAttribute($name) {
        $this->JRcredentials->removeAttribute($name);
    }
    
    public function getAttributeNames() {
        return $this->JRcredentials->getAttributeNames();
    }
}
