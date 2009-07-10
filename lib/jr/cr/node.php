<?php
class jr_cr_node implements PHPCR_NodeInterface {
    protected $uuid = null;
    protected $name = '';
    protected $nodeType = null;
    protected $parentNode = null;
    protected $properties = array();
    protected $propertiesLoaded = false;
    /**
     * Enter description here...
     *
     * @var jr_cr_session
     */
    protected $session = null;
    public $JRnode = null; //querymanager needs access to the java instance
    protected $new = false;
    
    protected $modified = false;
    protected $path = '';
    /**
     *
     */
    function __construct($session, $JRnode) {
        $this->session = $session;
        $this->JRnode = $JRnode;
    }
    
    /**
     *
     * @param string
     * The mixin name
     * @throws {@link NoSuchNodeTypeException}
     * If the specified <i>mixinName</i> is not recognized and this
     * implementation performs this validation immediately instead of
     * waiting until {@link save()}.
     * @throws {@link ConstraintViolationException}
     * If the specified mixin node type is prevented from being assigned.
     * @throws {@link VersionException}
     * If this node is versionable and checked-in or is non-versionable but
     * its nearest versionable ancestor is checked-in and this
     * implementation performs this validation immediately instead of
     * waiting until {@link save()}..
     * @throws {@link LockException}
     * If a lock prevents the addition of the mixin and this implementation
     * performs this validation immediately instead of waiting until
     * {@link save()}.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Node::addMixin()
     */
    public function addMixin($mixinName) {
        $this->JRnode->addMixin($mixinName);
    }
    
    /**
     *
     * @param string
     * The path of the new {@link Node} to be created.
     * @param string|null
     * The name of the primary {@link NodeType} of the new {@link Node}.
     * (Optional)
     * @return jr_cr_node
     * A {@link Node} object
     * @throws {@link ItemExistsException}
     * If an item at the specified path already exists, same-name siblings
     * are not allowed and this implementation performs this validation
     * immediately instead of waiting until {@link save()}.
     * @throws {@link PathNotFoundException}
     * If the specified path implies intermediary {@link Node}s that do not
     * exist or the last element of <i>$relPath</i> has an index, and
     * this implementation performs this validation immediately instead of
     * waiting until {@link save()}.
     * @throws {@link NoSuchNodeTypeException}
     * If the specified node type is not recognized and this implementation
     * performs this validation immediately instead of waiting until
     * {@link save()}.
     * @throws {@link ConstraintViolationException}
     * If a node type or implementation-specific constraint is violated or
     * if an attempt is made to add a node as the child of a property and
     * this implementation performs this validation immediately instead of
     * waiting until {@link save()}.
     * @throws {@link VersionException}
     * If the node to which the new child is being added is versionable and
     * checked-in or is non-versionable but its nearest versionable ancestor
     * is checked-in and this implementation performs this validation
     * immediately instead of waiting until {@link save()}.
     * @throws {@link LockException}
     * If a lock prevents the addition of the node and this implementation
     * performs this validation immediately instead of waiting until
     * {@link save()}.
     * @throws {@link RepositoryException}
     * If the last element of <i>$relPath</i> has an index or if
     * another error occurs.
     * @see PHPCR_Node::addNode()
     */
    public function addNode($relPath, $primaryNodeTypeName = null, $identifier = null) {
        try {
            if ($node = $this->getNode($relPath)) {
                // FIXME, should throw an exception
                return $node;
            }
        } catch (Exception $e) {
            
        }
        if (substr($relPath, 0, 1) == '/') {
            $node = $this->session->getRootNode();
            $relPath = substr($relPath, 1);
        } else {
            if (! $relPath || $relPath == '') {
                $relPath = ".";
                $node = $this;
            } else {
                $node = $this;
            }
        }
        
        if (! $primaryNodeTypeName) {
            $jrnode = $node->JRnode->addNode($relPath);
        } else {
            $jrnode = $node->JRnode->addNode($relPath, $primaryNodeTypeName);
        }
        $node = new jr_cr_node($this->session, $jrnode);
        $node->setNew(true);
        $node->setModified(true);
        return $node;
        //TODO - Insert your code here
    }
    
