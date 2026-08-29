<?php
// @codingStandardsIgnoreFile

class AWeberEntryDataArray implements ArrayAccess, Countable, Iterator  {
    private $counter = 0;

    protected $data;
    protected $keys;
    protected $name;
    protected $parent;

    public function __construct($data, $name, $parent) {
        $this->data = $data;
        $this->keys = array_keys($data);
        $this->name = $name;
        $this->parent = $parent;
    }

    //260816 Suppress PHP 8.1+ tentative Countable return-type deprecation while preserving PHP 5.6 compatibility.
    #[\ReturnTypeWillChange]
    public function count() {
        return sizeOf($this->data);
    }

    //260816 Suppress PHP 8.1+ tentative ArrayAccess return-type deprecations while preserving PHP 5.6 compatibility.
    #[\ReturnTypeWillChange]
    public function offsetExists($offset) {
        return (isset($this->data[$offset]));
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset) {
        return $this->data[$offset];
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value) {
        $this->data[$offset] = $value;
        $this->parent->{$this->name} = $this->data;
        return $value;
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }

    //260816 Suppress PHP 8.1+ tentative Iterator return-type deprecations while preserving PHP 5.6 compatibility.
    #[\ReturnTypeWillChange]
    public function rewind() {
        $this->counter = 0;
    }

    #[\ReturnTypeWillChange]
    public function current() {
        return $this->data[$this->key()];
    }

    #[\ReturnTypeWillChange]
    public function key() {
        return $this->keys[$this->counter];
    }

    #[\ReturnTypeWillChange]
    public function next() {
        $this->counter++;
    }

    #[\ReturnTypeWillChange]
    public function valid() {
        if ($this->counter >= sizeOf($this->data)) {
            return false;
        }
        return true;
    }


}
