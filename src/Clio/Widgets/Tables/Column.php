<?php


namespace Clio\Widgets\Tables;


use ANSI\Color\Color;
use ANSI\Color\ColorInterface;
use Clio\Styling\Markup\Definition;
use Clio\Styling\Markup\Justification;

/**
 * This class is part of the Clio Open Source project, Clio.1happyplace.com
 * Copyright, Katie Ayres, katie@1happyplace.com
 *
 * Available through the MIT license
 * @license MIT
 *
 * The Column class represents one column in a table.  It keeps track of whether
 * the width is static or calculated (and the corresponding max width), left
 * and right padding, justification and colors.
 *
 */

class Column
{

    /**
     * The text for the first line with headers
     * @var string | null
     */
    protected $headerText = null;
    
    /**
     * Whether the width is based on the widest data
     * @var bool
     */
    private $calculateWidth = true;

    /**
     * The width (in characters) of the column, including any padding
     *
     * @var integer | null - null indicates it should be calculated based on the data
     */
    protected $width = null;

    /**
     * If the width is calculated, then optional value to ensure the calculated width
     * never goes wider than this
     * 
     * @var integer | null
     */
    protected $maximumWidth = null;

    /**
     * The left padding (if desired), this will be subtracted from the width
     *
     * @var int
     */
    protected $leftPadding = 0;

    /**
     * The right padding for the table (number of empty spaces), this will be subtracted 
     * from the $width
     *
     * @var int
     */
    protected $rightPadding = 3;
    
    /**
     * The justification of the column
     * @var int - Justification::LEFT, ::RIGHT, ::CENTER
     */
    protected $justification = Justification::LEFT;

    /**
     * The text color of the column
     *
     * @var Color | null
     */
    protected $textColor = null;

    /**
     * The fill color of the column
     *
     * @var Color | null
     */
    protected $fillColor = null;

    /**
     * Column constructor.
     * 
     * @param string|null $headerText 
     * @param int|null $width - set to null if it should be based on the widest text in the column, or set it to 
     * an integer to set a static width.  This width will include the left and right padding as well. So if 
     * width is set to 10 and right padding is at the default 3, the text will have 7 spaces of space.
     */
    public function __construct($headerText = null, $width = null)
    {
        // save the header Text
        $this->headerText = $headerText;
        
        // if the width is defined, then it will not be calculated
        if ($width) {
            
            // save the width
            $this->width = $width;
            
            // indicate the width is not calculated
            $this->calculateWidth = false;
        }
        
    }


    
    /////////////////////////////////////////////////////////////////////////////////////
    //                               Setters and Getters                               //
    /////////////////////////////////////////////////////////////////////////////////////   

    /**
     * Setter for the header text
     * 
     * @param null|string $headerText
     */
    public function setHeaderText($headerText)
    {
        $this->headerText = $headerText;
    }
    
    /**
     * Getter for the header text
     * @return null|string
     */
    public function getHeaderText()
    {
        return $this->headerText;
    }

    /**
     * Whether the header text was defined
     * 
     * @return bool
     */
    public function hasHeaderText() {
        
        // return true if it is not null
        return !is_null($this->headerText);
    }

    
    /**
     * Setter for width
     * @param int|null $width
     * 
     * @return $this
     */
    public function setWidth($width)
    {
        // if the width was set
        if ($width) {
            
            // turn off the calculated width flag
            $this->calculateWidth = false;

            // save the desired static width
            $this->width = $width;   
            
        // width is not specified
        } else {
            
            // turn on the calculated with flag
            $this->calculateWidth = true;

            // clear out the width 
            $this->width = null;
            
        }

        // chaining
        return $this;
    }

    /**
     * Getter for width
     * 
     * @return int|null
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Getter for calculated width flag
     * 
     * @return boolean
     */
    public function isCalculateWidth()
    {
        return $this->calculateWidth;
    }


    /**
     * Setter for maximum width (used with calculated fields)
     * 
     * @param null $maximumWidth
     * 
     * @return $this
     */
    public function setMaximumWidth($maximumWidth)
    {
        $this->maximumWidth = $maximumWidth;
        
        // chaining
        return $this;
    }

    
    /**
     * Getter for maximum width
     * 
     * @return int|null
     */
    public function getMaximumWidth()
    {
        return $this->maximumWidth;
    }

    /**
     * Setter for left padding
     * 
     * @param int $leftPadding
     * @return $this
     */
    public function setLeftPadding($leftPadding)
    {
        $this->leftPadding = $leftPadding;

        // chaining
        return $this;
    }

    /**
     * Getter for left padding
     * 
     * @return int
     */
    public function getLeftPadding()
    {
        return $this->leftPadding;
    }

