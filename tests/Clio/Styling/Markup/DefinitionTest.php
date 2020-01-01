<?php


use Clio\Styling\Markup\Definition;
use Clio\Styling\Markup\Markup;
use Clio\Styling\Style;
use PHPUnit\Framework\TestCase;

class DefinitionTest extends TestCase
{


    public function setUp(): void
    {


    }

    public function tearDown(): void
    {

    }

    /**
     * protected function merge($originalText, $wrappedText)
     *
     * Merge together two strings, the original text which might have markup and
     * the wrapped version of it which has had the markup removed, but has been wrapped
     *
     * param string $originalText
     * param string $wrappedText
     *
     * return string - the new string with the original text, but the newlines inserted
     *                  in the proper place
     */
    public function test_merge() {

        // get a reflection of the class
        $class = new ReflectionClass("Clio\\Styling\\Markup\\Definition");

        // get the method of interest
        $method = $class->getMethod("merge");

        // make that method accessible
        $method->setAccessible(true);

        // no difference between original and wordwrapped
        $definition = new Definition();
        $original = "A green cat meowed.";
        $wordwrapped = "A green cat meowed.";

        $answer = $method->invokeArgs($definition,[$original,$wordwrapped]);
        $this->assertSame($original,$answer);

        // add a newline to the beginning
        $original = "A green cat meowed.";
        $wordwrapped = "\nA green cat meowed.";

        $answer = $method->invokeArgs($definition,[$original,$wordwrapped]);
        $this->assertSame($wordwrapped,$answer);

        // add a newline to the middle
        $original = "A green cat meowed.";
        $wordwrapped = "A g\nreen cat meowed.";

        $answer = $method->invokeArgs($definition,[$original,$wordwrapped]);
        $this->assertSame($wordwrapped,$answer);


        // add a newline to the end
        $original = "A green cat meowed.";
        $wordwrapped = "A green cat meowed.\n";

        $answer = $method->invokeArgs($definition,[$original,$wordwrapped]);
        $this->assertSame($wordwrapped,$answer);


        // add one markup ////////
        $definition->addMarkup("*",(new Style()));

        // redo the last run just to make sure the markup doesn't change anything
        $original = "A green cat meowed.";
        $wordwrapped = "A green cat meowed.\n";

        $answer = $method->invokeArgs($definition,[$original,$wordwrapped]);
        $this->assertSame($wordwrapped,$answer);


        // markup at the beginning
        $original = "*A green cat meowed.";
        $wordwrapped = "A green cat meowed.";

        $answer = $method->invokeArgs($definition,[$original,$wordwrapped]);
        $this->assertSame($original,$answer);

        // markup in the middle
        $original = "*A green *cat meowed.";
        $wordwrapped = "A green cat meowed.";

        $answer = $method->invokeArgs($definition,[$original,$wordwrapped]);
        $this->assertSame($original,$answer);

        // markup at the beginning and end
        $original = "*A green cat meowed.*";
        $wordwrapped = "A green cat meowed.";

        $answer = $method->invokeArgs($definition,[$original,$wordwrapped]);
        $this->assertSame($original,$answer);
        
        
        // put both markup and newlines
        $original = "*A g*reen cat meowed.*";
        $wordwrapped = "\nA g\nreen\ncat meowed.\n";
        $answer = $method->invokeArgs($definition,[$original,$wordwrapped]);
        $this->assertSame("*\nA g*\nreen\ncat meowed.*\n",$answer);  
        
        // another markup
        $definition->addMarkup("--",(new Style()));

        $original = "*A g*reen cat --meowed.*";
        $wordwrapped = "\nA g\nreen\ncat meowed.\n";
        $answer = $method->invokeArgs($definition,[$original,$wordwrapped]);
        $this->assertSame("*\nA g*\nreen\ncat --meowed.*\n",$answer);

        $original = "*A g*reen cat --meowed.*";
        $wordwrapped = "\n\nA g\nre\nen\ncat meowed.\n";
        $answer = $method->invokeArgs($definition,[$original,$wordwrapped]);
        $this->assertSame("*\n\nA g*\nre\nen\ncat --meowed.*\n",$answer);


    }

