<?php


class jr_cr_workspace implements PHPCR_WorkspaceInterface {
    /**
     * The session object
     *
     * @var jr_cr_session
     */
    protected $session = null;
    
    /**
     * Enter description here...
     *
     * @var lx_cr_querymanager
     */
    protected $querymanager = null;
    
    protected $JRworkspace;
    protected $name = '';
    /**
     *
     */
    function __construct($jrworkspace,$session) {
        $this->JRworkspace = $jrworkspace;
        $this->session = $session;
    }
    
    /**
     *
     * @param string
     * The name of the workspace from which the node is to be copied.
     * @param string
     * The path of the node to be copied in <i>$srcWorkspace</i>.
     * @param string
     * The location to which the node at <i>$srcAbsPath</i> is to be
     * copied in <i>$this</i> workspace.
     * @param boolean
     * If <i>false</i> then this method throws an
     * {@link ItemExistsException} on UUID conflict with an incoming node.
     * If <i>true</i> then a UUID conflict is resolved by removing the
     * existing node from its location in this workspace and cloning (copying
     * in) the one from <i>$srcWorkspace</i>.
     * @throws {@link NoSuchWorkspaceException}
     * If <i>$destWorkspace</i> does not exist.
     * @throws {@link ConstraintViolationException}
     * If the operation would violate a node-type or other
     * implementation-specific constraint.
     * @throws {@link VersionException}
     * If the parent node of <i>$destAbsPath</i> is versionable and
     * checked-in, or is non-versionable but its nearest versionable ancestor
     * is checked-in. This exception will also be thrown if
     * <i>$removeExisting</i> is <i>$true</i>, and a UUID
     * conflict occurs that would require the moving and/or altering of a
     * node that is checked-in.
     * @throws {@link AccessDeniedException}
     * If the current session does not have sufficient access rights to
     * complete the operation.
     * @throws {@link PathNotFoundException}
     * If the node at <i>$srcAbsPath</i> in <i>$srcWorkspace</i>
     * or the parent of <i>$destAbsPath</i> in this workspace does not
     * exist.
     * @throws {@link ItemExistsException}
     * If a property already exists at <i>$destAbsPath</i> or a node
     * already exist there, and same name siblings are not allowed or if
     * <i>$removeExisting</i> is false and a UUID conflict occurs.
     * @throws {@link LockException}
     * If a lock prevents the clone.
     * @throws {@link RepositoryException}
     * If the last element of <i>$destAbsPath</i> has an index or if
     * another error occurs.
     * @see PHPCR_Workspace::clone_()
     */
    public function clone_($srcWorkspace, $srcAbsPath, $destAbsPath, $removeExisting) {
        //TODO - Insert your code here
    }
    
    public function createWorkspace($nome, $srcWorkspace = NULL) {
        if (null === $srcWorkspace) {
            $this->JRworkspace->createWorkspace($nome);
        } else {
            //TODO: Insert Code
        }
    }
    
