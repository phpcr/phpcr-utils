<?php
class jr_cr_querymanager implements PHPCR_Query_QueryManagerInterface {
    /**
     * Enter description here...
     *
     * @var jr_cr_workspace
     */
    protected $workspace = null;
    
    protected $JRquerymanager = null;
    /**
     *
     */
    function __construct($workspace, $jrquerymanager) {
       $this->workspace = $workspace;
       $this->JRquerymanager = $jrquerymanager;
    }
    
    /**
     *
     * @param string
     * @param string
     * @return jr_cr_query
     * A {@link Query} object.
     * @throws {@link InvalidQueryException}
     * If statement is invalid or language is unsupported.
     * @throws {@link RepositoryException}
     * If another error occurs
     * @see PHPCR_QueryManager::createQuery()
     */
    public function createQuery($statement, $language ) {
        return new jr_cr_query($this->JRquerymanager->createQuery($statement,$language),$this->workspace->getSession());
    }
    
    /**
     *
     * @see Query::storeAsNode()
     * @param object
     * A {@link Node} object
     * @return object
     * A {@link Query} object
     * @throws {@link InvalidQueryException}
     * If <i>$node</i> is not a valid persisted query (that is, a node
     * of type <i>nt:query</i>)
     * @throws {@link RepositoryException}
     * If another error occurs
     * @see PHPCR_QueryManager::getQuery()
     */
    public function getQuery($node) {
        try {
            $r = new jr_cr_query($this->JRquerymanager->getQuery($node->JRnode),
                                   $this->workspace->getSession());
        } catch(JavaException $e) {
            $str = split("\n", $e->getMessage(), 1);
            if (strstr($str[0], 'InvalidQueryException')) {
                throw new PHPCR_InvalidQueryException($e->getMessage());
            } elseif (strstr($str[0], 'RepositoryException')) {
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
     * @see PHPCR_QueryManager::getSupportedQueryLanguages()
     */
    public function getSupportedQueryLanguages() {
        return $this->JRquerymanager->getSupportedQueryLanguages();
    }
    
    /**
     * Creates a new prepared query by specifying the query statement itself and the language
     * in which the query is stated.
     *
     * @param string $statement
     * @param string $language
     * @return PHPCR_Query_PreparedQueryInterface a PreparedQuery object
     * @throws PHPCR_Query_InvalidQueryException if the query statement is syntactically invalid or the specified language is not supported
     * @throws PHPCR_RepositoryException if another error occurs
     */
    public function createPreparedQuery($statement, $language) {
        //TODO: Insert Code
    }
    
    /**
     * Returns a QueryObjectModelFactory with which a JCR-JQOM query can be built
     * programmatically.
     *
     * @return PHPCR_Query_QOM_QueryObjectModelFactoryInterface a QueryObjectModelFactory object
     */
    public function getQOMFactory() {
        //TODO: Insert Code
    }
}