    ///////////////////////////////////////////////////////////////////////////////////
    //                              Public Methods                                   //
    ///////////////////////////////////////////////////////////////////////////////////

    /**
     * public function addMarkupDefinition($markup)
     * 
     * Add a markup to the array
     *
     * param Markup $markup
     * return boolean - true if it added OK, false if it was not added
     */
    public function test_addMarkup() {

        $definitions = new Definition();

        // add something other than a Markup object
        $answer = $definitions->addMarkup([134], new Style());
        $this->assertFalse($answer);

        $bold = (new Style())->setBold();

        // add markup and returns true
        $answer = $definitions->addMarkup("bold",$bold);
        $this->assertTrue($answer);

        // add the same markup, return false
        $answer = $definitions->addMarkup("bold",$bold);
        $this->assertFalse($answer);

        // add another markup
        $boldOrangeFill = (new Style())->setBold()->setFillColor("orange");
        $answer = $definitions->addMarkup("boldOrangeFill", $boldOrangeFill);
        $this->assertTrue($answer);

        // add the same markup, return false
        $answer = $definitions->addMarkup("boldOrangeFill", $boldOrangeFill);
        $this->assertFalse($answer);
        
        // add a different markup with the same symbol, return false
        $anyStyle = new Style();
        $answer = $definitions->addMarkup("bold",$anyStyle);
        $this->assertFalse($answer);
    }

    /**
     * public function getSymbols()
     * 
     * Return a list of the symbols for all the markups in the list
     *
     * return string[]
     */
    public function test_getSymbols() {

        $definitions = new Definition();

        // get symbols for an empty list
        $symbols = $definitions->getSymbols();
        $this->assertSame([],$symbols);
        
        // get the one symbol
        $bold = (new Style())->setBold();

        // add markup and returns true
        $answer = $definitions->addMarkup("bold",$bold);
        $this->assertTrue($answer);

        $symbols = $definitions->getSymbols();
        $this->assertSame(["bold"],$symbols);
        
        // get many symbols
        // add another markup
        $boldOrangeFill = (new Style())->setBold()->setFillColor("orange");
        $answer = $definitions->addMarkup("boldOrangeFill",$boldOrangeFill);
        $this->assertTrue($answer);

        $green = (new Style())->setTextColor("green");
        $answer = $definitions->addMarkup("green",$green);
        $this->assertTrue($answer);

        $symbols = $definitions->getSymbols();
        $this->assertSame(["bold","boldOrangeFill","green"],$symbols);

    }

    /**
     * public function getStyling($symbol)
     * 
     * Get the styling that the list creates by combining all
     * styles.
     *
     * param string $symbol
     *
     * return StyleInterface | null (if not found)
     */
    public function test_getStyling() {

        $definitions = new Definition();

        // invalid data
        $answer = $definitions->getStyling([1240]);
        $this->assertNull($answer);

        // get styling on a empty list (returns null)
        $answer = $definitions->getStyling("one");
        $this->assertNull($answer);
        
        // get styling
        $bold = (new Style())->setBold();
        $answer = $definitions->addMarkup("b",$bold);
        $this->assertTrue($answer);

        $style = $definitions->getStyling("b");
        $this->assertSame($bold,$style);
        
        // get styling that doesn't exist on a list
        $underscore = (new Style())->setUnderscore();
        $answer = $definitions->addMarkup("underscore",$underscore);
        $this->assertTrue($answer);

        // now get styling in list of two
        $style = $definitions->getStyling("b");
        $this->assertSame($bold,$style);

        $style = $definitions->getStyling("underscore");
        $this->assertSame($underscore,$style);

        // doesn't exist
        $style = $definitions->getStyling("junk");
        $this->assertNull($style);
        

    }

