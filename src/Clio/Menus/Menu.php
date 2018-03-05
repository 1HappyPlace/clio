<?php

namespace Clio\Menus;
use Clio\Clio;
use Clio\Styling\Style;
use Clio\Styling\StyleInterface;


/**
 * This class is part of the Clio Open Source project, Clio.1happyplace.com
 * Copyright, Katie Ayres, katie@1happyplace.com
 *
 * Available through the MIT license
 * @license MIT
 *
 * The Menu class generates a horizontal menu for option selection.  Based on the choices
 * offered, this class will figure out the minimal number of characters needed for the
 * selection to be made and highlight those colors, allowing for easy shell terminal style
 * efficient typing.
 *
 */
class Menu
{
    /**
     * The first characters of the menu
     * @var string
     */
    private $title = "Menu";
    
    /**
     * The Menu title text and fill color
     * @var null|string
     */
    private $titleStyle = null;
    

    /**
     * The background text and fill color in the space between each choice and the unique part of each choice
     * @var null|string
     */
    private $highlightStyle = null;
    
    
    /**
     * The text and fill color of all the choices
     * @var null|string
     */
    private $choiceStyle = null;
    

    /**
     * The clio object that handles the output to the terminal
     * @var Clio|null
     */
    private $clio = null;


    /**
     * Menu constructor.
     * @param clio $clio -the HTML Clio object, generally want just one instance to represent the current terminal
     * @param string | null $title - if null, then no text is shown before the choices
     * @param StyleInterface | null $titleStyle - colors for the first few characters that are the style
     * @param StyleInterface | null $highlightStyle - colors for the unique character highlighting
     * @param StyleInterface | null $choiceStyle - colors for the regular text
     */
    public function __construct($clio, StyleInterface $titleStyle = null, StyleInterface $highlightStyle = null, StyleInterface $choiceStyle = null, $title = "Menu" )
    {
        // save the HTML object
        $this->clio = $clio;
        
        // save the title
        $this->title = $title;
        
        // if the title style is not set
        if (is_null($titleStyle)) {
            
            // use base colors of white and black
            $this->titleStyle = (new Style())->setColors("white","black");   
           
        // title colors are defined
        } else {
            
            // save the desired title style
            $this->titleStyle = clone $titleStyle;
        }

        // if the highlight style is not defined
        if (is_null($highlightStyle)) {
            
            // use the base colors of black and gray
            $this->highlightStyle = (new Style())->setColors("black","gray218");           
        
        // if highlight color has been defined
        } else {
            
            // save it
            $this->highlightStyle = clone $highlightStyle;
        }

        // if the choice style is undefined
        if (is_null($choiceStyle)) {
            
            // use the base colors of gray and white
            $this->choiceStyle = (new Style())->setColors("gray18","white");     
            
        // if it has been defined
        } else {
            
            // save it
            $this->choiceStyle = clone $choiceStyle;
        }
        

    }

    /**
     * Setter for the title
     * 
     * @param string | null $title - if null then no title will be shown
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
    


    /**
     * Find the narrowest length in an array of strings
     * @param array $arr - array of strings, it will skip over anything that is not a string
     * @return int
     */
    private function narrowest($arr) {

        // return the narrowest string
        return min(array_map('strlen', $arr));
    }


    /**
     * See if the needle is in the haystack, but matching is loose:
     *      - case insensitive
     *      - one or more characters from the left to right (entire word not necessary)
     * @param string $needle - the string in which to test against the haystack
     * @param array  $haystack - it will skip anything that is not a string
     * @return bool
     */
    private function inArrayStartsWith($needle, $haystack) {

        // lower the needle
        $needle = strtolower($needle);

        // get the length of the needle
        $length = strlen($needle);

        // go through the haystack array
        foreach ($haystack AS $hay) {

            // ensure it is valid
            if (is_string($hay)) {

                // lower the hay
                $hayLower = strtolower($hay);

                // if the first part of the hay matches the needle, you are in business
                if (substr($hayLower, 0, $length) === $needle) {

                    // return current hay in the haystack array
                    return $hay;
                }
            }

        }

        // never found the needle
        return false;
    }

