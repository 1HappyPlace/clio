<?php

require_once __DIR__ . "/../TestStubs/ClioStub.php";

use ANSI\Color\Color;
use ANSI\Color\Mode;
use Clio\Clio;
use Clio\Styling\Markup\Markup;
use Clio\Styling\Style;
use PHPUnit\Framework\TestCase;


class ClioTest extends TestCase
{



    public function setUp() {



    }

    public function tearDown()
    {

    }


    /**
     * public function __construct($mode = Mode::XTERM, $defaultTextColor = null, $defaultFillColor = null)
     * 
     * Clio constructor.
     * param Mode | int | string $mode - VT100, XTERM, RGB, either in constant form or a case independent string "xterm", "VT100", etc
     * param ColorInterface | string | integer | array | null $defaultTextColor - the go-to text color for the terminal
     * param ColorInterface | string | integer | array | null $defaultFillColor - the go-to fill color for the terminal
     *
     *      Color parameters can be:
     *          - Object adhering to the Color Interface
     *          - A color name "Antique White" or "antiquewhite"
     *          - Xterm integer from 0-255
     *          - [R,G,B]
     *          - null
     *
     * return $this
     */
    public function test__construct()
    {
        // create a new object and do nothing, both construct and destruct will fire
        $clio = new ClioStub();
        $output = ClioStub::$startupSequencePrintable;

        // mode
        // default is XTERM
        $clio->setTextColor("red")->outputEscapeSequence();
        $output .= "\\e[38;5;9m";

        // valid mode
        $clio = new ClioStub("RGB");
        $output .= ClioStub::$startupSequencePrintable;
        $clio->setTextColor("red")->outputEscapeSequence();
        $output .=  "\\e[38;2;255;0;0m";


        // text color
        $clio = new ClioStub(Mode::XTERM, "black");
        $output .= ClioStub::$startupSequencePrintable;
        $clio->outputEscapeSequence();
        $output .=  "\\e[38;5;0m";

        // fill color
        $clio = new ClioStub(Mode::XTERM, null, "black");
        $output .= ClioStub::$startupSequencePrintable;
        $clio->outputEscapeSequence();
        $output .=  "\\e[48;5;0m";

        // both
        $clio = new ClioStub(Mode::XTERM, "red", "black");
        $output .= ClioStub::$startupSequencePrintable;
        $clio->outputEscapeSequence();
        $output .=  "\\e[38;5;9;48;5;0m";


        // chaining
        $clio = (new ClioStub(Mode::XTERM))->setBold();
        $output .= ClioStub::$startupSequencePrintable;
        $this->assertTrue($clio->getBold());

        $this->expectOutputString($output);
        return;

    }


    /////////////////////////////////////////////////////////////////////////////////////////
    //                                       Overrides                                     //
    /////////////////////////////////////////////////////////////////////////////////////////

    /**
     * ABSTRACT
     * public function output($text)
     * 
     * All output goes to this function
     *
     * param string $text
     */
    public function test_output()
    {
        // it just echos, not much to test here
        $clio = new Clio();
        $clio->output("hello");
        $this->expectOutputString(ClioStub::$startupSequence . "hello");

    }

    /**
     * ABSTRACT
     * public function carriageReturn()
     * 
     * Anytime the cursor is returned to the leftmost column, this is fired
     */
    public function test_carriageReturn()
    {
        // nothing to be done
    }


    /**
     * OVERRIDE
     * public function clear($rightAway = false)
     * 
     *
     * Clear away all formatting - bold, underscore, text and fill color
     *
     * param boolean $rightAway - whether to send out the escape sequence right away
     *          or allow the display to do it later
     * return $this
     */
    public function test_clear() {

        // ensure the text and fill are set to default colors when clear is called

        // no default colors
        $clio = new ClioStub();
        $output = ClioStub::$startupSequencePrintable ;
        $this->assertNull($clio->getDefaultTextColor());
        $this->assertNull($clio->getDefaultTextColor());
        $clio->clear(true);

        // clear should go out
        $output .= "\\e[0m";

        $clio = new ClioStub(19000,"Floral White", "Dark Cyan");
        $output .= ClioStub::$startupSequencePrintable;
        $clio->clear(true);
        $output .= "\\e[0m\\e[38;2;255;250;240;48;2;0;139;139m";


        // should just see the text color go out
        $clio->setTextColor("brown")->clear()->setTextColor("red")->outputEscapeSequence();
        $output .= "\\e[38;2;255;0;0m";


        $clio = new ClioStub(Mode::VT100);
        $output .= ClioStub::$startupSequencePrintable;

        // hit clear without any styling
        $clio->clear();

        // hit clear with something on the stack
        $clio->addMarkupDefinition("!blue!",(new Style())->setTextColor(Color::blue()));


        $text = "This is !blue!blue.";
        $clio->display($text);
        $output .= "This is \\e[34mblue.";

        // now send the clear
        $clio->clear();
        $text = "This is plain text.";
        $clio->display($text);
        $output .= "\\e[0mThis is plain text.";

        $this->expectOutputString($output);


    }





