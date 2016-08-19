<?php

namespace Clio\Style;

use ANSI\Color\Color;
use ANSI\Color\ColorInterface;
use ANSI\TerminalStateInterface;


/**
 * This class is part of the Clio Open Source project, Clio.1happyplace.com
 * Copyright, Katie Ayres, katie@1happyplace.com
 *
 * Available through the MIT license
 * @license MIT
 *
 * Class Style stores the styling including:
 *      Bold            - whether or not to bold text
 *      Underscore      - whether or not to underscore text
 *      Text Color      - the text color (@see Color)
 *      Fill Color      - the fill color (@see Color)
 *
 * Bold and underscore have three states:
 *      null - it will have no effect
 *      false - it is specifically turned off
 *      true - it is specifically turned on
 *
 * Styles can then pancake on one another, so if you override a styling, but
 * a property is set to null, it won't change the current styling, if it is
 * set to false, then it will be specifically turned off.  This is true of
 * colors as well, if they are set to null.
 *
 */
class Style implements StyleInterface
{

    /**
     * Current state of bold (on or off)
     * var bool | null - if null, it means to have no effect on the current state (particularly in an override)
     */
    protected $bold = null;

    /**
     * Current state of underscoring (on or off)
     * @var bool | null - if null, it means to have no effect on the current state (particularly in an override)
     */
    protected $underscore = null;

    /**
     * Store the current text color
     * @var Color | null
     */
    protected $textColor = null;

    /**
     * Store the current fill color
     * @var Color |  null
     */
    protected $fillColor = null;


    /**
     * Style constructor.
     * @param null $bold
     * @param null $underscore
     * @param ColorInterface | null $textColor
     * @param ColorInterface | null $fillColor
     *
     */
    public function __construct($bold = null, $underscore = null, ColorInterface $textColor = null, ColorInterface $fillColor = null)
    {
        // set all the properties
        $this->bold = $bold;
        $this->underscore = $underscore;
        $this->textColor = $textColor;
        $this->fillColor = $fillColor;

        // chaining
        return $this;
    }


    /**
     * Initialize a style to the state of a terminal
     * 
     * @param TerminalStateInterface $state
     * @return $this
     */
    public function initialize(TerminalStateInterface $state) {
        
        // both bold and underscore will have a value of true or false (not undefined
        $this->setBold($state->isBold());
        $this->setUnderscore($state->isUnderscore());
        
        // for colors, if the color is not valid, then it will stay undefined in this context
        
        // get the text color
        $textColor = $state->getTextColor();
        
        // if the text color is valid
        if ($textColor->isValid()) {
            
            // set it 
            $this->setTextColor($textColor);
        
        } else {
            
            // the text color is not valid, null out the text color
            $this->setTextColor(null);
        }
        
        // get the fill color
        $fillColor = $state->getFillColor();
        
        // if the fill color is valid
        if ($fillColor->isValid()) {
            
            // set it
            $this->setFillColor($fillColor);
            
        } else {

            // the fill color is not valid, null out the fill color
            $this->setFillColor(null);
        }
        
        // chaining
        return $this;
        
        
    }

    /**
     * Override a style, if anything is set to null, then don't change the value of the recipient object
     *
     * @param StyleInterface $style
     * @return $this
     * 
     */
    public function overrideMembersTo(StyleInterface $style) {

        // text color
        $styleTextColor = $style->getTextColor();
        
        // only change if there is a text color set
        if ($styleTextColor) {
            
            // clone the text color
            $this->textColor = clone $styleTextColor;
        }

        // fill color
        $styleFillColor = $style->getFillColor();
        
        // only change if there is a fill color set
        if ($styleFillColor) {
            
            // clone the fill color
            $this->fillColor = clone $styleFillColor;
        }


        // font styling
        
        // get the overriding bold
        $bold = $style->getBold();
        
        // only change the bold, if it is set to something
        if (!is_null($bold)) {
            
            // save the new bold state
            self::setBold($bold);           
        
        }


        // get the overriding underscore
        $underscore = $style->getUnderscore();

        // only change the underscore, if it is set to something
        if (!is_null($underscore)) {
            
            // save the new underscore state
            self::setUnderscore($underscore);           
        }


        // chaining
        return $this;
    }

    
    /**
     * Clear out all formatting
     * 
     * @return $this
     */
    public function clearStyling() {

        // reset the colors to null
        $this->textColor = $this->fillColor = null;

        // reset the underscore and bold to false
        $this->underscore = $this->bold = null;

        // chaining
        return $this;
    }

    /////////////////////////////////////////////////////////////////////////////////////////
    //                                     font styling                                    //
    /////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Setter for bold
     *
     * @param boolean $on
     * @return $this
     */
    public function setBold($on = true) {

        // save it
        $this->bold = $on;

        // chaining
        return $this;
    }

    /**
     * Clear any bold styling, it will have no effect, nor override
     */
    public function clearBoldStyling() {
        
        // clear out the bold
        $this->bold = null;
        
        // chaining 
        return $this;
    }

    /**
     * Getter for bold
     *
     * @return boolean - whether bold is turned on or off
     */
    public function getBold() {

        // return the value
        return $this->bold;
    }

