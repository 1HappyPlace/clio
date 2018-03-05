<?php

namespace Clio\Layout;

use Clio\Clio;
use Clio\Styling\Style;


/**
 * This class is part of the Clio Open Source project, Clio.1happyplace.com
 * Copyright, Katie Ayres, katie@1happyplace.com
 *
 * Available through the MIT license
 * @license MIT
 *
 * This class displays text in paragraph form.
 *
 */
class Paragraph
{

    /**
     * The clio object that handles the output to the terminal
     * @var Clio|null
     */
    public $clio = null;

    /**
     * The style for the paragraph, if defined
     * @var Style | null
     */
    public $style = null;

    /**
     * The number of lines after the paragraph
     * @var int
     */
    protected $spaceAfter = 1;

    /**
     * Whether to trim the incoming text
     * @var bool
     */
    protected $trim = true;


    /**
     * ModernParagraph constructor.
     * @param Clio $clio -the Clio object, generally want just one instance to represent the current terminal
     * @param Style | null $style
     * @param int $spaceAfter
     * @param bool $trim - whether to trim text coming in
     */
    public function __construct($clio, $style = null, $spaceAfter = 1, $trim = true)
    {
        // save the clio object
        $this->clio = $clio;

        // save the style object
        $this->style = $style;

        // save the number of empty lines to show after the paragraph
        $this->spaceAfter = $spaceAfter;

        // save whether to trim the incoming text
        $this->trim = $trim;
        
    }

    /**
     * Helper function to display a line with styling (if it has been specified)
     */
    private function displayWithStyling($text) {

        // create an empty line for space after
        $emptyLine = str_pad(" ",$this->clio->getWidth());

        // if the style was defined
        if ($this->style) {

            // set the style
            $this->clio->style($this->style);
        }

        // display the text on multiple lines
        $this->clio->display($text);

        // if there is a style
        if ($this->style) {

            // clear the styling
            $this->clio->clear();
        }
    }


    /**
     * Display one paragraph, which will be word-wrapped according
     * to the left margin and width specified in the HTML object.
     *
     * Additionally, any markup will trigger styles. If the markup
     * isn't closed, it will continue across multiple paragraphs.
     * @param $text
     * @return $this
     */
    public function display($text) {

        // if trimming was desired
        if ($this->trim) {

            // trim any front or ending spaces
            $text = trim($text);
        }

        // if there is no text, no need to continue
        if (strlen($text) == 0) {

            // no need to continue
            return $this;
        }

        // wordwrap to the width of the html screen
        $text = $this->clio->wordwrap($text, $this->clio->getWidth(), false);

        // now get the lines that have been wordwrapped
        $lines = explode("\n",$text);

        // shortcut to the width
        $width = $this->clio->getWidth();

        // start the justified version of the wrapped text
        $justifiedText = "";

        // create an empty line for space after
        $emptyLine = str_pad(" ",$this->clio->getWidth());

        // shortcut
        $numLines = count($lines);

        // go through each line
        for ($i=0; $i<$numLines; ++$i) {

            // get the next line
            $next = $lines[$i];

            // ask Clio to justify the text and pad to the edge
            $justifiedText .= $this->clio->justify($next,"left",$width) . "\n";

            // if this is not the last line
//            if ($i < ($numLines-1)) {
//
//                // add a new line (the last one is left for space after
//                $justifiedText .= "\n";
//            }n


        }

        // now display the text
        $this->displayWithStyling($justifiedText);

        $this->nl($this->spaceAfter);
        
        // chaining
        return $this;
    }

    /**
     * Create a new line with the styling of the paragraph
     * @param int $count
     */
    public function nl($count = 1) {

        // create an empty line for space after
        $emptyLine = str_pad(" ",$this->clio->getWidth()) . "\n";

        // go through each line
        for ($i=0; $i<$count; ++$i) {

            // display an empty line, so the styling carries through to the right edge
            $this->displayWithStyling($emptyLine);

        }
    }
    
}