    /////////////////////////////////////////////////////////////////////////////////////////
    //                                     Font styling                                    //
    /////////////////////////////////////////////////////////////////////////////////////////


    /**
     * public function b($on = true)
     * 
     * Start or stop bolding
     *
     * param bool $on
     * return $this
     */
    public function test_b() {
        // simple bold
        (new ClioStub())->b()->outputEscapeSequence();

        // ensure the text is surrounded with the correct sequence
        $output = ClioStub::$startupSequencePrintable . "\\e[1m";

        $clio = new ClioStub();
        $clio->b()->b()->outputEscapeSequence();
        $output .= ClioStub::$startupSequencePrintable . "\\e[1m";

        $clio->b(false)->outputEscapeSequence();
        $output .= "\\e[0m";

        // ensure red is restored after the bold is turned off
        $clio->textColor("red")->b()->b(false)->outputEscapeSequence();
        $output .= "\\e[38;5;9m";

        $this->expectOutputString($output);

    }


    /**
     * public function u($on = true)
     *
     * Start or stop underscoring
     *
     * param bool|true $on
     * return $this
     */
    public function test_u() {

        // simple bold
        (new ClioStub())->u()->outputEscapeSequence();

        // ensure the text is surrounded with the correct sequence
        $output = ClioStub::$startupSequencePrintable . "\\e[4m";

        $clio = new ClioStub();
        $clio->u()->u()->outputEscapeSequence();
        $output .= ClioStub::$startupSequencePrintable . "\\e[4m";

        $clio->u(false)->outputEscapeSequence();
        $output .= "\\e[0m";

        // ensure red is restored after the bold is turned off
        $clio->fillColor("red")->u()->b()->u(false)->outputEscapeSequence();
        $output .= "\\e[1;48;5;9m";

        $this->expectOutputString($output);
    }


    /////////////////////////////////////////////////////////////////////////////////////////
    //                                       Colors                                        //
    /////////////////////////////////////////////////////////////////////////////////////////

    /**
     * public function getDefaultTextColor()
     * 
     * Get the default text color
     *
     * return Color|null
     */
    public function test_getDefaultTextColor()
    {
        // no default colors
        $clio = new Clio();
        $this->assertSame(null, $clio->getDefaultTextColor());

        // set up a default text color
        $clio = new Clio("xterm","black");
        $color = $clio->getDefaultTextColor();
        $this->assertInstanceOf("ANSI\\Color\\Color",$color);
        $this->assertSame("black",$color->getName());
    }

    /**
     * public function getDefaultFillColor()
     * 
     * Get the default fill color
     *
     * return Color|null
     */
    public function test_getDefaultFillColor()
    {
        // no default fill
        $clio = new Clio();
        $this->assertSame(null, $clio->getDefaultFillColor());

        // set up default fill
        $clio = new Clio("xterm",null, "lime");
        $color = $clio->getDefaultFillColor();
        $this->assertInstanceOf("ANSI\\Color\\Color",$color);
        $this->assertSame("lime",$color->getName());
    }


    /**
     * public function textColor($color = null)
     * 
     * Start drawing text in a particular color
     * param ColorInterface | string | integer | array | null $color - the text color
     *
     *      Color parameters can be:
     *          - Object adhering to the Color Interface
     *          - A color name "Antique White" or "antiquewhite"
     *          - Xterm integer from 0-255
     *          - [R,G,B]
     *          - null
     *
     * return $this
     */
    public function test_textColor() {

        $clio = new ClioStub();
        $output = ClioStub::$startupSequencePrintable;

        // start with a new color
        $clio->textColor("aquamarine")->outputEscapeSequence();
        $output .= "\\e[38;5;122m";


        // same color (in name) should have no effect
        $clio->textColor("aquamarine")->outputEscapeSequence();

        // chaining
        $clio->textColor("blue")->textColor("red")->outputEscapeSequence();
        $output .= "\\e[38;5;9m";

        // turn bold on
        $clio->b();

        // set the text color to null, ensure the bold is protected
        $clio->textColor(null)->outputEscapeSequence();
        $output .= "\\e[0;1m";

        // new color
        $clio->textColor("dark khaki")->outputEscapeSequence();
        $output .= "\\e[38;5;143m";

        // now do RGB just cause
        $clio = (new ClioStub(Mode::RGB))->setTextColor("dark khaki")->b();
        $clio->outputEscapeSequence();
        $output .= ClioStub::$startupSequencePrintable . "\\e[1;38;2;189;183;107m";


        $this->expectOutputString($output);
        return;

    }



