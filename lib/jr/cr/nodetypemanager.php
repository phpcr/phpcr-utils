<?php
// $Id$

/**
 * This file contains {@link NodeTypeManager} which is part of the PHP Content
 * Repository (PHPCR), a derivative of the Java Content Repository JSR-170,
 * and is licensed under the Apache License, Version 2.0.
 *
 * This file is based on the code created for
 * {@link http://www.jcp.org/en/jsr/detail?id=170 JSR-170}
 *
 * @author Travis Swicegood <development@domain51.com>
 * @copyright PHP Code Copyright &copy; 2004-2005, Domain51, United States
 * @copyright Original Java and Documentation
 *  Copyright &copy; 2002-2004, Day Management AG, Switerland
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License,
 *  Version 2.0
 * @package phpContentRepository
 * @package NodeTypes
 */

/**
 * Allows for the retrieval of {@link NodeType}s.
 *
 * Accessed via {@link Workspace::getNodeTypeManager()}.
 *
 * @package phpContentRepository
 * @package NodeTypes
 */
class jr_cr_nodetypemanager implements PHPCR_NodeType_NodeTypeManagerInterface {
    protected $JRnodetypemanager = null;
    
    public function __construct($jrnodetypemgr) {
        $this->JRnodetypemanager = $jrnodetypemgr;
    }
    
    /**
     * Returns the named {@link NodeType}.
     *
     * @param string
     *   The name of an existing {@link NodeType}.
     * @return object
     *   A {@link NodeType} object.
     *
     * @throws {@link NoSuchNodeTypeException}
     *   If no {@link NodeType} by the given name exists.
     * @throws {@link RepositoryException}
     *  If any other error occurs.
     */
    public function getNodeType($nodeTypeName) {
        //TODO: Insert Code
    }
    
    /**
     * Returns an iterator over all available {@link NodeType}s (primary and
     * mixin).
     *
     * @return object
     *   A {@link NodeTypeIterator} object.
     *
     * @throws {@link RepositoryException}
     *   If an error occurs.
     */
    public function getAllNodeTypes() {
       return new jr_cr_nodetypeiterator($this->JRnodetypemanager->getAllNodeTypes());
    }
    
    /**
     * Returns an iterator over all available primary {@link NodeType}s.
     *
     * @return object
     *   A {@link NodeTypeIterator} object.
     *
     * @throws {@link RepositoryException}
     *   If an error occurs.
     */
    public function getPrimaryNodeTypes() {
        //TODO: Insert Code
    }
    
    /**
     * Returns an iterator over all available mixin {@link NodeType}s.
     *
     * If none are available, an empty iterator is returned.
     *
     * @return object
     *  A {@link NodeTypeIterator} object.
     *
     * @throws {@link RepositoryException}
     *   If an error occurs.
     */
    public function getMixinNodeTypes() {
        //TODO: Insert Code
    }
    
    /**
     * Returns true if a node type with the specified name is registered. Returns
     * false otherwise.
     *
     * @param string $name - a String.
     * @return boolean a boolean
     * @throws PHPCR_RepositoryException if an error occurs.
     */
    public function hasNodeType($name) {
        //TODO: Insert Code
    }
    
    /**
     * Returns an empty NodeTypeTemplate which can then be used to define a node type
     * and passed to NodeTypeManager.registerNodeType.
     *
     * If $ntd is given:
     * Returns a NodeTypeTemplate holding the specified node type definition. This
     * template can then be altered and passed to NodeTypeManager.registerNodeType.
     *
     * @param PHPCR_NodeType_NodeTypeDefinitionInterface $ntd a NodeTypeDefinition.
     * @return PHPCR_NodeType_NodeTypeTemplateInterface A NodeTypeTemplate.
     * @throws PHPCR_UnsupportedRepositoryOperationException if this implementation does not support node type registration.
     * @throws PHPCR_RepositoryException if another error occurs.
     */
    public function createNodeTypeTemplate($ntd = NULL) {
        //TODO: Insert Code
    }