    /**
     * public function getMarkup($symbol)
     *
     * Get the Markup object related to the symbol
     *
     * param string $symbol
     *
     * return Markup | null (if not found)
     */
    public function test_getMarkup() {
        $definitions = new Definition();

        // invalid data
        $answer = $definitions->getMarkup([1240]);
        $this->assertNull($answer);

        // get styling on a empty list (returns null)
        $answer = $definitions->getMarkup("one");
        $this->assertNull($answer);

        // get styling
        $bold = (new Style())->setBold();
        $answer = $definitions->addMarkup("b",$bold);
        $boldMarkup = new Markup("b",$bold);
        $this->assertTrue($answer);

        $markup = $definitions->getMarkup("b");
        $this->assertEquals($boldMarkup,$markup);

        // get styling that doesn't exist on a list
        $underscore = (new Style())->setUnderscore();
        $underscoreMarkup = new Markup("underscore",$underscore);
        $answer = $definitions->addMarkup("underscore",$underscore);
        $this->assertTrue($answer);

        // now get styling in list of two
        $markup = $definitions->getMarkup("b");
        $this->assertEquals($boldMarkup,$markup);

        $markup = $definitions->getMarkup("underscore");
        $this->assertEquals($underscoreMarkup,$markup);

        // doesn't exist
        $markup = $definitions->getStyling("junk");
        $this->assertNull($markup);
    }


    /**
     * public function findNextMarkup($text, &$markupFound)
     *
     * Find the next position that contains markup
     *
     * param string $text
     * param string $markupFound - the markup that was found at that position
     *
     * return int|null - the position in the text where the markup was found or null if nothing was found
     */
    public function test_findNextMarkup() {

        $text = "12345678901234567890";

        $definitions = new Definition();

        // no markup in array
        $markupFound = null;

        $answer = $definitions->findNextMarkup($text,$markupFound);
        $this->assertNull($answer);
        $this->assertNull($markupFound);

        // markup is in array, but nothing in the string
        $bold = (new Style())->setBold();
        $answer = $definitions->addMarkup("**",$bold);
        $this->assertTrue($answer);

        $underscore = (new Style())->setUnderscore();
        $answer = $definitions->addMarkup("__",$underscore);
        $this->assertTrue($answer);

        $answer = $definitions->findNextMarkup($text,$markupFound);
        $this->assertNull($answer);
        $this->assertNull($markupFound);


        // now there is something in the string
        // at the beginning
        $text = "**12345678901234567890";
        $answer = $definitions->findNextMarkup($text,$markupFound);
        $this->assertSame(0,$answer);
        $this->assertSame("**",$markupFound);

        // in the middle
        $text = "123456789__01234567890";
        $answer = $definitions->findNextMarkup($text,$markupFound);
        $this->assertSame(9,$answer);
        $this->assertSame("__",$markupFound);


        // at the end
        $text = "12345678901234567890__";
        $answer = $definitions->findNextMarkup($text,$markupFound);
        $this->assertSame(20,$answer);
        $this->assertSame("__",$markupFound);

        // more than one
        $text = "123456**7890__1234567890**";
        $answer = $definitions->findNextMarkup($text,$markupFound);
        $this->assertSame(6,$answer);
        $this->assertSame("**",$markupFound);

    }

