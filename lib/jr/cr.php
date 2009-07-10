<?php

class jr_cr {

    /** All instances loaded so far by api_db::factory(). */
    protected static $instances = array();

    //FIXME: api_config is okapi specific, we have to remove that later ...
    public static function api_factory ($name = "default", $force = false) {

        $jcr = api_config::getInstance()->jcr;
        if (empty($jcr[$name])) {
            return false;
        }
        return self::factory($jcr[$name],$name,$force);
    }

    public static function factory( $config, $name = "default", $force = false) {
        if (isset(self::$instances[$name]) && $force == false) {
            return self::$instances[$name];
        }
        self::$instances[$name] = self::get($config);
        return self::$instances[$name];
    }

    private static function get($config) {
        if (empty($config['url'])) {
            return false;
        }

        if (empty($config['workspace'])) {
            $config['workspace'] = "default";
        }

        $repository = jr_cr::lookup($config['url'], $config['transport']);

        if (isset($config['pass'])) {
            $credentials = new jr_cr_simplecredentials($config['user'], $config['pass']);
           return $repository->login($credentials, $config['workspace']);

        } else {
            return $repository->login(null, $config['workspace']);
        }
    }




    /**
     *
     * @param unknown_type $repo
     * @return lx_cr_repository the created repository
     */

    static function lookup($storage, $transport = '') {
        return new jr_cr_repository($storage, $transport);
    }

    /**
     * Constructor. Private according to the singleton pattern.
     */
    private function __construct() {
    }
}