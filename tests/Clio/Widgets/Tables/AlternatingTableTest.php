<?php


use ANSI\Color\Mode;
use Clio\Widgets\Tables\AlternatingTable;
use Clio\Widgets\Tables\Column;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . "/../../../TestStubs/ClioStub.php";

class AlternatingTableTest extends TestCase
{



    public function setUp(): void
    {



    }

    public function tearDown(): void
    {

    }




    /**
     * public function draw($data)
     * public function __construct($html, $columns, $alternatingBackground)
     * 
     * Draw the table of data
     * param $data
     */
    public function test_draw() {

        $clio = new ClioStub(Mode::VT100);
        $output = ClioStub::$startupSequencePrintable;

        $columns[] = new Column("x");
        $columns[] = new Column("y");
        $columns[] = new Column("answer");

        $data = [
            [3,2,5],
            [4,5,9],
            [5,-1,4]
        ];

        // default constructor (no title)
        $table = new AlternatingTable($clio, $columns);

        $table->draw($data);

        $output .= "\\e[1mx   y    answer   \\e[0m\n";
        $output .= "\\e[107m3   2    5        \\e[0m\n";
        $output .= "4   5    9        \n";
        $output .= "\\e[107m5   -1   4        \\e[0m\n";

        // default constructor (no title, line at the bottom)
        $table = new AlternatingTable($clio, $columns);
        $table->setAlternatingFill("yellow");

        $table->draw($data);

        $output .= "\\e[1mx   y    answer   \\e[0m\n";
        $output .= "\\e[103m3   2    5        \\e[0m\n";
        $output .= "4   5    9        \n";
        $output .= "\\e[103m5   -1   4        \\e[0m\n";


        $this->expectOutputString($output);

        // add colors
        
    }

}


