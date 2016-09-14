<?php

namespace Clio\Style;

use ANSI\Color\Color;
use ANSI\Color\ColorInterface;
use ANSI\TerminalState;
use ANSI\TerminalStateInterface;



/**
 * This interface is part of the Clio Open Source project, Clio.1happyplace.com
 * Copyright, Katie Ayres, katie@1happyplace.com
 *
 * Available through the MIT license
 * @license MIT
 *
 * The Styling interface provides the method signatures to interact with a Style object
 *
 */
interface StyleInterface
{

    /**
     * Initialize a style to the state of a terminal
     *
     * @param TerminalStateInterface $state
     */
    public function initialize(TerminalStateInterface $state);

    /**
     * Return a Terminal State object based on the styling settings
     * NOTE: The styling interface and terminal interfaces are similar
     *       in structure, but not how they work, so they have been
     *       kept separate. Terminal states are concrete, bold and
     *       underscore are on or off, colors are instantiated, but
     *       might be no color.  Styles are desired states and therefore
     *       have a third state which is undefined, which means let another
     *       style define this state (e.g. if bold is undefined, but another
     *       style in a stack turns bold on, then it will be on.
     *
     * @return TerminalState
     */
    public function getState();
    
    /**
     * Override a style, if anything is set to null, then don't change the value of the recipient object
     *
     * @param StyleInterface $style
     */
    public function overrideMembersTo(StyleInterface $style);


    /**
     * Clear out all formatting
     *
     * @return $this
     */
    public function clearStyling();
    

    /////////////////////////////////////////////////////////////////////////////////////////
    //                                    font styling                                     //
    /////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Setter for bold
     *
     * @param boolean $on
     * @return $this
     */
    public function setBold($on = true);

    /**
     * Clear any bold styling, it will have no effect, nor override
     */
    public function clearBoldStyling();

    /**
     * Getter for bold
     *
     * @return boolean - whether bold is turned on or off
     */
    public function getBold();

    /**
     * Whether bolding is on
     *
     * @return boolean
     */
    public function isBoldOn();


    /**
     * Set the underscore setting
     *
     * @param boolean $on
     * @return $this
     */
    public function setUnderscore($on = true);

    /**
     * Clear any underscore styling, it will have no effect, nor override
     */
    public function clearUnderscoreStyling();

    /**
     * Getter for the underscore setting
     *
     * @return boolean - whether underscore is turned on
     */
    public function getUnderscore();

    /**
     * Whether the underscore is turned on
     *
     * @return boolean
     */
    public function isUnderscoreOn();

    /**
     * Turn off both underscore and bold
     * 
     * @return $this
     */
    public function normal();


    /////////////////////////////////////////////////////////////////////////////////////////
    //                                        Colors                                       //
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
    public function setTextColor($color);

    /**
     * Getter for the text color
     *
     * @return ColorInterface | null - null if it has been cleared, otherwise the Color object stored
     */
    public function getTextColor();

    /**
     * Getter for the text color name
     *
     * @return String | null - null if no color defined, or the name
     */
    public function getTextColorName();

    /**
     * Whether a text color has been defined, or has been cleared
     *
     * @return bool
     */
    public function hasTextColor();

    /**
     * Clear out the text color
     *
     * @return $this
     */
    public function clearTextColor();


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
    public function setFillColor($color);

    /**
     * Getter for fill color
     *
     * @return null | ColorInterface - null if it is cleared, otherwise the color object stored
     */
    public function getFillColor();

    /**
     * Getter for the text color name
     *
     * @return string | null - null if no color defined, or the name
     */
    public function getFillColorName();

    /**
     * Whether a fill color has been defined
     *
     * @return bool
     */
    public function hasFillColor();

    /**
     * Clear out the text color
     *
     * @return $this
     */
    public function clearFillColor();

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
    public function setColors($textColor, $fillColor);

    /**
     * Switch the text and fill colors
     *
     * @return $this
     */
    public function reverseColors();





}