    /**
     * This method copies the subgraph rooted at, and including, the node at
     * $srcWorkspace (if given) and $srcAbsPath to the new location in this
     * Workspace at $destAbsPath.
     *
     * This is a workspace-write operation and therefore dispatches changes
     * immediately and does not require a save.
     *
     * When a node N is copied to a path location where no node currently
     * exists, a new node N' is created at that location.
     * The subgraph rooted at and including N' (call it S') is created and is
     * identical to the subgraph rooted at and including N (call it S) with the
     * following exceptions:
     * * Every node in S' is given a new and distinct identifier
     *   - or if $srcWorkspace is given -
     *   Every referenceable node in S' is given a new and distinct identifier
     *   while every non-referenceable node in S' may be given a new and
     *   distinct identifier.
     * * The repository may automatically drop any mixin node type T present on
     *   any node M in S. Dropping a mixin node type in this context means that
     *   while M remains unchanged, its copy M' will lack the mixin T and any
     *   child nodes and properties defined by T that are present on M. For
     *   example, a node M that is mix:versionable may be copied such that the
     *   resulting node M' will be a copy of N except that M' will not be
     *   mix:versionable and will not have any of the properties defined by
     *   mix:versionable. In order for a mixin node type to be dropped it must
     *   be listed by name in the jcr:mixinTypes property of M. The resulting
     *   jcr:mixinTypes property of M' will reflect any change.
     * * If a node M in S is referenceable and its mix:referenceable mixin is
     *   not dropped on copy, then the resulting jcr:uuid property of M' will
     *   reflect the new identifier assigned to M'.
     * * Each REFERENCE or WEAKEREFERENCE property R in S is copied to its new
     *   location R' in S'. If R references a node M within S then the value of
     *   R' will be the identifier of M', the new copy of M, thus preserving the
     *   reference within the subgraph.
     *
     * When a node N is copied to a location where a node N' already exists, the
     * repository may either immediately throw an ItemExistsException or attempt
     * to update the node N' by selectively replacing part of its subgraph with
     * a copy of the relevant part of the subgraph of N. If the node types of N
     * and N' are compatible, the implementation supports update-on-copy for
     * these node types and no other errors occur, then the copy will succeed.
     * Otherwise an ItemExistsException is thrown.
     *
     * Which node types can be updated on copy and the details of any such
     * updates are implementation-dependent. For example, some implementations
     * may support update-on-copy for mix:versionable nodes. In such a case the
     * versioning-related properties of the target node would remain unchanged
     * (jcr:uuid, jcr:versionHistory, etc.) while the substantive content part
     * of the subgraph would be replaced with that of the source node.
     *
     * The $destAbsPath provided must not have an index on its final element. If
     * it does then a RepositoryException is thrown. Strictly speaking, the
     * $destAbsPath parameter is actually an absolute path to the parent node of
     * the new location, appended with the new name desired for the copied node.
     * It does not specify a position within the child node ordering. If ordering
     * is supported by the node type of the parent node of the new location, then
     * the new copy of the node is appended to the end of the child node list.
     *
     * This method cannot be used to copy an individual property by itself. It
     * copies an entire node and its subgraph (including, of course, any
     * properties contained therein).
     *
     * @param string $srcAbsPath the path of the node to be copied.
     * @param string $destAbsPath the location to which the node at srcAbsPath is to be copied in this workspace.
     * @param string $srcWorkspace the name of the workspace from which the copy is to be made.
     * @return void
     * @throws PHPCR_NoSuchWorkspaceException if srcWorkspace does not exist or if the current Session does not have permission to access it.
     * @throws PHPCR_ConstraintViolationException if the operation would violate a node-type or other implementation-specific constraint
     * @throws PHPCR_Version_VersionException if the parent node of destAbsPath is read-only due to a checked-in node.
     * @throws PHPCR_AccessDeniedException if the current session does have access srcWorkspace but otherwise does not have sufficient access to complete the operation.
     * @throws PHPCR_PathNotFoundException if the node at srcAbsPath in srcWorkspace or the parent of destAbsPath in this workspace does not exist.
     * @throws PHPCR_ItemExistsException if a node already exists at destAbsPath and either same-name siblings are not allowed or update on copy is not supported for the nodes involved.
     * @throws PHPCR_Lock_LockException if a lock prevents the copy.
     * @throws PHPCR_RepositoryException if the last element of destAbsPath has an index or if another error occurs.
     */
    public function copy($srcAbsPath, $destAbsPath, $srcWorkspace = NULL) {
           if ($srcWorkspace) {
           } else {
               $this->JRworkspace->copy($srcAbsPath,$destAbsPath);
          }
    }
    
    /**
     *
     * @return array
     * Containing names of accessible workspaces.
     * @throws {@link RepositoryException}
     * @see PHPCR_Workspace::getAccessibleWorkspaceNames()
     */
    public function getAccessibleWorkspaceNames() {
        return $this->JRworkspace->getAccessibleWorkspaceNames();
    }
    
