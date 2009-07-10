<?php

class jr_cr_repository implements PHPCR_RepositoryInterface {

	protected $JRrepository = null;
    protected $storage = null;

	public function __construct($storage,$transport, $repository = null) {

	/*    $this->JRrepository = new Java("org.apache.jackrabbit.rmi.repository.URLRemoteRepository","http://localhost:8080/rmi");
	    return;
*/
        if (isset($repository)) {
            $this->JRrepository = $repository;
        } else if ($transport == 'webdav') {
	        $factory = new java("com.day.crx.jcr.davex.Client",$storage);
	        $this->JRrepository = $factory->getRepository();
	    } else if ($transport == 'davex') {
	        $client  = new java("ch.liip.jcr.davex.DavexClient",$storage);
		    $this->JRrepository = $client->getRepository();
	    } else {
	       $factory = new Java("org.apache.jackrabbit.rmi.client.ClientRepositoryFactory");
	       $this->JRrepository = $factory->getRepository($storage);
	    }


	}

	/**
	 * Returns the descriptor for the specified key.
	 *
	 * Used to query information about this repository implementation. The set
	 * of available keys can be found by calling {@link getDescriptorKeys()}.
	 * If the specifed key is not found, <i>NULL</i> is returned.
	 *
	 * @param string
	 *    A string corresponding to a descriptor for this repository
	 *    implementation.
	 * @return string|null
	 *    A descriptor string or null if unavailable
	 */
	public function getDescriptor($key) {
	    $key = preg_replace('/^PHPCR\./', 'jcr.', $key);
        return $this->JRrepository->getDescriptor($key);
	}

	/**
	 * Returns a string array holding all descriptor keys available for this
	 * implementation.
	 *
	 * This set must contain at least the built-in keys defined by the string
	 * constants in this interface.  It is used in conjunction with
	 * {@link getDescriptor()} to query information about this repository
	 * implementation.
	 *
	 * @return array
	 */
	public function getDescriptorKeys() {
	    $keys = $this->JRrepository->getDescriptorKeys();
	    for ($x=0, $count = count($keys); $x < $count ;$x++) {
            $keys[$x] = preg_replace('/^jcr\./', 'PHPCR.', $keys[$x]);
	    }
	    return $keys;
	}
	   /**
     * Authenticates the user using the supplied <i>credentials</i>.
     *
     * If <i>$workspaceName</i> is recognized as the name of an existing
     * workspace in the repository and authorization to access that workspace
     * is granted, then a new {@link Session} object is returned.  The format
     * of the string <i>$workspaceName</i> depends upon the
     * implementation.
     *
     * If <i>$credentials</i> is <i>NULL</i>, it is assumed that
     * authentication is handled by a mechanism external to the repository
     * itself (for example, through the JAAS framework) and that the repository
     * implementation exists within a context (for example, an application
     * server) that allows it to handle authorization of the request for
     * access to the specified workspace.
     *
     * If <i>$workspaceName</i> is <i>NULL</i>, a default workspace
     * is automatically selected by the repository implementation.  This may,
     * for example, be the "home workspace" of the user whose credentials were
     * passed, though this is entirely up to the configuration and
     * implementation of the repository.  Alternatively, it may be a
     * "null workspace" that serves only to provide the method
     * {@link Workspace::getAccessibleWorkspaceNames()}, allowing the client
     * to select from among available "real" workspaces.
     *
     * <b>PHP Note</b>: As <i>$credentials</i> can be left
     * <i>NULL</i>, no type hinting is done.  It is incumbent upon the
     * implementation to check for the proper type prior to executing.
     *
     * @param object|null
     *  A {@link Credentials} object or null
     * @param string|null
     *    The name of a workspace or null
     * @return lx_cr_session
     *  A {@link Session} object
     *    A valid session for the user to access the repository.
     *
     * @throws {@link LoginException}
     *      If the login fails.
     * @throws {@link NoSuchWorkspaceException}
     *     If the specified <i>$workspaceName</i> is not recognized.
     * @throws {@link RepositoryException}
     *    If another error occurs.
     */
    public function login($credentials=NULL, $workspaceName=NULL) {
        try {
            if ( !is_object( $credentials) ) {
                return new jr_cr_session($this->JRrepository->login($workspaceName),$workspaceName,$this->storage);
            } else {
                return new jr_cr_session($this->JRrepository->login($credentials->getJRcredentials(),$workspaceName),$workspaceName,$this->storage);
            }
        } catch (JavaException $e) {
            $str = split("\n", $e->getMessage(), 1);
            $str = $str[0];
            if (strstr($str, 'LoginException')) {
                throw new PHPCR_LoginException($e->getMessage());
            } elseif (strstr($str, 'NoSuchWorkspaceException')) {
                throw new PHPCR_NoSuchWorkspaceException($e->getMessage());
            } elseif (strstr($str, 'RepositoryException')) {
                throw new PHPCR_RepositoryException($e->getMessage());
            } else {
                throw $e;
            }
        }
    }
    
    /**
     * Returns TRUE if $key is a standard descriptor
     * defined by the string constants in this interface and FALSE if it is
     * either a valid implementation-specific key or not a valid key.
     *
     * @param string $key a descriptor key.
     * @return boolan whether $key is a standard descriptor.
     */
    public function isStandardDescriptor($key) {
        //TODO: Add Code Here
    }

    /**
     * Returns TRUE if $key is a valid single-value descriptor;
     * otherwise returns FALSE.
     *
     * @param string $key a descriptor key.
     * @return boolean whether the specified descriptor is multi-valued.
     */
    public function isSingleValueDescriptor($key) {
        //TODO: Add Code Here
    }


    /**
     * The value of a single-value descriptor is found by
     * passing the key for that descriptor to this method.
     * If $key is the key of a multi-value descriptor
     * or not a valid key this method returns NULL.
     *
     * @param string $key a descriptor key.
     * @return PHPCR_ValueInterface The value of the indicated descriptor
     */
    public function getDescriptorValue($key) {
        //TODO: Add Code Here
    }

    /**
     * The value array of a multi-value descriptor is found by
     * passing the key for that descriptor to this method.
     * If $key is the key of a single-value descriptor
     * then this method returns that value as an array of size one.
     * If $key is not a valid key this method returns NULL.
     *
     * @param string $key a descriptor key.
     * @return array of PHPCR_ValueInterface the value array for the indicated descriptor
     */
    public function getDescriptorValues($key) {
        //TODO: Add Code Here
    }

}
