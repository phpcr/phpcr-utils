<?php
/**
 * {@link NamespaceRegistry} represents the global persistent namespace
 * registry of the PHPCR Repository.
 *
 * @see Workspace::getNamespaceRegistry()
 *
 * @package phpContentRepository
 */
class jr_cr_namespaceregistry implements PHPCR_NamespaceRegistryInterface {
    protected $JRnamespaceregistry;

    public function __construct($NamespaceRegistry) {
        $this->JRnamespaceregistry = $NamespaceRegistry;
    }

    /**
     * Sets a one-to-one mapping between prefix and URI in the global namespace
     * registry of this repository.
     *
     * Assigning a new prefix to a URI that already exists in the namespace
     * registry erases the old prefix.  In general this can almost always be
     * done, though an implementation is free to prevent particular remappings
     * by throwing a {@link NamespaceException}.
     *
     * On the other hand, taking a prefix that is already assigned to a URI and
     * re-assigning it to a new URI in effect unregisters that URI. Therefore,
     * the same restrictions apply to this operation as to
     * {@link unregisterNamespace()}>:
     * <ul>
     *    <li>
     *        Attempting to unregister a built-in namespace (<i>jcr</i>,
     *        <i>nt</i>, <i>mix</i>, <i>sv</i>,
     *        <i>xml</i> or the empty namespace) will throw a
     *        {@link NamespaceException}.
     *    </li>
     *    <li>
     *        Attempting to unregister a namespace that is currently present in
     *        content (either within an item name or within the value of a
     *        <i>NAME</i> or <i>PATH</i> property) will throw a
     *        {@link NamespaceException}. This includes prefixes in use within
     *        in-content node type  definitions.
     *    </li>
     *    <li>
     *        An attempt to unregister a namespace that is not currently
     *        registered will throw a {@link NamespaceException}.
     *    </li>
     *    <li>
     *        An implementation may prevent the unregistering of any other
     *        namespace for implementation-specific reasons by throwing a
     *        {@link NamespaceException}.
     *    </li>
     * </ul>
     *
     * @param string
     *    The prefix to be mapped.
     * @param string
     *    The URI to be mapped.
     *
     * @throws {@link NamespaceException}
     *    If an illegal attempt is made to register a mapping.
     * @throws {@link UnsupportedRepositoryOperationException}
     *    In a level 1 implementation
     * @throws {@link AccessDeniedException}
     *    If the session associated with the {@link Workspace} object through
     *    which this registry was acquired does not have sufficient permissions
     *    to register the namespace.
     * @throws {@link RepositoryException}
     *    If another error occurs.
     */
    public function registerNamespace($prefix, $uri) {
        //TODO: Implement
    }


    /**
     * Removes a namespace mapping from the registry.
     *
     * The following restriction apply:
     *
     * <ul>
     *    <li>
     *        Attempting to unregister a built-in namespace (<i>jcr</i>,
     *        <i>nt</i>, <i>mix</i>, <i>sv</i>,
     *        <i>xml</i> or the empty namespace) will throw a
     *        {@link NamespaceException}.
     *    </li>
     *    <li>
     *        Attempting to unregister a namespace that is currently present in
     *        content (either within an item name or within the value of a
     *        <i>NAME</i> or <i>PATH</i> property) will throw a
     *        {@link NamespaceException}. This includes prefixes in use within
     *        in-content node type  definitions.
     *    </li>
     *    <li>
     *        An attempt to unregister a namespace that is not currently
     *        registered will throw a {@link NamespaceException}.
     *    </li>
     *    <li>
     *        An implementation may prevent the unregistering of any other
     *        namespace for implementation-specific reasons by throwing a
     *        {@link NamespaceException}.
     *    </li>
     * </ul>
     *
     * @param string
     *    The prefix of the mapping to be removed.
     *
     * @throws {@link NamespaceException}
     *    If an illegal attempt is made to remove a mapping.
     * @throws {@link UnsupportedRepositoryOperationException}
     *    In a level 1 implementation
     * @throws {@link AccessDeniedException}
     *    If the session associated with the {@link Workspace} object through
     *    which this registry was acquired does not have sufficient permissions
     *    to unregister the namespace.
     * @throws {@link RepositoryException}
     *    If another error occurs.
     */
    public function unregisterNamespace($prefix) {
        //TODO: Implement

    }

    /**
     * Returns an array holding all currently registered prefixes.
     *
     * @return array
     *
     * @throws {@link RepositoryException}
     *    If an error occurs
     */
    public function getPrefixes() {
        try {
            return $this->JRnamespaceregistry->getPrefixes();
        } catch(JavaException $e) {
            throw new PHPCR_RepositoryException($e->getMessage());
        }
    }

    /**
     * Returns an array holding all currently registered URIs.
     *
     * @return array
     *
     * @throws {@link RepositoryException}
     *    If an error occurs
     */
    public function getURIs() {
        try {
            return $this->JRnamespaceregistry->getURIs();
        } catch(JavaException $e) {
            throw new PHPCR_RepositoryException($e->getMessage());
        }
    }

    /**
     * Returns the URI to which the given $prefix is mapped.
     *
     * @param string
     * @return string
     *
     * @throws {@link NamespaceException}
     *    If the URI is unknown
     * @throws {@link RepositoryException}
     *    If another error occurs
     */
    public function getURI($prefix) {
        try {
            return $this->JRnamespaceregistry->getURI($prefix);
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
     * Returns the prefix to which the given $uri is mapped.
     *
     * @param string
     * @return string
     *
     * @throws {@link NamespaceException}
     *    If the URI is unknown
     * @throws {@link RepositoryException}
     *    If another error occurs
     */
    public function getPrefix($uri) {
        try {
            return $this->JRnamespaceregistry->getPrefix($uri);
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
}
