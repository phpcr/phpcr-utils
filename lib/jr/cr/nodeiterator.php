<?php

class jr_cr_nodeiterator extends jr_cr_rangeiterator implements PHPCR_NodeIteratorInterface {
    protected $session = null;
    
    function __construct($jrnodeiterator,$session) {
        parent::__construct($jrnodeiterator);
        $this->session = $session;
    }
    
    protected function createElement($n) {
        return new jr_cr_node($this->session,$n);
    }
    
    /**
     * @return object
     * @throws {@link NoSuchElementException} If iteration has no more {@link Node}s.
     * @see PHPCR_NodeIterator::nextNode()
     */
    public function nextNode() {
        $this->next();
        if ($this->valid()) {
            return $this->current();
        } else {
            throw new OutOfBoundsException('nextNode called after end of iterator');
        }
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