    /**
     *
     * @param $string
     * The name of the mixin to be tested.
     * @return boolean
     * @throws {@link NoSuchNodeTypeException}
     * If the specified mixin node type name is not recognized.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Node::canAddMixin()
     */
    public function canAddMixin($mixinName) {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @param version a version referred to by this node's
     * <i>jcr:mergeFailed</i> property.
     * @throws {@link VersionException}
     * If the version specified is not among those referenced in this node's
     * <i>jcr:mergeFailed</i> or if this node is currently checked-in.
     * @throws {@link InvalidItemStateException}
     * If there are unsaved changes pending on this node.
     * @throws {@link UnsupportedRepositoryOperationException}
     * If this node is not versionable.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Node::cancelMerge()
     */
    public function cancelMerge(PHPCR_Version $version) {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @return object
     * A {@link Version} object
     * @throws {@link VersionException}
     * If jcr:predecessors does not contain at least one value or if
     * a child item of this node has an <i>OnParentVersion</i> status
     * of <i>ABORT</i>.  This includes the case where an unresolved
     * merge failure exists on this node, as indicated by the presence of a
     * <i>jcr:mergeFailed</i> property.
     * @throws {@link UnsupportedRepositoryOperationException}
     * If this node is not versionable.
     * @throws {@link InvalidItemStateException}
     * If unsaved changes exist on this node.
     * @throws {@link LockException}
     * If a lock prevents the checkin.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Node::checkin()
     */
    public function checkin() {
        $this->JRnode->checkin();
    }
    
    /**
     *
     * @throws {@link UnsupportedRepositoryOperationException}
     * If this node is not versionable.
     * @throws {@link LockException}
     * If a lock prevents the checkout.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Node::checkout()
     */
    public function checkout() {
        $this->JRnode->checkout();
    }
    
    /**
     *
     * @param object
     * A {@link Version} object
     * A version referred to by this node's <i>jcr:mergeFailed</i>
     * property.
     * @throws {@link VersionException}
     * If the version specifed is not among those referenced in this node's
     * <i>jcr:mergeFailed</i> or if this node is currently checked-in.
     * @throws {@link InvalidItemStateException}
     * If there are unsaved changes pending on this node.
     * @throws {@link UnsupportedRepositoryOperationException}
     * If this node is not versionable.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Node::doneMerge()
     */
    public function doneMerge(PHPCR_Version $version) {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @return object
     * A {@link Version} object
     * @throws UnsupportedRepositoryOperationException
     * If this node is not versionable.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Node::getBaseVersion()
     */
    public function getBaseVersion() {
        return new jr_cr_node($this->session,$this->JRnode->getBaseVersion());
        //TODO - Insert your code here
    }
    
    /**
     *
     * @param string
     * @return string
     * @throws {@link ItemNotFoundException}
     * If no corresponding node is found.
     * @throws {@link NoSuchWorkspaceException}
     * If the workspace is unknown.
     * @throws {@link AccessDeniedException}
     * If the current <i>session</i> has insufficent rights to perform this operation.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Node::getCorrespondingNodePath()
     */
    public function getCorrespondingNodePath($workspaceName) {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @return object
     * A {@link NodeDefinition} object.
     * @see NodeType::getChildNodeDefinitions()
     * @see PHPCR_Node::getDefinition()
     */
    public function getDefinition() {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @return int
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Node::getIndex()
     */
    public function getIndex() {
        try {
            return $this->JRnode->getIndex();
        } catch (JavaException $e) {
            throw new PHPCR_ReposiotryException($e->getMessage());
        }
    }
    
    /**
     *
     * @return object
     * A {@link Lock} object
     * @throws {@link UnsupportedRepositoryOperationException}
     * If this implementation does not support locking.
     * @throws {@link LockException}
     * If no lock applies to this node.
     * @throws {@link AccessDeniedException}
     * If the curent session does not have pernmission to get the lock.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Node::getLock()
     */
    public function getLock() {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @return array
     * An array of NodeType objects.
     * @see PHPCR_Node::getMixinNodeTypes()
     */
    public function getMixinNodeTypes() {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @param string
     * The relative path of the {@link Node} to retrieve.
     * @return jr_cr_node
     * The {@link Node} at $relPath.
     * @throws {@link PathNotFoundException}
     * If no {@link Node} exists at the  specified path.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Node::getNode()
     */
    public function getNode($relPath) {
        if (substr($relPath, 0, 1) == '/') {
            $node = $this->session->getRootNode();
            $relPath = substr($relPath, 1);
        } else {
            if (! $relPath || $relPath == '') {
                $relPath = ".";
                $node = $this;
            } else {
                $node = $this;
            }
        }
        try {
            
            $jrnode = $node->JRnode->getNode($relPath);
            $node = $node->session->getNodeByPath($jrnode);
            if ($node instanceof jr_cr_node) {
                return $node;
            }
        } catch (Exception $e) {
            throw new PHPCR_PathNotFoundException($relPath);
        }
        throw new PHPCR_PathNotFoundException($relPath);
    }
    
    //FIXME selber erfunden, needed for query service later
    public function searchNodes($relPath) {
        $pps = $this->session->parsePath($relPath, $this);
        $nodes = array();
        foreach ($pps as $pp) {
            switch ($pp['name']) {
                case '..' :
                    $node = $this->getParent();
                break;
                case '' :
                case '.' :
                    $node = $this;
                break;
                default :
                    $uuid = $this->session->storage->getChildNodeUUID($pp['node'], $pp['name']);
                    $node = $this->session->getNodeByUUID($uuid);
                break;
            }
            $nodes[] = $node;
        }
        return $nodes;
    }
    
    /**
     *
     * @param string
     * A name pattern.
     * @return object
     * A {@link NodeIterator} over all child {@link Node}s of $this
     * {@link Node}.
     * @throws {@link RepositoryException}
     * If an unexpected error occurs.
     * @see PHPCR_Node::getNodes()
     */
    public function getNodes($namePattern = null) {
        try {
            if ($namePattern) {
                $jrnodes = $this->JRnode->getNodes($namePattern);
            } else {
                $jrnodes = $this->JRnode->getNodes();
            }
        } catch (JavaException $e) {
            throw new PHPCR_RepositoryException($e->getMessage());
        }

        return new jr_cr_nodeiterator($jrnodes, $this->session);
    }
    
    /**
     *
     * @return object
     * The deepest primary child {@link Item} accessible from $this
     * {@link Node} via a chain of primary child {@link Item}s.
     * @throws {@link ItemNotFoundException}
     * If $this {@link Node} does not have a primary child {@link Item}.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Node::getPrimaryItem()
     */
    public function getPrimaryItem() {
        try {
            $node = $this->JRnode->getPrimaryItem();
        } catch (JavaException $e) {
            $str = split("\n", $e->getMessage(), 1);
            if (strstr($str[0], 'ItemNotFound')) {
                throw new PHPCR_ItemNotFoundException($e->getMessage());
            } elseif (strstr($str[0], 'RepositoryException')) {
                throw new PHPCR_RepositoryException($e->getMessage());
            } else {
                throw $e;
            }
        }
        return new jr_cr_node($this->session, $node);
    }
    
    /**
     *
     * @return object
     * A {@link NodeType} object.
     * @see PHPCR_Node::getPrimaryNodeType()
     */
    public function getPrimaryNodeType() {
        $md5path = md5($this->getPath());
        $cacheKey = md5("node::getPrimaryNodeType::".$md5path);
        if (!$p = $this->session->cache->load($cacheKey)) {
                $p = $this->JRnode->getPrimaryNodeType()->getName();
                $this->session->cache->save($p,$cacheKey,array($md5path));
        }
        return $p;
    }
    
    /**
     *
     * @return object
     * A {@link PropertyIterator} object
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Node::getProperties()
     */
    public function getProperties($namePattern = '') {
        try {
            if ($namePattern) {
                $jrproperties = $this->JRnode->getProperties($namePattern);
            } else {
                $jrproperties = $this->JRnode->getProperties();
            }
        } catch (JavaException $e) {
            throw new PHPCR_RepositoryException($e->getMessage());
        }
        return new jr_cr_propertyiterator($jrproperties, $this);
    }
    
    /**
     *
     * @param string
     * The relative path of the {@link Property} to retrieve.
     * @return jr_cr_property
     * A {@link Property} object
     * @throws {@link PathNotFoundException}
     * If no {@link Property} exists at the specified path.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Node::getProperty()
     */
    public function getProperty($relPath) {
        if (substr($relPath, 0, 1) == '/') {
            $node = $this->session->getRootNode();
            $relPath = substr($relPath, 1);
        } else {
            if (! $relPath || $relPath == '') {
                $relPath = ".";
                $node = $this;
            } else {
                $node = $this;
            }
        }
        
        try {
            $jrnode = $node->JRnode->getProperty($relPath);
        } catch (Exception $e) {
            throw new PHPCR_PathNotFoundException($relPath);
        }
        
        $prop = $this->getPropertyFromList($jrnode);
        if ($prop instanceof jr_cr_property) {
            return clone($prop);
        }
        throw new PHPCR_PathNotFoundException($relPath);
    }
    
    /**
     *
     * @return object
     * A {@link PropertyIterator} object
     * @throws {@link RepositoryException}
     * If an error occurs
     * @see PHPCR_Node::getReferences()
     */
    public function getReferences($name = null) {
        if (null === $name) {
            $iterator = $this->JRnode->getReferences();
            return new jr_cr_propertyIterator($iterator, $this);
        } else {
            //TODO: Insert Code
        }
    }
    
    /**
     *
     * @return string
     * The UUID of $this {@link Node}
     * @throws {@link UnsupportedRepositoryOperationException}
     * If $this {@link Node} nonreferenceable.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Node::getUUID()
     */
    public function getUUID() {
        if (empty($this->uuid)) {
            try {
                $this->uuid = $this->JRnode->getUUID();
            } catch (JavaException $e) {
                $str = split("\n", $e->getMessage(), 1);
                if (strstr($str[0], 'UnsupportedRepositoryOperationException')) {
                    throw new PHPCR_UnsupportedRepositoryOperationException($e->getMessage());
                } elseif (strstr($str[0], 'RepositoryException')) {
                    throw new PHPCR_RepositoryException($e->getMessage());
                } else {
                    throw $e;
                }
            }
        }
        return $this->uuid;
    }
    
    /**
     *
     * @return object
     * A {@link VersionHistory} object
     * @throws {@link UnsupportedRepositoryOperationException}
     * If this node is not versionable.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Node::getVersionHistory()
     */
    public function getVersionHistory() {
        return new jr_cr_versionhistory($this->JRnode->getVersionHistory(),$this->session);
    }
    
    /**
     *
     * @param string
     * The path of a possible {@link Node}.
     * @return bool
     * TRUE if a {@link Node} exists at relPath;
     * FALSE otherwise.
     * @throws {@link RepositoryException}
     * If an unspecified error occurs.
     * @see PHPCR_Node::hasNode()
     */
    public function hasNode($relPath) {
        return $this->JRnode->hasNode($relPath);
    }
    
    /**
     *
     * @return bool
     * TRUE if $this {@link Node} has one or more child
     * {@link Node}s; FALSE otherwise.
     * @throws {@link RepositoryException}
     * If an unspecified error occurs.
     * @see PHPCR_Node::hasNodes()
     */
    public function hasNodes() {
        return $this->JRnode->hasNodes();
    }
    
    /**
     *
     * @return bool
     * TRUE if $this {@link Node} has one or more
     * {@link Property}s; FALSE otherwise.
     * @throws {@link RepositoryException}
     * If an unspecified error occurs.
     * @see PHPCR_Node::hasProperties()
     */
    public function hasProperties() {
        return $this->JRnode->hasProperties();
     }
     
    /**
     *
     * @param string
     * The path of a possible {@link Property}.
     * @return bool
     * TRUE if a {@link Property} exists at $relPath;
     * FALSE otherwise.
     * @throws {@link RepositoryException}
     * If an unspecified error occurs.
     * @see PHPCR_Node::hasProperty()
     */
    public function hasProperty($relPath) {
        try {
            return (bool) $this->JRnode->hasProperty($relPath);
        } catch (JavaException $e) {
            throw new PHPCR_RepositoryException($e->getMessage());
        }
    }
    
    /**
     *
     * @return boolean
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Node::holdsLock()
     */
    public function holdsLock() {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @return bool
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Node::isCheckedOut()
     */
    public function isCheckedOut() {
        return $this->JRnode->isCheckedOut();
    }
    
    /**
     *
     * @return boolean
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Node::isLocked()
     */
    public function isLocked() {
        //TODO - Insert your code here
    }

    /**
     *
     * @param string
     * The name of a {@link NodeType}.
     * @return bool
     * TRUE if $this {@link Node} is of the specified
     * {@link NodeType} or a subtype of the specified {@link NodeType}; returns
     * FALSE otherwise.
     * @throws {@link RepositoryException}
     * If an unspecified error occurs.
     * @see PHPCR_Node::isNodeType()
     */
    public function isNodeType($nodeTypeName) {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @param boolean
     * @param boolean
     * @return object
     * A {@link Lock} object
     * @throws {@link UnsupportedRepositoryOperationException}
     * If this implementation does not support locking.
     * @throws {@link LockException}
     * If this node is not <i>mix:lockable</i> or this node is already
     * locked or <i>isDeep</i> is <i>true</i> and a descendant
     * node of this node already holds a lock.
     * @throws {@link AccessDeniedException}
     * If this session does not have permission to lock this node.
     * @throws {@link InvalidItemStateException}
     * If this node has pending unsaved changes.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Node::lock()
     */
    public function lock($isDeep, $isSessionScoped) {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @param string
     * The name of the source workspace.
     * @param boolean
     * @return object
     * A {@link NodeIterator} object
     * Iterator over all nodes that received a merge result of "fail" in the
     * course of this operation.
     * @throws {@link MergeException}
     * If <i>$bestEffort</i> is <i>false</i> and a failed merge
     * result is encountered.
     * @throws {@link InvalidItemStateException}
     * If this session (not necessarily this node) has pending unsaved changes.
     * @throws {@link NoSuchWorkspaceException}
     * If <i>srcWorkspace</i> does not exist.
     * @throws {@link AccessDeniedException}
     * If the current session does not have sufficient rights to perform the operation.
     * @throws {@link LockException}
     * If a lock prevents the merge.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Node::merge()
     */
    public function merge($srcWorkspace, $bestEffort) {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @param string
     * The relative path to the child node (that is, name plus possible
     * index) to be moved in the ordering
     * @param string
     * The the relative path to the child node (that is, name plus possible
     * index) before which the node <i>$srcChildRelPath</i> will be
     * placed.
     * @throws {@link UnsupportedRepositoryOperationException}
     * If ordering is not supported.
     * @throws {@link ConstraintViolationException}
     * If an implementation-specific ordering restriction is violated and
     * this implementation performs this validation immediately instead of
     * waiting until {@link save()}.
     * @throws {@link ItemNotFoundException}
     * If either parameter is not the relative path of a child node of this
     * node.
     * @throws {@link VersionException}
     * If this node is versionable and checked-in or is non-versionable
     * but its nearest versionable ancestor is checked-in and this
     * implementation performs this validation immediately instead of
     * waiting until {@link save()}..
     * @throws {@link LockException}
     * If a lock prevents the re-ordering and this implementation performs
     * this validation immediately instead of waiting until {@link save()}.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Node::orderBefore()
     */
    public function orderBefore($srcChildRelPath, $destChildRelPath) {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @param string
     * The name of the mixin node type to be removed.
     * @throws {@link NoSuchNodeTypeException}
     * If the specified <i>$mixinName</i> is not currently assigned to
     * this node and this implementation performs this validation
     * immediately instead of waiting until {@link save()}.
     * @throws {@link ConstraintViolationException}
     * If the specified mixin node type is prevented from being removed and
     * this implementation performs this validation immediately instead of
     * waiting until {@link save()}.
     * @throws {@link VersionException}
     * If this node is versionable and checked-in or is non-versionable but
     * its nearest versionable ancestor is checked-in and this implementation
     * performs this validation immediately instead of waiting until
     * {@link save()}.
     * @throws {@link LockException}
     * If a lock prevents the removal of the mixin and this implementation
     * performs this validation immediately instead of waiting until
     * {@link save()}.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Node::removeMixin()
     */
    public function removeMixin($mixinName) {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @param string|{@link Version}
     * @param boolean
     * @param string
     * @todo Update docs to reflect full version...
     * @throws {@link UnsupportedRepositoryOperationException}
     * If this node is not versionable.
     * @throws {@link VersionException}
     * If the specified <i>version</i> is not part of this node's version history
     * or if an attempt is made to restore the root version (<i>jcr:rootVersion</i>).
     * @throws {@link ItemExistsException}
     * If <i>removeExisting</i> is <i>false</i> and a UUID collision occurs.
     * @throws {@link LockException}
     * If a lock prevents the restore.
     * @throws {@link InvalidItemStateException}
     * If this {@link Session} (not necessarily this {@link Node}) has pending unsaved changes.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Node::restore()
     */
    public function restore($versionName, $removeExisting, $relPath = '') {
        if ($relPath) {
            $this->JRnode->restore($versionName,$removeExisting,$relPath);
        } else {
           $this->JRnode->restore($versionName,$removeExisting);
        }
        $this->session->cache->clean(Zend_Cache::CLEANING_MODE_ALL);
        //TODO - Insert your code here
    }
    
    /**
     *
     * @param string
     * @param boolean
     * @throws {@link UnsupportedRepositoryOperationException}
     * If this node is not verisonable.
     * @throws {@link VersionException}
     * If the specified <i>versionLabel</i> does not exist in this
     * node's version history.
     * @throws {@link ItemExistsException}
     * If <i>removeExisting</i> is <i>false</i> and a UUID collision occurs.
     * @throws {@link LockException}
     * If a lock prevents the restore.
     * @throws {@link InvalidItemStateException}
     * If this {@link Session} (not necessarily this {@link Node}) has pending unsaved changes.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Node::restoreByLabel()
     */
    public function restoreByLabel($versionLabel, $removeExisting) {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @param string
     * The name of a {@link Property} of $this {@link Node}
     * @param mixed
     * The value to be assigned
     * @param int|null
     * The type of the {@link Property} (Optional: NULL if not
     * specified).
     * @return object
     * A {@link Property} object
     * @throws {@link ValueFormatException}
     * If <i>$value</i> cannot be converted to the specified type or
     * if the property already exists and is multi-valued.
     * @throws {@link VersionException}
     * If this node is versionable and checked-in or is non-versionable but
     * its nearest versionable ancestor is checked-in and this implementation
     * performs this validation immediately instead of waiting until
     * {@link save()}.
     * @throws {@link LockException}
     * If a lock prevents the setting of the property and this implementation
     * performs this validation immediately instead of waiting until
     * {@link save()}.
     * @throws {@link ConstraintViolationException}
     * If the change would violate a node-type or other constraint and this
     * implementation performs this validation immediately instead of
     * waiting until {@link save()}.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Node::setProperty()
     */
    public function setProperty($name, $value, $type = 1) {
        $isNew = true;
        if ($this->hasProperty($name)) {
            $isNew = false;
        }
        
        $filename = null;
        
        switch ($type) {
            case PHPCR_PropertyType::BINARY :
                $pr = new Java("javax.jcr.PropertyType");
                $type = $pr->BINARY;
                
                $strlen = strlen($value);
                // keep it in memory, if small
                if ($strlen < 500) {
                    $out = new Java("java.io.ByteArrayOutputStream");
                    $arr = array();
                    
                    for ($i = 0; $i < $strlen; $i ++) {
                        $val = ord(substr($value, $i, 1));
                        if ($val >= 128) {
                            $val = ($val) - 256;
                        }
                        $arr[] = $val;
                    }
                    $out->write($arr);
                    $value = new Java("java.io.ByteArrayInputStream", $out->toByteArray());
                } else {
                    $filename = tempnam(sys_get_temp_dir(),"jrcr");
                    chmod($filename,0666);
                    file_put_contents($filename,$value);
                    $value = new Java("java.io.FileInputStream",$filename);
                }
            break;
            case PHPCR_PropertyType::DATE :
                $pr = new Java("javax.jcr.PropertyType");
                $type = $pr->DATE;
                //$ValueFactory = new Java("javax.jcr.ValueFactory");
                $cal = new Java("java.util.Calendar");
                $val = $cal->getInstance();
                if ($value instanceof DateTime ) {
                    $val->setTimeInMillis($value->format('U') * 1000);
                } else {
                    $val->setTimeInMillis($value * 1000);
                }
                $value = $val;
                break;
            }
            if (! is_object($value) && $type) {
                $jrprop = $this->JRnode->setProperty($name, $value, $type);
            } else {
                $jrprop = $this->JRnode->setProperty($name, $value);
            }
            
            if (null === $jrprop) {
                throw new PHPCR_RepositoryException("Couldn't create new property");
            }
            
            if ($filename) {
                unlink($filename);
            }
            $property = new jr_cr_property($this, $jrprop);
            $this->addPropertyToList($property);
            if ($isNew) {
                $property->setNew(true);
            }
            $property->setModified(true);
            $this->setModified(true);
            if ($this->session->cache) {
                $this->session->cache->clean(Zend_Cache::CLEANING_MODE_ALL);
            }
    }
    
    /**
     *
     * @throws {@link UnsupportedRepositoryOperationException}
     * If this implementation does not support locking.
     * @throws {@link LockException}
     * If this node does not currently hold a lock or holds a lock for which this Session does not have the correct lock token
     * @throws {@link AccessDeniedException}
     * If the current session does not have permission to unlock this node.
     * @throws {@link InvalidItemStateException}
     * If this node has pending unsaved changes.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Node::unlock()
     */
    public function unlock() {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @param string
     * The name of the source workspace.
     * @throws {@link NoSuchWorkspaceException}
     * If <i>srcWorkspace</i> does not exist.
     * @throws {@link InvalidItemStateException}
     * If this {@link Session} (not necessarily this {@link Node}) has
     * pending unsaved changes.
     * @throws {@link AccessDeniedException}
     * If the current session does not have sufficient rights to perform
     * the operation.
     * @throws {@link LockException}
     * If a lock prevents the update.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Node::update()
     */
    public function update($scrWorkspaceName) {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @param object
     * A {@link ItemVisitor} object
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Item::accept()
     */
    public function accept(PHPCR_ItemVisitorInterface $visitor) {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @param int
     * An integer, 0 &lt;= $degree &lt;= n where
     * n is the depth of $this {@link Item} along the
     * path returned by {@link getPath()}.
     * @return object
     * The ancestor of the specified absolute degree of $this
     * {@link Item} along the path returned by{@link getPath()}.
     * @throws {@link ItemNotFoundException}
     * If $degree &lt; 0 or $degree &gt; n
     * where n is the is the depth of $this {@link Item}
     * along the path returned by {@link getPath()}.
     * @throws {@link AccessDeniedException}
     * If the current {@link Ticket} does not have sufficient access rights to
     * complete the operation.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Item::getAncestor()
     */
    public function getAncestor($degree) {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @return int
     * The depth of this {@link Item} in the repository hierarchy.
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Item::getDepth()
     */
    public function getDepth() {
        return $this->JRnode->getDepth();
    }
    
    /**
     *
     * @return string
     * The (or a) name of this {@link Item} or an empty string if this
     * {@link Item} is the root {@link Node}.
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Item::getName()
     */
    public function getName() {
        if (!$this->name) {
            $this->name = $this->JRnode->getName();
        }
        return $this->name;
    }
    
    /**
     *
     * @return object
     * The parent of this {@link Item} along the path returned by
     * {@link getPath()}.
     * @throws {@link ItemNotFoundException}
     * If there is no parent.  This only happens if $this
     * {@link Item} is the root node.
     * @throws {@link AccessDeniedException}
     * If the current {@link Ticket} does not have sufficient access rights to
     * complete the operation.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Item::getParent()
     */
    public function getParent() {
        try {
        $p = $this->JRnode->getParent();
        return new jr_cr_node($this->session,$p);
        } catch (Exception $e) {
            throw new PHPCR_ItemNotFoundException;
        }
    }
    
    /**
     *
     * @return string
     * The path (or one of the paths) of this {@link Item}.
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Item::getPath()
     */
    public function getPath() {
        if (!$this->path) {
            $this->path = $this->JRnode->getPath();
        }
        return $this->path;
    }
    
    /**
     *
     * @return object
     * A {@link Session} object
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Item::getSession()
     */
    public function getSession() {
        return $this->session;
    }
    
    /**
     *
     * @return boolean
     * @see PHPCR_Item::isModified()
     */
    public function isModified() {
        return $this->modified;
    }
    
    public function setModified($m) {
        if ($m) {
            $this->session->addNodeToModifiedList($this);
        }
        $this->modified = $m;
    }
    
    /**
     *
     * @return boolean
     * @see PHPCR_Item::isNew()
     */
    public function isNew() {
        return $this->new;
    }
    
    public function setNew($new) {
        $this->new = $new;
        if ($new) {
            $this->session->addNodeToList($this);
            $this->session->addNodeToModifiedList($this);
        }
    }
    
    /**
     *
     * @return bool
     * Returns TRUE if this {@link Item} is a {@link Node};
     * Returns FALSE if this {@link Item} is a {@link Property}.
     * @see PHPCR_Item::isNode()
     */
    public function isNode() {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @param object
     * A {@link Item} object
     * @return boolean
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Item::isSame()
     */
    public function isSame(PHPCR_ItemInterface $otherItem) {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @param boolean
     * @throws {@link InvalidItemStateException}
     * If this {@link Item} object represents a workspace item that has been
     * removed (either by this session or another).
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Item::refresh()
     */
    public function refresh($keepChanges) {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @throws {@link VersionException}
     * If the parent node of this item is versionable and checked-in or is
     * non-versionable but its nearest versionable ancestor is checked-in
     * and this implementation performs this validation immediately instead
     * of waiting until {@link save()}.
     * @throws {@link LockException}
     * If a lock prevents the removal of this item and this implementation
     * performs this validation immediately instead of waiting until
     * {@link save()}.
     * @throws {@link ConstraintViolationException}
     * If removing the specified item would violate a node type or
     * implementation-specific constraint and this implementation performs
     * this validation immediately instead of waiting until {@link save()}.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Item::remove()
     */
    public function remove() {
        $this->JRnode->remove();
    }
    
    /**
     * Helper function not in specs :
     *
     * @param string $toAbsPath
     */
    public function copy($toAbsPath) {
          $this->session->getWorkspace()->copy($this->getPath(), $toAbsPath);
          return ;
     }
     
    /**
     *
     * @throws {@link AccessDeniedException}
     * If any of the changes to be persisted would violate the access
     * privileges of the this {@link Session}. Also thrown if any of the
     * changes to be persisted would cause the removal of a node that is
     * currently referenced by a <i>REFERENCE</i> property that this
     * Session <i>does not</i> have read access to.
     * @throws {@link ItemExistsException}
     * If any of the changes to be persisted would be prevented by the
     * presence of an already existing item in the workspace.
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
     * @see PHPCR_Item::save()
     */
    public function save() {
        /*foreach ($this->properties as $p) {
            if ($p->isModified()) {
                $p->save();
            }
        }*/
        $this->JRnode->save();
        //some storage providers have to write all properties at once (jr_cr_storage_file eg)
    }
    
    public static function uuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
    }
    
    protected function addPropertyToList(jr_cr_property $property) {
        $this->properties[$property->getName()] = $property;
    }
    
    protected function getPropertyFromList($jrnode) {
        $path = $jrnode->getName();
        if (! $path) {
            return null;
        }
        
        if (isset($this->properties[$path]) && $this->properties[$path]) {
            return $this->properties[$path];
        }
        
        $prop = new jr_cr_property($this, $jrnode);
        if ($prop) {
            $this->addPropertyToList($prop);
            return $prop;
        } else {
            return null;
        }
    }
    
    /**
     * Returns the identifier of this node. Applies to both referenceable and
     * non-referenceable nodes.
     *
     * @return string the identifier of this node
     * @throws PHPCR_RepositoryException If an error occurs.
     */
    public function getIdentifier() {
        //TODO: Insert Code
    }
    
    /**
     * This method returns all WEAKREFERENCE properties that refer to this node,
     * have the specified name and that are accessible through the current Session.
     * If the name parameter is null then all referring WEAKREFERENCE are returned
     * regardless of name.
     *
     * Some level 2 implementations may only return properties that have been
     * saved (in a transactional setting this includes both those properties that
     * have been saved but not yet committed, as well as properties that have
     * been committed). Other level 2 implementations may additionally return
     * properties that have been added within the current Session but are not yet
     * saved.
     *
     * In implementations that support versioning, this method does not return
     * properties that are part of the frozen state of a version in version storage.
     *
     * If this node has no referring properties with the specified name, an empty
     * iterator is returned.
     *
     * @param string $name name of referring WEAKREFERENCE properties to be returned; if null then all referring WEAKREFERENCEs are returned
     * @return PHPCR_PropertyIteratorInterface A PropertyIterator.
     * @throws PHPCR_RepositoryException if an error occurs
     */
    public function getWeakReferences($name = NULL) {
        //TODO: Insert Code
    }
    
    /**
     * Changes the primary node type of this node to nodeTypeName. Also immediately
     * changes this node's jcr:primaryType property appropriately. Semantically,
     * the new node type may take effect immediately or on dispatch but must take
     * effect on persist.
     * Whichever behavior is adopted it must be the same as the behavior adopted
     * for addMixin() (see below) and the behavior that occurs when a node is
     * first created.
     *
     * @param string $nodeTypeName the name of the new node type.
     * @return void
     * @throws PHPCR_ConstraintViolationException If the specified primary node type creates a type conflict and this implementation performs this validation immediately.
     * @throws PHPCR_NodeType_NoSuchNodeTypeException If the specified nodeTypeName is not recognized and this implementation performs this validation immediately.
     * @throws PHPCR_Version_VersionException if this node is read-only due to a checked-in node and this implementation performs this validation immediately.
     * @throws PHPCR_Lock_LockException if a lock prevents the change of the primary node type and this implementation performs this validation immediately.
     * @throws PHPCR_RepositoryException if another error occurs.
     */
    public function setPrimaryType($nodeTypeName) {
        //TODO: Insert Code
    }
    
    /**
     * Returns an iterator over all nodes that are in the shared set of this node.
     * If this node is not shared then the returned iterator contains only this node.
     *
     * @return PHPCR_NodeIteratorInterface a NodeIterator
     * @throws PHPCR_RepositoryException if an error occurs.
     */
    public function getSharedSet() {
        //TODO: Insert Code
    }

    /**
     * Removes this node and every other node in the shared set of this node.
     *
     * This removal must be done atomically, i.e., if one of the nodes cannot be
     * removed, the method throws the exception Node#remove() would have thrown
     * in that case, and none of the nodes are removed.
     *
     * If this node is not shared this method removes only this node.
     *
     * @return void
     * @throws PHPCR_Version_VersionException if the parent node of this item is versionable and checked-in or is non-versionable but its nearest versionable ancestor is checked-in and this implementation performs this validation immediately.
     * @throws PHPCR_Lock_LockException if a lock prevents the removal of this item and this implementation performs this validation immediately.
     * @throws PHPCR_NodeType_ConstraintViolationException if removing the specified item would violate a node type or implementation-specific constraint and this implementation performs this validation immediately.
     * @throws PHPCR_RepositoryException if another error occurs.
     * @see removeShare()
     * @see Item::remove()
     * @see SessionInterface::removeItem
     */
    public function removeSharedSet(){
        //TODO: Insert Code
    }
    
    /**
     * Removes this node, but does not remove any other node in the shared set
     * of this node.
     *
     * @return void
     * @throws PHPCR_Version_VersionException if the parent node of this item is versionable and checked-in or is non-versionable but its nearest versionable ancestor is checked-in and this implementation performs this validation immediately instead of waiting until save.
     * @throws PHPCR_Lock_LockException if a lock prevents the removal of this item and this implementation performs this validation immediately instead of waiting until save.
     * @throws PHPCR_NodeType_ConstraintViolationException if removing the specified item would violate a node type or implementation-specific constraint and this implementation performs this validation immediately instead of waiting until save.
     * @throws PHPCR_RepositoryException if this node cannot be removed without removing another node in the shared set of this node or another error occurs.
     * @see removeSharedSet()
     * @see Item::remove()
     * @see SessionInterface::removeItem
     */
    public function removeShare() {
        //TODO: insert code
    }
    
    /**
     * Causes the lifecycle state of this node to undergo the specified transition.
     * This method may change the value of the jcr:currentLifecycleState property,
     * in most cases it is expected that the implementation will change the value
     * to that of the passed transition parameter, though this is an
     * implementation-specific issue. If the jcr:currentLifecycleState property
     * is changed the change is persisted immediately, there is no need to call
     * save.
     *
     * @param string $transition a state transition
     * @return void
     * @throws PHPCR_UnsupportedRepositoryOperationException  if this implementation does not support lifecycle actions or if this node does not have the mix:lifecycle mixin.
     * @throws PHPCR_InvalidLifecycleTransitionException if the lifecycle transition is not successful.
     * @throws PHPCR_RepositoryException if another error occurs.
     */
    public function followLifecycleTransition($transition) {
        //TODO: Insert Code
    }
    
    /**
     * Returns the list of valid state transitions for this node.
     *
     * @return array a string array.
     * @throws PHPCR_UnsupportedRepositoryOperationException  if this implementation does not support lifecycle actions or if this node does not have the mix:lifecycle mixin.
     * @throws PHPCR_RepositoryException if another error occurs.
     */
    public function getAllowedLifecycleTransitions() {
        //TODO: Insert Code
    }
}
