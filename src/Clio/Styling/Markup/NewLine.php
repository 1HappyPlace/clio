<?php

namespace Clio\Styling\Markup;


use Clio\Clio;

/**
 * This class is part of the Clio Open Source project, Clio.1happyplace.com
 * Copyright, Katie Ayres, katie@1happyplace.com
 *
 * Available through the MIT license
 * @license MIT
 *
 * Class Newline stores the desire to hit the carriage return X times
 *
 */
class NewLine
{
    /**
     * @var int
     */
    private $times;

    /**
     * NewLine constructor.
     * @param int $times
     */
    public function __construct($times = 1)
    {
        $this->times = $times;
    }

    /**
     * @param Clio $clio
     */
    public function display($clio) {

        // ask the Clio class to display new line
        $clio->newLine($this->times);
    }


}