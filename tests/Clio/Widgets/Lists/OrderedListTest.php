<?php

use ANSI\Color\Mode;
use Clio\Widgets\Lists\OrderedList;
use Clio\Styling\Style;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . "/../../../TestStubs/ClioStub.php";

class OrderedListTest extends TestCase
{


    public function setUp(): void
    {



    }

    public function tearDown(): void
    {

    }

    /**
     * function start($text = null)
     *
     * Start an ordered list
     * param $text String | null - text to show on the very first line, will not work for anything nested
     * return $this
     */
    public function test_start() {

        $clio = new ClioStub(Mode::VT100);
        $output = ClioStub::$startupSequencePrintable;

        // set up some markup
        $clio->addMarkupDefinition("!!",(new Style())->setBold());

        // set up a list
        $list = new OrderedList($clio);


        // no text
        $list->start()->end();
        $output .= "";

        // text with no markup
        $list->start("Start")->end();
        $output .= "Start\n";

        // text with markup
        $list->start("!!Hello!! World!")->end();
        $output .= "\\e[1mHello\\e[0m World!\n";


        $this->expectOutputString($output);
        return;


    }

    /**
     * function li($text)
     *
     * Display a list item
     * param $text - the item
     * return $this
     */
    public function test_li() {

        $clio = new ClioStub(Mode::VT100);
        $output = ClioStub::$startupSequencePrintable;

        $list = new OrderedList($clio);

        // ordered list
        $list->start();
        $list->li("one")->li("two")->end();
        $output .= "   1. one\n";
        $output .= "   2. two\n";


        // nested list
        $list->start()->start()->li("farther")->end()->end();
        $output .= "      a. farther\n";


        // deep nesting
        $list->start()->li('one')->start()->li('two')->start()->li('three')->start()->li('four')->end()->end()->end()->end();
        $output .= "   1. one\n";
        $output .= "      a. two\n";
        $output .= "         1. three\n";
        $output .= "            A. four\n";

        // now with markup
        $clio->addMarkupDefinition("!!", (new Style())->setBold());


        // ordered list with markup
        $list->start();
        $list->li("one")->li("!!two!!")->end();
        $output .= "   1. one\n";
        $output .= "   2. \\e[1mtwo\\e[0m\n";

        // nested list with markup
        $list->start()->start()->li("farther !!than!! that")->end()->end();
        $output .= "      a. farther \\e[1mthan\\e[0m that\n";


        // wrapping
        $clio = new ClioStub(Mode::VT100,null,null);
        $output .= ClioStub::$startupSequencePrintable;
        $clio->setWidth(30);
        $list = new OrderedList($clio);


        $list->start()->li("This is some looong text that will surely curl around.")->end();

        // 123456789012345678901234567890
        //    1. This is some looong text
        //       that will surely curl
        //       around
        $output .= "   1. This is some looong text\n";
        $output .= "      that will surely curl\n";
        $output .= "      around.\n";


        $this->expectOutputString($output);
        return;
    }

    /**
     * function end()
     *
     * End the current ordered or unordered list
     */
    public function test_end() {
        $html = new ClioStub(Mode::VT100);

        $list = new OrderedList($html);
        $output = ClioStub::$startupSequencePrintable;

        // unordered lists

        // simple list
        $list->start()->end();
        $output .= "";

        $list->start()->start()->end()->li("end")->end();
        $output .= "   1. end\n";

        $this->expectOutputString($output);
    }
}


