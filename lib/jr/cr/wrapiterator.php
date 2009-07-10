<?php

/**
 * abstract iterator class to implement php iterator behavior on top of a java.util.Iterator
 *
 * php next does not return the node. the operation is split into next() = advance position and current() = return the element at current position
 *
 * because the php iterator has rewind and java not, we need to cache elements.
 */

abstract class jr_cr_wrapiterator  implements iterator {
    protected $Jiterator = null;
    protected $jobjects = array();
    protected $pos = 0;

    /** Factory function to create a php element of the correct type
     *  @param jobject The java object
     *  @return the php object wrapping the java object
     */
    abstract protected function createElement($jobject);

    /** @param jiterator a java.util.Iterator instance */
    function __construct($jiterator) {
        $this->Jiterator = $jiterator;
        if ($this->Jiterator->hasNext()) {
            //fixme: should we preload first element or not?
            $this->jobject[0] = $this->createElement($this->Jiterator->next());
        }
    }

    public function current() {
        if (isset($this->jobject[$this->pos])) {
            return $this->jobject[$this->pos];
        } else {
            return null;
            //todo: throw exception if not valid?
        }
    }
    public function key() {
        if ($this->valid()) {
            return $this->pos;
        } else {
            //todo: what about first item, position 0?
            return 0;
        }
    }

    public function next() {
        if (isset($this->jobject[$this->pos+1])) {
            //there is a cached object
            $this->pos++;
        } elseif ($this->Jiterator->hasNext()) {
            $this->jobject[++$this->pos] = $this->createElement($this->Jiterator->next());
        } else {
            //increase beyond last item
            $this->pos++;
        }
    }

    public function rewind () {
        $this->pos = 0;
    }
    public function valid () {
        return isset($this->jobject[$this->pos]);
    }

}