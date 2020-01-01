<?php



use Clio\Styling\Markup\Definition;
use Clio\Styling\Markup\Justification;
use Clio\Widgets\Tables\Column;
use Clio\Styling\Style;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . "/../../../TestStubs/ClioStub.php";

class ColumnTest extends TestCase
{



    public function setUp(): void
    {



    }

    public function tearDown(): void
    {

    }

    /**
     * @param $name
     * @return ReflectionMethod
     */
    protected static function getMethod($name) {

        // get a reflection of the class
        $class = new ReflectionClass("Clio\\HTML\\Tables\\Table");

        // get the method of interest
        $method = $class->getMethod($name);

        // make that method accessible
        $method->setAccessible(true);

        // return the method
        return $method;
    }

    public function test_setGetColors() {

        // test the constructor
        $column = new Column();
        $column->setColors("red","black");

        // get the text color
        $textColor = $column->getTextColor();
        $this->assertInstanceOf("ANSI\\Color\\Color",$textColor);
        $this->assertSame("red",$textColor->getName());

        // get the fill color
        $fillColor = $column->getFillColor();
        $this->assertInstanceOf("ANSI\\Color\\Color",$fillColor);
        $this->assertSame("black",$fillColor->getName());


        // set only the fill color
        $column->setColors(null,"green");

        $this->assertNull($column->getTextColor());
        // get the fill color
        $fillColor = $column->getFillColor();
        $this->assertInstanceOf("ANSI\\Color\\Color",$fillColor);
        $this->assertSame("green",$fillColor->getName());


        // set only the text color
        $column->setColors("cyan",null);

        // get the text color
        $textColor = $column->getTextColor();
        $this->assertInstanceOf("ANSI\\Color\\Color",$textColor);
        $this->assertSame("cyan",$textColor->getName());

        // get the fill color
        $this->assertNull($column->getFillColor());

        // set both
        $column->setColors("lime","maroon");
        // get the text color
        $textColor = $column->getTextColor();
        $this->assertInstanceOf("ANSI\\Color\\Color",$textColor);
        $this->assertSame("lime",$textColor->getName());

        // get the fill color
        $fillColor = $column->getFillColor();
        $this->assertInstanceOf("ANSI\\Color\\Color",$fillColor);
        $this->assertSame("maroon",$fillColor->getName());


    }

