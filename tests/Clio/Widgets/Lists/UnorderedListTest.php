<?php

use ANSI\Color\Mode;
use Clio\Widgets\Lists\UnorderedList;
use Clio\Styling\Style;

require_once __DIR__ . "/../../../TestStubs/ClioStub.php";



class UnorderedListTest extends PHPUnit_Framework_TestCase
{



    public function setUp()
    {



    }

    public function tearDown()
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

        // set up a list
        $list = new UnorderedList($clio);


        // no text
        $list->start()->end();

        // text with no markup
        $list->start("Start")->end();
        $output .= "Start\n";

        // text with markup

        // set up some markup
        $clio->addMarkupDefinition("!!",(new Style())->setBold());

        $list->start("!!Hello!! World!")->end();
        $output .= "\\e[1mHello\\e[0m World!\n";

        // deeper nesting
        $list->start("First")->start("Second")->li("List Item")->end()->end();
        $output .= "First\n";
        $output .= "      - List Item\n";

        // markup across two lists
        $list->start("!!bold")->li("one")->end();
        $list->start("Still Bold")->li("!!no bold")->end();
        $output .= "\\e[1mbold\n";
        $output .= "   - one\n";
        $output .= "Still Bold\n";
        $output .= "   - \\e[0mno bold\n";


        $this->expectOutputString($output);
        return;


    }

    /**
     * function setBullet($str)
     *
     * Set the bullet string for lists
     *
     * param $str
     * return $this
     */
    public function test_setBullet() {

        $clio = new ClioStub(Mode::VT100);
        $output = ClioStub::$startupSequencePrintable;

        $list = new UnorderedList($clio);

        // no bullet
        $list->setBullet(null);
        $list->start();
        $list->li("one");
        $list->end();
        $output .= "   - one\n";


        // empty bullet
        $list->setBullet("");
        $list->start();
        $list->li("one");
        $list->end();
        $output .= "    one\n";

        // one character bullet
        $list->setBullet("*");
        $list->start();
        $list->li("one")->li("two");
        $list->end();
        $output .= "   * one\n";
        $output .= "   * two\n";


        // two character bullet
        $list->setBullet(">>");
        $list->start();
        $list->li("one")->start()->li("two")->end()->li("three");
        $list->end();
        $output .= "   >> one\n";
        $output .= "      >> two\n";
        $output .= "   >> three\n";


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

        $clio = new ClioStub(Mode::XTERM);
        $output = ClioStub::$startupSequencePrintable;

        $list = new UnorderedList($clio);
        $clio->newLine();
        $output .= "\n";

        // unordered lists

        // simple list
        $list->start();
        $list->li("one")->li("two")->end();
        $output .= "   - one\n   - two\n";

        // nested list
        $list->start()->start()->li("farther")->end()->end();
        $output .= "      - farther\n";



        // test word wrap
        $clio = new ClioStub();
        $output .= ClioStub::$startupSequencePrintable;
        $clio->setWidth(15);
        $list = new UnorderedList($clio);



        $list->start()->li("This is a bit longer and should break.")->end();
        $output .= "   - This is a\n     bit longer\n     and should\n     break.\n";



        // now test markup and word wrap

        $clio->addMarkupDefinition("%%",(new Style())->setTextColor("lime"));

        $list->start()->li("This is a bit %%longer%% and should break.")->end();
        $output .= "   - This is a\n     bit \\e[38;5;10mlonger\\e[0m\n     and should\n     break.\n";




        $clio = new ClioStub();
        $output .= ClioStub::$startupSequencePrintable;
        $clio->setWidth(30);
        $list = new UnorderedList($clio);

        $list->start()->li("This is some looong text that will surely curl around.")->end();

        // 123456789012345678901234567890
        //    1. This is some looong text
        //       that will surely curl
        //       around
        $output .= "   - This is some looong text\n     that will surely curl\n     around.\n";

        $this->expectOutputString($output);
        return;


    }

    
    public function test_liWrapping() {

        $clio = new ClioStub();
        $output = ClioStub::$startupSequencePrintable;
        $clio->setWidth(15);


        $list = new UnorderedList($clio);
        $clio->newLine();
        $output .= "\n";

        
        $list->start("")->li("A blue cat said meow.")->start()->li("A green dog barked exuberantly.");

                 // 123456789012345
        $output .= "   - A blue cat\n";
        $output .= "     said meow.\n";
        $output .= "      - A green\n";
        $output .= "        dog\n";
        $output .= "        barked\n";
        $output .= "        exubera\n";
        $output .= "        ntly.\n";

        $this->expectOutputString($output);
        
    }
    /**
     * function end()
     *
     * End the current ordered or unordered list
     */
    public function test_end() {
        $clio = new ClioStub(Mode::VT100);
        $output = ClioStub::$startupSequencePrintable;

        $list = new UnorderedList($clio);

        // simple list
        $list->start()->end();
        $output .= "";

        $list->start()->start()->end()->li("end")->end();
        $output .= "   - end\n";

        $this->expectOutputString($output);
    }

    public function test_Overall() {
        $clio = new ClioStub(Mode::VT100,"cyan","black");
        $output = ClioStub::$startupSequencePrintable;
        $clio->setWidth(70);

        $output .= "\\e[96;40m";
        $clio->addMarkupDefinition("<limeb>", (new Style())->setFillColor("lime"));
        $clio->addMarkupDefinition("<blue>", (new Style())->setTextColor("blue")->setBold());

        $ul = new UnorderedList($clio);
        $ul->start("Unordered List");
        $ul->li("One List Item")->li("Another list item that will have a line break, which will be based on the width.");
        $ul->start()->li("Second level of nesting caused by another **start()** and has lots of text for more wrapping");
        $ul->li("And some markup that creates <blue>blue bold<blue> text.")->end();
        $ul->li("To finish the deeper nesting, **End()** was called.");
        $ul->end();


        $output .= "Unordered List\n";
        $output .= "   - One List Item\n";
        $output .= "   - Another list item that will have a line break, which will be\n";
        $output .= "     based on the width.\n";
        $output .= "      - Second level of nesting caused by another \\e[1mstart()\\e[0;96;40m and has lots\n";
        $output .= "        of text for more wrapping\n";
        $output .= "      - And some markup that creates \\e[1;34mblue bold\\e[0;96;40m text.\n";
        $output .= "   - To finish the deeper nesting, \\e[1mEnd()\\e[0;96;40m was called.\n";
        $output .= "";

        $this->expectOutputString($output);
    }
}


