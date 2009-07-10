<?php


class jr_cr_row implements PHPCR_RowInterface {


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
A {@link Value} object
     * @throws {@link ItemNotFoundException}
If <i>$propertyName</i> s not among the column names of the
query result table.
     * @throws {@link RepositoryException}
If another error occurs
     * @see PHPCR_Row::getValue()
     */
    public function getValue($propertyName) {
        return new jr_cr_value($this->JRrow->getValue($propertyName));
    }

    /**
     *
     * @return array
     * @throws {@link RepositoryException}
If an error occurs
     * @see PHPCR_Row::getValues()
     */
    public function getValues() {
        $ret = array();
        $jarr = $this->JRrow->getValues();
        foreach($jarr as $key => $jval) $ret[$key] = new jr_cr_value($jval);
        return $ret;
    }
}