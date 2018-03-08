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
     * The overall height of the title in number of lines, even lines will place the empty line below
     * @var int
     */
    protected $height = 1;


    /**
     * Title constructor.
     * @param Clio $clio -the Clio object, generally want just one instance to represent the current terminal
     * @param int | string $justification - Justification constant
     * @param Style $style
     * @param int $spaceBefore
     * @param int $spaceAfter
     * @param int $height
     */
    public function __construct($clio, $justification = "left", $style, $spaceBefore = 2, $spaceAfter = 1, $height = 1)
    {
        // save the Clio object
        $this->clio = $clio;

        // set a default justification of left
        $this->justification = Justification::getJustificationConstant($justification);

        $this->style = $style;
        $this->spaceBefore = $spaceBefore;
        $this->spaceAfter = $spaceAfter;
        $this->height = $height;
        
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

        // calculate any empty lines needed to create the height
        $top = $bottom = 0;

        // if there was anything higher than one requiring empty styled lines
        if ($this->height > 1) {

            // take off the actual title line
            $spaces = $this->height - 1;

            // the top will be the base value of dividing by two
            $top = intdiv($spaces,2);

            // the bottom will be that base value + the remainder
            $bottom = intdiv($spaces,2) + ($spaces % 2);
        }

        // set up the styling
        $this->clio->style($this->style);

        // display any empty lines needed above the title for the height
        $this->displayEmptyLines($top);

        // display the title text
        $this->clio->line($text);

        // display any empty lines needed below the title for the height
        $this->displayEmptyLines($bottom);

        // clear the formatting
        $this->clio->clear();

        // if there is space after
        if ($this->spaceAfter > 0) {

            // display the space after
            $this->clio->newLine($this->spaceAfter);
        }


        // chaining
        return $this;
    }

    /**
     * Helper function to output a number of lines that have the full width
     * @param $lines
     */
    private function displayEmptyLines($lines)
    {
        // create an empty line
        $emptyLine = str_pad(" ", $this->clio->getWidth());

        // if there is more than one line
        for ($i = 0; $i < $lines; ++$i) {

            // generate an empty line
            $this->clio->line($emptyLine);
        }

    }

}