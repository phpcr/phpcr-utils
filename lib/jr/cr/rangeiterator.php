<?php

/**
 * abstract iterator class to implement PHPCR_RangeIterator behavior on top of a java.util.Iterator
 */
abstract class jr_cr_rangeiterator extends jr_cr_wrapiterator implements PHPCR_RangeIteratorInterface {

    /** @param jiterator a javax.jcr.RangeIterator instance */
    function __construct($jiterator) {
        parent::__construct($jiterator);
    }

    /**
     *
     * @return int
     * @see PHPCR_RangeIterator::getPosition()
     */
    public  function getPosition() {
        return $this->pos;
    }

    /**
     *
     * @return int
     * @see PHPCR_RangeIterator::getSize()
     */
    public  function getSize() {
        return $this->Jiterator->getSize();
    }

    /**
     *
     * @param int The non-negative number of elements to skip
     * @throws {@link NoSuchElementException} If skipped past the last element in the iterator.
     * @see PHPCR_RangeIterator::skip()
     */
    public function skip($skipNum) {
        if(isset($this->jobjects[$this->pos+$skipNum])) {
            $this->pos += $skipNum;
        } else {
            try {
                $this->Jiterator->skip($skipNum-1);
                $this->pos += $skipNum-1;
                $this->next(); //load the element we landed at
                if(!$this->valid()) throw new OutOfBoundsException('skipped beyond last element');
            } catch(JavaException $e) {
                $str = split("\n", $e->getMessage(), 1);
                $str = $str[0];
                if (strstr($str, 'NoSuchElementException')) {
                    throw new OutOfBoundsException($e->getMessage());
                } else {
                    throw $e;
                }
            }
        }
    }
}
