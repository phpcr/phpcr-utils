<?php

class jr_cr_session implements PHPCR_SessionInterface {
    public $storage = null;
    protected $rootNode = null;
    protected $nodes = array();
    protected $modifiedNodes = array();
    public $JRsession = null;
    
    /**
     * Enter description here...
     *
     * @var Zend_Cache_Core
     */
    public $cache = null;
    /**
     * Workspace
     *
     * @var jr_cr_workspace
     */
    protected $workspace = null;
    /**
     *
     */
    function __construct($session, $workspacename, $storage) {
        $this->JRsession = $session;
        $this->workspace = new jr_cr_workspace($this->JRsession->getWorkspace(), $this);
        $frontendOptions = array('lifetime' => 5, // cache lifetime in seconds
                                 'automatic_serialization' => true);
        $backendOptions = array('cache_dir' => '/tmp/');// Directory where to put the cache files
    }
    
    /**
     *
     * @param string
     * @see PHPCR_Session::addLockToken()
     */
    public function addLockToken($lt) {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @throws {@link AccessControlException}
     * If permission is denied.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Session::checkPermission()
     */
    public function checkPermission($absPath, $actions) {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @param absPath The path of the root of the subtree to be serialized.
     * This must be the path to a node, not a property
     * @param out The <i>OutputStream</i> to which the XML
     * serialization of the subtree will be output.
     * @param skipBinary A <i>boolean</i> governing whether binary
     * properties are to be serialized.
     * @param noRecurse A <i>boolean</i> governing whether the subtree at
     * absPath is to be recursed.
     * @throws {@link PathNotFoundException}
     * If no node exists at <i>absPath</i>.
     * @throws {@link IOException}
     * If an error during an I/O operation occurs.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @todo Determine how to handle this
     * @see PHPCR_Session::exportDocumentView()
     */
    public function exportDocumentView($absPath, XMLWriter $out, $skipBinary, $noRecurse) {
        //TODO: Insert code here
    }
    
    /**
     *
     * @param absPath The path of the root of the subtree to be serialized.
     * This must be the path to a node, not a property
     * @param skipBinary A <i>boolean</i> governing whether binary
     * properties are to be serialized.
     * @param noRecurse A <i>boolean</i> governing whether the subtree at
     * absPath is to be recursed.
     * @return object
     * A {@link http://us3.php.net/manual/en/ref.dom.php DOMDocument} object.
     * @throws {@link PathNotFoundException}
     * If no node exists at <i>$absPath</i>.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Session::exportSystemView()
     */
    public function exportSystemView($absPath, XMLWriter $out, $skipBinary, $noRecurse) {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @param string
     * The name of an attribute passed in the credentials used to acquire
     * this session.
     * @return object
     * @see PHPCR_Session::getAttribute()
     */
    public function getAttribute($name) {
        return $this->JRsession->getAttribute($name);
    }
    
    /**
     *
     * @return array
     * @see PHPCR_Session::getAttributeNames()
     */
    public function getAttributeNames() {
        return $this->JRsession->getAttributeNames();
    }
    
    /**
     *
     * @param parentAbsPath the absolute path of a node under which (as child) the imported subtree will be
     * built.
     * @param uuidBehavior a four-value flag that governs how incoming UUIDs are handled.
     * @return an org.xml.sax.ContentHandler whose methods may be called to feed SAX events
     * into the deserializer.
     * @throws {@link PathNotFoundException}
     * If no node exists at <i>parentAbsPath</i> and this
     * implementation performs this validation immediately instead of waiting until {@link save()}.
     * @throws {@link ConstraintViolationException}
     * If the new subtree cannot be added to the node at
     * <i>parentAbsPath</i> due to node-type or other implementation-specific constraints,
     * and this implementation performs this validation immediately instead of waiting until {@link save()}.
     * @throws {@link VersionException}
     * If the node at <i>parentAbsPath</i> is versionable
     * and checked-in, or is non-versionable but its nearest versionable ancestor is checked-in and this
     * implementation performs this validation immediately instead of waiting until {@link save()}..
     * @throws {@link LockException}
     * If a lock prevents the addition of the subtree and this
     * implementation performs this validation immediately instead of waiting until {@link save()}..
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @todo Determine how to handle this...
     * @see PHPCR_Session::getImportContentHandler()
     */
    public function getImportContentHandler($parentAbsPath, $uuidBehavior) {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @param string
     * @return object
     * A {@link Item} object
     * @throws {@link PathNotFoundException}
     * If the specified path cannot be found.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Session::getItem()
     */
    public function getItem($absPath) {
        if ($absPath == '' || $absPath == '/') {
            return $this->getRootNode();
        }
        return $this->getRootNode()->getNode($absPath);
    }
    
    /**
     *
     * @return array
     * @see PHPCR_Session::getLockTokens()
     */
    public function getLockTokens() {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @param string
     * @return string
     * @throws {@link NamespaceException}
     * If the URI is unknown.
     * @throws {@link RepositoryException}
     * If another error occurs
     * @see PHPCR_Session::getNamespacePrefix()
     */
    public function getNamespacePrefix($uri) {
        try {
            return $this->JRsession->getNamespacePrefix($uri);
        } catch(JavaException $e) {
            $str = split("\n", $e->getMessage(), 1);
            $str = $str[0];
            if (strstr($str, 'NamespaceException')) {
                throw new PHPCR_NamespaceException($e->getMessage());
            } elseif (strstr($str, 'RepositoryException')) {
                throw new PHPCR_RepositoryException($e->getMessage());
            } else {
                throw $e;
            }
        }
    }

    /**
     *
     * @return array
     * @throws {@link RepositoryException}
     * If an error occurs
     * @see PHPCR_Session::getNamespacePrefixes()
     */
    public function getNamespacePrefixes() {
        try {
            return $this->JRsession->getNamespacePrefixes();
        } catch(JavaException $e) {
            throw new PHPCR_RepositoryException($e->getMessage());
        }
    }
    
    /**
     *
     * @param string
     * @return string
     * @throws {@link NamespaceException}
     * If the prefix is unknown.
     * @throws {@link RepositoryException}
     * If another error occurs
     * @see PHPCR_Session::getNamespaceURI()
     */
    public function getNamespaceURI($prefix) {
        try {
            return $this->JRsession->getNamespaceURI($prefix);
        } catch(JavaException $e) {
            $str = split("\n", $e->getMessage(), 1);
            $str = $str[0];
            if (strstr($str, 'NamespaceException')) {
                throw new PHPCR_NamespaceException($e->getMessage());
            } elseif (strstr($str, 'RepositoryException')) {
                throw new PHPCR_RepositoryException($e->getMessage());
            } else {
                throw $e;
            }
        }
    }
    
    /**
     * Returns the node specifed by the given UUID.
     *
     * Only applies to nodes that expose a UUID, in other words, those of
     * mixin node type <i>mix:referenceable</i>
     *
     * @param string
     * @return object
     *  A {@link Node} object
     *
     * @throws {@link ItemNotFoundException}
     *    If the specified UUID is not found.
     * @throws {@link RepositoryException}
     *    If another error occurs.
     */
    
    public function getNodeByUUID($uuid) {
        try {
            $JRnode = $this->JRsession->getNodeByUUID($uuid);
        } catch (JavaException $e) {
            $str = split("\n", $e->getMessage(), 0);
            if (strstr($str[0], 'ItemNotFoundException')) {
                throw new PHPCR_ItemNotFoundException($e->getMessage());
            } elseif (strstr($str[0], 'RepositoryException')) {
                throw new PHPCR_RepositoryException($e->getMessage());
            } else {
                throw $e;
            }
        }
        $node = new jr_cr_node($this, $JRnode);
        if ($node) {
            $this->addNodeToList($node);
            return $node;
        } else {
            return null;
        }
    }
    
    /**
     * Returns the node specifed by the given UUID.
     *
     * Only applies to nodes that expose a UUID, in other words, those of
     * mixin node type <i>mix:referenceable</i>
     *
     * @param string
     * @return object
     *  A {@link Node} object
     *
     * @throws {@link ItemNotFoundException}
     *    If the specified UUID is not found.
     * @throws {@link RepositoryException}
     *    If another error occurs.
     */
    public function getNodeByPath($JRnode) {
        $path = $JRnode->getPath();
        if (! $path) {
            return null;
        }
        $node = $this->getFromNodesList($path);
        if ($node) {
            return $node;
        }
        
        $node = new jr_cr_node($this, $JRnode);
        if ($node) {
            $this->addNodeToList($node);
            return $node;
        } else {
            return null;
        }
    }
    
    /**
     *
     * @return object
     * A {@link Repository} object
     * @see PHPCR_Session::getRepository()
     */
    public function getRepository() {
        $rep = $this->JRsession->getRepository();
        return new jr_cr_repository(null, null, $rep);
    }
    
    /**
     * Returns the root node of the workspace.
     *
     * The root node, "/", is the main access point to the content of the
     * workspace.
     *
     * @return jr_cr_node The root node of the workspace: a {@link Node} object.
     *
     * @throws {@link RepositoryException}
     *    If an error occurs.
     */
    public function getRootNode() {
        try {
            if ($this->rootNode === null) {
                $this->rootNode = new jr_cr_node($this, $this->JRsession->getRootNode());
                $this->addNodeToList($this->rootNode);
            }
            return $this->rootNode;
        } catch (JavaException $e) {
            throw new PHPCR_RepositoryException($e->getMessage());
        }
    }
    
    /**
     *
     * @return string
     * @see PHPCR_Session::getUserID()
     */
    public function getUserID() {
        return $this->JRsession->getUserID();
    }
    
    /**
     *
     * @return object
     * A {@link ValueFactory} object
     * @throws {@link UnsupportedRepositoryOperationException}
     * If writing to the repository is not supported.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Session::getValueFactory()
     */
    public function getValueFactory() {
        return $this->JRsession->getValueFactory();
        //TODO - Insert your code here
    }
    
    /**
     *
     * @return jr_cr_workspace
     * A {@link Workspace} object
     * @see PHPCR_Session::getWorkspace()
     */
    public function getWorkspace() {
        return $this->workspace;
    }
    
    /**
     *
     * @return boolean
     * @throws {@link RepositoryException}
     * If an error occurs
     * @see PHPCR_Session::hasPendingChanges()
     */
    public function hasPendingChanges() {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @param object
     * A {@link Credentials} object
     * @return object
     * A {@link Session} object
     * @throws {@link LoginException}
     * If the current session does not have sufficient rights.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Session::impersonate()
     */
    public function impersonate(PHPCR_CredentialsInterface $credentials) {
        return $this->JRsession->impersonate($credentials->getJRcredentials());
    }
    
    /**
     *
     * @param parentAbsPath the absolute path of the node below which the deserialized subtree is added.
     * @param in The <i>Inputstream</i> from which the XML to be deserilaized is read.
     * @param uuidBehavior a four-value flag that governs how incoming UUIDs are handled.
     * @throws {@link java}
     * .io.IOException if an error during an I/O operation occurs.
     * @throws {@link PathNotFoundException}
     * If no node exists at <i>parentAbsPath</i> and this
     * implementation performs this validation immediately instead of waiting until {@link save()}..
     * @throws {@link ItemExistsException}
     * If deserialization would overwrite an existing item and this
     * implementation performs this validation immediately instead of waiting until {@link save()}..
     * @throws {@link ConstraintViolationException}
     * If a node type or other implementation-specific
     * constraint is violated that would be checked on a normal write method or if
     * <i>uuidBehavior</i> is set to <i>IMPORT_UUID_COLLISION_REMOVE_EXISTING</i>
     * and an incoming node has the same UUID as the node at <i>parentAbsPath</i> or one
     * of its ancestors.
     * @throws {@link VersionException}
     * If the node at <i>parentAbsPath</i> is versionable
     * and checked-in, or its nearest versionable ancestor is checked-in and this
     * implementation performs this validation immediately instead of waiting until {@link save()}..
     * @throws {@link InvalidSerializedDataException}
     * If incoming stream is not a valid XML document.
     * @throws {@link LockException}
     * If a lock prevents the addition of the subtree and this
     * implementation performs this validation immediately instead of waiting until {@link save()}..
     * @throws {@link RepositoryException}
     * if another error occurs.
     * @todo Determine how to handle this...
     * @see PHPCR_Session::importXML()
     */
    public function importXML($parentAbsPath, $in, $uuidBehavior) {
        //TODO - Add exceptions and stuff
        $in = new Java('java.io.FileInputStream', $in);
        $this->JRsession->importXML($parentAbsPath, $in, $uuidBehavior);
    }
    
    /**
     *
     * @return boolean
     * @see PHPCR_Session::isLive()
     */
    public function isLive() {
        return $this->JRsession->isLive();
    }
    
    /**
     *
     * @param string
     * @return boolean
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Session::itemExists()
     */
    public function itemExists($absPath) {
        try {
            if ('/' !== substr($absPath, 0, 1)) {
                $absPath = '/' . $absPath;
            }
            return $this->JRsession->itemExists($absPath);
        } catch (JavaException $e) {
            throw new PHPCR_RepositoryException($e->getMessage());
        }
    }
    
    /**
     *
     * @see PHPCR_Session::logout()
     */
    public function logout() {
        $this->JRsession->logout();
    }
    
    /**
     *
     * @param string
     * @param string
     * @throws {@link ItemExistsException}
     * If a property already exists at <i>destAbsPath</i> or a node
     * already exist there, and same name siblings are not allowed and this
     * implementation performs this validation immediately instead of
     * waiting until {@link save()}.
     * @throws {@link PathNotFoundException}
     * If either <i>srcAbsPath</i> or <i>destAbsPath</i> cannot
     * be found and this implementation performs this validation immediately
     * instead of waiting until {@link save()}.
     * @throws {@link VersionException}
     * If the parent node of <i>destAbsPath</i> or the parent node of
     * <i>srcAbsPath</i> is versionable and checked-in, or or is
     * non-verionable and its nearest versionable ancestor is checked-in and
     * this implementation performs this validation immediately instead of
     * waiting until {@link save()}.
     * @throws {@link ConstraintViolationException}
     * If a node-type or other constraint violation is detected immediately
     * and this implementation performs this validation immediately instead
     * of waiting until {@link save()}.
     * @throws {@link LockException}
     * If the move operation would violate a lock and this implementation
     * performs this validation immediately instead of waiting until
     * {@link save()}.
     * @throws {@link RepositoryException}
     * If the last element of <i>destAbsPath</i> has an index or
     * if another error occurs.
     * @see PHPCR_Session::move()
     */
    public function move($srcAbsPath, $destAbsPath) {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @param boolean
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Session::refresh()
     */
    public function refresh($keepChanges) {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @param string
     * @see PHPCR_Session::removeLockToken()
     */
    public function removeLockToken($lt) {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @throws {@link AccessDeniedException}
     * If any of the changes to be persisted would violate the access
     * privileges of the this {@link Session}. Also thrown if  any of the
     * changes to be persisted would cause the removal of a node that is
     * currently referenced by a <i>REFERENCE</i> property that this
     * Session <i>does not</i> have read access to.
     * @throws {@link ItemExistsException}
     * If any of the changes to be persisted would be prevented by the
     * presence of an already existing item in the workspace.
     * @throws {@link LockException}
     * If any of the changes to be persisted would violate a lock.
     * @throws {@link ConstraintViolationException}
     * If any of the changes to be persisted would violate a node type or
     * restriction. Additionally, a repository may use this exception to
     * enforce implementation- or configuration-dependent restrictions.
     * @throws {@link InvalidItemStateException}
     * If any of the changes to be persisted conflicts with a change already
     * persisted through another session and the implementation is such that
     * this conflict can only be detected at save-time and therefore was not
     * detected earlier, at change-time.
     * @throws {@link ReferentialIntegrityException}
     * If any of the changes to be persisted would cause the removal of a
     * node that is currently referenced by a <i>REFERENCE</i> property
     * that this {@link Session} has read access to.
     * @throws {@link VersionException}
     * If the {@link save()} would make a result in a change to persistent
     * storage that would violate the read-only status of a checked-in node.
     * @throws {@link LockException}
     * If the {@link save()} would result in a change to persistent storage
     * that would violate a lock.
     * @throws {@link NoSuchNodeTypeException}
     * If the {@link save()} would result in the addition of a node with an
     * unrecognized node type.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Session::save()
     */
    public function save() {
        foreach ($this->modifiedNodes as $node) {
            try {
                //$node->save();
            } catch (Exception $e) {
                //FIXME: throw an exception, if it can't be saved
                error_log(var_export($e, true));
            }
        }
        $this->JRsession->save();
        $this->modifiedNodes = array();
    }
    
    public function optimize() {
        $this->storage->optimize();
    }
    
    /**
     *
     * @param string
     * @param string
     * @throws {@link NamespaceException}
     * If the specified uri is not registered or an attempt is made to remap
     * to an illegal prefix.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Session::setNamespacePrefix()
     */
    public function setNamespacePrefix($prefix, $uri) {
        try {
            $this->JRsession->setNamespacePrefix($prefix, $uri);
        } catch(JavaException $e) {
            $str = split("\n", $e->getMessage(), 1);
            $str = $str[0];
            if (strstr($str, 'NamespaceException')) {
                throw new PHPCR_NamespaceException($e->getMessage());
            } elseif (strstr($str, 'RepositoryException')) {
                throw new PHPCR_RepositoryException($e->getMessage());
            } else {
                throw $e;
            }
        }
    }
    
    public function addNodeToList($node) {
        $this->nodes[$node->getPath()] = $node;
    }
    
    public function addNodeToModifiedList($node) {
        $this->modifiedNodes[$node->getPath()] = $node;
    }
    
    public function getFromNodesList($path) {
        if (! isset($this->nodes[$path])) {
            return null;
        }
        return $this->nodes[$path];
    }
    
    public function parsePath($path, jr_cr_node $node, $noAbsolute = false) {
        if (! $noAbsolute && substr($path, 0, 1) == '/') {
            $node = $this->getRootNode();
            $path = substr($path, 1);
        }
        
        $parts = explode('/', $path);
        if (count($parts) == 1) {
            return array(array("node" => $node, "name" => $parts[0]));
        }
        
        $firstname = array_shift($parts);
        $paths = array();
        
        if ($firstname == '') {
            $nodes = $this->getAllDescendants($node);
            
            foreach ($nodes as $n) {
                $subnode = $n->getNode($parts[0]);
                
                if ($subnode) {
                    $ps = $this->parsePath($path, $n, true);
                    $paths = array_merge($paths, $ps);
                }
            }
            $firstname = array_shift($parts);
        }
        
        $subnode = $node->getNode($firstname);
        if ($subnode) {
            return array_merge($paths, $this->parsePath(substr($path, strlen($firstname) + 1), $subnode, true));
        } else {
            return $paths;
        }
    }
    
    protected function getAllDescendants(jr_cr_node $node) {
        $nodes = $node->getNodes();
        if (count($nodes) > 0) {
            foreach ($nodes as $n) {
                $nodes = array_merge($nodes, $this->getAllDescendants($n));
            }
        } else {
            $nodes = array();
        }
        return $nodes;
    }
    
    /**
     * Returns the node specified by the given identifier. Applies to both referenceable
     * and non-referenceable nodes.
     *
     * @param string $id An identifier.
     * @return PHPCR_NodeInterface A Node.
     * @throws PHPCR_ItemNotFoundException if no node with the specified identifier exists or if this Session does not have read access to the node with the specified identifier.
     * @throws PHPCR_RepositoryException if another error occurs.
     */
    public function getNodeByIdentifier($id) {
        //TODO: Insert code
    }
    
    /**
     * Returns the node at the specified absolute path in the workspace.
     *
     * @param string $absPath An absolute path.
     * @return PHPCR_NodeInterface A node
     * @throws PHPCR_PathNotFoundException if no accessible node is found at the specified path.
     * @throws PHPCR_RepositoryException if another error occurs.
     */
    public function getNode($absPath) {
        //TODO: Insert code
    }
    
    /**
     * Returns the property at the specified absolute path in the workspace.
     *
     * @param string $absPath An absolute path.
     * @return PHPCR_PropertyInterface A property
     * @throws PHPCR_PathNotFoundException if no accessible property is found at the specified path.
     * @throws PHPCR_RepositoryException if another error occurs.
     */
    public function getProperty($absPath) {
        //TODO: Insert code
    }
    
    /**
     * Returns true if a node exists at absPath and this Session has read
     * access to it; otherwise returns false.
     *
     * @param string $absPath An absolute path.
     * @return boolean a boolean
     * @throws 3_PHPCR_RepositoryException if absPath is not a well-formed absolute path.
     */
    public function nodeExists($absPath) {
        //TODO: Insert Code
    }
    
    /**
     * Returns true if a property exists at absPath and this Session has read
     * access to it; otherwise returns false.
     *
     * @param string $absPath An absolute path.
     * @return boolean a boolean
     * @throws PHPCR_RepositoryException if absPath is not a well-formed absolute path.
     */
    public function propertyExists($absPath) {
        //TODO: Insert Code
    }
    
    /**
     * Removes the specified item and its subgraph.
     *
     * This is a session-write method and therefore requires a save in order to
     * dispatch the change.
     *
     * If a node with same-name siblings is removed, this decrements by one the
     * indices of all the siblings with indices greater than that of the removed
     * node. In other words, a removal compacts the array of same-name siblings
     * and causes the minimal re-numbering required to maintain the original
     * order but leave no gaps in the numbering.
     *
     * @param string $absPath the absolute path of the item to be removed.
     * @return void
     * @throws PHPCR_Version_VersionException if the parent node of the item at absPath is read-only due to a checked-in node and this implementation performs this validation immediately.
     * @throws PHPCR_Lock_LockException if a lock prevents the removal of the specified item and this implementation performs this validation immediately instead.
     * @throws PHPCR_ConstraintViolationException if removing the specified item would violate a node type or implementation-specific constraint and this implementation performs this validation immediately.
     * @throws PHPCR_PathNotFoundException if no accessible item is found at $absPath property or if the specified item or an item in its subgraph is currently the target of a REFERENCE property located in this workspace but outside the specified item's subgraph and the current Session does not have read access to that REFERENCE property.
     * @throws PHPCR_RepositoryException if another error occurs.
     * @see Item::remove()
     */
    public function removeItem($absPath) {
        //TODO: Insert code
    }
    
    /**
     * Returns true if this Session has permission to perform the specified
     * actions at the specified absPath and false otherwise.
     * The actions parameter is a comma separated list of action strings.
     * The following action strings are defined:
     *
     * add_node: If hasPermission(path, "add_node") returns true, then this
     * Session has permission to add a node at path.
     * set_property: If hasPermission(path, "set_property") returns true, then
     * this Session has permission to set (add or change) a property at path.
     * remove: If hasPermission(path, "remove") returns true, then this Session
     * has permission to remove an item at path.
     * read: If hasPermission(path, "read") returns true, then this Session has
     * permission to retrieve (and read the value of, in the case of a property)
     * an item at path.
     *
     * When more than one action is specified in the actions parameter, this method
     * will only return true if this Session has permission to perform all of the
     * listed actions at the specified path.
     *
     * The information returned through this method will only reflect the access
     * control status (both JCR defined and implementation-specific) and not
     * other restrictions that may exist, such as node type constraints. For
     * example, even though hasPermission may indicate that a particular Session
     * may add a property at /A/B/C, the node type of the node at /A/B may
     * prevent the addition of a property called C.
     *
     * @param string $absPath an absolute path.
     * @param string $actions a comma separated list of action strings.
     * @return boolean true if this Session has permission to perform the specified actions at the specified absPath.
     * @throws PHPCR_RepositoryException if an error occurs.
     */
    public function hasPermission($absPath, $actions) {
        //TODO: Insert Code
    }
    
    /**
     * Checks whether an operation can be performed given as much context as can
     * be determined by the repository, including:
     *
     * * Permissions granted to the current user, including access control privileges.
     * * Current state of the target object (reflecting locks, checkin/checkout
     *   status, retention and hold status etc.).
     * * Repository capabilities.
     * * Node type-enforced restrictions.
     * * Repository configuration-specific restrictions.
     *
     * The implementation of this method is best effort: returning false guarantees
     * that the operation cannot be performed, but returning true does not guarantee
     * the opposite.
     *
     * The methodName parameter identifies the method in question by its name
     * as defined in the Javadoc.
     *
     * The target parameter identifies the object on which the specified method
     * is called.
     *
     * The arguments parameter contains a Map object consisting of
     * name/value pairs where the name is a String holding the parameter name of
     * the method as defined in the Javadoc and the value is an Object holding
     * the value to be passed. In cases where the value is a Java primitive type
     * it must be converted to its corresponding Java object form before being
     * passed.
     *
     * For example, given a Session S and Node N then
     *
     * Map p = new HashMap();
     * p.put("relPath", "foo");
     * boolean b = S.hasCapability("addNode", N, p);
     *
     * will result in b == false if a child node called foo cannot be added to
     * the node N within the session S.
     *
     * @param string $methodName the nakme of the method.
     * @param object $target the target object of the operation.
     * @param array $arguments the arguments of the operation.
     * @return boolean FALSE if the operation cannot be performed, TRUE if the operation can be performed or if the repository cannot determine whether the operation can be performed.
     * @throws PHPCR_RepositoryException if an error occurs
     */
    public function hasCapability($methodName, $target, array $arguments) {
        //TODO: Insert Code
    }
    
    /**
     * Returns the access control manager for this Session.
     *
     * @return PHPCR_Security_AccessControlManager the access control manager for this Session
     * @throws PHPCR_UnsupportedRepositoryOperationException if access control is not supported.
     * @throws PHPCR_RepositoryException if another error occurs.
     */
    public function getAccessControlManager() {
        //TODO: Insert Code
    }
    
    /**
     * Returns the retention and hold manager for this Session.
     *
     * @return PHPCR_Retention_RetentionManagerInterface the retention manager for this Session.
     * @throws PHPCR_UnsupportedRepositoryOperationException if retention and hold are not supported.
     * @throws PHPCR_RepositoryException if another error occurs.
     */
    public function getRetentionManager() {
        //TODO: Insert Code
    }
}
