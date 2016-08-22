<?php

use ANSI\TerminalStateInterface;
use PHPUnit\Framework\TestCase;

use ANSI\BasicTerminal;
use ANSI\Color\Color;
use ANSI\Color\Mode;

use Clio\Style\Style;



class StyleTest extends TestCase
{

    public $CSI = null;
    public $CSE = null;
    public $clear = null;

    private $red;
    private $black;
    private $cyan;
    private $yellow;
    private $blue;
    private $green;
    private $magenta;


    public function setUp()
    {
        $this->CSI = chr(27) . "[";
        $this->CSE = "m";
        $this->clear = $this->CSI . "0" . $this->CSE;

        $this->red      = new Color("red");
        $this->black    = new Color("black");
        $this->cyan     = new Color("cyan");
        $this->yellow   = new Color("yellow");
        $this->blue     = new Color("blue");
        $this->magenta  = new Color("magenta");
        $this->green    = new Color("green");


    }

    public function tearDown()
    {

    }

    /**
     * __construct($bold = null, $underscore = null, ColorInterface $textColor = null, ColorInterface $fillColor = null)
     *
     * Style constructor.
     * param boolean | null $bold
     * param boolean | null $underscore
     * param ColorInterface | null $textColor
     * param ColorInterface | null $fillColor
     *
     * return $this
     */
    public function test__construct() {

        // default style
        $style = new Style();
        $this->assertSame(null, $style->getBold());
        $this->assertSame(null, $style->getUnderscore());
        $this->assertSame(null, $style->getTextColor());
        $this->assertSame(null, $style->getFillColor());


        // chaining
        $style = (new Style())->setBold(true);
        $this->assertTrue($style->getBold());


        // bold
        $style = new Style(true);
        $this->assertTrue($style->getBold());
        $this->assertNull($style->getUnderscore());
        $this->assertNull($style->getTextColor());
        $this->assertNull($style->getFillColor());

        // underscore
        $style = new Style(null, true);
        $this->assertNull($style->getBold());
        $this->assertTrue($style->getUnderscore());
        $this->assertNull($style->getTextColor());
        $this->assertNull($style->getFillColor());

        // text colors
        $style = new Style(null, null, new Color("ANSIbright Green"),null);
        $this->assertNull($style->getBold());
        $this->assertNull($style->getUnderscore());
        $this->assertNull($style->getFillColor());
        $this->assertNull($style->getUnderscore());
        $this->assertSame("ansibrightgreen",$style->getTextColorName());
        $textColor = $style->getTextColor();
        $this->assertSame([0,255,0],$textColor->getRGB());
        $this->assertSame(10,$textColor->getXTermCode());
        $this->assertSame(92,$textColor->getANSICode());


        // fill color
        $style = new Style(null, null, null, new Color("ANSIbright Green"));
        $this->assertSame("ansibrightgreen",$style->getFillColorName());
        $this->assertNull($style->getBold());
        $this->assertNull($style->getUnderscore());
        $this->assertNull($style->getTextColor());
        $fillColor = $style->getFillColor();
        $this->assertSame([0,255,0],$fillColor->getRGB());
        $this->assertSame(10,$fillColor->getXTermCode());
        $this->assertSame(92,$fillColor->getANSICode());


        // all of them
        $style = new Style(false, true, new Color("blue"), new Color("red"));
        $this->assertFalse($style->getBold());
        $this->assertTrue($style->getUnderscore());
        $this->assertSame("blue",$style->getTextColorName());
        $this->assertSame("red",$style->getFillColorName());


    }



