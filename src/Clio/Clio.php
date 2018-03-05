<?php

namespace Clio;

use ANSI\Color\Color;

use ANSI\Color\ColorInterface;
use ANSI\Color\Mode;
use ANSI\EscapeSequenceGenerator;
use ANSI\Terminal;
use Clio\Styling\Markup\Definition;
use Clio\Styling\Markup\Markup;
use Clio\Styling\Markup\NewLine;
use Clio\Styling\Markup\Stack;
use Clio\Menus\Menu;
use Clio\Styling\Style;
use Clio\Styling\StyleInterface;

/**
 * This class is part of the Clio Open Source project, Clio.1happyplace.com
 * Copyright, Katie Ayres, katie@1happyplace.com
 *
 * Available through the MIT license
 * @license MIT
 *
 * Class Clio generates and manages ANSI escape sequences for styling output to the command line interface, there
 * should be one instance to represent the terminal emulator and its state for clarity and simplicity.
 *
 *
 */
class Clio extends Terminal
{
    
    /**
     * The default text color of the terminal
     * @var ColorInterface | null
     */
    private $defaultTextColor = null;

    /**
     * The default fill color of the terminal
     * @var ColorInterface | null
     */
    private $defaultFillColor = null;
    /** @noinspection PhpDocSignatureInspection */

    /**
     * The desired width, the default is 80, which is a typical screen size for less newer models
     * @var int
     */
    protected $width = 80;

    /**
     * The list of markup definition, symbols and their corresponding style
     * @var Definition
     */
    protected $markupDefinition;

    /**
     * The stack of current styles based on the markup that has been found in the text
     * @var Stack
     */
    protected $stack;

    /**
     * Generates escape sequences based on the mode
     * @var EscapeSequenceGenerator
     */
    protected $generator;

    /**
     * The base style from direct styling with setBold, setUnderscore, setTextColor and setFillColor
     * @var Style
     */
    protected $baseStyle;


    /**
     * Clio constructor.
     * @param Mode|int|string $mode - VT100, XTERM, RGB, either in constant form or a case independent string "xterm", "VT100", etc
     * @param ColorInterface | string | integer | array | null $defaultTextColor - the go-to text color for the terminal
     * @param ColorInterface | string | integer | array | null $defaultFillColor - the go-to fill color for the terminal
     *
     *      Color parameters can be:
     *          - Object adhering to the Color Interface
     *          - A color name "Antique White" or "antiquewhite"
     *          - Xterm integer from 0-255
     *          - [R,G,B]
     *          - null
     *
     */
    public function __construct($mode = Mode::XTERM, $defaultTextColor = null, $defaultFillColor = null)
    {
        // send the mode to the parent
        parent::__construct($mode);

        // if the default text color was specified
        if ($defaultTextColor) {

            // save the default text color
            $this->defaultTextColor = new Color($defaultTextColor);

        }

        // if the default fill color was specified
        if ($defaultFillColor) {

            // save the default fill color
            $this->defaultFillColor = new Color($defaultFillColor);

        }

        // the base style that sits under the markup stack
        $this->baseStyle = new Style(false,false,$this->defaultTextColor, $this->defaultFillColor);

        // create a styling stack with the default text and fill
        $this->stack = new Stack();

        // set up the markup definition, by default, there is a ** for bold and __ for underscore
        $this->markupDefinition = new Definition();
        $this->markupDefinition->addMarkup("**",(new Style())->setBold());
        $this->markupDefinition->addMarkup("__",(new Style())->setUnderscore());

        // the escape sequence generator
        $this->generator = new EscapeSequenceGenerator($mode);

        // clear the screen and set the styling to the base styling
        $this->clearScreen();
        $this->setStyle($this->baseStyle);

        return $this;

    }


    /////////////////////////////////////////////////////////////////////////////////////////
    //                                       HTML                                          //
    /////////////////////////////////////////////////////////////////////////////////////////


