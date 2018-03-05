<?php

require_once __DIR__ . '/../../TestStubs/ClioStub.php';

use ANSI\Color\Color;
use ANSI\Color\Mode;
use Clio\Layout\Title;
use Clio\Styling\Style;


class TitleTest extends PHPUnit_Framework_TestCase
{



    public function setUp()
    {



    }

    public function tearDown()
    {

    }


    public function createHTMLStub() {


    }

    /**
     * function display($text, $emptyLineBeneath = true)
     *
     * Display one Title
     *
     * param string $text - the text of the title
     * param boolean $emptyLineBeneath - whether to add an empty line below the title
     * return $this
     */
    public function test_displayNoDefaultColors()
    {

        $emptyStyle = new Style();
        $emptyStyle->setBold(true);
        // no default colors
        $clio = new ClioStub(Mode::VT100);
        $output = ClioStub::$startupSequencePrintable;
        $clio->setWidth(5);

        $title = new Title($clio,"left",$emptyStyle,0,0);

        // bad data
        $title->display([100]);

        // empty title
        $title->display("     ");

        // out of the box title
        $title->display("1");
        $output .= "\\e[1m1    \\e[0m\n";


        $clio = new ClioStub(Mode::VT100);
        $clio->setWidth(10);
        $title = new Title($clio,"left",$emptyStyle,0,0);
        $output .= ClioStub::$startupSequencePrintable;

        // straight title
        $title->display("Title");
        $output .= "\\e[1mTitle     \\e[0m\n";


        // trimming
        $title->display("   Title     ");
        $output .= "\\e[1mTitle     \\e[0m\n";

        // center justified
        $title = new Title($clio,"center", $emptyStyle,0,0);

        $title->display("Title");
        $output .= "\\e[1m  Title   \\e[0m\n";

        // add a margin
        $clio = new ClioStub(Mode::VT100);
        $clio->setWidth(10);
        $title = new Title($clio,"left", $emptyStyle,0,0);
        $output .= ClioStub::$startupSequencePrintable;


        $title->display("Title");
        $output .= "\\e[1mTitle     \\e[0m\n";

        $this->expectOutputString($output);


    }

    public function test_displayDefaultColors()
    {
        $emptyStyle = new Style();
        // HTML default colors
        $clio = new ClioStub(Mode::VT100, "ansicyan","ansiwhite");
        $output = ClioStub::$startupSequencePrintable;

        $clio->setWidth(10);
        $title = new Title($clio,"left",$emptyStyle,0,0);
        $output .= "\\e[36;107m";

        // out of the box title
        $title->display("1");
        $output .= "1" . str_pad("", 9) . "\n";

        $this->expectOutputString($output);

    }

    public function test_displayBar() {

        $emptyStyle = new Style();
        $white = new Color("ansiwhite");
        $black = new Color("ansiblack");
        $style = new Style(true,null,$white,$black);

        // bad data (nothing is displayed)
        $clio = new ClioStub(Mode::VT100);
        $output = ClioStub::$startupSequencePrintable;
        $clio->setWidth(20);

        // color and centered
        $title = new Title($clio,"center",$style,0,0);
        $title->display("Bar Title");
        $output .= "\\e[1;97;40m     Bar Title      \\e[0m\n";


        $this->expectOutputString($output);
        return;


    }



    public function test_before_after() {

        $fullStyle = new Style(true, true, Color::red(), Color::blue());

        $clio = new ClioStub(Mode::RGB);
        $output = ClioStub::$startupSequencePrintable;
        $clio->setWidth(10);

        // test space before
        $title = new Title($clio, "right", $fullStyle, 1, 0);
        $title->display("123");

        $output .= "\n\\e[1;4;38;2;255;0;0;48;2;0;0;255m       123\\e[0m\n";

        // test space after
        $title = new Title($clio, "right", $fullStyle, 0, 1);
        $title->display("12345678901");

        $output .= "\\e[1;4;38;2;255;0;0;48;2;0;0;255m1234567890\\e[0m\n\n";

        $this->expectOutputString($output);

    }



    
}


