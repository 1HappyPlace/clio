<?php


use ANSI\Color\Mode;
use Clio\Widgets\Tables\BarTable;
use Clio\Widgets\Tables\Column;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . "/../../../TestStubs/ClioStub.php";

class BarTableTest extends TestCase
{



    public function setUp(): void
    {



    }

    public function tearDown(): void
    {

    }




    /**
     * public function draw($data)
     * public function setTitle($text)
     * public function setColors($barText, $barFill, $bodyText, $bodyFill)
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
            [4,5,9]
        ];

        // default constructor (no title, line at the bottom)
        $table = new BarTable($clio, $columns);
        $table->setColors("white","black","white","lightgray");

        $table->draw($data);

        $output .= "\\e[97;40mx   y   answer   \\e[0m\n";
        $output .= "\\e[97;47m3   2   5        \\e[0m\n";
        $output .= "\\e[97;47m4   5   9        \\e[0m\n";




        // title
        $table = new BarTable($clio, $columns,"Addition");
        $table->setColors("white","black","white","lightgray");

        $table->draw($data);

        $output .= "\\e[4;30;47m                 \\e[0;1;97m    Addition     \\e[0m\n";
        $output .= "\\e[97;40mx   y   answer   \\e[0m\n";
        $output .= "\\e[97;47m3   2   5        \\e[0m\n";
        $output .= "\\e[97;47m4   5   9        \\e[0m\n";


        // title no bottom
        $table = new BarTable($clio, $columns,"From Constructor",false);
        $table->setColors("white","black","white","lightgray");

        $table->draw($data);

        $output .= "\\e[4;30;47m                 \\e[0;1;97mFrom Constructor \\e[0m\n";
        $output .= "\\e[97;40mx   y   answer   \\e[0m\n";
        $output .= "\\e[97;47m3   2   5        \\e[0m\n";
        $output .= "\\e[97;47m4   5   9        \\e[0m\n";


        // title and bottom using setters
        $table = new BarTable($clio, $columns);
        $table->setTitle("Bottom");
        $table->setShowBottomLine(false);
        $table->setColors("white","black","white","lightgray");

        $table->draw($data);

        $output .= "\\e[1;97m     Bottom      \\e[0m\n";
        $output .= "\\e[97;40mx   y   answer   \\e[0m\n";
        $output .= "\\e[97;47m3   2   5        \\e[0m\n";
        $output .= "\\e[97;47m4   5   9        \\e[0m\n";

        // title and bottom using setters
        $table = new BarTable($clio, $columns);
        $table->setTitle("Really Big Title that Goes Too Far");
        $table->setShowBottomLine(false);
        $table->setColors("white","black","white","lightgray");

        $table->draw($data);

        $output .= "\\e[1;97mReally Big Title \\e[0m\n";
        $output .= "\\e[97;40mx   y   answer   \\e[0m\n";
        $output .= "\\e[97;47m3   2   5        \\e[0m\n";
        $output .= "\\e[97;47m4   5   9        \\e[0m\n";



        $this->expectOutputString($output);





        // add colors
        
    }

}