    /**
     * OVERRIDE
     * 
     * Clear away all formatting - bold, underscore, text and fill color
     *
     * @param boolean $rightAway - whether to send out the escape sequence right away
     *          or allow the display to do it later
     * @return $this
     */
    public function clear($rightAway = false) {
        
        // tell the parent
        parent::clear($rightAway);

        // reset the base styling
        $this->baseStyle->setBold(false);
        $this->baseStyle->setUnderscore(false);
        $this->baseStyle->setTextColor($this->defaultTextColor);
        $this->baseStyle->setFillColor($this->defaultFillColor);

        // request the terminal go to the state
        $this->setState($this->baseStyle->getState());

        // if the escaping should happen right away, send out the default colors 
        if ($rightAway) {
            
            // output the current state
            $this->outputEscapeSequence();
        }

        // tell the stack that all markup has been cleared
        $this->stack->clear();

        // chaining
        return $this;
        
    }
    
    

    /////////////////////////////////////////////////////////////////////////////////////////
    //                                     Font styling                                    //
    /////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Set bolding on or off
     *
     * @param boolean $on
     *
     * @return $this;
     */
    public function setBold($on = true) {

        // keep track of this new bold state
        $this->baseStyle->setBold($on);

        // tell the terminal
        parent::setBold($on);

        return $this;
    }

    /**
     * Start or stop bolding
     * 
     * @param bool $on
     * @return $this
     */
    public function b($on = true) {

        // shortcut for this call
        $this->setBold($on);
        
        return $this;
    }

    /**
     * Set underscoring on or off
     *
     * @param boolean $on
     *
     * @return $this;
     */
    public function setUnderscore($on = true) {

        // save this off to the base style
        $this->baseStyle->setUnderscore($on);

        // tell the terminal base class
        parent::setUnderscore($on);

        // chaining
        return $this;
    }

    /**
     * Start or stop underscoring
     *
     * @param bool|true $on
     * @return $this
     */
    public function u($on = true) {

        // shortcut for this routine
        $this->setUnderscore($on);

        // chaining
        return $this;
    }


    /////////////////////////////////////////////////////////////////////////////////////////
    //                                       Colors                                        //
    /////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Get the default text color
     * 
     * @return Color|null
     */
    public function getDefaultTextColor()
    {
        return $this->defaultTextColor;
    }

    /**
     * Get the default fill color
     * 
     * @return Color|null
     */
    public function getDefaultFillColor()
    {
        return $this->defaultFillColor;
    }

    /**
     * Set the text color
     *
     * @param ColorInterface | string | integer | array | null $color
     *      Color parameters can be:
     *          - Object adhering to the Color Interface
     *          - A color name "Antique White" or "antiquewhite"
     *          - Xterm integer from 0-255
     *          - [R,G,B]
     *          - null
     *
     * @return $this
     */
    public function setTextColor($color) {

        // if the color is defined
        if ($color) {

            // set the color
            $this->baseStyle->setTextColor($color);

        } else {

            // tell the terminal about the default color
            $this->baseStyle->setTextColor($this->defaultTextColor);
        }


        // tell the terminal about it
        parent::setTextColor($this->baseStyle->getTextColor());

        // chaining
        return $this;

    }

    /**
     * Shortcut to the setTextColor
     * @param Color | null $color
     * @return $this
     */
    public function textColor($color = null) {
        
        $this->setTextColor($color);

        // chaining
        return $this;

    }



    /**
     * Clear out the text color and return to the default text color (if one is specified)
     *
     * @return $this
     */
    public function clearTextColor() {
        
        // tell the terminal about it
        $this->setTextColor(null);

        // chaining
        return $this;
    }


    /**
     * Set the fill color
     *
     * @param ColorInterface | string | integer | array | null $color
     *     Color parameters can be:
     *          - Object adhering to the Color Interface
     *          - A color name "Antique White" or "antiquewhite"
     *          - Xterm integer from 0-255
     *          - [R,G,B]
     *          - null
     *
     * @return $this
     */
    public function setFillColor($color) {

        // if the color is not null
        if ($color) {

            // tell the terminal about it
            $this->baseStyle->setFillColor($color);

        } else {

            // tell the terminal about the default color
            $this->baseStyle->setFillColor($this->defaultFillColor);
        }

        // tell the terminal about it
        parent::setFillColor($this->baseStyle->getFillColor());

        // chaining
        return $this;
    }

