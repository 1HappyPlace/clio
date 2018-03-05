<?php

namespace Clio\Widgets\Tables;
use Clio\Clio;
use Clio\Styling\Style;
use Clio\Styling\StyleInterface;


/**
 * This class is part of the Clio Open Source project, Clio.1happyplace.com
 * Copyright, Katie Ayres, katie@1happyplace.com
 *
 * Available through the MIT license
 * @license MIT
 *
 * The Table class is the parent class for all tables.  It can be used independently to generate a simple table
 * with no added stylistic whiz-bangs.  If static column widths are not specified, the class will automatically
 * determine the width based on the width of the data, along with additional padding specified. The column
 * justification can also be set, but it is defaulted to LEFT.
 *
 * Additionally, each of the cell data can begin with markup to fill that cell with a markup style.
 * @see Style
 *
 */
class Table
{

    /**
     * The number of columns
     * @var int
     */
    private $columnCount = 0;
    
    /**
     * The column definitions
     * @var Column[]
     */
    private $columnDefinitions = null;
    
    /**
     * An array of Style objects, each one with a markup field indicating the
     * text embedded indicating a style is desired
     */
    protected $cellStyles = null;

    /**
     * The clio object that handles the output to the terminal
     * @var Clio|null
     */
    protected $clio = null;


    /**
     * Table constructor.
     * @param Clio $clio -the Clio object, generally want just one instance to represent the current terminal
     * @param Column[] $columnDefinitions - array of column definitions if column defaults are not desired, which are:
     *              - width is calculated based on the longest text in a column
     *              - maximum width of a calculated width is not set
     *              - right padding is 3 spaces
     *              - left padding is off
     *              - justification is LEFT
     *              - column text color and fill are not set
     */
    public function __construct($clio, $columnDefinitions)
    {
        // save the Clio object
        $this->clio = $clio;
        
        // save the column count
        $this->columnCount = count($columnDefinitions);

        // save the column definitions
        $this->columnDefinitions = $columnDefinitions;


    }

    /**
     * Set the column definition for one column
     *
     * @param int $columnIndex
     * @param Column $column
     */
    public function setColumnDefinition($columnIndex, Column $column) {

        // if the index is valid
        if ($columnIndex < $this->columnCount) {

            // copy the column information
            $this->columnDefinitions[$columnIndex] = clone $column;
        }
        
    }

    

    /**
     * Simply add up all the column widths
     * @return int
     */
    protected function calcTotalWidth() {


        $totalWidth = 0;

        // go through all the widths
        for ($w=0; $w<count($this->columnDefinitions); ++$w) {
            
            // add the column's width
            $totalWidth += $this->columnDefinitions[$w]->getWidth();
        }

        // return the total
        return $totalWidth;

    }

    /**
     * Whether there is at least one header defined in the column definitions
     * @return bool
     */
    protected function hasHeader() {
        
        // go through the column definitions
        for ($i=0; $i<count($this->columnDefinitions); ++$i) {
            
            // if one column has header text
            if ($this->columnDefinitions[$i]->hasHeaderText()) {
                
                // then there is a header
                return true;
            }
        }
        
        // none of the columns had header text
        return false;
    }

    /**
     * Return an array of the header text found in the header definitions
     * @return string[]
     */
    protected function getHeaderText() {
        
        $headers = [];
        
        // go through the column definitions
        for ($i=0; $i<count($this->columnDefinitions); ++$i) {
            
            $next = $this->columnDefinitions[$i];

            // if one column has header text
            if ($this->columnDefinitions[$i]->hasHeaderText()) {

                // then there is a header
                $headers[] = $next->getHeaderText();
            
            // there is no header
            } else {
                
                // put in a blank placeholder
                $headers[] = "";
            }
        }

        // none of the columns had header text
        return $headers;       
    }
    

