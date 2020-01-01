<?php


use ANSI\Color\Mode;
use Clio\Clio;
use Clio\Styling\Markup\Justification;
use Clio\Widgets\Tables\Column;
use Clio\Widgets\Tables\Table;
use Clio\Styling\Style;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . "/../../../TestStubs/ClioStub.php";

class TableTest extends TestCase
{



    public function setUp(): void
    {



    }

    public function tearDown(): void
    {

    }

    /**
     * @param $name
     * @return ReflectionProperty
     */
    protected static function getProperty($name) {

        // get a reflection of the class
        $class = new ReflectionClass("Clio\\Widgets\\Tables\\Table");

        // get the method of interest
        $property = $class->getProperty($name);

        // make it accessible
        $property->setAccessible(true);

        // return the method
        return $property;
    }

    /**
     * @param $name
     * @return ReflectionMethod
     */
    protected static function getMethod($name) {

        // get a reflection of the class
        $class = new ReflectionClass("Clio\\Widgets\\Tables\\Table");

        // get the method of interest
        $method = $class->getMethod($name);

        // make that method accessible
        $method->setAccessible(true);

        // return the method
        return $method;
    }






    /**
     * public function setColumnDefinition($columnIndex, Column $column)
     *
     * Set the column definition for one column
     *
     * param int $columnIndex
     * param Column $column
     */
    public function test_setColumnDefinition() {

        $clio = new Clio(Mode::VT100);

        // set a column
        $columns[] = new Column();
        $columns[] = new Column();

        $newDef = new column(null,35);
        $table = new Table($clio,$columns);
        $table->setColumnDefinition(1,$newDef);

        $columnDefinitions = $this->getProperty("columnDefinitions");
        $answer = $columnDefinitions->getValue($table);
        $this->assertSame(2, count($answer));
        $this->assertInstanceOf("\\Clio\\Widgets\\Tables\\Column",$answer[0]);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertSame(35,$answer[1]->getWidth());
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertNull($answer[0]->getWidth());


        // incorrect index
        $table = new Table($clio,$columns);
        $table->setColumnDefinition(2,$newDef);
        $answer = $columnDefinitions->getValue($table);
        $this->assertSame(2, count($answer));
        $this->assertInstanceOf("\\Clio\\Widgets\\Tables\\Column",$answer[0]);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertNull($answer[0]->getWidth());
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertNull($answer[1]->getWidth());

    }


    /**
     * protected function calcTotalWidth()
     *
     * Simply add up all the column widths
     * return int
     */
    public function test_calcTotalWidth() {

        $method = $this->getMethod("calcTotalWidth");

        $clio = new Clio(Mode::VT100);

        $columns[] = new Column();
        $columns[] = new Column(null,11);
        $columns[] = new Column(null,12,null,10);

        $table = new Table($clio, $columns);
        $answer = $method->invoke($table);
        $this->assertSame(23,$answer);


    }