    /**
     * public function stripMarkupSymbols($text)
     *
     * Strip out any defined markup symbols
     * param string $text
     *
     * return string - the text without any markup symbols
     */
    public function test_stripMarkupSymbols() {

        $definition = new Definition();

        // start with no markup in the definition
        $text = "The quick gray fox jumped over the brown cow";
        $answer = $definition->stripMarkupSymbols($text);
        $this->assertSame($text, $answer);


        // add one markup
        $definition->addMarkup("**",(new Style())->setBold());

        // no markup in the text
        $original = "The quick gray fox jumped over the brown cow";
        $text = $original;
        $answer = $definition->stripMarkupSymbols($text);
        $this->assertSame($text, $answer);

        // now add that markup once at the beginning
        $text = "**The quick gray fox jumped over the brown cow";
        $answer = $definition->stripMarkupSymbols($text);
        $this->assertSame($original, $answer);

        // now add that markup once in the middle
        $text = "The quick gray f**ox jumped over the brown cow";
        $answer = $definition->stripMarkupSymbols($text);
        $this->assertSame($original, $answer);

        // now add that markup once at the end
        $text = "The quick gray fox jumped over the brown cow**";
        $answer = $definition->stripMarkupSymbols($text);
        $this->assertSame($original, $answer);

        // add two copies of the symbols
        $text = "The **quick gray fox jump**ed over the brown cow";
        $answer = $definition->stripMarkupSymbols($text);
        $this->assertSame($original, $answer);

        // add a new markup
        $definition->addMarkup("--",(new Style())->setUnderscore());

        // not there yet
        $text = "The **quick gray fox jump**ed over the brown cow";
        $answer = $definition->stripMarkupSymbols($text);
        $this->assertSame($original, $answer);

        // just one of the new symbol
        $text = "Th--e quick gray fox jumped over the brown cow";
        $answer = $definition->stripMarkupSymbols($text);
        $this->assertSame($original, $answer);

        // now a mishmash
        $text = "**--Th--e qu**ick gray fox jumped over the brown cow--";
        $answer = $definition->stripMarkupSymbols($text);
        $this->assertSame($original, $answer);

    }

    /**
     * public function hasMarkup($text)
     *
     * Tests whether the string has any valid markup
     * param string $text - the text in which to search
     *
     * return boolean
     */
    public function test_hasMarkup() {

        $definition = new Definition();

        // no markup definitions
        $text = "A string.";
        $this->assertFalse($definition->hasMarkup($text));

        $definition->addMarkup("&",(new Style()));
        $this->assertFalse($definition->hasMarkup($text));

        // put markup at the beginning
        $text = "&A string.";
        $this->assertTrue($definition->hasMarkup($text));

        // put markup in the middle
        $text = "A s&tring.";
        $this->assertTrue($definition->hasMarkup($text));

        // put markup at the end
        $text = "A string.&";
        $this->assertTrue($definition->hasMarkup($text));

        // add one more markup
        $definition->addMarkup("!bold!",(new Style())->setBold());

        // still isn't in string
        $text = "A s&tring.";
        $this->assertTrue($definition->hasMarkup($text));

        // now add the new one only
        // still isn't in string
        $text = "A stri!bold!ng.";
        $this->assertTrue($definition->hasMarkup($text));

        // now add both
        $text = "&A stri!bold!ng.";
        $this->assertTrue($definition->hasMarkup($text));

        // neither
        $text = "A string.";
        $this->assertFalse($definition->hasMarkup($text));

    }