    /**
     * Returns an empty NodeDefinitionTemplate which can then be used to create a
     * child node definition and attached to a NodeTypeTemplate.
     *
     * @return PHPCR_NodeType_NodeDefinitionTemplateInterface A NodeDefinitionTemplate.
     * @throws PHPCR_UnsupportedRepositoryOperationException if this implementation does not support node type registration.
     * @throws PHPCR_RepositoryException if another error occurs.
     */
    public function createNodeDefinitionTemplate() {
        //TODO: Insert Code
    }
    
    /**
     * Returns an empty PropertyDefinitionTemplate which can then be used to create
     * a property definition and attached to a NodeTypeTemplate.
     *
     * @return PHPCR_NodeType_PropertyDefinitionTemplateInterface A PropertyDefinitionTemplate.
     * @throws PHPCR_UnsupportedRepositoryOperationException if this implementation does not support node type registration.
     * @throws PHPCR_RepositoryException if another error occurs.
     */
    public function createPropertyDefinitionTemplate() {
        //TODO: Insert Code
    }
    
    /**
     * Registers a new node type or updates an existing node type using the specified
     * definition and returns the resulting NodeType object.
     * Typically, the object passed to this method will be a NodeTypeTemplate (a
     * subclass of NodeTypeDefinition) acquired from NodeTypeManager.createNodeTypeTemplate
     * and then filled-in with definition information.
     *
     * @param PHPCR_NodeType_NodeTypeDefinitionInterface $ntd an NodeTypeDefinition.
     * @param boolean $allowUpdate a boolean
     * @return PHPCR_NodeType_NodeTypeInterface the registered node type
     * @throws PHPCR_InvalidNodeTypeDefinitionException if the NodeTypeDefinition is invalid.
     * @throws PHPCR_NodeType_NodeTypeExistsException if allowUpdate is false and the NodeTypeDefinition specifies a node type name that is already registered.
     * @throws PHPCR_UnsupportedRepositoryOperationException if this implementation does not support node type registration.
     * @throws PHPCR_RepositoryException if another error occurs.
     */
    public function registerNodeType(PHPCR_NodeType_NodeTypeDefinitionInterface $ntd, $allowUpdate) {
        //TODO: Insert Code
    }
    
    /**
     * Registers or updates the specified array of NodeTypeDefinition objects.
     * This method is used to register or update a set of node types with mutual
     * dependencies. Returns an iterator over the resulting NodeType objects.
     * The effect of the method is "all or nothing"; if an error occurs, no node
     * types are registered or updated.
     *
     * @param array $definitions an array of NodeTypeDefinitions
     * @param boolean $allowUpdate a boolean
     * @return PHPCR_NodeType_NodeTypeIteratorInterface the registered node types.
     * @throws PHPCR_InvalidNodeTypeDefinitionException - if a NodeTypeDefinition within the Collection is invalid or if the Collection contains an object of a type other than NodeTypeDefinition.
     * @throws PHPCR_NodeType_NodeTypeExistsException if allowUpdate is false and a NodeTypeDefinition within the Collection specifies a node type name that is already registered.
     * @throws PHPCR_UnsupportedRepositoryOperationException if this implementation does not support node type registration.
     * @throws PHPCR_RepositoryException if another error occurs.
     */
    public function registerNodeTypes(array $definitions, $allowUpdate) {
        //TODO: Insert Code
    }
    
    /**
     * Unregisters the specified node type.
     *
     * @param string $name a String.
     * @return void
     * @throws PHPCR_UnsupportedRepositoryOperationException if this implementation does not support node type registration.
     * @throws PHPCR_NodeType_NoSuchNodeTypeException if no registered node type exists with the specified name.
     * @throws PHPCR_RepositoryException if another error occurs.
     */
    public function unregisterNodeType($name) {
        //TODO: Insert Code
    }
    
    /**
     * Unregisters the specified set of node types. Used to unregister a set of node
     * types with mutual dependencies.
     *
     * @param array $names a String array
     * @return void
     * @throws PHPCR_UnsupportedRepositoryOperationException if this implementation does not support node type registration.
     * @throws PHPCR_NodeType_NoSuchNodeTypeException if one of the names listed is not a registered node type.
     * @throws PHPCR_RepositoryException if another error occurs.
     */
    public function unregisterNodeTypes(array $names) {
        //TODO: Insert Code
    }
}
