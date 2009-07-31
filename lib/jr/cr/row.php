<?php


class jr_cr_row implements PHPCR_Query_RowInterface {
    protected $JRrow = null;
    /**
     *
     */
    function __construct($jrrow) {
        $this->JRrow = $jrrow;
    }
    
    /**
     *
     * @return object
     * A {@link Value} object
     * @throws {@link ItemNotFoundException}
     * If <i>$propertyName</i> s not among the column names of the
     * query result table.
     * @throws {@link RepositoryException}
     * If another error occurs
     * @see PHPCR_Query_RowInterface::getValue()
     */
    public function getValue($propertyName) {
        return new jr_cr_value($this->JRrow->getValue($propertyName));
    }
    
    /**
     *
     * @return array
     * @throws {@link RepositoryException}
     * If an error occurs
     * @see PHPCR_Query_RowInterface::getValues()
     */
    public function getValues() {
        $ret = array();
        $jarr = $this->JRrow->getValues();
        foreach($jarr as $key => $jval) $ret[$key] = new jr_cr_value($jval);
        return $ret;
    }
    
    /**
     * Returns the Node corresponding to this Row and the specified selector,
     * if given.
     *
     * @param string $selectorName
     * @return PHPCR_NodeInterface a Node
     * @throws PHPCR_RepositoryException if selectorName is not the alias of a selector in this query or if another error occurs.
     */
    public function getNode($selectorName = NULL) {
        //TODO: Insert Code
    }
    
    /**
     * Equivalent to Row.getNode(selectorName).getPath(). However, some
     * implementations may be able gain efficiency by not resolving the actual Node.
     *
     * @param string $selectorName
     * @return string
     * @throws PHPCR_RepositoryException if selectorName is not the alias of a selector in this query or if another error occurs.
     */
    public function getPath($selectorName = NULL) {
        //TODO: Insert Code
    }
    
    /**
     * Returns the full text search score for this row associated with the specified
     * selector. This corresponds to the score of a particular node.
     * If no selectorName is given, the default selector is used.
     * If no FullTextSearchScore AQM object is associated with the selector
     * selectorName this method will still return a value. However, in that case
     * the returned value may not be meaningful or may simply reflect the minimum
     * possible relevance level (for example, in some systems this might be a s
     * core of 0).
     *
     * Note, in JCR-SQL2 a FullTextSearchScore AQM object is represented by a
     * SCORE() function. In JCR-JQOM it is represented by a Java object of type
     * PHPCR_Query_QOM_FullTextSearchScoreInterface.
     *
     * @param string $selectorName
     * @return float
     * @throws PHPCR_RepositoryException if selectorName is not the alias of a selector in this query or (in case of no given selectorName) if this query has more than one selector (and therefore, this Row corresponds to more than one Node) or if another error occurs.
     */
    public function getScore($selectorName = NULL) {
        //TODO: Insert Code
    }
}