    /**
     * public function shortenText($text, $length)
     * 
     * Shorten the text by the length, but leaving any markup symbols
     * param string $text
     * param int $length
     *
     * return string
     */
    public function test_shortenText() {
        
        $definition = new Definition();
        
        // start without markup and no text, no length
        $text = "";
        $answer = $definition->shortenText($text, 0);
        $this->assertSame("",$answer);
        
        // no markup, no text, bigger length
        $answer = $definition->shortenText($text, 20);
        $this->assertSame("",$answer);
        
        // no markup, some text, zero length
        $text = "12345678901234567890";
        $answer = $definition->shortenText($text,0);
        $this->assertSame("", $answer);

        // no markup, some text, smaller
        $text = "12345678901234567890";
        $answer = $definition->shortenText($text,5);
        $this->assertSame("123456789012345",$answer);
        
        // no markup, some text, same size length
        $answer = $definition->shortenText($text,20);
        $this->assertSame("",$answer);
        
        // no markup, some text, bigger length
        $answer = $definition->shortenText($text,25);
        $this->assertSame("",$answer);
        
        // one markup ///////
        $definition->addMarkup("*", (new Style()));

        // one markup, no text, zero length
        $text = "";
        $answer = $definition->shortenText($text,0);
        $this->assertSame("",$answer);           
        
        // one markup, no text, bigger length
        $answer = $definition->shortenText($text,5);
        $this->assertSame("",$answer);
        
        // one markup, text without markup, any length
        $text = "1234567890";
        $answer = $definition->shortenText($text,5);
        $this->assertSame("12345",$answer);       
        
        // one markup, text with markup at the beginning
        $text = "*1234567890";
        $answer = $definition->shortenText($text,5);
        $this->assertSame("*12345",$answer);

        // one markup, text with markup in the middle
        // that will be appended
        $text = "123456*7890";
        $answer = $definition->shortenText($text,7);
        $this->assertSame("123*",$answer);

        // one markup, text with markup in the middle
        // that will be included in the returned string
        $text = "1*234567890";
        $answer = $definition->shortenText($text,7);
        $this->assertSame("1*23",$answer);

        // one markup, text with markup at the end
        $text = "1234567890*";
        $answer = $definition->shortenText($text,1);
        $this->assertSame("123456789*",$answer);

        // two markups
        $definition->addMarkup("--", (new Style()));
        
        // two markups, no markup in text
        $text = "1234567890";
        $answer = $definition->shortenText($text,7);
        $this->assertSame("123",$answer);
        
        // two markups, one markup in text
        $text = "1234--567890";
        $answer = $definition->shortenText($text,9);
        $this->assertSame("1--",$answer);
        
        // two markups, both markups in text
        $text = "--123456*7890";
        $answer = $definition->shortenText($text,1);
        $this->assertSame("--123456*789",$answer);

        $text = "--123456*7890";
        $answer = $definition->shortenText($text,7);
        $this->assertSame("--123*",$answer);
        
        // two markups, put both markups at the end
        $text = "1234567890*--";
        $answer = $definition->shortenText($text,2);
        $this->assertSame("12345678*--",$answer);
        
        // two markups, put same markup twice in a row near the end
        $text = "1234567890----**";
        $answer = $definition->shortenText($text,8);
        $this->assertSame("12----**",$answer);
        
        // two markups, put markup1{$text}markup1markup2
        $text = "12345678**90--**";
        $answer = $definition->shortenText($text,8);
        $this->assertSame("12**--**",$answer);

        $text = "12345678**90--**";
        $answer = $definition->shortenText($text,1);
        $this->assertSame("12345678**9--**",$answer);
    }


    /**
     * public function justify($text, $justification, $width)
     *
     * Justify text
     *
     * param string $text - the text to justify
     * param string | int $justification - if a string, it can be "left", "RIGHT", etc..., if an int, just needs to be a constant
     * param int $width - the width to fill
     * return string
     */
    public function test_justifyNoMarkup()  {

        $definition = new Definition();

        // null text, which is just empty
        $answer = $definition->justify(null,"center",3);
        $this->assertSame("   ",$answer);

        // bad width
        $answer = $definition->justify("Hello","junk",-4);
        $this->assertSame("Hello",$answer);


        // no justification needed, same width
        $answer = $definition->justify("Hello","center",5);
        $this->assertSame("Hello",$answer);

        // justify left
        $answer = $definition->justify("Hello","left",10);
        $this->assertSame("Hello     ",$answer);

        // justify right
        $answer = $definition->justify("1","right",2);
        $this->assertSame(" 1",$answer);

        // center
        $answer = $definition->justify("1","center",2);
        $this->assertSame("1 ",$answer);

        $answer = $definition->justify("12","center",4);
        $this->assertSame(" 12 ",$answer);

        // string is longer than width
        $answer = $definition->justify("longer","center",4);
        $this->assertSame("long",$answer);

        $answer = $definition->justify("12","left",1);
        $this->assertSame("1",$answer);

    }

