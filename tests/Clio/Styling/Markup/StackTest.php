<?php


use ANSI\Color\Color;
use Clio\Styling\Markup\Markup;
use Clio\Styling\Markup\Stack;
use Clio\Styling\Style;
use PHPUnit\Framework\TestCase;

class StackItTest extends TestCase
{


    public function setUp(): void
    {


    }

    public function tearDown(): void
    {

    }

    /**
     * public function clear()
     *
     * Clear out the stack, a clear was sent to the terminal
     */
    public function test_clear() {
        // no default style
        $markupStack = new Stack();

        $baseStyle = new Style();

        // no styling added
        $style = $markupStack->getCurrentStyling($baseStyle);
        $this->assertNull($style);


        // clear should have no effect
        $markupStack->clear();

        $style = $markupStack->getCurrentStyling($baseStyle);
        $this->assertNull($style);

        // now add a style that has bold
        $style = (new Style())->setBold();
        $markup = new Markup("!bold!",$style);
        $markupStack->addMarkup($markup);

        $style = $markupStack->getCurrentStyling($baseStyle);
        $this->assertTrue($style->getBold());
        $this->assertNull($style->getUnderscore());
        $this->assertNull($style->getTextColor());
        $this->assertNull($style->getFillColor());

        // clear will remove the bold
        $markupStack->clear();

        $style = $markupStack->getCurrentStyling($baseStyle);
        $this->assertNull($style);



        // now add a style that has bold and underscore, separated it into
        // two markups just to be different from other test cases
        $buMarkup = new Markup("bu",(new Style())->setBold()->setUnderscore());
        $limeWhite = new Markup("limewhite",(new Style())->setColors("lime","white"));
        $markupStack->addMarkup($buMarkup);
        $markupStack->addMarkup($limeWhite);

        $style = $markupStack->getCurrentStyling($baseStyle);
        $this->assertTrue($style->getBold());
        $this->assertTrue($style->getUnderscore());
        $this->assertSame("lime",$style->getTextColor()->getName());
        $this->assertSame("white",$style->getFillColor()->getName());


        // clear it all out
        $markupStack->clear();

        $style = $markupStack->getCurrentStyling($baseStyle);
        $this->assertNull($style);


    }
    /**
     * public function addMarkup($markup)
     * 
     * Add a markup to the stack, if it is already on it, then remove it
     * param Markup $markup
     */
    public function test_addMarkupPancaking ()
    {
        // no default style
        $markupStack = new Stack();

        $baseStyle = new Style();

        // no styling added
        $style = $markupStack->getCurrentStyling($baseStyle);
        $this->assertNull($style);

        // now add a style that has bold
        $style = (new Style())->setBold();
        $markup = new Markup("!bold!",$style);
        $markupStack->addMarkup($markup);

        $style = $markupStack->getCurrentStyling($baseStyle);
        $this->assertTrue($style->getBold());
        $this->assertNull($style->getUnderscore());
        $this->assertNull($style->getTextColor());
        $this->assertNull($style->getFillColor());

        // now add a style that keeps bold at null and adds a text color
        $style = (new Style())->setTextColor("red");
        $markup = new Markup("!red!",$style);
        $markupStack->addMarkup($markup);

        $style = $markupStack->getCurrentStyling($baseStyle);
        $this->assertTrue($style->getBold());
        $this->assertNull($style->getUnderscore());
        $this->assertSame("red",$style->getTextColor()->getName());
        $this->assertNull($style->getFillColor());

        // now add a style that turns off bold and changes text to green, and fill to black
        $style = (new Style())->setTextColor("green")->setFillColor("black")->setBold(false);
        $markup = new Markup("!greenblack!",$style);
        $markupStack->addMarkup($markup);

        $style = $markupStack->getCurrentStyling($baseStyle);
        $this->assertFalse($style->getBold());
        $this->assertNull($style->getUnderscore());
        $this->assertSame("green",$style->getTextColor()->getName());
        $this->assertSame("black",$style->getFillColor()->getName());



    }