    /**
     * initialize(TerminalStateInterface $state)
     *
     * Initialize a style to the state of a terminal
     *
     * param TerminalStateInterface $state
     * return $this
     */
    public function test_initialize() {
        $term = new BasicTerminal(Mode::XTERM);

        // test initial state
        $style = new Style();
        $style->initialize($term->getState());

        $this->assertFalse($style->getBold());
        $this->assertFalse($style->getUnderscore());
        $this->assertNull($style->getTextColor());
        $this->assertNull($style->getFillColor());

        // chaining
        $style = new Style();
        $style->initialize($term->getState())->setBold();
        $this->assertTrue($style->getBold());

        // set the fill color
        $term->setFillColor("blue");
        $style->initialize($term->getState());

        $this->assertFalse($style->getBold());
        $this->assertFalse($style->getUnderscore());
        $this->assertNull($style->getTextColor());
        $this->assertInstanceOf("ANSI\\Color\\Color",$style->getFillColor());
        $this->assertSame("blue",$style->getFillColor()->getName());

        // set the text color
        $term->setBold()->setTextColor("red");
        $style->initialize($term->getState());

        $this->assertTrue($style->getBold());
        $this->assertFalse($style->getUnderscore());
        $this->assertInstanceOf("ANSI\\Color\\Color",$style->getTextColor());
        $this->assertSame("red",$style->getTextColor()->getName());
        $this->assertInstanceOf("ANSI\\Color\\Color",$style->getFillColor());
        $this->assertSame("blue",$style->getFillColor()->getName());

        // set the underscore and clear the text color
        $term->setUnderscore()->setTextColor(null);
        $style->initialize($term->getState());

        $this->assertTrue($style->getBold());
        $this->assertTrue($style->getUnderscore());
        $this->assertNull($style->getTextColor());
        $this->assertInstanceOf("ANSI\\Color\\Color",$style->getFillColor());
        $this->assertSame("blue",$style->getFillColor()->getName());


    }

    /**
     * public function getState()
     * 
     * Return a Terminal State object based on the styling settings
     * NOTE: The styling interface and terminal interfaces are similar
     *       in structure, but not how they work, so they have been
     *       kept separate, terminal states do not have undefined states
     *       bold is either on or off, while styles can pancake on each
     *       other where bold can also be undefined (null).
     *
     * @return TerminalStateInterface
     */
    public function test_getState() {

        // default style
        $style = new Style();
        $state = $style->getState();
        $this->assertInstanceOf("\\ANSI\\TerminalState",$state);
        $this->assertFalse($state->isBold());
        $this->assertFalse($state->isUnderscore());
        $this->assertFalse($state->getTextColor()->isValid());
        $this->assertFalse($state->getFillColor()->isValid());

        // turn off bold and underscore, still should be false
        $style->setUnderscore(false)->setBold(false);
        $state = $style->getState();
        $this->assertInstanceOf("\\ANSI\\TerminalState",$state);
        $this->assertFalse($state->isBold());
        $this->assertFalse($state->isUnderscore());
        $this->assertFalse($state->getTextColor()->isValid());
        $this->assertFalse($state->getFillColor()->isValid());

        // turn on bold and underscore, still should be false
        $style->setBold(true)->setUnderscore(true);
        $state = $style->getState();
        $this->assertInstanceOf("\\ANSI\\TerminalState",$state);
        $this->assertTrue($state->isBold());
        $this->assertTrue($state->isUnderscore());
        $this->assertFalse($state->getTextColor()->isValid());
        $this->assertFalse($state->getFillColor()->isValid());

        // clear bold
        $style->clearBoldStyling();
        $state = $style->getState();
        $this->assertInstanceOf("\\ANSI\\TerminalState",$state);
        $this->assertFalse($state->isBold());
        $this->assertTrue($state->isUnderscore());
        $this->assertFalse($state->getTextColor()->isValid());
        $this->assertFalse($state->getFillColor()->isValid());

        // set the text color
        $style->setTextColor("red");
        $state = $style->getState();
        $this->assertInstanceOf("\\ANSI\\TerminalState",$state);
        $this->assertFalse($state->isBold());
        $this->assertTrue($state->isUnderscore());
        $this->assertSame("red",$state->getTextColor()->getName());
        $this->assertFalse($state->getFillColor()->isValid());

        // clear text, add fill
        $style->clearTextColor()->setFillColor("green");
        $state = $style->getState();
        $this->assertInstanceOf("\\ANSI\\TerminalState",$state);
        $this->assertFalse($state->isBold());
        $this->assertTrue($state->isUnderscore());
        $this->assertFalse($state->getTextColor()->isValid());
        $this->assertSame("green",$state->getFillColor()->getName());

        // set all properties
        $style->setBold()->setUnderscore()->setTextColor("yellow")->setFillColor("orange");
        $state = $style->getState();
        $this->assertInstanceOf("\\ANSI\\TerminalState",$state);
        $this->assertTrue($state->isBold());
        $this->assertTrue($state->isUnderscore());
        $this->assertSame("yellow",$state->getTextColor()->getName());
        $this->assertSame("orange",$state->getFillColor()->getName());

        // finally clear everything
        $style->clearBoldStyling()->clearUnderscoreStyling()->clearTextColor()->clearFillColor();
        $state = $style->getState();
        $this->assertInstanceOf("\\ANSI\\TerminalState",$state);
        $this->assertFalse($state->isBold());
        $this->assertFalse($state->isUnderscore());
        $this->assertFalse($state->getTextColor()->isValid());
        $this->assertFalse($state->getFillColor()->isValid());

    }
    

