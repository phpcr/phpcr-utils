<?php
class jr_cr_queryresult implements PHPCR_Query_QueryResultInterface {
    /**
     *
     */
    protected $JRqueryresult = null;
    
    public function __construct($jrqueryresult,$session) {
        $this->session = $session;
        $this->JRqueryresult = $jrqueryresult;
        //TODO - Insert your code here
    }
    
    /**
     *
     * @return array
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_QueryResult::getColumnNames()
     */
    public function getColumnNames() {
        return $this->JRqueryresult->getColumnNames();
    }
    
    /**
     *
     * @return jr_cr_nodeiterator
     * A {@link NodeIterator} object
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_QueryResult::getNodes()
     */
    public function getNodes() {
        return new jr_cr_nodeiterator($this->JRqueryresult->getNodes(),$this->session);
        //TODO - Insert your code here
    }
    
    /**
     *
     * @return object
     * A {@link RowIterator} object
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_QueryResult::getRows()
     */
    public function getRows() {
        return new jr_cr_rowiterator($this->JRqueryresult->getRows(),$this->session);
        //TODO - Insert your code here
    }
    
    /**
     * Returns true if the iteration has more elements.
     *
     * This is an alias of valid().
     *
     * @return boolean
     */
    public function hasNext() {
        //TODO: Insert Code
    }
    
    /**
     * Removes from the underlying collection the last element returned by the iterator.
     * This method can be called only once per call to next. The behavior of an iterator
     * is unspecified if the underlying collection is modified while the iteration is in
     * progress in any way other than by calling this method.
     *
     * @return void
     * @throws IllegalStateException if the next method has not yet been called, or the remove method has already been called after the last call to the next method.
     */
    public function remove() {
        //TODO: Insert Code
    }
    
    /**
     * Append a new element to the iteration
     *
     * @param mixed $element
     * @return void
     */
    public function append($element) {
        //TODO: Insert Code
    }
}