    public function test_addMarkupRemoveIfExisting() {

        $baseStyle = new Style();

        // no default style
        $markupStack = new Stack();
        $style = (new Style())->setUnderscore(true)->setFillColor("black");
        $underBlackFill = new Markup("under",$style);

        $markupStack->addMarkup($underBlackFill);

        // add the underscore style
        $style = $markupStack->getCurrentStyling($baseStyle);
        $this->assertNull($style->getBold());
        $this->assertTrue($style->getUnderscore());
        $this->assertNull($style->getTextColor());
        $this->assertSame("black",$style->getFillColor()->getName());

        // add it again so it is deleted
        $markupStack->addMarkup($underBlackFill);
        $style = $markupStack->getCurrentStyling($baseStyle);
        $this->assertNull($style);

        // add it again so it is added
        $markupStack->addMarkup($underBlackFill);
        $style = $markupStack->getCurrentStyling($baseStyle);
        $this->assertNull($style->getBold());
        $this->assertTrue($style->getUnderscore());
        $this->assertNull($style->getTextColor());
        $this->assertSame("black",$style->getFillColor()->getName());

        // add another style that turns off underscore and add bold for kicks
        $bold = new Markup("bold",(new Style())->setUnderscore(false)->setBold(true));
        $markupStack->addMarkup($bold);
        $style = $markupStack->getCurrentStyling($baseStyle);
        $this->assertTrue($style->getBold());
        $this->assertFalse($style->getUnderscore());
        $this->assertNull($style->getTextColor());
        $this->assertSame("black",$style->getFillColor()->getName());

        // add under again so it would be deleted
        $markupStack->addMarkup($underBlackFill);
        $style = $markupStack->getCurrentStyling($baseStyle);
        $this->assertTrue($style->getBold());
        $this->assertFalse($style->getUnderscore());
        $this->assertNull($style->getTextColor());
        $this->assertNull($style->getFillColor());


        // now add bold again so it will be deleted
        $markupStack->addMarkup($bold);
        $style = $markupStack->getCurrentStyling($baseStyle);
        $this->assertNull($style);


    }

    public function test_addMarkupDefaultStyling() {

        $bold = new Markup("bold",(new Style())->setBold());
        $boldOff = new Markup("boldOff", (new Style())->setBold(false));

        $greenUnderscore = new Markup("greenUnderscore", (new Style())->setUnderscore()->setTextColor("green"));
        $orangeFillNoUnderscore = new Markup("orangeFill", (new Style())->setFillColor("orange")->setUnderscore(false));


        $baseStyle = new Style(true, true, new Color("black"), new Color("white"));
        // start a new markup with white text and dimgray background
        // get the default
        $markupStack = new Stack();
        $style = $markupStack->getCurrentStyling($baseStyle);
        $this->assertNull($style);

        // add green underscore, ensure the default fill still exists
        //      - default
        //      - green underscore
        //      - bold off
        $markupStack->addMarkup($greenUnderscore);
        $markupStack->addMarkup($boldOff);

        $style = $markupStack->getCurrentStyling($baseStyle);
        $this->assertFalse($style->getBold());
        $this->assertTrue($style->getUnderscore());
        $this->assertSame("green",$style->getTextColor()->getName());
        $this->assertSame("white",$style->getFillColor()->getName());

        // add orangeFill, ensure the default fill still exists
        //      - default
        //      - green underscore
        //      - bold off
        //      - orange fill
        $markupStack->addMarkup($orangeFillNoUnderscore);
        $style = $markupStack->getCurrentStyling($baseStyle);
        $this->assertFalse($style->getBold());
        $this->assertFalse($style->getUnderscore());
        $this->assertSame("green",$style->getTextColor()->getName());
        $this->assertSame("orange",$style->getFillColor()->getName());

        // delete green underscore
        //      - default
        //      - bold off
        //      - orange fill
        $markupStack->addMarkup($greenUnderscore);
        $style = $markupStack->getCurrentStyling($baseStyle);
        $this->assertFalse($style->getBold());
        $this->assertFalse($style->getUnderscore());
        $this->assertSame("black",$style->getTextColor()->getName());
        $this->assertSame("orange",$style->getFillColor()->getName());

        // add bold
        //      - default
        //      - bold off
        //      - orange fill
        //      - bold
        $markupStack->addMarkup($bold);
        $style = $markupStack->getCurrentStyling($baseStyle);
        $this->assertTrue($style->getBold());
        $this->assertFalse($style->getUnderscore());
        $this->assertSame("black",$style->getTextColor()->getName());
        $this->assertSame("orange",$style->getFillColor()->getName());

        // add green underscore again
        //      - default
        //      - bold off
        //      - orange fill
        //      - bold
        //      - green underscore
        $markupStack->addMarkup($greenUnderscore);
        $style = $markupStack->getCurrentStyling($baseStyle);
        $this->assertTrue($style->getBold());
        $this->assertTrue($style->getUnderscore());
        $this->assertSame("green",$style->getTextColor()->getName());
        $this->assertSame("orange",$style->getFillColor()->getName());

        // now delete orange fill
        //      - default
        //      - bold off
        //      - bold
        //      - green underscore
        $markupStack->addMarkup($orangeFillNoUnderscore);
        $style = $markupStack->getCurrentStyling($baseStyle);
        $this->assertTrue($style->getBold());
        $this->assertTrue($style->getUnderscore());
        $this->assertSame("green",$style->getTextColor()->getName());
        $this->assertSame("white",$style->getFillColor()->getName());

        // delete green underscore, should just be left with bold and default colors
        //      - default
        //      - bold off
        //      - bold
        $markupStack->addMarkup($greenUnderscore);
        $style = $markupStack->getCurrentStyling($baseStyle);
        $this->assertTrue($style->getBold());
        $this->assertTrue($style->getUnderscore());
        $this->assertSame("black",$style->getTextColor()->getName());
        $this->assertSame("white",$style->getFillColor()->getName());

        // delete green underscore, should just be left with bold and default colors
        //      - default
        //      - bold off
        $markupStack->addMarkup($bold);
        $style = $markupStack->getCurrentStyling($baseStyle);
        $this->assertFalse($style->getBold());
        $this->assertTrue($style->getUnderscore());
        $this->assertSame("black",$style->getTextColor()->getName());
        $this->assertSame("white",$style->getFillColor()->getName());

        // finally remove bold and all you have left is the default
        $markupStack->addMarkup($boldOff);
        $style = $markupStack->getCurrentStyling($baseStyle);
        $this->assertNull($style);


    }


