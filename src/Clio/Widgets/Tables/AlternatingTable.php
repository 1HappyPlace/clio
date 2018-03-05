<?php

namespace Clio\Widgets\Tables;

use ANSI\Color\Color;
use ANSI\Color\ColorInterface;
use Clio\Clio;
use Clio\Styling\Style;


/**
 * This class is part of the Clio Open Source project, Clio.1happyplace.com
 * Copyright, Katie Ayres, katie@1happyplace.com
 *
 * Available through the MIT license
 * @license MIT
 *
 * This class generates a table that is much like the basic table, only with an alternating soft fill color on
 * every other line
 *
 */
class AlternatingTable extends Table
{
    /**
     * The background to show every other line
     * @var null
     */
    private $alternatingBackground = null;

    /**
     * AlternatingTable constructor.
     * @param Clio $clio
     * @param Column[] $columnDefinitions
     * @param null | string | ColorInterface $alternatingBackground
     */
    public function __construct($clio, $columnDefinitions, $alternatingBackground = "oldlace")
    {
        // run the parent constructor
        parent::__construct($clio, $columnDefinitions);
        
        // save the alternating background
        $this->setAlternatingFill($alternatingBackground);
    }


    /**
     * Set the fill color for every other line in the data
     * @param null | string | ColorInterface $alternatingBackground
     */
    public function setAlternatingFill($alternatingBackground) {

        // save it
        $this->alternatingBackground = new Color($alternatingBackground);

    }


    /**
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
                $style->setBold();

                $headers = $this->getHeaderText();

                // draw the row of cells
                $this->drawRow($headers, $style);

            }

            // go through each row of data
            for ($row = 0; $row<count($data); ++$row) {

                // create a style object
                $style = new Style();

                // show every row filled with the color
                if ($row % 2 === 0) {

                    // bold and underscore for the title
                    $style->setFillColor($this->alternatingBackground);

                } 

                // draw the row of cells
                $this->drawRow($data[$row], $style);


            }
            
        }

    }

}