    /**
     * public function calculateWidth($data, $markupDefinition = null)
     * 
     * Run the data through and make the width as wide as the widest text
     *
     * param array $data
     * param Definition $markupDefinition
     */
    public function test_calculateWidth() {
        
        // calculated, no max width, no markup and no padding ////
        $column = new Column();
        $this->assertFalse($column->hasHeaderText());
        $column->setRightPadding(0);

        // no data
        $column->calculateWidth([]);
        $this->assertSame(null,$column->getWidth());

        // one empty item
        $column->calculateWidth([""]);
        $this->assertSame(0,$column->getWidth());

        // one simple item
        $data = ["one"];
        $column->calculateWidth($data);
        $this->assertSame(3,$column->getWidth());

        // two items the same
        $data = ["one", "two"];
        $column->calculateWidth($data);
        $this->assertSame(3,$column->getWidth());

        // two items, one is empty
        $data = ["three", ""];
        $column->calculateWidth($data);
        $this->assertSame(5,$column->getWidth());

        // three items first is empty, some numbers
        $data = ["", 1,2];
        $column->calculateWidth($data);
        $this->assertSame(1,$column->getWidth());

        // three items first is empty, some numbers
        $column = new Column();
        $column->setHeaderText("Hello");
        $this->assertSame("Hello",$column->getHeaderText());
        $this->assertTrue($column->hasHeaderText());
        $column->setRightPadding(0);
        $data = ["", 1,2];
        $column->calculateWidth($data);
        $this->assertSame(5,$column->getWidth());

        $column = new Column("Header");
        $column->setRightPadding(0);
        $this->assertSame("Header",$column->getHeaderText());
        $data = ["one","This is very long"];
        $column->calculateWidth($data);
        $this->assertSame(17,$column->getWidth());

        // no markup and add padding ////
        $column = new Column(12);
        $column->setWidth(null); // changed to calculated
        $this->assertTrue($column->isCalculateWidth());
        $column->setRightPadding(2);
        $column->setLeftPadding(1);
        $this->assertSame(1,$column->getLeftPadding());

        $data = ["one","This is very long"];
        $column->calculateWidth($data);
        $this->assertSame(20,$column->getWidth());

        // markup ///////
        $definition = new Definition();
        $definition->addMarkup("!",(new Style()));
        $definition->addMarkup("--",(new Style()));

        // no markup in data
        $data = ["hello","world"];
        $column->calculateWidth($data, $definition);
        $this->assertSame(8,$column->getWidth());

        // markup in the data
        $data = ["--hel!lo","!w!o!r!l!d!"];
        $column->calculateWidth($data, $definition);
        $this->assertSame(8,$column->getWidth());


        // now add a maximum width (no padding)
        $column = new Column();
        $column->setRightPadding(0);
        $column->setMaximumWidth(10);
        $this->assertSame(10,$column->getMaximumWidth());
        $this->assertSame(0,$column->getRightPadding());

        // under the maximum
        $data = ["one","two","three"];
        $column->calculateWidth($data, $definition);
        $this->assertSame(5,$column->getWidth());

        // exactly the maximum
        $data = ["one","two","three","1234567890"];
        $column->calculateWidth($data, $definition);
        $this->assertSame(10,$column->getWidth());

        // over the maximum
        $data = ["one","two","three","1234567890123"];
        $column->calculateWidth($data, $definition);
        $this->assertSame(10,$column->getWidth());

        // now add a maximum width (with padding)
        $column = new Column();
        $column->setMaximumWidth(10);
        $column->setRightPadding(1);
        $column->setLeftPadding(1);
        $this->assertSame(1,$column->getRightPadding());
        $this->assertSame(1,$column->getLeftPadding());

        // under the maximum
        $data = ["one","two","three"];
        $column->calculateWidth($data, $definition);
        $this->assertSame(7,$column->getWidth());

        // exactly the maximum
        $data = ["one","two","three","12345678"];
        $column->calculateWidth($data, $definition);
        $this->assertSame(10,$column->getWidth());

        // over the maximum
        $data = ["one","two","three","1234567890123"];
        $column->calculateWidth($data, $definition);
        $this->assertSame(10,$column->getWidth());

    }

    /**
     * public function justify($text, $markupDefinition)
     *
     * Justify text
     *
     * param string $text - the text to justify
     * param Definition $markupDefinition
     *
     * return string
     */
    public function test_justify() {

        $definition = new Definition();

        // zero width
        $column = new Column();

        $answer = $column->justify("Hello World", $definition);
        $this->assertSame("",$answer);

        // too little with padding
        $column = new Column(null, 2);
        $answer = $column->justify("Hello World", $definition);
        $this->assertSame("",$answer);

        // left justification, no padding
        $column = new Column();
        $column->setWidth(10);
        $answer = $column->justify("Hello",$definition);
        $this->assertSame("Hello     ",$answer);

        // right justification, no padding
        $column = new Column(null, 10);
        $column->setRightPadding(0)->setJustification(Justification::RIGHT);
        $this->assertSame(Justification::RIGHT, $column->getJustification());
        $answer = $column->justify("Hello",$definition);
        $this->assertSame("     Hello",$answer);

        // center justification, no padding
        $column = new Column(null,10);
        $column->setRightPadding(0)->setJustification(Justification::CENTER);
        $answer = $column->justify("Hello",$definition);
        $this->assertSame("  Hello   ",$answer);

        // right justification, padding
        $column = new Column(null,20);
        $column->setRightPadding(2)->setJustification(Justification::RIGHT);
        $column->setLeftPadding(4);
        $answer = $column->justify("Hello",$definition);
        $this->assertSame("             Hello  ", $answer);

        // add markup
        $definition->addMarkup("**",(new Style()));

        $column = new Column(null,20);
        $column->setLeftPadding(1);
        $answer = $column->justify("**Hel**lo**",$definition);
        $this->assertSame("** Hel**lo              **", $answer);

        $column = new Column(null,20);
        $column->setRightPadding(5)->setJustification(Justification::CENTER);
        $column->setLeftPadding(4);
        $answer = $column->justify("**Hel**lo**",$definition);
        $this->assertSame("**       Hel**lo        **", $answer);



    }


 

}


