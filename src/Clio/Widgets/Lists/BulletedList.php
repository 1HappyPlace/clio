<?php

namespace Clio\Widgets\Lists;

use Clio\Clio;


/**
 * This class is part of the Clio Open Source project, Clio.1happyplace.com
 * Copyright, Katie Ayres, katie@1happyplace.com
 *
 * Available through the MIT license
 * @license MIT
 *
 * The BulletedList class generates a list
 *      - such as this one, which is unordered
 *      - It can be ordered
 *          1. Like this
 *          2. As well as nested
 *
 * The bullet can also be defined to any string.  The end() method must be called if nesting is used to indicate
 * the end of a level.
 * 
 */
abstract class BulletedList
{
    
    /**
     * The number of blank spaces to indent
     * @var int
     */
    protected $indentation = 3;
    
    /**
     * Used for ordered and unordered lists, keeping track of current nesting level
     * 0 is used to indicate that no ul or ol is in progress
     * @var int
     */
    protected $nesting = 0;

    /**
     * The clio object that handles the output to the terminal
     * @var Clio|null
     */
    protected $clio = null;
    


    /**
     * BulletedList constructor.
     * @param Clio $clio -the Clio object, generally want just one instance to represent the current terminal
     */
    public function __construct($clio)
    {
        // save the clio html object
        $this->clio = $clio;
    }


    /**
     * Based on the current nesting, return a string to move text to the right
     * @return string
     */
    protected function createIndentation() {

        // determine the indentation based on the current level of nesting
        $indentation = $this->indentation * ($this->nesting);

        // return a blank string of that length
        return str_pad("",$indentation);
    }

    /**
     * Helper function to draw one list item
     *
     * @param string $bullet - the bullet plus any white space to the right
     * @param string $text
     */
    protected function drawListItem($bullet, $text) {

        // determine the blank space indentation
        $indentation = $this->createIndentation();

        // In unordered list, the bullet can be specified
        $left = $indentation . $bullet;

        // determine the width of the bullet text
        $bulletTextArea = $this->clio->getWidth() - strlen($left);
        
        // word wrap the rest of the bullet
        $text = $this->clio->wordwrap($text,$bulletTextArea, false);

        // create an empty string that is the same with of the bullet
        $emptyBullet = str_pad("",strlen($bullet));

        // replace all newlines with a newline then indentation, then an empty bullet
        $text = str_replace("\n", "\n" . $indentation . $emptyBullet, $text);

        // show the bullet and text on multiple lines (if needed)
        $this->clio->display($left . $text)->newLine();

    }

    /////////////////////////////////////////////////////////////////////////////////////////
    //                                 List Generation                                     //
    /////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Start an ordered list
     * @param $text String | null - text to show on the very first line, will not work for anything nested
     * @return $this
     */
    public function start($text = null) {


        // if text was defined and it the first level of nesting
        if ($text && ($this->nesting === 0)) {

            // display the text with any markup
            $this->clio->display($text)->newLine();

        }

        // add one to the nesting
        ++$this->nesting;

        // chaining
        return $this;
    }

    /**
     * Generate a list item in the list
     * 
     * @param string $text
     */
    abstract public function li($text);
    
    /**
     * End the current ordered or unordered list
     */
    public function end() {
        
        // subtract one from the nesting level
        --$this->nesting;

        // chaining
        return $this;
    }









}