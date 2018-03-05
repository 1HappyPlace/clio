<?php

namespace Clio\Widgets\Lists;


class OrderedList extends BulletedList
{

    /**
     * Keeps track of bullet numbers between calls to li
     * @var array - holds one index for each nesting level to keep separate counts
     */
    protected $orderedNumbers = [];

    /**
     * This returns the correct number for a list item depending on the current nesting level
     * @return string
     */
    protected function getNextOrderedNumber() {

        // see if the current nesting has an entry in the orderedNumbers array
        if (array_key_exists($this->nesting, $this->orderedNumbers)) {

            // it does, so increase the number by 1
            $this->orderedNumbers[$this->nesting] = $this->orderedNumbers[$this->nesting] + 1;

        } else {

            // it doesn't, so start a new index and initialize the number at 1
            $this->orderedNumbers[$this->nesting] = 1;
        }

        // if this is the second level nesting...
        if ($this->nesting === 2) {

            // return a character starting with a
            return chr(96 + $this->orderedNumbers[$this->nesting]);

            // if this is the fourth level nesting
        } else if ($this->nesting === 4) {

            // return a character starting with A
            return chr(64 + $this->orderedNumbers[$this->nesting]);

            // otherwise use numbers for nesting level 1,3,5 and greater
        } else {

            // return the current number for this nesting
            return $this->orderedNumbers[$this->nesting];
        }

    }




    /**
     * Display a list item
     * @param $text - the item
     * @return $this
     */
    public function li($text) {
        
        $this->drawListItem($this->getNextOrderedNumber() . ". ", $text);
        
        // chaining
        return $this;
    }

    /**
     * End the current ordered or unordered list
     */
    public function end() {

        // remove this ordered number tracking for this nesting
        if (array_key_exists($this->nesting, $this->orderedNumbers)) {

            // remove the item off the array
            unset ($this->orderedNumbers[$this->nesting]);
        }

        // close the start
        parent::end();
        
        // chaining
        return $this;
    }

}