    /**
     * setMembersTo(StyleInterface $style)
     *
     * This is provided in addition to cloning.  This allows a child class to clone an incoming style
     * into the parent class or itself (this is one vote for php to add copy constructors!)
     *
     * param StyleInterface $style
     */
    public function test_overrideMembersTo() {

        // set up the style that will override
        $overridingStyle = new Style();

        // set up a style with everything set
        $style = new Style();
        $style->setUnderscore();
        $style->setBold();
        $style->setTextColor($this->cyan);
        $style->setFillColor($this-> yellow);


        // now copy the overriding style, which actually will do nothing, since nothing has been set
        $style->overrideMembersTo($overridingStyle);

        $this->assertSame(true, $style->getBold());
        $this->assertSame(true, $style->getUnderscore());
        $this->assertSame("cyan", $style->getTextColorName());
        $this->assertSame("yellow", $style->getFillColorName());


        // now set up some of the characteristics
        $overridingStyle->setBold(false);
        $overridingStyle->setTextColor($this->red);


        // now copy the overriding style, which will override bold, text color and width
        $style->overrideMembersTo($overridingStyle);
        $this->assertSame(false, $style->getBold());
        $this->assertSame(true, $style->getUnderscore());
        $this->assertSame("red", $style->getTextColorName());
        $this->assertSame("yellow", $style->getFillColorName());



        // now set up the rest of the characteristics
        $overridingStyle->setUnderscore(false);
        $overridingStyle->setFillColor("teal");


        // now copy the overriding style, which will override bold, text color and width
        $style->overrideMembersTo($overridingStyle);
        $this->assertSame(false, $style->getBold());
        $this->assertSame(false, $style->getUnderscore());
        $this->assertSame("red", $style->getTextColorName());
        $this->assertSame("teal", $style->getFillColorName());
        
        $overridingStyle->setTextColor("blue");
        // make sure style 2 hasn't changed
        $this->assertSame("red", $style->getTextColorName());

        // chaining
        // now copy the overriding style, which will override bold, text color and width
        $style->overrideMembersTo($overridingStyle)->setBold();
        $this->assertSame(true, $style->getBold());
        $this->assertSame(false, $style->getUnderscore());
        $this->assertSame("blue", $style->getTextColorName());
        $this->assertSame("teal", $style->getFillColorName());
        
    }





    /**
     * function clearStyling()
     *
     * Clear out all formatting
     * return $this
     */
    public function test_clearStyling() {

        // test chaining
        $answer = (new Style())->setTextColor("red")->clearStyling()->getTextColor();
        $this->assertNull($answer);

        // give all the styles a value
        $style = new Style();
        $style->setTextColor($this->black)->setFillColor($this->yellow)->setBold(true)->setUnderscore(true);

        // check they are all in place
        $this->assertEquals("black",$style->getTextColorName());
        $this->assertEquals("yellow",$style->getFillColorName());
        $this->assertTrue($style->getBold());
        $this->assertTrue($style->getUnderscore());

        // now clear it
        $style->clearStyling();

        // assure all styles are cleared out
        $this->assertEquals(null,$style->getTextColor());
        $this->assertEquals(null,$style->getFillColor());
        $this->assertNull($style->getBold());
        $this->assertNull($style->getUnderscore());

    }

    /////////////////////////////////////////////////////////////////////////////////////////
    //                                     font styling                                    //
    /////////////////////////////////////////////////////////////////////////////////////////