    /**
     * Whether bolding is on
     *
     * @return boolean
     */
    public function isBoldOn() {

        // return whether bolding is currently on
        return $this->bold === true;

    }

    /**
     * Set the underscore setting
     *
     * @param boolean $on
     * @return $this
     */
    public function setUnderscore($on = true) {

        // save it
        $this->underscore = $on;

        // chaining
        return $this;
    }

    /**
     * Clear any underscore styling, it will have no effect, nor override
     */
    public function clearUnderscoreStyling() {

        // clear out the bold
        $this->underscore = null;
        
        // chaining
        return $this;
    }

    /**
     * Getter for the underscore setting
     *
     * @return boolean - whether underscore is turned on
     */
    public function getUnderscore() {

        // return the value
        return $this->underscore;

    }

    /**
     * Whether the underscore is turned on
     *
     * @return boolean
     */
    public function isUnderscoreOn() {

        // return whether underscoring is currently on
        return $this->underscore === true;

    }


    /**
     * Turn off both underscore and bold
     * 
     * @return $this
     */
    public function normal() {

        // turn off bold
        $this->bold = false;

        // turn off underscore
        $this->underscore = false;

        // chaining
        return $this;

    }



    /////////////////////////////////////////////////////////////////////////////////////////
    //                                       Colors                                        //
    /////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Set the text color
     * 
     * @param Color | string | int | array $color
     *      - Color - a Color object
     *      - String - a W3C color index name "darkblue"
     *      - integer - a number between 0-255 for the XTerm escape code
     *      - array - RGB values in the format [R,G,B]
     * @return $this
     */
    public function setTextColor($color) {

        // if it is null, just remember null
        if (is_null($color)) {

            // undefined
            $this->textColor = null;

        // it is an instance of Color
        } elseif ($color instanceof Color) {

            // clone it
            $this->textColor = clone $color;

        // it is some other type    
        } else {

            // let the Color object sort it out
            $this->textColor = new Color($color);

        }

        // chaining
        return $this;

    }
    

    /**
     * Getter for the text color
     * 
     * @return ColorInterface | null - null if it has been cleared, otherwise the Color object stored
     */
    public function getTextColor() {

        // return it
        return $this->textColor;
    }

    /**
     * Getter for the text color name
     * 
     * @return String | null - null if no color defined, or the name
     */
    public function getTextColorName() {

        // if there is a text color defined
        if ($this->textColor) {

            // return the name
            return $this->textColor->getName();

        // there is not text color defined
        } else {

            // return nothing
            return null;

        }

    }

    /**
     * Whether a text color has been defined, or has been cleared
     *
     * @return bool
     */
    public function hasTextColor() {

        // return true if it is not null
        return !is_null($this->textColor);

    }

    /**
     * Clear out the text color
     *
     * @return $this
     */
    public function clearTextColor() {

        // clear out the text color
        $this->textColor = null;

        // chaining
        return $this;
    }



    /**
     * Set the fill color
     * 
     * @param Color | string | int | array $color
     *      - Color - a Color object
     *      - string - a W3C color index name "darkblue"
     *      - integer - a number between 0-255 for the XTerm escape code
     *      - array - RGB values in the format [R,G,B]
     * @return $this
     */
    public function setFillColor($color) {

        // if it is null, just remember null
        if (is_null($color)) {

            // undefined
            $this->fillColor = null;
            
        // if it is an instance of a color
        } elseif ($color instanceof Color) {

            // clone it
            $this->fillColor = clone $color;

        } else
        {

            // it is something else, let the Color object sort it out
            $this->fillColor = new Color($color);

        }

        // chaining
        return $this;
    }

    /**
     * Getter for fill color
     *
     * @return null | ColorInterface - null if it is cleared, otherwise the color object stored
     */
    public function getFillColor() {

        // return fill color
        return $this->fillColor;

    }

    /**
     * Getter for the text color name
     * 
     * @return string | null - null if no color defined, or the name
     */
    public function getFillColorName() {

        // if there is a text color defined
        if ($this->fillColor) {

            // return the name
            return $this->fillColor->getName();

            // there is not text color defined
        } else {

            // return nothing
            return null;

        }

    }

    /**
     * Whether a fill color has been defined
     *
     * @return bool
     */
    public function hasFillColor() {

        // return true if the fill color is not null
        return !is_null($this->fillColor);
    }

    /**
     * Clear out the text color
     *
     * @return $this
     */
    public function clearFillColor() {

        // clear out the fill color
        $this->fillColor = null;

        // chaining
        return $this;
    }


    /**
     * Set both text and fill colors
     * 
     * @param Color | string | int | array $textColor
     * @param Color | string | int | array $fillColor
     *      - String - a W3C color index name "darkblue"
     *      - integer - a number between 0-255 for the XTerm escape code
     *      - array - RGB values in the format [R,G,B]
     * @return $this
     */
    public function setColors($textColor, $fillColor) {

        // set the text color
        self::setTextColor($textColor);

        // set the fill color
        self::setFillColor($fillColor);


        // chaining
        return $this;
    }
    

    /**
     * Switch the text and fill colors
     *
     * @return $this
     */
    public function reverseColors() {

        // switch them
        $text = $this->textColor;
        $this->textColor = $this->fillColor;
        $this->fillColor = $text;

        return $this;
    }

 
}