    /**
     * public function addStyling($style)
     * public function removeStyling($id)
     * public function getCurrentStyling()
     *
     * Add styling without markup symbols, return an ID
     * param StyleInterface $style
     *
     * return string
     */
    public function test_addRemoveStyling() {

        $stack = new Stack();

        $baseStyle = new Style();

        // one simple bold style
        $bold = (new Style())->setBold();
        $id = $stack->addStyling($bold);
        $style = $stack->getCurrentStyling($baseStyle);
        $this->assertTrue($style->getBold());
        $this->assertNull($style->getUnderscore());
        $this->assertNull($style->getTextColor());
        $this->assertNull($style->getFillColor());
        
        $stack->removeStyling($id);
        $style = $stack->getCurrentStyling($baseStyle);
        $this->assertNull($style);


        // styling with everything set, make sure new ID is different than the bold
        $all = (new Style())->setBold(false)->setUnderscore()->setTextColor("lime")->setFillColor("maroon");
        $newID = $stack->addStyling($all);
        $this->assertNotSame($id, $newID);

        $style = $stack->getCurrentStyling($baseStyle);
        $this->assertFalse($style->getBold());
        $this->assertTrue($style->getUnderscore());
        $this->assertSame("lime",$style->getTextColor()->getName());
        $this->assertSame("maroon",$style->getFillColor()->getName());

        $stack->removeStyling($newID);
        $style = $stack->getCurrentStyling($baseStyle);
        $this->assertNull($style);

        // add other regular markup in between styling calls
        $boldMarkup = new Markup("b",$bold);
        $orangeFillMarkup = new Markup("orange",(new Style())->setFillColor("orange"));


        $underscore = (new Style())->setUnderscore();
        $greenTextColor = (new Style())->setTextColor("green");


        // start with the underscore style
        //    underscore
        $underscoreID = $stack->addStyling($underscore);

        $style = $stack->getCurrentStyling($baseStyle);
        $this->assertNull($style->getBold());
        $this->assertTrue($style->getUnderscore());
        $this->assertNull($style->getTextColor());
        $this->assertNull($style->getFillColor());

        // now add regular markup
        //    orange fill
        //    underscore
        $stack->addMarkup($orangeFillMarkup);

        $style = $stack->getCurrentStyling($baseStyle);
        $this->assertNull($style->getBold());
        $this->assertTrue($style->getUnderscore());
        $this->assertNull($style->getTextColor());
        $this->assertSame("orange",$style->getFillColor()->getName());

        // add a complete style
        //    no bold, underscore, lime, maroon
        //    orange fill
        //    underscore
        $allID = $stack->addStyling($all);
        $style = $stack->getCurrentStyling($baseStyle);
        $this->assertFalse($style->getBold());
        $this->assertTrue($style->getUnderscore());
        $this->assertSame("lime",$style->getTextColor()->getName());
        $this->assertSame("maroon",$style->getFillColor()->getName());

        // now add a simple markup
        //    bold
        //    no bold, underscore, lime, maroon
        //    orange fill
        //    underscore
        $stack->addMarkup($boldMarkup);
        $style = $stack->getCurrentStyling($baseStyle);
        $this->assertTrue($style->getBold());
        $this->assertTrue($style->getUnderscore());
        $this->assertSame("lime",$style->getTextColor()->getName());
        $this->assertSame("maroon",$style->getFillColor()->getName());

        // add one more styling
        //    green text
        //    bold
        //    no bold, underscore, lime, maroon
        //    orange fill
        //    underscore
        $greenID = $stack->addStyling($greenTextColor);
        $style = $stack->getCurrentStyling($baseStyle);
        $this->assertTrue($style->getBold());
        $this->assertTrue($style->getUnderscore());
        $this->assertSame("green",$style->getTextColor()->getName());
        $this->assertSame("maroon",$style->getFillColor()->getName());

        $this->assertNotSame($allID,$greenID);

        // now remove bold markup
        //    green text
        //    no bold, underscore, lime, maroon
        //    orange fill
        //    underscore
        $stack->addMarkup($boldMarkup);
        $style = $stack->getCurrentStyling($baseStyle);
        $this->assertFalse($style->getBold());
        $this->assertTrue($style->getUnderscore());
        $this->assertSame("green",$style->getTextColor()->getName());
        $this->assertSame("maroon",$style->getFillColor()->getName());

        // now remove bold all styling
        //    green text
        //    orange fill
        //    underscore
        $stack->removeStyling($allID);
        $style = $stack->getCurrentStyling($baseStyle);
        $this->assertNull($style->getBold());
        $this->assertTrue($style->getUnderscore());
        $this->assertSame("green",$style->getTextColor()->getName());
        $this->assertSame("orange",$style->getFillColor()->getName());

        // now add back the all styling
        //    no bold, underscore, lime, maroon
        //    green text
        //    orange fill
        //    underscore
        $allID = $stack->addStyling($all);
        $style = $stack->getCurrentStyling($baseStyle);
        $this->assertFalse($style->getBold());
        $this->assertTrue($style->getUnderscore());
        $this->assertSame("lime",$style->getTextColor()->getName());
        $this->assertSame("maroon",$style->getFillColor()->getName());

        // remove the underscore
        //    no bold, underscore, lime, maroon
        //    green text
        //    orange fill
        $stack->removeStyling($underscoreID);
        $style = $stack->getCurrentStyling($baseStyle);
        $this->assertFalse($style->getBold());
        $this->assertTrue($style->getUnderscore());
        $this->assertSame("lime",$style->getTextColor()->getName());
        $this->assertSame("maroon",$style->getFillColor()->getName());

        // change the all styling
        //    bold, underscore, lime, no clear
        //    no bold, underscore, lime, maroon
        //    green text
        //    orange fill
        $all->clearFillColor()->setBold();
        $anotherAllID = $stack->addStyling($all);
        $this->assertNotSame($anotherAllID,$allID);
        
        $style = $stack->getCurrentStyling($baseStyle);
        $this->assertTrue($style->getBold());
        $this->assertTrue($style->getUnderscore());
        $this->assertSame("lime",$style->getTextColor()->getName());
        $this->assertSame("maroon",$style->getFillColor()->getName());

        // pull off the original all
        //    bold, underscore, lime, no fill
        //    green text
        //    orange fill
        $stack->removeStyling($allID);
        $this->assertNotSame($anotherAllID,$allID);
        
        $style = $stack->getCurrentStyling($baseStyle);
        $this->assertTrue($style->getBold());
        $this->assertTrue($style->getUnderscore());
        $this->assertSame("lime",$style->getTextColor()->getName());
        $this->assertSame("orange",$style->getFillColor()->getName());


        // remove the orange fill
        //    bold, underscore, lime, no fill
        //    green text
        $stack->addMarkup($orangeFillMarkup);
        $this->assertNotSame($anotherAllID,$allID);

        $style = $stack->getCurrentStyling($baseStyle);
        $this->assertTrue($style->getBold());
        $this->assertTrue($style->getUnderscore());
        $this->assertSame("lime",$style->getTextColor()->getName());
        $this->assertNull($style->getFillColor());

        // remove the second version of all
        //    green text
        $stack->removeStyling($anotherAllID);

        $style = $stack->getCurrentStyling($baseStyle);
        $this->assertNull($style->getBold());
        $this->assertNull($style->getUnderscore());
        $this->assertSame("green",$style->getTextColor()->getName());
        $this->assertNull($style->getFillColor());

        // remove the green text
        $stack->removeStyling($greenID);

        $style = $stack->getCurrentStyling($baseStyle);
        $this->assertNull($style);

    }

}