    /**
     * setBold($on = true)
     *
     * Setter for bold
     * param boolean $on
     * return $this
     */
    public function test_setBold() {

        $style = new Style();

        // set to true and chaining
        $answer = $style->setBold(true)->isBoldOn();
        $this->assertTrue($answer);

        // default
        $answer = $style->setBold()->isBoldOn();
        $this->assertTrue($answer);

        // false
        $answer = $style->setBold(false)->isBoldOn();
        $this->assertFalse($answer);


        // null which is false
        $answer = $style->setBold(null)->isBoldOn();
        $this->assertFalse($answer);
    }

    /**
     * function clearBoldStyling()
     * 
     * Clear any bold styling, it will have no effect, nor override
     */
    public function test_clearBoldStyling()  {
        
        $style = new Style();

        // set to true and chaining
        $answer = $style->setBold(true)->isBoldOn();
        $this->assertTrue($answer);
        
        $answer = $style->clearBoldStyling()->getBold();
        $this->assertNull($answer);
        
    }

    /**
     * getBold()
     * Getter for bold
     * return boolean - whether bold is turned on or off
     */
    public function test_getBold() {

        $style = new Style();

        // first out the door
        $answer = $style->getBold();
        $this->assertNull($answer);

        // true
        $answer = $style->setBold(true)->getBold();
        $this->assertTrue($answer);

        // false
        $answer = $style->setBold(false)->getBold();
        $this->assertFalse($answer);
        
        // null
        $answer = $style->setBold(null)->getBold();
        $this->assertNull($answer);

    }

    /**
     * isBoldOn()
     *
     * Getter for bold
     * return boolean
     */
    public function test_isBoldOn() {

        $style = new Style();

        // bold is null (default)
        $answer = $style->isBoldOn();
        $this->assertFalse($answer);

        // bold is on
        $answer = $style->setBold(true)->isBoldOn();
        $this->assertTrue($answer);

        // bold is off
        $answer = $style->setBold(false)->isBoldOn();
        $this->assertFalse($answer);
        
        // bold is null
        $answer = $style->clearBoldStyling()->isBoldOn();
        $this->assertFalse($answer);
        
    }


    /**
     * setUnderscore($on = true)
     *
     * Set the underscore setting
     * param boolean $on
     * return $this
     */
    public function test_setUnderscore() {

        $style = new Style();

        // true and chaining
        $answer = $style->setUnderscore(true)->isUnderscoreOn();
        $this->assertTrue($answer);

        // default
        $answer = $style->setUnderscore()->isUnderscoreOn();
        $this->assertTrue($answer);

        // false
        $answer = $style->setUnderscore(false)->isUnderscoreOn();
        $this->assertFalse($answer);

        // null - false
        $answer = $style->setUnderscore(null)->isUnderscoreOn();
        $this->assertFalse($answer);
    }

    /**
     * function clearUnderscoreStyling()
     * 
     * Clear any underscore styling, it will have no effect, nor override
     */
    public function test_clearUnderscoreStyling() {
        
        $style = new Style();

        // set to true and chaining
        $answer = $style->setUnderscore(true)->isUnderscoreOn();
        $this->assertTrue($answer);

        $answer = $style->clearUnderscoreStyling()->getUnderscore();
        $this->assertNull($answer);
        
    }

    /**
     * getUnderscore()
     *
     * Getter for the underscore setting
     * return boolean - whether underscore is turned on
     */
    public function test_getUnderscore() {

        $style = new Style();

        // first out the door
        $answer = $style->getUnderscore();
        $this->assertNull($answer);

        // true
        $answer = $style->setUnderscore(true)->getUnderscore();
        $this->assertTrue($answer);

        // false
        $answer = $style->setUnderscore(false)->getUnderscore();
        $this->assertFalse($answer);

        // null
        $answer = $style->setUnderscore(null)->getUnderscore();
        $this->assertNull($answer);
    }