    /**
     * public function draw($data)
     * Draw the grid of data
     * param $data
     */
    public function test_draw() {

        $clio = new ClioStub(Mode::VT100);
        $output = ClioStub::$startupSequencePrintable;

        // no columns
        $table = new Table($clio, []);
        $table->draw([["one"]]);
        $output .= "one   \n";

        // only a header
        $columns[] = new Column("First");
        $columns[] = new Column("Second",5);
        $columns[] = new Column("Third",15);
        $table = new Table($clio,$columns);

        $table->draw([["one"]]);
        $output .= "\\e[1;4mFirst   Se   Third          \\e[0m\n";
        $output .= "one                         \n";



        // no data
        $columns = [];
        $columns[] = new Column("Title",10);
        $columns[] = new Column(null,7);

        $table = new Table($clio, $columns);

        // no data
        $table->draw([]);
        $output .= "";

        
        // random column counts in each row
        $data = [
            ["one","two"],
            ["three"],
            [],
            [null],
            ["one","two","three"]];

        // draw a table that has rows that do not have two columns or too many
        $table->draw($data);
        $output .= "\\e[1;4mTitle                    \\e[0m\n";
        $output .= "one       two            \n";
        $output .= "three                    \n";
        $output .= "                         \n";
        $output .= "                         \n";
        $output .= "one       two    three   \n";


        // not enough columns
        $columns = [];
        $columns[] = new Column(null,8);

        $data = [
            ["one"],
            ["one", "two"],
            ["one", "two", "three"],
            ["one", "two"]
        ];

        $table = new Table($clio, $columns);
        $table->draw($data);
        $output .= "one                   \n";
        $output .= "one     two           \n";
        $output .= "one     two   three   \n";
        $output .= "one     two           \n";


        // static column widths ////

        // no padding
        $columns = [];
        $columns[] = (new Column(null,10))->setRightPadding(0);
        $columns[] = (new Column(null,5))->setRightPadding(0);
        $columns[] = (new Column("Big",15))->setRightPadding(0);

        $table = new Table($clio,$columns);

        $data = [
            ["one","two","three"],
            ["another","too big","lots of space"],
            ["empty","",'a'],
        ];

        $table->draw($data);
        $output .= "\\e[1;4m               Big            \\e[0m\n";
        $output .= "one       two  three          \n";
        $output .= "another   too blots of space  \n";
        $output .= "empty          a              \n";

        // no padding and width of 1
        $columns = [];
        $columns[] = (new Column(null,1))->setRightPadding(0);
        $columns[] = (new Column(null,1))->setRightPadding(0);
        $columns[] = (new Column(null,1))->setRightPadding(0);

        $table = new Table($clio,$columns);

        $data = [
            ["","two","three"],
            ["a","too big","lots of space"],
            ["bb","",'a'],
        ];

        $table->draw($data);
        $output .= " tt\n";
        $output .= "atl\n";
        $output .= "b a\n";


        // with padding
        $columns = [];
        $columns[] = (new Column(null,10))->setRightPadding(2);
        $columns[] = (new Column(null,5))->setRightPadding(1);
        $columns[] = (new Column(null,15))->setRightPadding(10);

        $table = new Table($clio,$columns);

        $data = [
            ["one","two","three"],
            ["another","too big","lots of space"],
            ["empty","",'a'],
        ];

        $table->draw($data);
        $output .= "one       two  three          \n";
        $output .= "another   too  lots           \n";
        $output .= "empty          a              \n";



        // left padding
        $columns = [];
        $columns[] = (new Column("Start",10))->setRightPadding(2)->setLeftPadding(2);
        $columns[] = (new Column("Middle",5))->setRightPadding(1);
        $columns[] = (new Column(null,15))->setRightPadding(10);


        $table = new Table($clio,$columns);

        $data = [
            ["one","two","three"],
            ["another","too big","lots of space"],
            ["empty","",'a'],
        ];

        $table->draw($data);
        $output .= "\\e[1;4m  Start   Midd                \\e[0m\n";
        $output .= "  one     two  three          \n";
        $output .= "  anothe  too  lots           \n";
        $output .= "  empty        a              \n";


        // justification
        $columns = [];
        $columns[] = (new Column("Fruit",10))->setRightPadding(0)->setLeftPadding(2);
        $columns[] = (new Column("Color",10))->setRightPadding(0)->setJustification(Justification::CENTER);
        $columns[] = (new Column("Taste",10))->setRightPadding(3)->setJustification(Justification::RIGHT);

        $table = new Table($clio, $columns);

        $data = [
            ["Orange","orange","tangy"],
            ["Apple","red","sweet"]
        ];

        $table->draw($data);

        $output .= "\\e[1;4m  Fruit     Color     Taste   \\e[0m\n";
        $output .= "  Orange    orange    tangy   \n";
        $output .= "  Apple      red      sweet   \n";


        // Calculated widths /////////

        $columns = [];
        $columns[] = new Column("Name");
        $columns[] = new Column("Address");
        $columns[] = new Column("Phone");
        
        $table = new Table($clio, $columns);

        $data = [
            ["Daffy Duck","123 Anywhere","555-1212"],
            ["Bugs","Rabbit Hole near the stream","555-2342"],
            ["","",""]

        ];

        $table->draw($data);
        $output .= "\\e[1;4mName         Address                       Phone      \\e[0m\n";
        $output .= "Daffy Duck   123 Anywhere                  555-1212   \n";
        $output .= "Bugs         Rabbit Hole near the stream   555-2342   \n";
        $output .= "                                                      \n";



        // with markup (is stripped)
        $clio->addMarkupDefinition("**",(new Style())->setBold());
        $data = [
            ["Daffy **Duck**","123 Anywhere","555-1212"],
            ["Bugs","**Rabbit** Hole near the stream","555-2342"],
            ["","",""]

        ];

        $table->draw($data);
        $output .= "\\e[1;4mName         Address                       Phone      \\e[0m\n";
        $output .= "Daffy \\e[1mDuck   \\e[0m123 Anywhere                  555-1212   \n";
        $output .= "Bugs         \\e[1mRabbit\\e[0m Hole near the stream   555-2342   \n";
        $output .= "                                                      \n";

        // maximum width with padding
        $columns = [];
        $columns[] = (new Column("12"))->setMaximumWidth(10);
        $data = [["123456789012"]];
        $table = new Table($clio, $columns);

        $table->draw($data);
        $output .= "\\e[1;4m12        \\e[0m\n";
        $output .= "1234567   \n";

        // maximum width without padding
        $columns = [];
        $columns[] = (new Column())->setMaximumWidth(10)->setRightPadding(0);
        $data = [["12"],["123456789012"]];
        $table = new Table($clio, $columns);

        $table->draw($data);
        $output .= "12        \n";
        $output .= "1234567890\n";

        // text and fill for column

        $columns = [];
        $columns[] = (new Column("Item"))->setLeftPadding(2);
        $columns[] = (new Column("Cost",10))->setRightPadding(0)->setJustification(Justification::RIGHT)->setColors("white","black");
        $table = new Table($clio, $columns);

        $data = [
            ["Apples","$12.45"],
            ["Oranges","$1.54"]
        ];

        $table->draw($data);

        $output .= "\\e[1;4m  Item      \\e[97;40m      Cost\\e[0m\n";
        $output .= "  Apples    \\e[97;40m    $12.45\\e[0m\n";
        $output .= "  Oranges   \\e[97;40m     $1.54\\e[0m\n";


        // now put in markup
        $data = [
            ["**Apples**","**$12.45**"],
            ["**Oranges**","**$1.54**"]
        ];

        $table->draw($data);

        $output .= "\\e[1;4m  Item      \\e[97;40m      Cost\\e[0m\n";
        $output .= "\\e[1m  Apples    \\e[0m\\e[97;40m\\e[1m    $12.45\\e[0;97;40m\\e[0m\n";
        $output .= "\\e[1m  Oranges   \\e[0m\\e[97;40m\\e[1m     $1.54\\e[0;97;40m\\e[0m\n";

        $this->expectOutputString($output);
        return;

    }


}


