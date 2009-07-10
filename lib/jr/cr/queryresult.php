<?php


class jr_cr_queryresult implements PHPCR_QueryResultInterface {

    /**
     *
     */

    protected $JRqueryresult = null;

    function __construct($jrqueryresult,$session) {
        $this->session = $session;
        $this->JRqueryresult = $jrqueryresult;

    //TODO - Insert your code here
    }

    /**
     *
     * @return array
     * @throws {@link RepositoryException}
If an error occurs.
     * @see PHPCR_QueryResult::getColumnNames()
     */
    public function getColumnNames() {

            return $this->JRqueryresult->getColumnNames();
    }

    /**
     *
     * @return jr_cr_nodeiterator
A {@link NodeIterator} object
     * @throws {@link RepositoryException}
If an error occurs.
     * @see PHPCR_QueryResult::getNodes()
     */
    public function getNodes() {
            return new jr_cr_nodeiterator($this->JRqueryresult->getNodes(),$this->session);
    //TODO - Insert your code here
    }

    /**
     *
     * @return object
A {@link RowIterator} object
     * @throws {@link RepositoryException}
If an error occurs.
     * @see PHPCR_QueryResult::getRows()
     */
    public function getRows() {
return new jr_cr_rowiterator($this->JRqueryresult->getRows(),$this->session);
    //TODO - Insert your code here
    }
}

?>