    /**
     * public function clearTextColor()
     * 
     * Clear out the text color and return to the default text color (if one is specified)
     *
     * return $this
     */
    public function test_clearTextColor() {

        $clio = new ClioStub(Mode::VT100);
        $output = ClioStub::$startupSequencePrintable;

        // set up a text color of ansiblack
        $clio->textColor("ansiblack")->outputEscapeSequence();
        $output .= "\\e[30m";

        // clear it out
        $clio->clearTextColor()->outputEscapeSequence();
        $output .= "\\e[0m";

        // now set up a fill and a text color, then clear it, make sure the fill is still preserved
        $clio->fillColor("ansiblack")->textColor("ansiblue")->b();
        $clio->clearTextColor()->outputEscapeSequence();
        $output .= "\\e[1;40m";

        // default colors
        $clio = new ClioStub("xterm","lime","orange");
        $clio->outputEscapeSequence();
        $output .= ClioStub::$startupSequencePrintable . "\\e[38;5;10;48;5;214m";

        // now ask for a fill, text color, then clear the text color, should just see the commanding of the fill (and bold)
        $clio->fillColor("ansiblack")->textColor("ansiblue")->clearTextColor()->b()->outputEscapeSequence();
        $output .= "\\e[1;48;5;0m";


        // chaining and add underline
        $clio->setTextColor("red")->clearTextColor()->u()->clearTextColor()->outputEscapeSequence();
        $output .= "\\e[4m";

        $this->expectOutputString($output);
        return;
    }


    /**
     * public function fillColor($color = null)
     * 
     * Start drawing text with a fill (or background) of a particular color
     * param ColorInterface | string | integer | array | null $color - the text color
     *
     *      Color parameters can be:
     *          - Object adhering to the Color Interface
     *          - A color name "Antique White" or "antiquewhite"
     *          - Xterm integer from 0-255
     *          - [R,G,B]
     *          - null
     *
     * return $this
     */
    public function test_fillColor() {

        $clio = new ClioStub();
        $output = ClioStub::$startupSequencePrintable;

        // start with a new color
        $clio->fillColor("aquamarine")->outputEscapeSequence();
        $output .= "\\e[48;5;122m";


        // should have no effect (same color by name)
        $clio->fillColor("aquamarine")->outputEscapeSequence();

        // chaining
        $clio->fillColor("blue")->fillColor("red")->outputEscapeSequence();
        $output .= "\\e[48;5;9m";

        // turn bold on
        $clio->b();

        // set the fill color to null, ensure the bold is still preserved
        $clio->fillColor(null)->outputEscapeSequence();
        $output .= "\\e[0;1m";


        // set up RGB for fun
        $clio = (new ClioStub(Mode::RGB))->setFillColor("dark khaki")->b();
        $clio->outputEscapeSequence();
        $output .= ClioStub::$startupSequencePrintable . "\\e[1;48;2;189;183;107m";


        $this->expectOutputString($output);
        return;

    }



    /**
     * public function clearFillColor()
     * 
     * Clear out the fill color and return to the default fill color (if one is specified)
     *
     * return $this
     */
    public function test_clearFillColor() {

        $clio = new ClioStub(Mode::VT100);
        $output = ClioStub::$startupSequencePrintable;

        // set up a fill color of black
        $clio->fillColor("ansiblack")->outputEscapeSequence();
        $output .= "\\e[40m";

        // clear it
        $clio->clearFillColor()->outputEscapeSequence();
        $output .= "\\e[0m";

        // set up a text color of black and fill of blue, clear the blue fill and ensure the text is preserved
        $clio->textColor("ansiblack")->fillColor("ansiblue")->b();
        $clio->clearFillColor()->outputEscapeSequence();
        $output .= "\\e[1;30m";

        // default colors
        $clio = new ClioStub("xterm","lime","orange");
        $clio->outputEscapeSequence();
        $output .= ClioStub::$startupSequencePrintable . "\\e[38;5;10;48;5;214m";

        // set up a text color and fill, clear the fill and add bold
        $clio->textColor("ansiblack")->fillColor("ansiblue")->clearFillColor()->b()->outputEscapeSequence();
        $output .= "\\e[1;38;5;0m";


        // chaining and add underline
        $clio->setFillColor("red")->clearFillColor()->u()->clearFillColor()->outputEscapeSequence();
        $output .= "\\e[4m";

        $this->expectOutputString($output);
        return;
    }


