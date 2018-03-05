<?php

namespace Clio\Widgets\Tables;

use ANSI\Color\Color;
use Clio\Clio;
use Clio\Styling\Style;


/**
 * This class is part of the Clio Open Source project, Clio.1happyplace.com
 * Copyright, Katie Ayres, katie@1happyplace.com
 *
 * Available through the MIT license
 * @license MIT
 *
 * The BarTable class generates a basic table, with additional features. There can be an overarching title along the top with no columns
 * and a single title.  Below that will be the column headers.  Both of these lines are filled with specified text and fill color.
 *
 */
class BarTable extends Table
{

    /**
     * The title along the top, one full bar with the singular text centered
     * @var null
     */
    private $title = null;

    /**
     * Whether to show the last empty line that is underlined
     * @var bool
     */
    private $showBottomLine = true;
    
    /**
     * The bar text color
     * @var null | Color
     */
    private $barText = null;

    /**
     * The bar fill color
     * @var null | Color
     */
    private $barFill = null;

    /**
     * The body (showing data) text color
     * @var null
     */
    private $bodyText = null;

    /**
     * The body (showing data) fill color
     * @var null | Color
     */
    private $bodyFill = null;
    


    /**
     * BarTable constructor.
     * @param Clio $clio
     * @param Column[] $columnDefinitions
     * @param null|string $title  - the title at the top (if desired)
     * @param bool $showBottomLine - whether to draw an empty line at the bottom with an underscore
     */
    public function __construct($clio, $columnDefinitions, $title = null, $showBottomLine = true)
    {
        // run the parent constructor
        parent::__construct($clio, $columnDefinitions);

        // save the title and bottom line flag
        $this->title = $title;
        $this->showBottomLine = $showBottomLine;

    }


    /**
     * Set the title text (if desired, if this is not specified, then the title bar is not shown)
     * @param $text
     */
    public function setTitle($text) {

        // save the title
        $this->title = $text;
    }


    /**
     * Set the colors, you can send in anything a Color object constructor can take (most everything)
     * 
     * @param string | int | Color $barText
     * @param string | int | Color $barFill
     * @param string | int | Color $bodyText
     * @param string | int | Color $bodyFill
     */
    public function setColors($barText, $barFill, $bodyText, $bodyFill) {

        // save the bar text and fill
        $this->barText = new Color($barText);
        $this->barFill = new Color($barFill);

        // save the body text and fill
        $this->bodyText = new Color($bodyText);
        $this->bodyFill = new Color($bodyFill);
        
        
    }

    /**
     * Setter the for the last line with a underline
     * 
     * @param boolean $showBottomLine
     */
    public function setShowBottomLine($showBottomLine)
    {
        $this->showBottomLine = $showBottomLine;
    }
    
    

    /**
     * Draw the table of data
     * @param $data
     */
    public function draw($data) {

        // if the data is sane, and the widths have been set (if not calculate them)
        if ($this->dataCheck($data)) {

            $barStyle = new Style();

            // set the colors to the bar text and fill
            $barStyle->setColors($this->barText, $this->barFill);
            
            $bodyStyle = new Style();
            
            // set the colors to the body text and fill
            $bodyStyle->setColors($this->bodyText, $this->bodyFill);
            
            
            
            // calculate the widths
            $this->calculateWidths($data);
            
            // calculate the total width
            $totalWidth = $this->calcTotalWidth();

            // if at least one of the columns has header text
            if ($this->hasHeader()) {

                
                $headers = $this->getHeaderText();
                
                // if a title was specified
                if ($this->title) {

                    // center the title
                    $justifiedText = $this->clio->justify($this->title, "center", $totalWidth);

                    // create a title style
                    $titleStyle = new Style();
                    $titleStyle->setBold()->setTextColor($this->bodyText);

                    // ask Clio to draw it
                    $this->clio->style($titleStyle)->display($justifiedText)->clear()->newLine();
                }

                // draw the row of cells
                $this->drawRow($headers, $barStyle);

            }
            
            // go through each row of data
            for ($row = 0; $row<count($data); ++$row) {

                // draw the row of cells
                $this->drawRow($data[$row], $bodyStyle);
                
            }

            // if the last line is desired
            if ($this->showBottomLine) {
                
                // now add an empty line
                $emptyLine = str_pad("",$totalWidth);

                // underline it to create a bottom line with the bar fill
                $underline = (new Style())->setUnderscore()->setColors($this->barFill, $this->bodyFill);

                // ask Clio to display it
                $this->clio->style($underline)->display($emptyLine);
            }
            

        }

    }
 
}