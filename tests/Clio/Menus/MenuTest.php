<?php

require_once __DIR__ . "/../../TestStubs/ClioStub.php";

use ANSI\Color\Mode;
use Clio\Clio;
use Clio\Menus\Menu;
use Clio\Styling\Style;

class MenuTest extends PHPUnit_Framework_TestCase
{



    public function setUp()
    {



    }

    public function tearDown()
    {

    }

    /**
     * 
     * @param $name
     * @return ReflectionMethod
     */
    protected static function getMethod($name) {

        // get a reflection of the class
        $class = new ReflectionClass("Clio\\Menus\\Menu");

        // get the method of interest
        $method = $class->getMethod($name);

        // make that method accessible
        $method->setAccessible(true);

        // return the method
        return $method;
    }


    /**
     * public function __construct($html, StyleInterface $titleStyle = null, StyleInterface $highlightStyle = null, StyleInterface $choiceStyle = null)
     * 
     * Menu constructor.
     * param HTML $html -the HTML Clio object, generally want just one instance to represent the current terminal
     * param StyleInterface | null $titleStyle - colors for the first few characters that are the style
     * param StyleInterface | null $highlightStyle - colors for the unique character highlighting
     * param StyleInterface | null $choiceStyle - colors for the regular text
     */
    public function test__construct() {

        $clio = new ClioStub(Mode::VT100);
        $output = ClioStub::$startupSequencePrintable;

        $titleStyle = (new Style())->setColors("cyan","yellow");
        $highlightStyle = (new Style())->setColors("red","green");
        $choiceStyle = (new Style())->setColors("blue","yellow");
        
        
        $menu = new Menu($clio, $titleStyle,$highlightStyle,$choiceStyle);
        

        // answer with a simple selection
        $clio->setAnswer(["one"]);
        $answer = $menu->menu(["one","two"],"three");
        $this->assertSame("one",$answer);
        $output .= "\\e[96;103mMenu  \\e[91;102m  on\\e[34;103me\\e[0m \\e[91;102m  tw\\e[34;103mo\\e[0m [three]";

        $this->expectOutputString($output);
    }

    /**
     * determineUniqueCharacterCount($choices)
     *
     * Determine how many characters are needed to uniquely identify each choice in the beginning characters
     * It will generally be 1, but might be 2, if there are two conflicting choices such as Text and Transform
     * param $choices
     * return int | null will return the unique character count or null if it can't be done
     */
    public function test_determineUniqueCharacterCount() {

        $clio = new Clio(Mode::VT100);

        $menu = new Menu($clio);

        $method = $this->getMethod("determineUniqueCharacterCount");

        //  null choices, null default
        $choices = [];
        $default = null;
        $answer = $method->invokeArgs($menu,[$choices, $default]);
        $this->assertNull($answer);

        // empty choices
        $choices = [];
        $answer = $method->invokeArgs($menu,[$choices, $default]);
        $this->assertNull($answer);

        // one choice
        $choices = ["one"];
        $answer = $method->invokeArgs($menu,[$choices, $default]);
        $this->assertSame(1,$answer);

        // two choices, no collision
        $choices = ["one", "two"];
        $answer = $method->invokeArgs($menu,[$choices, $default]);
        $this->assertSame(1,$answer);

        // two choices, collision
        $choices = ["one", "Out"];
        $answer = $method->invokeArgs($menu,[$choices, $default]);
        $this->assertSame(2,$answer);

        // two choices, same choices
        $choices = ["one", "one"];
        $answer = $method->invokeArgs($menu,[$choices, $default]);
        $this->assertSame(null,$answer);

        // two choices different lengths, but still no real choice
        $choices = ["one", "o"];
        $answer = $method->invokeArgs($menu,[$choices, $default]);
        $this->assertSame(null,$answer);

        // Causes 3 choices
        $choices = ["apple","banana","apricot"];
        $answer = $method->invokeArgs($menu,[$choices, $default]);
        $this->assertSame(3,$answer);


        // default in play
        $choices = [];
        $default = "default";
        $answer = $method->invokeArgs($menu,[$choices, $default]);
        $this->assertSame(1,$answer);

        // empty choices
        $choices = [];
        $answer = $method->invokeArgs($menu,[$choices, $default]);
        $this->assertSame(1,$answer);

        // one choice
        $choices = ["one"];
        $answer = $method->invokeArgs($menu,[$choices, $default]);
        $this->assertSame(1,$answer);

        // two choices, no collision
        $choices = ["one", "two"];
        $answer = $method->invokeArgs($menu,[$choices, $default]);
        $this->assertSame(1,$answer);

        // two choices, collision
        $default = "out";
        $choices = ["One"];
        $answer = $method->invokeArgs($menu,[$choices, $default]);
        $this->assertSame(2,$answer);

        // two choices, same choices
        $default = "one";
        $choices = ["one"];
        $answer = $method->invokeArgs($menu,[$choices, $default]);
        $this->assertSame(null,$answer);

        // two choices different lengths, but still no real choice
        $default = "o";
        $choices = ["one", "ones"];
        $answer = $method->invokeArgs($menu,[$choices, $default]);
        $this->assertSame(null,$answer);

        // Causes 3 choices
        $default = "apricot";
        $choices = ["apple","banana"];
        $answer = $method->invokeArgs($menu,[$choices, $default]);
        $this->assertSame(3,$answer);


    }