    /**
     * public function colors($textColor = null, $fillColor = null)
     * 
     * Change both the fill and text colors
     *
     * param $textColor int|string|null $color - can be either a Color constant Color::Blue or a string with the same spelling "blue", "Red", "LIGHT CYAN", etc
     * param $fillColor int|string|null $color - can be either a Color constant Color::Blue or a string with the same spelling "blue", "Red", "LIGHT CYAN", etc
     * return $this
     */
    public function test_colors() {

        // this only calls textColor and fillColor, so just ensure the data flows through
        $clio = new Clio();
        $clio->colors("red","blue");
        $this->assertSame("red",$clio->getTextColor()->getName());
        $this->assertSame("blue",$clio->getFillColor()->getName());

    }



    /////////////////////////////////////////////////////////////////////////////////////////
    //                                     styling                                         //
    /////////////////////////////////////////////////////////////////////////////////////////



    /**
     * public function setStyle(StyleInterface $style)
     * 
     * Set the styling of underscore, bold, text and fill
     *
     * param StyleInterface $style
     * return $this
     */
    public function test_setStyle_style() {

        // empty style
        $style = new Style();
        $clio = new ClioStub();
        $output = ClioStub::$startupSequencePrintable;

        $clio->setStyle($style)->outputEscapeSequence();


        $style->setBold();
        $clio->setStyle($style)->outputEscapeSequence();
        $output .= "\\e[1m";

        $style->setUnderscore()->setTextColor(Color::red())->setFillColor("black");
        $clio->setStyle($style)->outputEscapeSequence();
        $output .= "\\e[4;38;5;9;48;5;0m";

        // turn off underscore, ensure the rest is preserved
        $style->setUnderscore(false);
        $clio->style($style)->outputEscapeSequence();
        $output .= "\\e[0;1;38;5;9;48;5;0m";


        $this->expectOutputString($output);


    }





    /////////////////////////////////////////////////////////////////////////////////////////
    //                                    text output                                      //
    /////////////////////////////////////////////////////////////////////////////////////////

    /**
     * public function out($text)
     *
     * Shortcut for the Terminal method display($text)
     *
     * param string $text
     * return $this
     */
    public function test_out() {

        // this function merely asks the parent to display music

        $clio = new Clio();
        $clio->out("Hello World!");
        $output = ClioStub::$startupSequence . "Hello World!";

        // chaining
        $clio->out("a")->out("b");
        $output .= "ab";

        $this->expectOutputString($output);
    }

    /**
     * public function line($text)
     *
     * Output text and hit a carriage return
     *
     * param string $text
     * return $this
     */
    public function test_line() {

        // this function merely call display and newline, so just ensure the calls work

        $clio = new ClioStub();
        $output = ClioStub::$startupSequencePrintable;


        // ensure the internal calls work properly
        $clio->line("Here is a line.");
        $output .= "Here is a line.\n";

        // chaining
        $clio->line("a")->line("b");
        $output .= "a\nb\n";

        $this->expectOutputString($output);
    }


    /**
     * public function nl($count = 1)
     * 
     * Shortcut for the newline method
     *
     * param int $count - the number of newlines to output
     * return $this
     */
    public function test_nl() {

        // it is just a shortcut, so just need to test $count gets through
        $clio = new Clio();


        $clio->nl();
        $output = ClioStub::$startupSequence . "\n";

        $clio->nl(3);
        $output .= "\n\n\n";

        $clio->nl()->nl(2);
        $output .= "\n\n\n";

        $this->expectOutputString($output);
    }



    /**
     * public function pause()
     * 
     * Shortcut function to pause execution until the user hits return
     */
    public function test_pause() {

        $clio = new ClioStub();

        $clio->setAnswer(["",""]);

        // not a lot to test, just chaining and the fact that it doesn't blow up
        $clio->pause()->pause();

        $this->expectOutputString(ClioStub::$startupSequencePrintable);
    }


    /// former HTML testing
    /**
     * @param $name
     * @return ReflectionMethod
     */
    protected static function getMethod($name) {

        // get a reflection of the class
        $class = new ReflectionClass("Clio\\Clio");

        // get the method of interest
        $method = $class->getMethod($name);

        // make that method accessible
        $method->setAccessible(true);

        // return the method
        return $method;
    }