    /**
     * isUnderscoreOn()
     *
     * Whether the underscore is turned on
     * return boolean
     */
    public function test_isUnderscoreOn() {

        $style = new Style();

        // underscore is off (default)
        $answer = $style->isUnderscoreOn();
        $this->assertFalse($answer);

        // true
        $answer = $style->setUnderscore(true)->isUnderscoreOn();
        $this->assertTrue($answer);

        // false
        $answer = $style->setUnderscore(false)->isUnderscoreOn();
        $this->assertFalse($answer);
        
        // null
        $answer = $style->clearUnderscoreStyling()->isUnderscoreOn();
        $this->assertFalse($answer);
    }



    /**
     * normal()
     *
     * Turn off both underscore and bold
     * return $this
     */
    public function test_normal() {

        $style = new Style();

        $style->normal();
        $this->assertFalse($style->isBoldOn());
        $this->assertFalse($style->isUnderscoreOn());

        // set both underscore and bold
        $style->setUnderscore()->setBold();
        $bold = $style->isBoldOn();
        $underscore = $style->isUnderscoreOn();
        $this->assertSame($bold, true);
        $this->assertSame($underscore, true);

        // chaining
        $bold = $style->setBold(true)->normal()->isBoldOn();
        $this->assertSame($bold, false);
    }


    /////////////////////////////////////////////////////////////////////////////////////////
    //                                       Colors                                        //
    /////////////////////////////////////////////////////////////////////////////////////////

    /**
     * function setTextColor($color)
     *
     * Set the text color
     * param ColorInterface | string | int | array $color
     *      - ColorInterface - an object implementing the Color Interface
     *      - String - a W3C color index name "darkblue"
     *      - integer - a number between 0-255 for the XTerm escape code
     *      - array - RGB values in the format [R,G,B]
     * return $this
     */
    public function test_setTextColor() {

        $style = new Style();

        // Color object
        // chaining
        $answer = $style->setTextColor($this->blue)->getTextColorName();
        $this->assertSame("blue", $answer);
        
        // name
        $color = $style->setTextColor("cyan")->getTextColor();
        $this->assertSame([0,255,255], $color->getRGB());

        // null
        $color = $style->setTextColor(null)->getTextColor();
        $this->assertSame(null, $color);
        

    }

    /**
     * function getTextColor()
     *
     * Getter for the text color
     * return ColorInterface | null - null if it has been cleared, otherwise the Color object stored
     */
    public function test_getTextColor() {

        $style = new Style();

        // no text color
        $answer = $style->getTextColor();
        $this->assertNull($answer);

        // has text color
        $answer = $style->setTextColor($this->black)->getTextColor();
        $this->assertNotSame($answer, $this->black);
        $this->assertSame("black",$style->getTextColorName());
    }

    /**
     * function getTextColorName()
     *
     * Getter for the text color name
     * return String | null - null if no color defined, or the name
     */
    public function test_getTextColorName() {
        $style = new Style();

        // no text color
        $answer = $style->getTextColorName();
        $this->assertNull($answer);

        // has text color
        $answer = $style->setTextColor($this->black)->getTextColorName();
        $this->assertSame($answer, "black");
    }
    /**
     * hasTextColor()
     *
     * Whether a text color has been defined, or has been cleared
     * return bool
     */
    public function test_hasTextColor() {

        $style = new Style();

        // no text color
        $answer = $style->hasTextColor();
        $this->assertFalse($answer);

        // has text color
        $answer = $style->setTextColor(new Color("red"))->hasTextColor();
        $this->assertTrue($answer);

        // has text color
        $answer = $style->setTextColor(new Color("junk"))->hasTextColor();
        $this->assertTrue($answer);
        
        // no text color
        $answer = $style->clearTextColor()->hasTextColor();
        $this->assertFalse($answer);
        

    }

    /**
     * clearTextColor()
     *
     * Clear out the text color
     * return $this
     */
    public function test_clearTextColor() {


        // test chaining
        $answer = (new Style())->setTextColor($this->blue)->clearTextColor()->getTextColor();
        $this->assertNull($answer);

        // set a color blue
        $style = new Style();
        $style->setTextColor($this->blue);
        $this->assertEquals($this->blue,$style->getTextColor());

        // clear it out
        $style->clearTextColor();

        // ensure the blue is gone
        $this->assertNull($style->getTextColor());

        // make sure it does not clear out the text color
        $style->setTextColor($this->red)->setFillColor($this->blue)->clearFillColor();
        $this->assertNull($style->getFillColor());
        $this->assertSame("red",$style->getTextColorName());


    }