    /**
     * public function menu($choices)
     *
     * Display a menu and listen for a selection of one or more characters
     * param array $choices - simple array of choices
     *
     * return string - the selection
     */
    public function test_menuAnswers() {

        $clio = new ClioStub(Mode::VT100);
        $output = ClioStub::$startupSequencePrintable;

        $menu = new Menu($clio);

        // no choices
        $answer = $menu->menu([]);
        $this->assertNull($answer);

        // same choices
        $answer = $menu->menu(["one","one"]);
        $this->assertNull($answer);

        // answer with a simple selection
        $clio->setAnswer(["one"]);
        $answer = $menu->menu(["one","two"]);
        $this->assertSame("one",$answer);
        $output .= "\\e[97;40mMenu  \\e[30;47m  o\\e[30;107mne\\e[0m \\e[30;47m  t\\e[30;107mwo\\e[0m ";

        $clio->newLine();
        $output .= "\n";

        // answer with default
        $clio->setAnswer([""]);
        $answer = $menu->menu(["one","two"],"four");
        $this->assertSame("four",$answer);
        $output .= "\\e[97;40mMenu  \\e[30;47m  o\\e[30;107mne\\e[0m \\e[30;47m  t\\e[30;107mwo\\e[0m [four]";

        $clio->newLine();
        $output .= "\n";

        // answer with just one character
        $clio->setAnswer(["t"]);
        $answer = $menu->menu(["one","two"]);
        $this->assertSame("two",$answer);
        $output .= "\\e[97;40mMenu  \\e[30;47m  o\\e[30;107mne\\e[0m \\e[30;47m  t\\e[30;107mwo\\e[0m ";

        $clio->newLine();
        $output .= "\n";

        // answer with a blank
        $clio->setAnswer(["t"]);
        $answer = $menu->menu(["one","two"]);
        $this->assertSame("two",$answer);
        $output .= "\\e[97;40mMenu  \\e[30;47m  o\\e[30;107mne\\e[0m \\e[30;47m  t\\e[30;107mwo\\e[0m ";

        $clio->newLine();
        $output .= "\n";

        // answer with a junk
        $clio->setAnswer(["x","on"]);
        $answer = $menu->menu(["one","two"]);
        $this->assertSame("one",$answer);
        $output .= "\\e[97;40mMenu  \\e[30;47m  o\\e[30;107mne\\e[0m \\e[30;47m  t\\e[30;107mwo\\e[0m ";
        $output .= "Try again.\n";

        $clio->newLine();
        $output .= "\n";

        // answer needing two characters
        $clio->setAnswer(["th"]);
        $answer = $menu->menu(["one","two","three"]);
        $this->assertSame("three",$answer);
        $output .= "\\e[97;40mMenu  \\e[30;47m  on\\e[30;107me\\e[0m \\e[30;47m  tw\\e[30;107mo\\e[0m \\e[30;47m  th\\e[30;107mree\\e[0m ";


        $clio->newLine();
        $output .= "\n";


        // test the user has answer badly 10 times and it drops out
        $userInput = array_fill(0,10,"junk");
        // answer needing two characters
        $clio->setAnswer($userInput);
        $answer = $menu->menu(["one","two","three"]);
        $this->assertNull($answer);
        $output .= "\\e[97;40mMenu  \\e[30;47m  on\\e[30;107me\\e[0m \\e[30;47m  tw\\e[30;107mo\\e[0m \\e[30;47m  th\\e[30;107mree\\e[0m ";

        for ($i=0; $i<10; ++$i) {
            $output .= "Try again.\n";
        }

        $this->expectOutputString($output);

    }