    /**
     * Call the protected function processText
     *
     * @param Clio $html - the HTML object
     * @param [] $args
     * @return mixed
     */
    public function callProcessText($html, $args) {

        // make the method accessible
        $method = $this->getMethod("processText");

        // call it and return the results
        return $method->invokeArgs($html,$args);

    }
    /**
     * protected function processText($text, $markupArray)
     *
     * Find all the markup in the text and build an array of objects
     * representing a markup text stream
     *
     * param string $text
     * param Definition | null $markupArray
     *
     * return array of strings and markup objects
     */
    public function test_processText() {

        $clio = new ClioStub(Mode::VT100);
        $output = ClioStub::$startupSequencePrintable;

        $bold = (new Style())->setBold();
        $boldMarkup = new Markup("**",$bold);
        $underscore = (new Style())->setUnderscore();
        $underscoreMarkup = new Markup("--",$underscore);

        // first there is no text
        $text = "";
        $value = $this->callProcessText($clio,[$text]);
        $this->assertSame([],$value);

        // then there is no markup
        $text = "OK";
        $value = $this->callProcessText($clio,[$text]);
        $this->assertSame($text,$value[0]);

        //// ONE MARKUP
        $clio->addMarkupDefinition("**",$bold);

        // but it is not in the stream
        $text = "1234567890";
        $value = $this->callProcessText($clio,[$text]);

        $this->assertSame("1234567890",$value[0]);


        // now it is in the stream in the beginning
        $text = "**1234567890";
        $value = $this->callProcessText($clio,[$text]);

        $this->assertEquals($boldMarkup,$value[0]);
        $this->assertSame("1234567890",$value[1]);

        // now it is in the stream in the middle
        $text = "12345**67890";
        $value = $this->callProcessText($clio,[$text]);
        $this->assertSame("12345",$value[0]);
        $this->assertEquals($boldMarkup,$value[1]);
        $this->assertSame("67890",$value[2]);

        // now it is in the stream in the end
        $text = "1234567890**";
        $value = $this->callProcessText($clio,[$text]);

        $this->assertSame("1234567890", $value[0]);
        $this->assertEquals($boldMarkup, $value[1]);

        //// TWO Markups
        $clio->addMarkupDefinition("--",$underscore);

        // just one in the stream
        $text = "12345678--90";
        $value = $this->callProcessText($clio,[$text]);

        $this->assertSame("12345678", $value[0]);
        $this->assertEquals($underscoreMarkup, $value[1]);
        $this->assertSame("90",$value[2]);


        // both are in the stream
        $text = "**12345678--90";
        $value = $this->callProcessText($clio,[$text]);

        $this->assertEquals($boldMarkup,$value[0]);
        $this->assertSame("12345678", $value[1]);
        $this->assertEquals($underscoreMarkup, $value[2]);
        $this->assertSame("90",$value[3]);

        // both are in the stream next to each other
        $text = "**--1234567890";
        $value = $this->callProcessText($clio,[$text]);

        $this->assertEquals($boldMarkup,$value[0]);
        $this->assertEquals($underscoreMarkup, $value[1]);
        $this->assertSame("1234567890", $value[2]);

        // only markup
        $text = "**--";
        $value = $this->callProcessText($clio,[$text]);
        $this->assertEquals($boldMarkup,$value[0]);
        $this->assertEquals($underscoreMarkup, $value[1]);


        // both are in the stream all over the place
        $text = "**--123**456--789**0**";
        $value = $this->callProcessText($clio,[$text]);

        $this->assertEquals($boldMarkup,$value[0]);
        $this->assertEquals($underscoreMarkup, $value[1]);
        $this->assertSame("123", $value[2]);
        $this->assertEquals($boldMarkup,$value[3]);
        $this->assertSame("456", $value[4]);
        $this->assertEquals($underscoreMarkup,$value[5]);
        $this->assertSame("789", $value[6]);
        $this->assertEquals($boldMarkup,$value[7]);
        $this->assertSame("0", $value[8]);
        $this->assertEquals($boldMarkup,$value[9]);

        $this->expectOutputString($output);

    }



    /////////////////////////////////////////////////////////////////////////////////////////
    //                                 Markup and Styling                                  //
    /////////////////////////////////////////////////////////////////////////////////////////


    /**
     * public function addMarkupDefinition($symbol, $style)
     *
     * Add a markup symbol and the style associated with it
     *
     * param string $symbol - the markup symbol in the text
     * param StyleInterface $style - the style to kick in when it is found
     */
    public function test_addMarkupDefinition_get() {

        // ensure markup is placed in the markupDefinition
        $clio = new ClioStub(Mode::VT100);
        $output = ClioStub::$startupSequencePrintable;

        $text = "This is !!bold text!!!";
        $clio->display($text)->newLine();
        $output .= "This is !!bold text!!!\n";

        $clio->addMarkupDefinition("!!",(new Style())->setBold());
        $markup = $clio->getMarkupDefinition();
        $this->assertInstanceOf("Clio\\Styling\\Markup\\Definition",$markup);
        $style = $markup->getStyling("!!");
        $this->assertTrue($style->getBold());


        $clio->display($text)->newLine();
        $output .= "This is \\e[1mbold text\\e[0m!\n";

        // chaining
        $clio->addMarkupDefinition("UU",(new Style())->setUnderscore())->addMarkupDefinition("BU",(new Style())->setBold()->setUnderscore());
        $text = "UUUnderscoreUU and BUbothBU";
        $clio->display($text);
        $output .= "\\e[4mUnderscore\\e[0m and \\e[1;4mboth\\e[0m";


        $this->expectOutputString($output);
    }