    /**
     * function setFillColor($color)
     *
     * Set the fill color
     * param ColorInterface | string | int | array $color
     *      - ColorInterface - an object implementing the Color Interface
     *      - String - a W3C color index name "darkblue"
     *      - integer - a number between 0-255 for the XTerm escape code
     *      - array - RGB values in the format [R,G,B]
     * return $this
     */
    public function test_setFillColor() {


        $style = new Style();

        // set a valid color and chaining
        $answer = $style->setFillColor($this->blue)->getFillColor();
        $this->assertSame($answer->getName(), "blue");
        $this->assertNotSame($answer,$this->blue);

        // set a valid color and chaining
        $answer = $style->setFillColor("cyan")->getFillColor();
        $this->assertSame($answer->getName(), "cyan");
    }

    /**
     * getFillColor()
     *
     * Getter for fill color
     * return null | int - null if it is cleared, otherwise the color constant defined
     */
    public function test_getFillColor() {

        $style = new Style();

        // no fill color
        $answer = $style->getFillColor();
        $this->assertNull($answer);

        // has fill color
        $answer = $style->setFillColor($this->cyan)->getFillColorName();
        $this->assertSame($answer, "cyan");
    }

    /**
     * function getFillColorName()
     *
     * Getter for the text color name
     * return String | null - null if no color defined, or the name
     */
    public function test_getFillColorName() {
        $style = new Style();

        // no fill color
        $answer = $style->getFillColorName();
        $this->assertNull($answer);

        // has fill color
        $answer = $style->setFillColor("dark blue")->getFillColorName();
        $this->assertSame($answer, "darkblue");
    }
    /**
     * hasFillColor()
     *
     * Whether a fill color has been defined
     * return bool
     */
    public function test_hasFillColor() {

        $style = new Style();

        // no fill color
        $answer = $style->hasFillColor();
        $this->assertFalse($answer);

        // has fill color
        $answer = $style->setFillColor($this->black)->hasFillColor();
        $this->assertTrue($answer);
        
        // null
        $answer = $style->clearFillColor()->hasFillColor();
        $this->assertFalse($answer);

    }

    /**
     * clearFillColor()
     *
     * Clear out the text color
     * return $this
     */
    public function test_clearFillColor() {


        // test chaining
        $answer = (new Style())->setFillColor($this->blue)->clearFillColor()->getFillColor();
        $this->assertNull($answer);

        // set a color blue
        $style = new Style();
        $style->setFillColor($this->blue);
        $this->assertEquals($this->blue,$style->getFillColor());

        // clear it out
        $style->clearFillColor();

        // ensure the blue is gone
        $this->assertNull($style->getFillColor());

        // make sure it does not clear out the text color
        $style->setTextColor("   brown")->setFillColor("blue")->clearTextColor();
        $this->assertNull($style->getTextColor());
        $this->assertSame("blue",$style->getFillColorName());
    }



    /**
     * function setAlternatingColor($textColor, $fillColor)
     *
     * Set both text and fill colors
     * param ColorInterface | string | int | array $textColor
     * param ColorInterface | string | int | array $fillColor
     *      - String - a W3C color index name "darkblue"
     *      - integer - a number between 0-255 for the XTerm escape code
     *      - array - RGB values in the format [R,G,B]
     * @return $this
     */
    public function test_setColors() {

        $style = new Style();

        // two valid colors and chaining
        $textColor = $style->setColors("green",$this->black)->getTextColor();
        $this->assertSame("green", $textColor->getName());
        $fillColor = $style->getFillColor();
        $this->assertSame("black", $fillColor->getName());



    }

    /**
     * reverseColors()
     *
     * Switch the text and fill colors
     * return $this
     */
    public function test_reverseColors() {

        $style = new Style();

        // valid colors and chaining
        $style->setColors($this->green,$this->magenta);
        $textColor = $style->reverseColors()->getTextColor();
        $this->assertSame("magenta",$textColor->getName());
        $fillColor = $style->getFillColor();
        $this->assertSame("green",$fillColor->getName());


    }



}
