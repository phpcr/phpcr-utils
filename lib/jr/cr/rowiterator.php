<?php


class jr_cr_rowiterator extends jr_cr_rangeiterator implements PHPCR_RowIteratorInterface {

    protected $session = null;

    function __construct($jrrowiterator,$session) {
        parent::__construct($jrrowiterator);
        $this->session = $session;
    }

    protected function createElement($r) {
        return new jr_cr_row($r);
    }

    /**
     *
     * @return object A {@link Row} object
     * @throws {@link NoSuchElementException} If iteration has no more {@link Row}s.
     * @see PHPCR_RowIterator::nextRow()
     */
    public function nextRow() {
        $this->next();
        if ($this->valid()) {
            return $this->current();
        } else {
            throw new PHPCR_NoSuchElementException('nextRow called after end of iterator');
        }
    }
}