    /**
     * Shortcut to setFillColor
     * @param null | Color $color
     * @return $this
     */
    public function fillColor($color = null) {

        // shortcut to this routine
        $this->setFillColor($color);

        // chaining
        return $this;

    }



    /**
     * Clear out the fill color and return to the default fill color (if one is specified)
     *
     * @return $this
     */
    public function clearFillColor() {

        // tell the terminal about the default color (if there is one)
        $this->setFillColor(null);
        
        // chaining
        return $this;
    }


    /**
     * Change both the fill and text colors
     *
     * @param $textColor int|string|null $color - can be either a Color constant Color::Blue or a string with the same spelling "blue", "Red", "LIGHT CYAN", etc
     * @param $fillColor int|string|null $color - can be either a Color constant Color::Blue or a string with the same spelling "blue", "Red", "LIGHT CYAN", etc
     * @return $this
     */
    public function colors($textColor = null, $fillColor = null) {

        // simply call the setters for the text and fill
        $this->setTextColor($textColor);
        $this->setFillColor($fillColor);

        // chaining
        return $this;
    }



    /////////////////////////////////////////////////////////////////////////////////////////
    //                                     styling                                         //
    /////////////////////////////////////////////////////////////////////////////////////////



    /**
     * Set the styling of underscore, bold, text and fill
     *
     * @param StyleInterface $style
     * @return $this
     */
    public function setStyle(StyleInterface $style) {

        // change the state of the terminal
        $this->setBold($style->isBoldOn());
        $this->setUnderscore($style->isUnderscoreOn());
        $this->setTextColor($style->getTextColor());
        $this->setFillColor($style->getFillColor());


        // chaining
        return $this;
    }


    /**
     * Shortcut for setStyle
     *
     * @param StyleInterface  $style - if defined, a temporary style to use for this text only
     * 
     * @return $this
     */
    public function style(StyleInterface $style) {
        
        $this->setStyle($style);
        
        // chaining
        return $this;
    }


    /////////////////////////////////////////////////////////////////////////////////////////
    //                                    text output                                      //
    /////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Shortcut for the Terminal method display($text)
     *
     * @param string $text
     * @return $this
     */
    public function out($text) {

        // ask the parent to display music
        $this->display($text);
        
        // chaining 
        return $this;
    }

    /**
     * Output text and hit a carriage return
     *
     * @param string $text
     * @return $this
     */
    public function line($text) {

        // ask the parent to display music
        $this->display($text);

        // hit the carriage return
        $this->newLine(1);
        
        // chaining
        return $this;
    }

    /**
     * Shortcut for the newline method
     * 
     * @param int $count - the number of newlines to output
     * @return $this
     */
    public function nl($count = 1) {

        // echo one "\n"
        $this->newLine($count);

        // chaining
        return $this;
    }


    /**
     * Shortcut function to pause execution until the user hits return
     */
    public function pause() {

        // prompt for carriage return
        $this->prompt("Hit Return to Continue");
        
        // once the user has hit return (ignore any other characters typed), return this for chaining
        return $this;
    }

    /**
     * Take a string and break it up into an array of
     * newline objects and segments
     *
     * @param string $text
     *
     * @return array
     */
    protected function breakupNewLines($text) {

        // start with an offset in the string
        $offset = 0;

        // build the array to return
        $returnedArray = [];

        // while there are still newlines in the text
        while (($pos = strpos($text,"\n", $offset)) !== false) {

            // if the \n is not at the very beginning
            if ($pos > 0) {

                // add the segment of text before the \n
                $returnedArray[] = substr($text,$offset, $pos - $offset);
            }

            // add a newline object
            $returnedArray[] = new newLine();

            // move the offset past the \n
            $offset = $pos + 1;
        }

        // if there are leftovers
        if (substr($text, $offset) !== false) {

            // add them to the array
            $returnedArray[] = substr($text, $offset);
        }

        // return the list of text and newline objects
        return $returnedArray;

    }