    /**
     * Determine how many characters are needed to uniquely identify each choice in the beginning characters
     * It will generally be 1, but might be 2, if there are two conflicting choices such as Text and Transform
     * @param string[] $choices
     * @param string $default
     * @return int | null will return the unique character count or null if it can't be done
     */
    private function determineUniqueCharacterCount($choices, $default) {

        // we need at least one choice
        if ((count($choices) === 0) && is_null($default)) {

            // bad data
            return null;
        }

        // if the default was provided
        if ($default) {

            // temporarily add it to the choices for calculate of the unique character count
            $choices[] = $default;
        }

        $temp = [];
        // change all the choices to lower case, since everything is capitalization agnostic in menus
        foreach ($choices AS $choice) {
            $temp[] = strtolower($choice);
        }
        $choices = $temp;

        // find the shortest string in the choices, this is our farthest we can go with unique characters
        $maxLimit = $this->narrowest($choices);

        // keep track of the character count needed
        $minCharacterCount = 0;

        // determine whether to keep trying an even longer width
        $keepGoing = true;

        // while we should keep going
        while ($keepGoing) {

            // store an array of unique choices
            $unique = [];

            // increase the character count
            ++$minCharacterCount;

            // if we have gone beyond the smallest string
            if ($minCharacterCount > $maxLimit) {

                // error, it is impossible to have a unique substring to indicate one string out of choices
                return null;
            }

            // scan the choices
            foreach ($choices AS $choice) {

                // get the leading characters based on the current character count
                $leadingCharacters = substr($choice,0,$minCharacterCount);

                // if the leading characters are not in the array...
                if (!in_array($leadingCharacters,$unique)) {

                    // add it to the list of unique choices
                    $unique[] = $leadingCharacters;

                } else {

                    // we have a collision, time to break out
                    break;
                }
            }

            // if we have as many unique choices as we do choices, we are done
            $keepGoing = (count($choices) !== count($unique));

        }

        // return the smallest width with uniqueness
        return $minCharacterCount;

    }



    /**
     * Display a menu and listen for a selection of one or more characters
     * @param array $choices - simple array of choices
     * @param string | null $default - the default choice
     *          - if null, then the last choice will be the default
     *          - if it is in the choices, then it will be listed again as a default
     *          - if it is not in the list of choices, it will be only listed as the default
     *
     * @return string - the selection
     */
    public function menu($choices, $default = null) {


        // if there are no choices
        if (count($choices) == 0) {

            // drop out
            return null;
        }

        // if a default was defined
        if ($default) {

            // if the default is in the array
            if (($index = array_search($default, $choices)) !== false) {

                // move it to the end
                array_splice($choices, $index, 1);

            };           
        }

        
        // figure out how many characters are needed to make each choice unique
        $uniqueCharacterCount = $this->determineUniqueCharacterCount($choices, $default);

        // if there was a problem figuring out the minimum characters needed to make each choice unique
        if (is_null($uniqueCharacterCount)) {

            // don't draw the menu
            return null;
        }

        // only show the title if one is defined
        if ($this->title) {
            
            // display the header
            $this->clio->style($this->titleStyle)->display($this->title . "  ")->clear();
        }


        // go through each choice and display it
        for ($c=0; $c<count($choices); ++$c) {

            // shortcut for the next choice
            $choice = $choices[$c];


            //  display the fill space and the unique characters
            $this->clio->style($this->highlightStyle)->display("  " . substr($choice, 0, $uniqueCharacterCount))->clear();

            // display the rest of the menu choice
            $this->clio->style($this->choiceStyle)->display(substr($choice, $uniqueCharacterCount))->clear();

            // empty space
            $this->clio->display(" ");
            

        }
        
        if ($default) {
            
            // display it in brackets and no particular styling
            $this->clio->display("[" . $default . "]");
            
        }

        // initialize the stop gap counter
        $numTries = 0;
        
        // make a list of valid answers
        $validAnswers = $choices;
        
        // if the default is also defined
        if ($default) {
            
            // append it
            $validAnswers[] = $default;
        }
        
        // keep going until the menu says a choice has been made
        while (true) {

            // prompt for a selection
            $answer = $this->clio->prompt("");

            // if the answer is empty
            if ($answer == "") {

                // if a default was defined
                if ($default) {

                    // return it
                    return $default;

                } else {

                    // default wasn't defined
                    echo "Try again.\n";
                }

            // have an actual answer, check to see if it is the beginning of a selection
            } else if ($answer = $this->inArrayStartsWith($answer, $validAnswers)) {

                // it is the beginning of an answer, return that answer
                return $answer;
                
            // doesn't match    
            } else {
                
                // didn't match anything
                echo "Try again.\n";
            }
            
            // increase the backup counter
            ++$numTries;
            
            // if they have tried 10 times
            if ($numTries > 9) {
                
                // drop out
                return null;
            }
            
        };

        // this will never happen, but to make code inspector happy
        return null; // @codeCoverageIgnore


    }



}