<?php

use Clio\Clio;
use Clio\HTML\HTML;




/**
 * Class HTMLStub
 * 
 * This uses a Terminal stub and also provides its own stubbing
 * switching the escape character for a printable one
 */
class ClioStub extends Clio {
    
    protected $answers = [];
    protected $answerPlacement = 0;

    public static $startupSequence = "\e[H\e[2J";
    public static $startupSequencePrintable = "\\e[H\\e[2J";


    /**
     *
     * All output goes to this function
     *
     * @param string $text
     */
    public function output($text)
    {
        echo str_replace("\033","\\e",$text);
    }

    /**
     * Anytime the cursor is returned to the leftmost column, this is fired
     */
    public function carriageReturn()
    {

    }
    /**
     * All about goes to this function
     * @param $text
     */
    public function echoIt($text) {
        // replace the escape with a printable version of escape
        $text = str_replace("\e","\\e",$text);

        // echo that text instead
        echo $text;
    }
    
    public function setAnswer($answers) {
        $this->answers = $answers;
        $this->answerPlacement = 0;
    }

    public function justPrompt($text) {

        $answer = $this->answers[$this->answerPlacement];

        if ($this->answerPlacement < (count($this->answers) - 1)) {
            ++$this->answerPlacement;
        }

        return $answer;
    }
    

}