    /**
     * Find all the markup in the text and build an array of objects
     * representing a markup text stream
     *
     * @param string $text
     *
     * @return array
     *
     */
    protected function processText($text) {

        // initialize the returned array
        $stream = [];

        // get the next markup in the text
        while (!is_null($index = $this->markupDefinition->findNextMarkup($text, $markupFound))) {

            // if there is a string before the markup
            if ($index > 0) {

                // get the next segment
                $segment = substr($text, 0, $index);

                // break up any text into newline chunks
                $segments = $this->breakupNewLines($segment);

                // push the array of text segments broken by \n onto the stream
                $stream = array_merge($stream, $segments);

            }

            // add the styling
            $stream[] = $this->markupDefinition->getMarkup($markupFound);

            // finally shorten the string
            $text = substr($text, $index + strlen($markupFound));
        }

        // if there is any leftover text
        if (strlen($text)) {

            // break up the new lines
            $segments = $this->breakupNewLines($text);

            // append it to the stream
            $stream = array_merge($stream, $segments);
        }

        return $stream;

    }


    /**
     * Generate the escape coding for the current styling
     */
    protected function outputCurrentStyling() {

        // get the pancaked styling from the stack of markups
        $style = $this->stack->getCurrentStyling($this->baseStyle);

        // if there is stacked styling, use it
        if ($style) {

            // ask the terminal to set to that styling state
            $this->setState($style->getState());

            // send out that styling
            $this->outputEscapeSequence();

        // otherwise, use the base style
        } else {

            $this->setState($this->baseStyle->getState());
            $this->outputEscapeSequence();
        }


    }



    /**
     * Displays the empty spaces that create a left margin
     */

    /////////////////////////////////////////////////////////////////////////////////////////
    //                                       Public                                        //
    /////////////////////////////////////////////////////////////////////////////////////////


    /////////////////////////////////////////////////////////////////////////////////////////
    //                                 Markup and Styling                                  //
    /////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Add a markup symbol and the style associated with it
     *
     * @param string $symbol - the markup symbol in the text
     * @param StyleInterface $style - the style to kick in when it is found
     *
     * @return $this
     */
    public function addMarkupDefinition($symbol, $style) {

        // store it in the markup definition of all symbols-styles
        $this->markupDefinition->addMarkup($symbol, $style);

        // chaining
        return $this;
    }

    /**
     * Getter for the markupDefinition
     */
    public function getMarkupDefinition() {

        // return the markup definition
        return $this->markupDefinition;
    }




    /////////////////////////////////////////////////////////////////////////////////////////
    //                                        Width                                        //
    /////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Setter for the width
     * @param int $width
     */
    public function setWidth($width) {

        // set the width
        $this->width = (int)$width;
    }

    /**
     * Getter for the width
     * @return int
     */
    public function getWidth() {

        // return the stored width
        return $this->width;
    }


    /////////////////////////////////////////////////////////////////////////////////////////
    //                                  Text Display                                       //
    /////////////////////////////////////////////////////////////////////////////////////////

    /**
     * This is isolated mainly for testing (since prompting needs to be mocked)
     * @param string $text
     * @return string - the answer
     */
    public function justPrompt($text) {

        // ask the terminal to prompt and return the answer
        $answer = parent::prompt($text);

        // go down to the next line (which also draw the left margin)
        $this->newLine();

        return $answer;
    }

    /**
     * Prompt for a value.
     *
     * @param string $text - the prompt string
     * @param string | null $default - the default value
     *
     * @return string - the answer
     */
    public function prompt($text, $default = null) {

        // if a default is specified, append it to the prompt text
        if ($default) {
            $text .= " [" . $default . "] ";
        }

        // prompt for the answer
        $answer = $this->justPrompt($text);

        // trim away any leading or trailing blanks
        $answer = trim($answer);

        // if a default is specified
        if ($default) {

            // if it is a carriage return or the default, return it
            if (($answer === "") || ($answer === $default)) {

                // return the default answer
                return $default;
            }

        }

        // otherwise return the answer
        return $answer;


    }


    /**
     * Prompt for y/n, and return true if y, ye, or yes is answered
     *
     * @param string $text
     * @return bool
     */
    public function promptForYes($text) {

        // get the yes/no answer
        $answer = $this->justPrompt($text . " (y/n)");

        // set the answer to all lowercase
        $answer = strtolower($answer);

        // check to see if the answer is y, ye, yes
        if ($answer === "y" || $answer === "ye" || $answer === "yes") {

            // user answered yes
            return true;

            // anything else is not a yes
        } else {

            // user did not answer yes
            return false;
        }

    }

