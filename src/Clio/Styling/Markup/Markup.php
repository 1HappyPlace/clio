<?php

namespace Clio\Styling\Markup;

use Clio\Styling\StyleInterface;

/**
 * This class is part of the Clio Open Source project, Clio.1happyplace.com
 * Copyright, Katie Ayres, katie@1happyplace.com
 *
 * Available through the MIT license
 * @license MIT
 *
 * Class Markup simply stores a symbol to trigger the stored style.
 *
 */
class Markup 
{
    /**
     * The symbol that starts (or stops) the styling
     * @var string
     */
    protected $symbol;

    /**
     * The styling
     * @var StyleInterface
     */
    protected $style;

    /**
     * Markup constructor.
     * @param $symbol
     * @param $style
     */
    public function __construct($symbol, $style)
    {
        // store the symbol and style
        $this->symbol = $symbol;
        $this->style = $style;
    }

    /**
     * @return string
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * @return StyleInterface
     */
    public function getStyle()
    {
        return $this->style;
    }




}