    /**
     * Setter for right padding
     * 
     * @param int $rightPadding
     * @return $this
     */
    public function setRightPadding($rightPadding)
    {
        $this->rightPadding = $rightPadding;
        
        // chaining
        return $this;
    }
    
    /**
     * Getter for right padding
     * 
     * @return int
     */
    public function getRightPadding()
    {
        return $this->rightPadding;
    }

    /**
     * Setter for justification
     * @param int $justification
     * 
     * @return $this
     */
    public function setJustification($justification)
    {
        $this->justification = $justification;
        
        // chaining
        return $this;
    }
    
    /**
     * Getter for justification
     * 
     * @return int - Justification::LEFT, ::CENTER, ::RIGHT
     */
    public function getJustification()
    {
        return $this->justification;
    }


    /**
     * Set the colors of a column
     * 
     * @param null | int | string | ColorInterface $textColor - any valid setting for the Color constructor
     * @param null | int | string | ColorInterface $fillColor
     * @return $this
     */
    public function setColors($textColor = null, $fillColor = null) {
        
        // if the text color was specified
        if ($textColor) {
            
            // store it
            $this->textColor = new Color($textColor);
            
        // not set
        } else {

            // clear out the text color
            $this->textColor = null;
        }

        // if the fill color was specified
        if ($fillColor) {
            
            // store it
            $this->fillColor = new Color($fillColor);
            
        // not set
        } else {

            // clear out the text color
            $this->fillColor = null;
        }
        
        // chaining
        return $this;
        
    }

    /**
     * Getter for text color
     * 
     * @return Color|null
     */
    public function getTextColor()
    {
        return $this->textColor;
    }

    /**
     * Getter for fill color
     * 
     * @return Color|null
     */
    public function getFillColor()
    {
        return $this->fillColor;
    }
    

    /**
     * Calculate the text area by taking the width and subtracting any padding
     * @return int
     */
    protected function getTextArea() {
        
        // initialize the return value
        $textArea = 0;
        
        // if the width has a value (if it is calculated, it may not)
        if ($this->width) {
            // subtract the padding from the width
            $textArea = $this->width - $this->rightPadding - $this->leftPadding;          
        }
        
        
        // if it goes below zero
        if ($textArea < 0) {
            
            // make it zero
            $textArea = 0;
        }
        
        return $textArea;
    }
    
    
    /////////////////////////////////////////////////////////////////////////////////////////
    //                                Public functions                                     //
    /////////////////////////////////////////////////////////////////////////////////////////
    

    /**
     * Run the data through and make the width as wide as the widest text
     *
     * @param array $data
     * @param Definition $markupDefinition
     */
    public function calculateWidth($data, $markupDefinition = null) {

        // if the width is not calculated, or the data is invalid
        if (($this->calculateWidth === false) || !is_array($data) || (count($data) === 0)) {

            // nothing to do, it is not a calculated width
            return;
        }
        
        // initialize the array of sizes of each string
        $sizes = [];
        
        // reset the width
        $this->width = null;

        // if the header text was specified
        if ($this->headerText) {
            
            // add the length to the array of string lengths
            $sizes[] = strlen($this->headerText);
        }

        // go through and create an array sizes
        for ($i=0; $i<count($data); ++$i) {

            // get the text (make it into a string)
            $text = strval($data[$i]);

            // if the markup definition was sent in
            if ($markupDefinition) {

                // strip out any markup symbols
                $text = $markupDefinition->stripMarkupSymbols($text);
            }

            // add the size of this text to the array of sizes
            $sizes[] = strlen($text);
        }

        // now get the highest value in the array of sizes and add the padding
        $widestText = max($sizes) + $this->rightPadding + $this->leftPadding;

        // if the maximum width is defined and the width hasn't already maxed out
        if ($this->maximumWidth) {

            // check to ensure the text length is less than or equal to the maximum width
            if ($widestText <= $this->maximumWidth) {

                // the width is now the newest text length
                $this->width = $widestText;

            } else {
                // set the width to the maximum width (text will be truncated)
                $this->width = $this->maximumWidth;
            }

        // there is no maximum width
        } else {

            // just set the width to that widest text
            $this->width = $widestText;
        }

    }

    /**
     * Justify text
     *
     * @param string $text - the text to justify
     * @param Definition $markupDefinition
     *
     * @return string
     */
    public function justify($text, $markupDefinition) {

        // if the width is null
        if ((is_null($this->width)) || ($this->getTextArea() === 0)) {

            // just return the text, something was wrong
            return "";

        }
        
        // ask the markup definition object to do the justification so all
        // the markup text does not affect the justification
        $justifiedText = $markupDefinition->justify($text, $this->justification, $this->getTextArea());

        // put the left pad and right padding around the justified text
        return $markupDefinition->pad($justifiedText, $this->leftPadding, $this->rightPadding);

        
    }


}