    /////////////////////////////////////////////////////////////////////////////////////////
    //                                       Getters                                       //
    /////////////////////////////////////////////////////////////////////////////////////////

    /**
     * public function getWidth()
     *
     * Getter for the width
     */
    public function test_getWidth() {

        // default
        $clio = new Clio(Mode::VT100);
        $output = ClioStub::$startupSequence;;
        $this->assertSame(80,$clio->getWidth());


        // actual data
        $clio->setWidth(1142);
        $this->assertSame(1142,$clio->getWidth());

        $this->expectOutputString($output);
        return;


    }




    /////////////////////////////////////////////////////////////////////////////////////////
    //                                  Text Display                                       //
    /////////////////////////////////////////////////////////////////////////////////////////

    /**
     * public function newLine($count = 1)
     *
     * Tell the terminal to hit carriage return
     *
     * param int $count
     */
    public function test_newLine() {

        $clio = new ClioStub(Mode::VT100);
        $output = ClioStub::$startupSequencePrintable;
        // ensure the newline goes out X times
        $clio->newLine()->newLine();
        $output .= "\n\n";

        $clio->newLine(2);
        $output .= "\n\n";


        $this->expectOutputString($output);
        return;



    }


    /**
     * public function prompt($text)
     *
     * Prompt for a value.
     *
     * param $text - the prompt string
     * return $this
     */
    public function test_prompt() {

        // Create a Subject object and attach the mocked
        // Observer object to it.
        $clio = new ClioStub(Mode::VT100);
        $clio->setAnswer(" ");
        $clio->setAnswer(" ");

        $clio->prompt("something");
        $clio->prompt('something');

        $this->expectOutputString(ClioStub::$startupSequencePrintable);

        // ask the terminal to prompt and return the answer
        //return $this->terminal->prompt($text);
    }

    /**
     * public function promptWithDefault($text, $default)
     *
     * Prompt for a value.
     *
     * param string $text - the prompt string
     * param string $default - the default value
     *
     * return string | null, it will return a string if it is different from the default
     */
    public function test_promptWithDefault() {

        $clio = new ClioStub(Mode::VT100);

        // answer without default
        $clio->setAnswer([""]);
        $answer = $clio->prompt("Question");
        $this->assertEquals("",$answer);

        $clio->setAnswer(["one"]);
        $answer = $clio->prompt("Question");
        $this->assertEquals("one",$answer);

        // answer by hitting return
        $clio->setAnswer([""]);
        $answer = $clio->prompt("Question","Answer");
        $this->assertEquals("Answer",$answer);

        // answer by entering some blanks
        $clio->setAnswer(["   "]);
        $answer = $clio->prompt("Question","Answer");
        $this->assertEquals("Answer",$answer);

        // answer with the same answer
        $clio->setAnswer(["Answer"]);
        $answer = $clio->prompt("Question","Answer");
        $this->assertEquals("Answer",$answer);

        // answer with the same answer
        $clio->setAnswer(["answer"]);
        $answer = $clio->prompt("Question","Answer");
        $this->assertEquals("answer",$answer);

        // answer with a different answer
        $clio->setAnswer(["Dnswer"]);
        $answer = $clio->prompt("Question","Answer");
        $this->assertEquals("Dnswer",$answer);

        // answer by hitting return
        $clio->setAnswer(["Answer is OK"]);
        $answer = $clio->prompt("Question","Answer");
        $this->assertEquals("Answer is OK",$answer);



        $this->expectOutputString("\\e[H\\e[2J");



    }

    /**
     * public function promptSelect($intro, $choices, $default)
     *
     * Prompt with a given set of choices, with highlighting showing the characters needed
     *
     * param string $intro - the beginning of the prompt
     * param string[] $choices - the available choices
     * param string $default - the default
     * return null|string - return null if the default is chosen, otherwise the answer
     */
    public function test_promptSelect()  {
        $clio = new ClioStub(Mode::VT100);
        $output = ClioStub::$startupSequencePrintable;

        // answer with default
        $clio->setAnswer([""]);
        $answer = $clio->promptSelect("Intro",["one","two"],"four");
        $this->assertEquals("four",$answer);

        $output .= "\\e[30;107mIntro  \\e[47m  o\\e[107mne\\e[0m \\e[30;47m  t\\e[107mwo\\e[0m [four]";

        $clio->newLine();
        $output .= "\n";

        // answer with just one character
        $clio->setAnswer(["t"]);
        $answer = $clio->promptSelect("Intro",["one","two"]);
        $this->assertSame("two",$answer);
        $output .= "\\e[30;107mIntro  \\e[47m  o\\e[107mne\\e[0m \\e[30;47m  t\\e[107mwo\\e[0m ";

        $clio->newLine();
        $output .= "\n";

        $this->expectOutputString($output);
    }