    /**
     * @param $row
     * @param StyleInterface $style
     */
    protected function drawRow($row, $style) {

        // ask the HTML for the markup definition (used in justifying)
        $markupDefinition = $this->clio->getMarkupDefinition();

        // go through each cell
        for ($c=0; $c<$this->columnCount; ++$c) {

            // if the cell is null
            if (is_null($row[$c])) {

                // change it to an empty string so it behaves better
                $row[$c] = "";
            }

            // get the column definition for this column
            $columnDefinition = $this->columnDefinitions[$c];

            // shortcut for cell
            $cell = $row[$c];

            // justify the text
            $justifiedText = $columnDefinition->justify($cell, $markupDefinition);

            // get the column colors
            $textColor = $columnDefinition->getTextColor();
            $fillColor = $columnDefinition->getFillColor();

            // check if a style was sent in the parameter
            if (is_null($style)) {

                // create a new style
                $newStyle = new Style();

            // a style was sent in
            } else {

                // clone it
                $newStyle = clone $style;
            }

            // if the colors were defined
            if ($textColor || $fillColor) {

                // add them to the new style
                $newStyle->setColors($textColor, $fillColor);

            }

            // display the cell
            $this->clio->style($newStyle)->display($justifiedText)->clear();

            
        }
        
        // move to the next line
        $this->clio->newLine();
    }


    /**
     * Helper function to ensure the data is correct
     * 
     * @param array $data
     * @return bool
     */
    protected function dataCheck(&$data) {
        
        // no need to go on, if there is no data
        if (count($data) < 1 || !is_array($data)) {

            // exit
            return false;
        }
        
        // first check that there are enough Column definitions for the data supplied
        for ($r=0; $r<count($data); ++$r) {

            $rowColumnCount = count($data[$r]);

            // if this row does not have enough columns, add them
            if ($rowColumnCount > $this->columnCount) {

                // add as many Columns as needed to handle this larger row
                for ($i=$this->columnCount; $i<$rowColumnCount; ++$i) {

                    // add one default column
                    $this->columnDefinitions[] = new Column();
                }

                // update the column count to reflect this new size
                $this->columnCount = $rowColumnCount;
            }
        }      

        // now, pad any row that doesn't have enough data to fill the columns
        for ($r=0; $r<count($data); ++$r) {

            // if this row does not have enough columns, add them
            if (count($data[$r]) < $this->columnCount) {

                // pad the array with empty space to fill out any columns not there
                $data[$r] = array_pad($data[$r],$this->columnCount,"");

            }
        }
        
        // we got past the hurdles, data is good
        return true;
        
    }


    /**
     * Calculate the width of any automatically calculated widths
     * @param $data
     */
    protected function calculateWidths($data) {
    
        // get the markup definition from the HTML object
        $markupDefinition = $this->clio->getMarkupDefinition();
        
        // go through and calculate any column widths that were not calculated
        for ($column = 0; $column < $this->columnCount; ++$column) {

            // get the column definition for this column
            $columnDefinition = $this->columnDefinitions[$column];

            // get the column of data
            $columnData = array_column($data, $column);
            
            // calculate it based on the widest text for that column
            $columnDefinition->calculateWidth($columnData, $markupDefinition);

        }
              
    }
    

    /**
     * Draw the grid of data
     * @param $data
     */
    public function draw($data) {
        
        // if the data is sane, and the widths have been set (if not calculate them)
        if ($this->dataCheck($data)) {
            
            // calculate the widths
            $this->calculateWidths($data);
            
            // if at least one of the columns has header text
            if ($this->hasHeader()) {
                
                // bold and underscore for the title
                $style = new Style();
                $style->setBold()->setUnderscore();

                $headers = $this->getHeaderText();
                
                // draw the row of cells
                $this->drawRow($headers, $style);       
                
            }
            
            // go through each row of data
            for ($row = 0; $row<count($data); ++$row) {

                // draw the row of cells
                $this->drawRow($data[$row], null);

            }

            
        }
        

    }



}