    /**
     *
     * @param parentAbsPath the absolute path of a node under which (as child) the imported subtree will be built.
     * @param uuidBehavior a four-value flag that governs how incoming UUIDs are handled.
     * @return an org.xml.sax.ContentHandler whose methods may be called to feed SAX events into the deserializer.
     * @throws {@link PathNotFoundException}
     * If no node exists at <i>$parentAbsPath</i>.
     * @throws {@link ConstraintViolationException}
     * If the new subtree cannot be added to the node at
     * <i>$parentAbsPath</i> due to node-type or other implementation-specific constraints,
     * and this can be determined before the first SAX event is sent.
     * @throws {@link VersionException}
     * If the node at <i>$parentAbsPath</i> is versionable
     * and checked-in, or is non-versionable but its nearest versionable ancestor is checked-in.
     * @throws {@link LockException}
     * If a lock prevents the addition of the subtree.
     * @throws {@link AccessDeniedException}
     * If the session associated with this {@link Workspace} object does not have
     * sufficient permissions to perform the import.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @todo Determine if feasiable within PHP
     * @see PHPCR_Workspace::getImportContentHandler()
     */
    public function getImportContentHandler( $parentAbsPath,  $uuidBehavior) {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @return string
     * @see PHPCR_Workspace::getName()
     */
    public function getName() {
        if (empty($this->name)) {
            $this->name = $this->JRworkspace->getName();
        }
        return $this->name;
    }
    
    /**
     *
     * @return object
     * A {@link NamespaceRegisty} object
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Workspace::getNamespaceRegistry()
     */
    public function getNamespaceRegistry() {
        $nr = $this->JRworkspace->getNamespaceRegistry();
        return new jr_cr_namespaceregistry($nr);
    }
    
    /**
     *
     * @return object
     * A {@link NodeTypeManager} object
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Workspace::getNodeTypeManager()
     */
    public function getNodeTypeManager() {
        return new jr_cr_nodetypemanager($this->JRworkspace->getNodeTypeManager());
    }
    
    /**
     *
     * @return object
     * A {@link ObservationManager} object
     * @throws {@link UnsupportedRepositoryOperationException}
     * If the implementation does not support observation.
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Workspace::getObservationManager()
     */
    public function getObservationManager() {
         return $this->JRworkspace->getObservationManager();
    }
    
    /**
     *
     * @return jr_cr_querymanager
     * A {@link QueryManager} object
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Workspace::getQueryManager()
     */
    public function getQueryManager() {
        if (!$this->querymanager) {
            $this->querymanager = new jr_cr_querymanager($this,$this->JRworkspace->getQueryManager());
        }
        return $this->querymanager;
    }
    
    /**
     *
     * @return object
     * A {@link Session} object
     * @see PHPCR_Workspace::getSession()
     */
    public function getSession() {
        return $this->session;
    }
    
    /**
     *
     * @param parentAbsPath the absolute path of the node below which the deserialized subtree is added.
     * @param in The <i>Inputstream</i> from which the XML to be deserilaized is read.
     * @param uuidBehavior a four-value flag that governs how incoming UUIDs are handled.
     * @throws {@link java}
     * .io.IOException if an error during an I/O operation occurs.
     * @throws {@link PathNotFoundException}
     * If no node exists at <i>$parentAbsPath</i>.
     * @throws {@link ConstraintViolationException}
     * If node-type or other implementation-specific constraints
     * prevent the addition of the subtree or if <i>$uuidBehavior</i>
     * is set to <i>IMPORT_UUID_COLLISION_REMOVE_EXISTING</i> and an incoming node has the same
     * UUID as the node at <i>$parentAbsPath</i> or one of its ancestors.
     * @throws {@link VersionException}
     * If the node at <i>$parentAbsPath</i> is versionable
     * and checked-in, or is non-versionable but its nearest versionable ancestor is checked-in.
     * @throws {@link InvalidSerializedDataException}
     * If incoming stream is not a valid XML document.
     * @throws {@link ItemExistsException}
     * If the top-most element of the incoming XML would deserialize
     * to a node with the same name as an existing child of <i>$parentAbsPath</i> and that
     * child does not allow same-name siblings, or if a <i>$uuidBehavior</i> is set to
     * <i>IMPORT_UUID_COLLISION_THROW</i> and a UUID collision occurs.
     * @throws {@link LockException}
     * If a lock prevents the addition of the subtree.
     * @throws {@link AccessDeniedException}
     * If the session associated with this {@link Workspace} object does not have
     * sufficient permissions to perform the import.
     * @throws {@link RepositoryException}
     * is another error occurs.
     * @todo Determine if feasiable within PHP
     * @see PHPCR_Workspace::importXML()
     */
    public function importXML( $parentAbsPath,  $in,  $uuidBehavior) {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @param string
     * The path of the node to be moved.
     * @param string
     * The location to which the node at <i>$srcAbsPath</i> is to be
     * moved.
     * @throws {@link ConstraintViolationException}
     * If the operation would violate a node-type or other
     * implementation-specific constraint
     * @throws {@link VersionException}
     * If the parent node of <i>$destAbsPath</i> or the parent node
     * of <i>$srcAbsPath</i> is versionable and checked-in, or is
     * non-versionable but its nearest versionable ancestor is checked-in.
     * @throws {@link AccessDeniedException}
     * If the current session (i.e. the session that was used to aqcuire
     * this {@link Workspace} object) does not have sufficient access rights
     * to complete the operation.
     * @throws {@link PathNotFoundException}
     * If the node at <i>$srcAbsPath</i> or the parent of
     * <i>$destAbsPath</i> does not exist.
     * @throws {@link ItemExistsException}
     * If a property already exists at <i>$destAbsPath</i> or a node
     * already exist there, and same name siblings are not allowed.
     * @throws {@link LockException}
     * If a lock prevents the move.
     * @throws {@link RepositoryException}
     * If the last element of <i>$destAbsPath</i> has an index or if
     * another error occurs.
     * @see PHPCR_Workspace::move()
     */
    public function move($srcAbsPath, $destAbsPath) {
         $this->JRworkspace->move($srcAbsPath,$destAbsPath);
    }
    
    /**
     *
     * @param array
     * The set of versions to be restored
     * @param boolean
     * Governs what happens on UUID collision.
     * @throws {@link ItemExistsException}
     * If <i>$removeExisting</i> is <i>$false</i> and a UUID
     * collision occurs with a node being restored.
     * @throws {@link UnsupportedRepositoryOperationException}
     * If one or more of the nodes to be restored is not versionable.
     * @throws {@link VersionException}
     * If the set of versions to be restored is such that the original path
     * location of one or more of the versions cannot be determined or if
     * the <i>$restore</i> would change the state of a existing
     * verisonable node that is currently checked-in or if a root version
     * (<i>jcr:rootVersion</i>) is among those being restored.
     * @throws {@link LockException}
     * If a lock prevents the restore.
     * @throws {@link InvalidItemStateException}
     * If this {@link Session} (not necessarily this <i>Node</i>) has
     * pending unsaved changes.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Workspace::restore()
     */
    public function restore($versions, $removeExisting) {
        //TODO - Insert your code here
    }
    
    /**
     * Clones the subgraph at the node srcAbsPath in srcWorkspace to the new
     * location at destAbsPath in this workspace.
     * Unlike the signature of copy that copies between workspaces, this method
     * does not assign new identifiers to the newly cloned nodes but preserves
     * the identifiers of their respective source nodes. This applies to both
     * referenceable and non-referenceable nodes.
     *
     * In some implementations there may be cases where preservation of a
     * non-referenceable identifier is not possible, due to how non-referenceable
     * identifiers are constructed in that implementation. In such a case this
     * method will throw a RepositoryException.
     *
     * If removeExisting is true and an existing node in this workspace (the
     * destination workspace) has the same identifier as a node being cloned
     * from srcWorkspace, then the incoming node takes precedence, and the
     * existing node (and its subgraph) is removed. If removeExisting is false
     * then an identifier collision causes this method to throw a
     * ItemExistsException and no changes are made.
     *
     * If successful, the change is persisted immediately, there is no need
     * to call save.
     *
     * The destAbsPath provided must not have an index on its final element.
     * If it does then a RepositoryException is thrown.
     * If ordering is supported by the node type of the parent node of the new
     * location, then the new clone of the node is appended to the end of the
     * child node list.
     *
     * This method cannot be used to clone just an individual property; it
     * clones a node and its subgraph.
     *
     * @param string $srcWorkspace - The name of the workspace from which the node is to be copied.
     * @param string $srcAbsPath - the path of the node to be copied in srcWorkspace.
     * @param string $destAbsPath - the location to which the node at srcAbsPath is to be copied in this workspace.
     * @param boolean $removeExisting - if false then this method throws an ItemExistsException on identifier conflict with an incoming node. If true then a identifier conflict is resolved by removing the existing node from its location in this workspace and cloning (copying in) the one from srcWorkspace.
     * @return void
     * @throws PHPCR_NoSuchWorkspaceException if destWorkspace does not exist.
     * @throws PHPCR_ConstraintViolationException if the operation would violate a node-type or other implementation-specific constraint.
     * @throws PHPCR_Version_VersionException if the parent node of destAbsPath is read-only due to a checked-in node. This exception will also be thrown if removeExisting is true, and an identifier conflict occurs that would require the moving and/or altering of a node that is checked-in.
     * @throws PHPCR_AccessDeniedException if the current session does not have sufficient access to complete the operation.
     * @throws PHPCR_PathNotFoundException if the node at srcAbsPath in srcWorkspace or the parent of destAbsPath in this workspace does not exist.
     * @throws PHPCR_ItemExistsException if a node already exists at destAbsPath and same-name siblings are not allowed or if removeExisting is false and an identifier conflict occurs.
     * @throws PHPCR_Lock_LockException if a lock prevents the clone.
     * @throws PHPCR_RepositoryException if the last element of destAbsPath has an index or if another error occurs.
     */
    public function klone($srcWorkspace, $srcAbsPath, $destAbsPath, $removeExisting) {
        //TODO: Insert Code
    }
    
    /**
     * Returns the LockManager object, through which locking methods are accessed.
     *
     * @return PHPCR_Lock_LockManagerInterface
     * @throws PHPCR_UnsupportedRepositoryOperationException if the implementation does not support locking.
     * @throws PHPCR_RepositoryException if an error occurs.
     */
    public function getLockManager() {
        //TODO: Insert Code
    }
    
    /**
     * Returns the VersionManager object.
     *
     * @return PHPCR_Version_VersionManagerInterface a VersionManager object.
     * @throws PHPCR_UnsupportedRepositoryOperationException if the implementation does not support versioning.
     * @throws PHPCR_RepositoryException if an error occurs.
     */
    public function getVersionManager() {
        //TODO: Insert Code
    }
    
    /**
     * Deletes the workspace with the specified name from the repository,
     * deleting all content within it.
     *
     * @param string $name A String, the name of the workspace to be deleted.
     * @return void
     * @throws PHPCR_AccessDeniedException if the session through which this Workspace object was acquired does not have sufficient access to remove the workspace.
     * @throws PHPCR_UnsupportedRepositoryOperationException if the repository does not support the removal of workspaces.
     * @throws PHPCR_NoSuchWorkspaceException if $name does not exist.
     * @throws PHPCR_RepositoryException if another error occurs.
     */
    public function deleteWorkspace($name) {
        //TODO: Insert Code
    }
}
