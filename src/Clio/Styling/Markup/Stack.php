<?php

namespace Clio\Styling\Markup;

use Clio\Styling\Style;
use Clio\Styling\StyleInterface;

/**
 * This class is part of the Clio Open Source project, Clio.1happyplace.com
 * Copyright, Katie Ayres, katie@1happyplace.com
 *
 * Available through the MIT license
 * @license MIT
 *
 * This class keeps track of the Markup stack, with each styling pancaking on the
 * other, so any undefined Style properties do not affect any earlier submitted
 * styles.  For example, if the first style submitted sets bold, then the next one
 * has null for bold, the bold will continue based on the first style.
 *
 */
class Stack
{


    /**
     * The array of styles
     * @var array
     */
    protected $stack = [];
    

    /**
     * Remove markup from the stack based on index
     * @param int $index
     */
    private function removeFromStack($index) {
        
        // delete the style off the stack
        unset($this->stack[$index]);

        // this resets the index
        $this->stack = array_values($this->stack);
    }



    /**
     * @param String $symbol
     * 
     * @return int | false
     */
    private function findOnStack($symbol) {

        // first need to check if this markup is already on the stack
        for ($i = 0; $i < count($this->stack); ++$i) {

            // get the next markup
            $nextMarkup = $this->stack[$i];

            // if the symbols are the same
            if ($symbol == $nextMarkup->getSymbol()) {
                
                // return the index
                return $i;

            };
        }
        
        // nothing matched
        return false;
    }

    /**
     * Add styling without markup symbols, return an ID
     * @param StyleInterface $style
     *
     * @return string
     */
    public function addStyling($style) {

        // generate a unique ID
        $id = uniqid();

        // take a picture of the style in case it gets changed later
        $styleClone = clone $style;

        // create the markup
        $markup = new Markup($id,$styleClone);

        // add it to the stack
        $this->addMarkup($markup);

        // return the unique ID to be used with removeStyling
        return $id;


    }

    /**
     * Remove styling using the ID previously supplied
     * @param $id
     */
    public function removeStyling($id) {

        // find the symbol on the stack
        $index = $this->findOnStack($id);

        // if it is on the stack
        if ($index !== false ) {

            // remove this markup from the stack
            $this->removeFromStack($index);

        };

    }


    /**
     * Clear out the stack, a clear was sent to the terminal
     */
    public function clear() {

        // clear out the stack, the default styling is still active
        $this->stack = [];

    }


    /**
     * Add a markup to the stack, if it is already on it, then remove it
     * @param Markup $markup
     */
    public function addMarkup($markup) {

        // if this is markup
        if ($markup instanceof Markup) {

            // find the symbol on the stack
            $index = $this->findOnStack($markup->getSymbol());
            
            // if it is on the stack
            if ($index !== false ) {
                
                // remove this markup from the stack
                $this->removeFromStack($index);
            
            // it is not on the stack
            } else {
                
                // add it to the stack
                $this->stack[] = $markup;
            }

        }
        
    }


    /**
     * Pancake down the stack of styles and return
     * a new styling that represents each property
     * and the topmost definition (no matter where it is
     * on the stack)
     *
     * @param Style $baseStyle
     * @return Style
     */
    public function getCurrentStyling($baseStyle) {

        if (count($this->stack) > 0) {
            // start a new style
            $style = clone $baseStyle;

            // go through each of the styles, starting with the oldest
            foreach ($this->stack AS $nextMarkup) {

                // and override any effect they may have, allowing the styles to be pancaked together
                // <red>This is <b>bold<b> red<red>, bold will be red and bold
                $style->overrideMembersTo($nextMarkup->getStyle());
            }

            // return the combined style
            return $style;

        // there is no styling
        } else {

            // there is no styling
            return null;
        }
        


    }


}