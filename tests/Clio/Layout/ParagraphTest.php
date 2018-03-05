<?php

use Clio\Layout\Paragraph;
use Clio\Styling\Style;

require_once __DIR__ . "/../../TestStubs/ClioStub.php";

class ParagraphTest extends PHPUnit_Framework_TestCase
{



    public function setUp()
    {


    }

    public function tearDown()
    {

    }

    /**
     * public function display($text)
     * 
     * Display one paragraph
     * param $text
     * return $this
     */
    public function test_displayNoMarkup() {

        // set a width of 20
        $clio = new ClioStub("VT100");
        $output = ClioStub::$startupSequencePrintable;

        $p = new Paragraph($clio);

        $clio->setWidth(10);
        $emptyLine = str_pad(" ",10) . "\n";

        // no data displayed
        $p->display('   ');

        // padding not needed
        $p->display('1234567890');
        $output .= "1234567890\n" . $emptyLine;

        // padding needed
        $p->display('123456789');
        $output .= "123456789 \n" . $emptyLine;

        // wrapping
        $p->display('12345678901');
        $output .= "1234567890\n" . "1         \n" . $emptyLine;

        // add bold styling and wrap
        $bold = new Style(true);
        $styleP = new Paragraph($clio, $bold);
        $styleP->display("12345678901");
        $output .= "\\e[1m1234567890\n";
        $output .= "1         \n";
        $output .= $emptyLine;
        $output .= "\\e[0m";

        // no styling
        // 12345678901234567890
        // An old fox went back
        // to his den, and
        // slept.
        //$html->display("12345678901234567890")->nl();
        $clio->setWidth(20);
        $emptyLine = str_pad(" ",20) . "\n";
        $p->display("An old fox went back to his den, and slept.");

        $output .= "An old fox went back\n";
        $output .= "to his den, and     \n";
        $output .= "slept.              \n";
        $output .= $emptyLine;

        $this->expectOutputString($output);

    }

    /**
     * public function display($text)
     *
     * Display one paragraph
     * param $text
     * return $this
     */
    public function test_displayMarkup() {

        $clio = new ClioStub();
        $clio->setWidth(20);
        $output = ClioStub::$startupSequencePrintable;
        
        $clio->addMarkupDefinition("<limeb>", (new Style())->setFillColor("lime"));
        $clio->addMarkupDefinition("**",(new Style())->setBold());
        
        $p = new Paragraph($clio);

        $p->display("<limeb>An **old** fox went back to his den<limeb>, and slept.");

        $output .= "\\e[48;5;10mAn \\e[1mold\\e[0;48;5;10m fox went back\n";
        $output .= "to his den\\e[0m, and     \n";
        $output .= "slept.              \n";
        $output .= "                    \n";
        
        $this->expectOutputString($output);
    
    }

    public function test_displaySpaceAfter() {
        $clio = new ClioStub();
        $clio->setWidth(10);
        $output = ClioStub::$startupSequencePrintable;

        $p = new Paragraph($clio, null, 0);
        $p->display("1234567890");
        $output .= "1234567890\n";

        $p = new Paragraph($clio, null, 1);
        $p->display("1234567890");
        $output .= "1234567890\n          \n";

        $p = new Paragraph($clio, null, 2);
        $p->display("1234567890");
        $output .= "1234567890\n          \n          \n";

        $this->expectOutputString($output);
    }

    public function test_trim() {
        $clio = new ClioStub();
        $clio->setWidth(10);
        $output = ClioStub::$startupSequencePrintable;

        $trim = new Paragraph($clio, null, 0, true);
        $trim->display("   abc   ");
        $output .= "abc       \n";

        $noTrim = new Paragraph($clio, null, 0, false);
        $noTrim->display("   abc   ");
        $output .= "   abc    \n";

        $this->expectOutputString($output);

    }


    
}