    /**
     * public function justify($text, $justification, $width)
     *
     * Justify text
     *
     * param string $text - the text to justify
     * param string | int $justification - if a string, it can be "left", "RIGHT", etc..., if an int, just needs to be a constant
     * param int $width - the width to fill
     * return string
     */
    public function test_justifyMarkup()  {

        $definition = new Definition();

        // no text, no markup

        $answer = $definition->justify(null,"center",3);
        $this->assertSame("   ",$answer);


        $definition->addMarkup("**",(new Style()));

        // justify left, no markup
        $answer = $definition->justify("Hello","left",10);
        $this->assertSame("Hello     ",$answer);

        // justify left with markup at the beginning
        $answer = $definition->justify("**Hello","left",10);
        $this->assertSame("**Hello     ",$answer);

        // justify left with markup in the middle
        $answer = $definition->justify("Hel**lo","left",10);
        $this->assertSame("Hel**lo     ",$answer);

        // justify left with markup at the end
        $answer = $definition->justify("Hello**","left",10);
        $this->assertSame("Hello     **",$answer);


        // add more Markup
        $definition->addMarkup("-",(new Style()));

        // justify right
        $answer = $definition->justify("-1","right",2);
        $this->assertSame("- 1",$answer);
        $answer = $definition->justify("1-","right",2);
        $this->assertSame(" 1-",$answer);
        $answer = $definition->justify("-1-","right",2);
        $this->assertSame("- 1-",$answer);

        // center
        $answer = $definition->justify("**1-","center",2);
        $this->assertSame("**1 -",$answer);

        $answer = $definition->justify("1**2","center",4);
        $this->assertSame(" 1**2 ",$answer);

        // string is longer than width
        $answer = $definition->justify("--longer-","center",4);
        $this->assertSame("--long-",$answer);

        $answer = $definition->justify("**1-2-","left",1);
        $this->assertSame("**1--",$answer);


        // left justification
        $answer = $definition->justify("Testing","left",10);
        $this->assertSame("Testing   ",$answer);


        // left with definition
        $answer = $definition->justify("**Testing","left",10);
        $this->assertSame("**Testing   ",$answer);

        // some more real world data

        // remove the word that is in bold
        $answer = $definition->justify("Here are some **words**","right",10);
        $this->assertSame("Here are s****",$answer);

        // just the right size
        $answer = $definition->justify("Here are some **words**","right",19);
        $this->assertSame("Here are some **words**",$answer);

        // justify left
        $answer = $definition->justify("Here -are- some **words**","left",30);
        $this->assertSame("Here -are- some **words           **",$answer);

        // justify center
        $answer = $definition->justify("Here -are- some **words**","center",30);
        $this->assertSame("     Here -are- some **words      **",$answer);

        // justify right
        $answer = $definition->justify("--Here -are- some **words**","right",30);
        $this->assertSame("-           -Here -are- some **words**",$answer);

    }

    /**
     * public function pad($text, $leftPadding, $rightPadding)
     * 
     * Pad the text with left and right padding, but place any first
     * and last markup beyond the padding, so it gets the same styling.
     *
     * param string $text
     * param int $leftPadding
     * param int $rightPadding
     *
     * return string
     */
    public function test_pad() {
        
        // test no markup in definition ///////////////
        $definition = new Definition();
        
        $text = "The book is there.";
        
        // no padding
        $answer = $definition->pad($text,0,0);
        $this->assertSame($text, $answer);
        
        // left padding
        $answer = $definition->pad($text,2,0);
        $this->assertSame("  " . $text, $answer);
        
        // right padding
        $answer = $definition->pad($text,0,1);
        $this->assertSame($text . " ", $answer);
        
        // both
        $answer = $definition->pad($text,3,2);
        $this->assertSame("   ". $text . "  ", $answer);
        
         
        // markup in definition, but not in text ///////////
        $definition->addMarkup("!",(new Style()));
        $definition->addMarkup("[only]",(new Style()));

        // both
        $answer = $definition->pad($text,3,2);
        $this->assertSame("   ". $text . "  ", $answer);     
        
        // markup in definition, and only in middle of text //////////////////
        $text = "The book !is! there.";
        $answer = $definition->pad($text,3,2);
        $this->assertSame("   ". $text . "  ", $answer);
        
        // markup in definition at the beginning //////////////
        $text = "!The book is there.";
        $answer = $definition->pad($text,3,2);
        $this->assertSame("!   ". "The book is there." . "  ", $answer);

        $text = "[only]The book is there.";
        $answer = $definition->pad($text,1,0);
        $this->assertSame("[only] ". "The book is there.", $answer);

        $text = "![only]The book is there.";
        $answer = $definition->pad($text,1,0);
        $this->assertSame("! ". "[only]The book is there.", $answer);
        
        // markup in definition at the end ////////////////
        $text = "The book is there.!";
        $answer = $definition->pad($text,3,2);
        $this->assertSame("   ". "The book is there." . "  !", $answer);

        $text = "The book is there.[only]";
        $answer = $definition->pad($text,0,1);
        $this->assertSame("The book is there." . " [only]", $answer);

        $text = "The book is there.![only]";
        $answer = $definition->pad($text,1,1);
        $this->assertSame(" ". "The book is there.!" . " [only]", $answer);
        
        
        // markup in definition at beginning, middle and end ////////////////
        $text = "[only][only]!The !book![only] is there.![only]";
        $answer = $definition->pad($text,3,1);
        $this->assertSame("[only]   ". "[only]!The !book![only] is there.!" . " [only]", $answer);
    }