    /**
     * public function menu($choices)
     *
     * Display a menu and listen for a selection of one or more characters
     * param array $choices - simple array of choices
     *
     * return string - the selection
     */
    public function test_menuDefaults() {

        $clio = new ClioStub(Mode::VT100);
        $output = ClioStub::$startupSequencePrintable;

        $menu = new Menu($clio);

        // no choice, only default
        $answer = $menu->menu([],"default");
        $this->assertNull($answer);

        // same choice and default
        $answer = $menu->menu(["one","one"]);
        $this->assertNull($answer);

        // answer with a simple selection
        $clio->setAnswer(["one"]);
        $answer = $menu->menu(["one"],"two");
        $this->assertSame("one",$answer);

        $menuOutput = "\\e[97;40mMenu  \\e[30;47m  o\\e[30;107mne\\e[0m [two]";
        $output .= $menuOutput;

        $clio->newLine();
        $output .= "\n";

        // answer with just one character
        $clio->setAnswer(["t"]);
        $answer = $menu->menu(["one"],"two");
        $this->assertSame("two",$answer);
        $output .= $menuOutput;

        $clio->newLine();
        $output .= "\n";

        // answer with a blank
        $clio->setAnswer([""]);
        $answer = $menu->menu(["one"],"two");
        $this->assertSame("two",$answer);
        $output .= $menuOutput;

        $clio->newLine();
        $output .= "\n";

        // answer with a junk
        $clio->setAnswer(["x","on"]);
        $answer = $menu->menu(["one"],"two");
        $this->assertSame("one",$answer);
        $output .= $menuOutput;
        $output .= "Try again.\n";

        $clio->newLine();
        $output .= "\n";

        // answer needing two characters
        $clio->setAnswer(["th"]);
        $answer = $menu->menu(["one","two"],"three");
        $this->assertSame("three",$answer);
        $output .= "\\e[97;40mMenu  \\e[30;47m  on\\e[30;107me\\e[0m \\e[30;47m  tw\\e[30;107mo\\e[0m [three]";


        $clio->newLine();
        $output .= "\n";

        // provide the default in both choices and the default
        $clio->setAnswer([""]);
        $answer = $menu->menu(["one","two"],"one");
        $this->assertSame("one",$answer);
        $output .= "\\e[97;40mMenu  \\e[30;47m  t\\e[30;107mwo\\e[0m [one]";


        $clio->newLine();
        $output .= "\n";

        $this->expectOutputString($output);

    }

    public function test_menuTitles() {

        $clio = new ClioStub(Mode::VT100);
        $output = ClioStub::$startupSequencePrintable;

        $menu = new Menu($clio);

        // default menu
        $clio->setAnswer(["one"]);
        $answer = $menu->menu(["one"],"two");
        $this->assertSame("one",$answer);
        $menuOutput = "\\e[97;40mMenu  \\e[30;47m  o\\e[30;107mne\\e[0m [two]";
        $output .= $menuOutput;

        // no title
        $clio->setAnswer(["one"]);
        $menu->setTitle("");
        $answer = $menu->menu(["one"],"two");
        $this->assertSame("one",$answer);
        $output .= "\\e[30;47m  o\\e[30;107mne\\e[0m [two]";

        // longer title
        $clio->setAnswer(["one"]);
        $menu->setTitle("Please select:");
        $answer = $menu->menu(["one"],"two");
        $this->assertSame("one",$answer);
        $output .= "\\e[97;40mPlease select:  \\e[30;47m  o\\e[30;107mne\\e[0m [two]";

        $this->expectOutputString($output);
    }

}


