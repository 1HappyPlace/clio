<?php

require_once __DIR__ . "/../../../TestStubs/ClioStub.php";
use ANSI\Color\Mode;
use Clio\Styling\Markup\NewLine;

class NewLineTest extends PHPUnit_Framework_TestCase
{


    public function setUp()
    {


    }

    public function tearDown()
    {

    }


    /**
     * public function __construct($times = 1)
     * public function display($html)
     *
     * NewLine constructor.
     * param int $times
     */
    public function test__construct() {

        $clio = new ClioStub(Mode::VT100);
        $output = ClioStub::$startupSequencePrintable;

        $newline = new NewLine();
        $newline->display($clio);
        $output .= "\n";

        $newline = new NewLine(2);
        $newline->display($clio);
        $output .= "\n\n";

        $this->expectOutputString($output);

    }
}