    /**
     * public function promptForYes($text)
     *
     * Prompt for y/n, and return true if y, ye, or yes is answered
     *
     * param string $text
     * return bool
     */
    public function test_promptForYes() {
        $clio = new ClioStub(Mode::VT100);

        // answer with a simple yes
        $clio->setAnswer(["yes"]);
        $answer = $clio->promptForYes("");
        $this->assertSame(true,$answer);

        // answer with a simple ye
        $clio->setAnswer(["ye"]);
        $answer = $clio->promptForYes("");
        $this->assertSame(true,$answer);

        // answer with a simple y
        $clio->setAnswer(["y"]);
        $answer = $clio->promptForYes("");
        $this->assertSame(true,$answer);

        // answer with an empty string
        $clio->setAnswer([""]);
        $answer = $clio->promptForYes("");
        $this->assertSame(false,$answer);

        $clio->setAnswer(["no"]);
        $answer = $clio->promptForYes("");
        $this->assertSame(false,$answer);

        $this->expectOutputString("\\e[H\\e[2J");



    }

    /**
     * public function stripMarkup($text)
     *
     * Strip out the currently defined markup symbols from the text
     *
     * param string $text
     * return string
     */
    public function test_stripMarkup() {

        $clio = new Clio(Mode::VT100);
        $clio->addMarkupDefinition("!!", (new Style()));

        // just make sure it works
        $answer = $clio->stripMarkupSymbols("!!one!!two!!");
        $this->assertEquals("onetwo",$answer);

    }

    /**
     * public function justify($text, $justification, $width = 0)
     *
     * Justify text
     *
     * param string $text - the text to justify
     * param string | int $justification - if a string, it can be "left", "RIGHT", etc..., if an int, just needs to be a Justification constant
     * param int $width - the width to fill, if zero it will fill whatever width of the overall HTML page
     * return string
     */
    public function test_justify() {

        // ensure that the width is switched to the HTML width if set a zero
        $clio = new Clio(Mode::VT100);
        $clio->setWidth(20);
        $text = $clio->justify("Sun","right",10);
        $this->assertSame("       Sun",$text);

        $text = $clio->justify("Sun","right");
        $this->assertSame("                 Sun", $text);

        $this->expectOutputString(ClioStub::$startupSequence);


    }

    /**
     * public function wordwrap($text, $width = 0, $appendLastNewLine = true)
     *
     * Wordwrap text
     *
     * param string $text - the text to wordwrap
     * param int $width - the width to do the wrapping
     * param boolean $appendLastNewLine - whether to attach a newline to the end of the
     *                                     of the last character
     *
     * return string - the text with \n's within for the wordwrapping as well as extra spaces on
     *                  subsequent lines if deeper margin was set
     */
    public function test_wordwrap() {

        // ensure the wordwrap switches to the HTML width if it is zero
        $clio = new Clio(Mode::VT100);
        $clio->setWidth(20);

        $text = $clio->wordwrap("This is some long text that will be word wrapped",10);
        $this->assertSame("This is\nsome long\ntext that\nwill be\nword\nwrapped\n",$text);

        $text = $clio->wordwrap("This is some long text that will be word wrapped");
        $this->assertSame("This is some long\ntext that will be\nword wrapped\n",$text);

        // append new line
        $text = $clio->wordwrap("Simple",10,true);
        $this->assertSame("Simple\n",$text);


        $this->expectOutputString(ClioStub::$startupSequence);


    }


    /**
     * public function display($text, StyleInterface $temporaryStyle = null)
     *
     * Display text, display text in styles triggered by markup or the temporary style
     *
     * param string $text - with markup and \n's
     * param StyleInterface $temporaryStyle - style to use for the duration of this text
     *
     * return $this
     */

    public function test_display_simple() {

        $clio = new ClioStub(Mode::VT100);
        $output = ClioStub::$startupSequencePrintable;

        // test the base styling
        // empty string
        $text = "";
        $clio->display($text);

        // empty string
        $text = "This is some text.";
        $clio->display($text);
        $output .= $text;

        $this->expectOutputString($output);

    }