    /**
     * Prompt with a given set of choices, with highlighting showing the characters needed
     *
     * @param string $intro - the beginning of the prompt
     * @param string[] $choices - the available choices
     * @param string $default - the default
     * @return string - return the answer
     */
    public function promptSelect($intro, $choices, $default = null) {

        // setup the styles to look much like the other prompts
        $titleStyle = (new Style())->setColors("black","white");
        $highlightStyle = (new Style())->setColors("black","lightgray");
        $choiceStyle = (new Style())->setColors("black","white");
        $menu = new Menu($this,$titleStyle,$highlightStyle,$choiceStyle,$intro);

        // ask the question
        return $menu->menu($choices, $default);


    }

    /**
     * Strip out the currently defined markup symbols from the text
     *
     * @param string $text
     * @return string
     */
    public function stripMarkupSymbols($text) {

        // ask the markup definition object to strip out the markup symbols
        return $this->markupDefinition->stripMarkupSymbols($text);
    }

    /**
     * Justify text
     *
     * @param string $text - the text to justify
     * @param string | int $justification - if a string, it can be "left", "RIGHT", etc..., if an int, just needs to be a Justification constant
     * @param int $width - the width to fill, if zero it will fill whatever width of the overall HTML page
     * @return string
     */
    public function justify($text, $justification, $width = 0) {

        // if the width is zero
        if ($width === 0) {

            // set it to the width of the HTML screen
            $width = $this->getWidth();
        }

        // ask the markup definition object to do the justification so all
        // the markup text does not affect the justification
        return $this->markupDefinition->justify($text, $justification, $width);
    }

    /**
     * Wordwrap text
     *
     * @param string $text - the text to wordwrap
     * @param int $width - the width to do the wrapping
     * @param boolean $appendLastNewLine - whether to attach a newline to the end of the
     *                                     of the last character
     *
     * @return string - the text with \n's within for the wordwrapping as well as extra spaces on
     *                  subsequent lines if deeper margin was set
     */
    public function wordwrap($text, $width = 0, $appendLastNewLine = true) {

        // if the width is not specified
        if ($width === 0) {

            // use the overall width
            $width = $this->getWidth();
        }

        // ask the markup definition to wordwrap ignoring any markup text
        $wordWrappedText =  $this->markupDefinition->wordwrap($text, $width);

        // if a new line is desired at the very end
        if ($appendLastNewLine) {

            // append it
            $wordWrappedText .= "\n";
        }

        // finally return the newly organized text
        return $wordWrappedText;

    }

    /**
     * Display text, display text in styles triggered by markup or the temporary style
     *
     * @param string $text - with markup and \n's
     *
     * @return $this
     */
    public function display($text) {

        // send out any escaping to implement anything sitting in the desired state
        $this->outputEscapeSequence();

        // create a stream
        $stream = $this->processText($text);

        // go through each object in the stream
        foreach ($stream AS $next) {

            // if the next object is just text
            if (is_string($next)) {

                // output the new styling
                $this->outputCurrentStyling();

                // display the text
                parent::display($next);

                // the next is a markup definition
            } else if ($next instanceOf Markup) {

                // simply add the markup
                $this->stack->addMarkup($next);

                // output the new styling
                $this->outputCurrentStyling();

                // if it is a new line
            } else if ($next instanceOf NewLine) {

                // send out a \n
                $this->newLine(1);
            }
        }

        // chaining
        return $this;

    }






    /////////////////////////////////////////////////////////////////////////////////////////
    //                                       Overrides                                     //
    /////////////////////////////////////////////////////////////////////////////////////////

    /**
     * ABSTRACT
     * All output goes to this function
     *
     * @param string $text
     */
    public function output($text)
    {
        echo $text;
    }

    /**
     * ABSTRACT
     * Anytime the cursor is returned to the leftmost column, this is fired
     */
    public function carriageReturn()
    {
        // nothing to be done
    }

}