    /**
     * public function wordwrap($text, $width, $cut = true)
     *
     * Word wrap just the text with the width specified, ignoring
     * the space taken by the markup characters
     * .
     * The beginning offset is the placement of the first character
     * which might not be at 0.
     *
     * param string $text
     * param int $width
     *
     * return string - the same text, with newlines to wordwrap
     *                  ignoring but preserving the markup
     */
    public function test_wordwrap() {


        $definition = new Definition();

        ///// leave out the markup for now  /////

        // simply wordwrap at 10 characters
        $text = "This is a nice sentence.";
        $answer = $definition->wordwrap($text, 10);
        $this->assertSame("This is a\nnice\nsentence.",$answer);
        
        // simply wordwrap at 10 characters, but add some \n's that should be cleared
        $text = "This is a\n nice sen\ntence.";
        $answer = $definition->wordwrap($text, 10);
        $this->assertSame("This is a\nnice\nsentence.",$answer);
        
        // add a space where the \n will go to see if it is preserved
        $text = "This is a nice  sentence.";
        $answer = $definition->wordwrap($text, 20);
        $this->assertSame("This is a nice \nsentence.", $answer);

        // Width is so big, that there is no wrapping
        $text = "This is a nice sentence.";
        $answer = $definition->wordwrap($text, 40);
        $this->assertSame($text,$answer);
        


        ///// Add some markup
        $definition = new Definition();
        $definition->addMarkup("**",(new Style())->setBold());

        // markup, but no markup in it
        $text = "This is a nice sentence.";
        $answer = $definition->wordwrap($text, 10);
        $this->assertSame("This is a\nnice\nsentence.",$answer);


        // Now add one simple markup
        $text = "This **is a nice sentence.";
        $answer = $definition->wordwrap($text, 10);
        $this->assertSame("This **is a\nnice\nsentence.", $answer);


        // Add two of the same markups
        $text = "one **two** three";
        $answer = $definition->wordwrap($text, 3);
        $this->assertSame("one\n**two**\nthr\nee", $answer);


        // add markup at the beginning and end
        $text = "**one two three**";
        $answer = $definition->wordwrap($text, 3);
        $this->assertSame("**one\ntwo\nthr\nee**",$answer);


        ///// Add a second markup
        $definition->addMarkup("--",(new Style())->setUnderscore());

        // don't add the new markup
        $text = "**one two three**";
        $answer = $definition->wordwrap($text, 3);
        $this->assertSame("**one\ntwo\nthr\nee**",$answer);


        // now add only the new markup
        $text = "--one-- two three";
        $answer = $definition->wordwrap($text,3);
        $this->assertSame("--one--\ntwo\nthr\nee",$answer);

        // a whole mishmash of the two
        $text = "--one** two-- t**hree";
        $answer = $definition->wordwrap($text,3);
        $this->assertSame("--one**\ntwo--\nt**hr\nee",$answer);





    }


}


