<?php


use ANSI\Color\Color;
use ANSI\Color\Mode;
use Clio\Clio;
use Clio\Style\Style;
use PHPUnit\Framework\TestCase;

class ClioEcho extends Clio {

    /**
     *
     * All output goes to this function
     *
     * @param string $text
     */
    public function output($text) {
        // echo the text
        echo str_replace("\033","\\e",$text);
    }
}


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
        $clio = new ClioEcho();

        // mode
        // default is XTERM
        $clio->setTextColor("red")->outputEscapeSequence();
        $output = "\\e[38;5;9m";

        $this->expectOutputString($output);


        // valid mode
        $clio = new ClioEcho("RGB");
        $clio->setTextColor("red")->outputEscapeSequence();
        $output .= "\\e[38;2;255;0;0m";


        // text color
        $clio = new ClioEcho(Mode::XTERM, "black");
        $clio->outputEscapeSequence();
        $output .= "\\e[38;5;0m";

        // fill color
        $clio = new ClioEcho(Mode::XTERM, null, "black");
        $clio->outputEscapeSequence();
        $output .= "\\e[48;5;0m";

        // both
        $clio = new ClioEcho(Mode::XTERM, "red", "black");
        $clio->outputEscapeSequence();
        $output .= "\\e[38;5;9;48;5;0m";


        // chaining
        $clio = (new Clio(Mode::XTERM))->setBold();
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
        $this->expectOutputString("hello");

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
        $clio = new ClioEcho();
        $this->assertNull($clio->getDefaultTextColor());
        $this->assertNull($clio->getDefaultTextColor());
        $clio->clear(true);

        // clear should go out
        $output = "\\e[0m";

        $clio = new ClioEcho(19000,"Floral White", "Dark Cyan");
        $clio->clear(true);
        $output .= "\\e[0m\\e[38;2;255;250;240;48;2;0;139;139m";


        // should just see the text color go out
        $clio->setTextColor("brown")->clear()->setTextColor("red")->outputEscapeSequence();
        $output .= "\\e[38;2;255;0;0m";



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
        (new ClioEcho())->b()->outputEscapeSequence();

        // ensure the text is surrounded with the correct sequence
        $output = "\\e[1m";

        $clio = new ClioEcho();
        $clio->b()->b()->outputEscapeSequence();
        $output .= "\\e[1m";

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
        (new ClioEcho())->u()->outputEscapeSequence();

        // ensure the text is surrounded with the correct sequence
        $output = "\\e[4m";

        $clio = new ClioEcho();
        $clio->u()->u()->outputEscapeSequence();
        $output .= "\\e[4m";

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

        $clio = new ClioEcho();

        // start with a new color
        $clio->textColor("aquamarine")->outputEscapeSequence();
        $output = "\\e[38;5;122m";


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
        $clio = (new ClioEcho(Mode::RGB))->setTextColor("dark khaki")->b();
        $clio->outputEscapeSequence();
        $output .= "\\e[1;38;2;189;183;107m";


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

        $clio = new ClioEcho(Mode::VT100);

        // set up a text color of ansiblack
        $clio->textColor("ansiblack")->outputEscapeSequence();
        $output = "\\e[30m";

        // clear it out
        $clio->clearTextColor()->outputEscapeSequence();
        $output .= "\\e[0m";

        // now set up a fill and a text color, then clear it, make sure the fill is still preserved
        $clio->fillColor("ansiblack")->textColor("ansiblue")->b();
        $clio->clearTextColor()->outputEscapeSequence();
        $output .= "\\e[1;40m";

        // default colors
        $clio = new ClioEcho("xterm","lime","orange");
        $clio->outputEscapeSequence();
        $output .= "\\e[38;5;10;48;5;214m";

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

        $clio = new ClioEcho();

        // start with a new color
        $clio->fillColor("aquamarine")->outputEscapeSequence();
        $output = "\\e[48;5;122m";


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
        $clio = (new ClioEcho(Mode::RGB))->setFillColor("dark khaki")->b();
        $clio->outputEscapeSequence();
        $output .= "\\e[1;48;2;189;183;107m";


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

        $clio = new ClioEcho(Mode::VT100);

        // set up a fill color of black
        $clio->fillColor("ansiblack")->outputEscapeSequence();
        $output = "\\e[40m";

        // clear it
        $clio->clearFillColor()->outputEscapeSequence();
        $output .= "\\e[0m";

        // set up a text color of black and fill of blue, clear the blue fill and ensure the text is preserved
        $clio->textColor("ansiblack")->fillColor("ansiblue")->b();
        $clio->clearFillColor()->outputEscapeSequence();
        $output .= "\\e[1;30m";

        // default colors
        $clio = new ClioEcho("xterm","lime","orange");
        $clio->outputEscapeSequence();
        $output .= "\\e[38;5;10;48;5;214m";

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
        $clio = new ClioEcho();
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
    public function test_setStyle() {

        // empty style
        $style = new Style();
        $clio = new ClioEcho();

        $clio->setStyle($style)->outputEscapeSequence();
        $output = "";

        $style->setBold();
        $clio->setStyle($style)->outputEscapeSequence();
        $output .= "\\e[1m";

        $style->setUnderscore()->setTextColor(Color::red())->setFillColor("black");
        $clio->setStyle($style)->outputEscapeSequence();
        $output .= "\\e[4;38;5;9;48;5;0m";

        // turn off underscore, ensure the rest is preserved
        $style->setUnderscore(false);
        $clio->setStyle($style)->outputEscapeSequence();
        $output .= "\\e[0;1;38;5;9;48;5;0m";


        $this->expectOutputString($output);


    }


    /**
     * public function style($text, StyleInterface $temporaryStyle)
     * 
     * Display the text.  This does not jump down to a new line.
     * If a temporary style is used, only the values that are not null will be used.
     *
     * param $text
     * param StyleInterface|null $style - if defined, a temporary style to use for this text only
     *
     * return $this
     */
    public function test_displayInStyle() {

        $clio = new ClioEcho();

        // no styling anywhere
        $style = new Style();
        $clio->style("a",$style);

        $output = "a";

        // no styling in place, add underscore
        $style->setUnderscore();
        $clio->style("b",$style)->display("c");

        $output .= "\\e[4mb\\e[0mc";

        // add bolding to styling in place, add text color to temporary styling
        $clio->b();
        $style->setTextColor("lime");
        $clio->style("d",$style)->display("e");

        $output .= "\\e[1;4;38;5;10md\\e[0;1me";

        // keep styling in place, but tell the temporary style to turn off bolding
        $style->setBold(false);
        $clio->style("f",$style)->display("g");

        $output .= "\\e[0;4;38;5;10mf\\e[0;1mg";

        // put in complete styling in place, let the temporary style completely override it
        $clio->u()->b()->setTextColor("lightslategray")->setFillColor(Color::black());
        $style->setUnderscore(false)->setBold(false)->setTextColor("Purple")->setFillColor("PaleGreen");
        $clio->style("h",$style)->display("i");

        $output .= "\\e[0;38;5;90;48;5;120mh\\e[1;4;38;5;102;48;5;0mi";

        // let the temporary style have no effect
        $empty = new Style();
        $clio->style("j",$empty)->display("k");

        $output .= "jk";

        // have the temporary style just change the text color
        $empty->setTextColor("purple");
        $clio->style("purple",$empty)->display("no purple");

        $output .= "\\e[38;5;90mpurple\\e[38;5;102mno purple";



        $this->expectOutputString($output);

    }

    /**
     * public function styleLine($text, StyleInterface $style)
     *
     * Send out text with the specified style and hit the carriage return
     *
     * param string $text
     * param StyleInterface $style
     * return $this
     */
    public function test_styleLine() {

        // this function merely call style and newline, so just ensure the calls work

        $clio = new ClioEcho();

        // create a style
        $style = (new Style())->setBold();

        // ensure the internal calls work properly
        $clio->styleLine("Here is a bold line.",$style);
        $output = "\\e[1mHere is a bold line.\\e[0m\n";

        // chaining
        $clio->styleLine("a",$style)->styleLine("b",$style);
        $output .= "\\e[1ma\\e[0m\n\\e[1mb\\e[0m\n";

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
        $output = "Hello World!";

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

        $clio = new ClioEcho();


        // ensure the internal calls work properly
        $clio->line("Here is a line.");
        $output = "Here is a line.\n";

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
        $output = "\n";

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
        /**
         * @var Clio $stub
         */
        $stub = $this->getMockBuilder('ClioEcho')->setMethods(["readUserInput"])->getMock();
        /** @noinspection PhpUndefinedMethodInspection */
        $stub->method('readUserInput')
            ->will($this->returnArgument(0));

        // not a lot to test, just chaining and the fact that it doesn't blow up
        $stub->pause()->pause();


    }


}
