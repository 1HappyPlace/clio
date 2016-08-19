<?php

namespace Clio;

use ANSI\Color\Color;

use ANSI\Color\ColorInterface;
use ANSI\Color\Mode;
use ANSI\Terminal;
use Clio\Style\Style;
use Clio\Style\StyleInterface;

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
     * Clio constructor.
     * @param Mode | int | string $mode - VT100, XTERM, RGB, either in constant form or a case independent string "xterm", "VT100", etc
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
     * @return $this
     */
    public function __construct($mode = Mode::XTERM, $defaultTextColor = null, $defaultFillColor = null)
    {
        // send the mode to the parent
        parent::__construct($mode);
        
        // if the default text color was specified
        if ($defaultTextColor) {

            // save the default text color
            $this->defaultTextColor = new Color($defaultTextColor);
            
            // tell the terminal about it
            $this->setTextColor($this->defaultTextColor);

        }

        // if the default fill color was specified
        if ($defaultFillColor) {

            // save the default fill color
            $this->defaultFillColor = new Color($defaultFillColor);

            // tell the terminal about it
            $this->setFillColor($this->defaultFillColor);
        }
        
        return $this;

    }





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
        
        // set the defaults (if they are defined)
        $this->setTextColor($this->defaultTextColor);
        $this->setFillColor($this->defaultFillColor);

        // if the escaping should happen right away, send out the default colors 
        if ($rightAway) {
            
            // output the current state
            $this->outputEscapeSequence();
        }

        // chaining
        return $this;
        
    }
    
    

    /////////////////////////////////////////////////////////////////////////////////////////
    //                                     Font styling                                    //
    /////////////////////////////////////////////////////////////////////////////////////////


    /**
     * Start or stop bolding
     * 
     * @param bool $on
     * @return $this
     */
    public function b($on = true) {

        // tell the terminal about it
        parent::setBold($on);
        
        return $this;
    }
    

    /**
     * Start or stop underscoring
     *
     * @param bool|true $on
     * @return $this
     */
    public function u($on = true) {

        parent::setUnderscore($on);

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
     * Start drawing text in a particular color
     * @param ColorInterface | string | integer | array | null $color - the text color
     *
     *      Color parameters can be:
     *          - Object adhering to the Color Interface
     *          - A color name "Antique White" or "antiquewhite"
     *          - Xterm integer from 0-255
     *          - [R,G,B]
     *          - null
     * 
     * @return $this
     */
    public function textColor($color = null) {
        
        // if the color is defined
        if ($color) {
            
            // tell the terminal about it
            $this->setTextColor($color);           
        
        } else {
            
            // tell the terminal about the default color
            $this->setTextColor($this->defaultTextColor);
        } 


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
        $this->setTextColor($this->defaultTextColor);

        // chaining
        return $this;
    }


    /**
     * Start drawing text with a fill (or background) of a particular color
     * @param ColorInterface | string | integer | array | null $color - the text color
     *
     *      Color parameters can be:
     *          - Object adhering to the Color Interface
     *          - A color name "Antique White" or "antiquewhite"
     *          - Xterm integer from 0-255
     *          - [R,G,B]
     *          - null
     * 
     * @return $this
     */
    public function fillColor($color = null) {

        // if the color is not null
        if ($color) {
            
            // tell the terminal about it
            $this->setFillColor($color);          
        
        } else {
            
            // tell the terminal about the default color
            $this->setFillColor($this->defaultFillColor);
        }


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
        $this->setFillColor($this->defaultFillColor);
        
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
        $this->textColor($textColor);
        $this->fillColor($fillColor);

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
     * Display the text using temporary styling.  This styling will be layered onto any styling
     * already in effect.  For example, if the temporaryStyle only sets Bold, then bolding will
     * happen and all other styles stay the same.
     * 
     * @param string $text
     * @param StyleInterface  $style - if defined, a temporary style to use for this text only
     * 
     * @return $this
     */
    public function style($text, StyleInterface $style) {

        // save off the current styling
        $currentState = $this->getState();
        
        /// now need to initialize a style with that current state
        $currentStyling = (new Style())->initialize($currentState);
        
        // make a copy of the current styling
        $copy = clone $currentStyling;
        
        // sandwich on the new styling (only styles set in the temporaryStyle will have an effect, any null 
        // will be ignored)
        $copy->overrideMembersTo($style);

        // set the new style to the current styling + the $temporary style laid on top
        $this->setStyle($copy);
        
        // Send out the text
        $this->display($text);
        
        // return the styling to the previous styling
        $this->setStyle($currentStyling);
        
        // chaining
        return $this;
    }


    /**
     * Send out text with the specified style and hit the carriage return
     *
     * @param string $text
     * @param StyleInterface $style
     * @return $this
     */
    public function styleLine($text, StyleInterface $style) {
        
        // send out the styled text
        $this->style($text, $style);
        
        // hit the carriage return
        parent::newLine(1);
        
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
        parent::display($text);
        
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
        parent::display($text);

        // hit the carriage return
        parent::newLine(1);
        
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