    public function test_display_default_markup() {

        $clio = new ClioStub(Mode::VT100);
        $output = ClioStub::$startupSequencePrintable;

        $text = "This is __underlined__ and **bold**";
        $clio->display($text)->newLine();
        $output .= "This is \\e[4munderlined\\e[0m and \\e[1mbold\\e[0m\n";


        // test chaining
        $clio->display("one ")->display("**two**")->newLine();
        $output .= "one \\e[1mtwo\\e[0m\n";

        // put in some newlines as if wordwrap had been called
        $clio->display("**bold**\n")->display("__underline__\n");
        $output .= "\\e[1mbold\\e[0m\n\\e[4munderline\\e[0m\n";

        $this->expectOutputString($output);

    }

    public function test_display_new_markup() {
        $clio = new ClioStub(Mode::VT100);
        $output = ClioStub::$startupSequencePrintable;

        // add yellow markup
        $yellow = (new Style())->setTextColor("yellow");
        $clio->addMarkupDefinition("!",$yellow);

        $text = "This is !yellow! text.";
        $clio->display($text)->newLine();
        $output .= "This is \\e[93myellow\\e[0m text.\n";

        $this->expectOutputString($output);
    }

    public function test_display_do_not_close_style() {
        $clio = new ClioStub(Mode::VT100);
        $output = ClioStub::$startupSequencePrintable;

        $yellow = (new Style())->setTextColor("yellow");
        $clio->addMarkupDefinition("!",$yellow);

        // don't close the styling
        $text = "This is !yellow text continuing...";
        $clio->display($text)->newLine();
        $output .= "This is \\e[93myellow text continuing...\n";

        $this->expectOutputString($output);
    }

    public function test_display_pancake_markup() {

        $clio = new ClioStub(Mode::VT100);
        $output = ClioStub::$startupSequencePrintable;

        $yellow = (new Style())->setTextColor("yellow");
        $clio->addMarkupDefinition("!",$yellow);

        $blue = (new Style())->setFillColor("blue");
        $clio->addMarkupDefinition("<blue>",$blue);

        // don't close the styling
        $text = "This is !yellow text continuing...";
        $clio->display($text)->newLine();
        $output .= "This is \\e[93myellow text continuing...\n";

        // add some more styling and allow the previous styling to come through
        $text = "Yellow text <blue>filled with blue<blue> now back to yellow.";
        $clio->display($text)->newLine();

        $output .= "Yellow text \\e[44mfilled with blue\\e[0;93m now back to yellow.\n";

        // start fresh
        $clio->clear();

        // overlay some predefined bold
        $text = "**This is !yellow** and !<blue>blue<blue> text.";
        $clio->display($text)->newLine();
        $output .= "\\e[0m\\e[1mThis is \\e[93myellow\\e[0;93m and \\e[0m\\e[44mblue\\e[0m text.\n";

        $this->expectOutputString($output);

    }
    public function test_display() {


        $clio = new ClioStub(Mode::VT100);
        $output = ClioStub::$startupSequencePrintable;

        $blue = (new Style())->setFillColor("blue");
        $clio->addMarkupDefinition("<blue>",$blue);


        // width of 15
        $clio = new ClioStub(Mode::VT100);
        $clio->setWidth(15);
        $output .= ClioStub::$startupSequencePrintable;

        // wordwrap
        $clio->display($clio->wordwrap("This is some long text, that is going to be wrapped."));
        $output .= "This is some\n";
        $output .= "long text, that\n";
        $output .= "is going to be\n";
        $output .= "wrapped.\n";


        // margin of 2
        $clio = new ClioStub(Mode::VT100);
        $clio->setWidth(15);
        $output .= ClioStub::$startupSequencePrintable;


        $clio->display($clio->wordwrap("This is some long text, that is going to be wrapped."));
        $output .= "This is some\n";
        $output .= "long text, that\n";
        $output .= "is going to be\n";
        $output .= "wrapped.\n";


        // width of 15, margin of 2 and markup
        $clio->addMarkupDefinition("<blue>",$blue);

        $clio->display($clio->wordwrap("Going to start some <blue>blue text that will go on for awhile<blue> no more blue."));
        $output .= "Going to start\n";
        $output .= "some \\e[44mblue text\n";
        $output .= "that will go on\n";
        $output .= "for awhile\\e[0m no\n";
        $output .= "more blue.\n";


        // finally width of 15, margin of 2, default text and fill and markup
        $clio = new ClioStub(Mode::VT100,"white","black");
        $clio->setWidth(15);
        $clio->addMarkupDefinition("<blue>",$blue);
        $output .= ClioStub::$startupSequencePrintable . "\\e[97;40m";


        $clio->display($clio->wordwrap("Going to start **some <blue>blue** text that will go on for awhile<blue> no more blue."));
        $output .= "Going to start\n";
        $output .= "\\e[1msome \\e[44mblue\\e[0;97;44m text\n";
        $output .= "that will go on\n";
        $output .= "for awhile\\e[40m no\n";
        $output .= "more blue.\n";

        $this->expectOutputString($output);
        return;


    }



}
