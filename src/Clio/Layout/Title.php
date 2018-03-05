<?php

namespace Clio\Layout;

use Clio\Clio;
use Clio\Styling\Markup\Justification;
use Clio\Styling\Style;


/**
 * This class is part of the Clio Open Source project, Clio.1happyplace.com
 * Copyright, Katie Ayres, katie@1happyplace.com
 *
 * Available through the MIT license
 * @license MIT
 *
 * The Title class is the base class for other title widgets.  Used alone
 * it creates a simple title.
 *
 */
class Title 
{
    /**
     * The clio object that handles the output to the terminal
     * @var Clio|null
     */
    protected $clio = null;

    /**
     * The justification of the title
     * @var int | string | null
     */
    protected $justification = null;

    /**
     * The style to use for the title
     * @var Style|null
     */
    protected $style = null;

    /**
     * The number of lines to place before
     * @var int
     */
    protected $spaceBefore = 2;

    /**
     * The number of lines to place after
     * @var int
     */
    protected $spaceAfter = 1;


    /**
     * Title constructor.
     * @param Clio $clio -the Clio object, generally want just one instance to represent the current terminal
     * @param int | string $justification - Justification constant
     * @param Style $style
     * @param int $spaceBefore
     * @param int $spaceAfter
     */
    public function __construct($clio, $justification = "left", $style, $spaceBefore = 2, $spaceAfter = 1)
    {
        // save the Clio object
        $this->clio = $clio;

        // set a default justification of left
        $this->justification = Justification::getJustificationConstant($justification);

        $this->style = $style;
        $this->spaceBefore = $spaceBefore;
        $this->spaceAfter = $spaceAfter;
        
    }
    

    /**
     * Prepare the text, if the text has no length, return false to indicate something went wrong
     * @param $text
     * @return bool
     */
    protected function prepareText(&$text) {

        // check if it is a string
        if (!is_string($text)) {

            // it is not, raise a flag
            return false;
        }

        // trim any front or ending spaces
        $text = trim($text);

        // if there is no text, no need to continue
        if (strlen($text) == 0) {

            // there is not text, raise a flag
            return false;
        }

        return true;
    }

    /**
     * Display one Title
     *
     * @param string $text - the text of the title
     * @return $this
     */
    public function display($text) {

        // prepare the text for the particular style of text
        // if it returns false, the text is bad
        if (!$this->prepareText($text)) {

            // chaining
            return $this;
        }

        // justify the text
        $text = $this->clio->justify($text, $this->justification);

        // create the specified number of lines before
        if ($this->spaceBefore > 0) {

            //  move down the space before number of lines
            $this->clio->newLine($this->spaceBefore);
        }


        // display the title
        $this->clio->style($this->style)->display($text)->clear()->nl();

        // if there is space after
        if ($this->spaceAfter > 0) {

            // display the space after
            $this->clio->newLine($this->spaceAfter);
        }


        // chaining
        return $this;
    }

}