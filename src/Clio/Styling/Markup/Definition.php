<?php
namespace Clio\Styling\Markup;


use Clio\Styling\StyleInterface;

/**
 * This class is part of the Clio Open Source project, Clio.1happyplace.com
 * Copyright, Katie Ayres, katie@1happyplace.com
 *
 * Available through the MIT license
 * @license MIT
 *
 * The Definition class provides the capability to define a set of markup 
 * symbols and the corresponding styles.  It then helps process text with
 * that markup.
 *
 */
class Definition
{

    /**
     * The list of Markup objects
     * @var Markup[]
     */
    protected $list = [];

    /**
     * Load up an associative array with the rightmost positions of symbols
     * 
     * @param string $text - the text to scan 
     * @return array - associative array with keys as the list of symbols
     *                 and values of the rightmost position of it in the text
     */
    private function getSymbolPositions($text) {
        
        // get the list of symbols
        $symbols = $this->getSymbols();
        
        // create an associative array to store the right most position
        // of each of the symbols
        $positions = array_combine($symbols, array_fill(0,count($symbols),-1));

        // go through each symbol
        foreach ($symbols AS $nextSymbol) {

            // get the rightmost position
            $pos = strrpos($text, $nextSymbol);

            // if one was found
            if ($pos !== false) {

                // save the position of the last character of the symbol
                $positions[$nextSymbol] = $pos + (strlen($nextSymbol)-1);
            }

        }

        // return the array of symbol positions
        return $positions;
    }



    ///////////////////////////////////////////////////////////////////////////////////
    //                              Public Methods                                   //
    ///////////////////////////////////////////////////////////////////////////////////

    /**
     * Add a markup to the array
     * 
     * @param string $symbol
     * @param StyleInterface $style
     * 
     * @return boolean - true if it added OK, false if it was not added
     */
    public function addMarkup($symbol, StyleInterface $style) {

        // if it is not a string
        if (!is_string($symbol)) {
            
            // don't add it
            return false;
        }

        // Get the list of symbols
        $symbols = $this->getSymbols();

        // Ensure the symbol of the submitted markup was not already on the list
        if (in_array($symbol,$symbols)) {

            // markup was not added
            return false;
        }

        // made it! add it to the list
        $this->list[] = new Markup($symbol, $style);

        // it was added to the list
        return true;
    }

    /**
     * Return a list of the symbols for all the markups in the list
     *
     * @return string[]
     */
    public function getSymbols() {

        // initialize the list of symbols
        $symbols = [];
        
        // go through each markup
        foreach ($this->list AS $nextMarkup) {
            
            // append it to the list
            $symbols[] = $nextMarkup->getSymbol();
            
        }
        
        // return the list
        return $symbols;
        
    }

    /**
     * Get the styling that relates to a particular symbol
     * 
     * @param string $symbol
     * 
     * @return StyleInterface | null (if not found)
     */
    public function getStyling($symbol) {

        // if the symbol is not a string
        if (!is_string($symbol)) {

            // return null
            return null;
        }

        // go through each markup
        foreach ($this->list AS $nextMarkup) {

            // if the symbols match
            if ($nextMarkup->getSymbol() === $symbol) {
                
                // return the style
                return $nextMarkup->getStyle();
            }
        }
        
        return null;
    }

    /**
     * Get the Markup object related to the symbol
     *
     * @param string $symbol
     *
     * @return Markup | null (if not found)
     */
    public function getMarkup($symbol) {

        // if the symbol is not a string
        if (!is_string($symbol)) {

            // return null
            return null;
        }

        // go through each markup
        foreach ($this->list AS $nextMarkup) {

            // if the symbols match
            if ($nextMarkup->getSymbol() === $symbol) {

                // return the markup
                return $nextMarkup;
            }
        }

        return null;
    } 


    /**
     * Find the next position that contains markup
     *
     * @param string $text
     * @param string $markupFound - the markup that was found at that position
     *
     * @return int|null - the position in the text where the markup was found or null if nothing was found
     */
    public function findNextMarkup($text, &$markupFound) {

        // initialize the smallest position with the length of string
        $smallestPos = strlen($text);
        
        // get the markup symbols
        $symbols = $this->getSymbols();
        
        // if there is no markup defined, then there are none to be found
        if (count($symbols) === 0) {
            
            // return that nothing was found
            return null;
        }

        // initialize the markupFound
        $markupFound = null;

        // go through each markup
        foreach ($symbols AS $nextSymbol) {

            // search for markup in the text
            $pos = strpos($text,$nextSymbol);

            // if something was found
            if ($pos !== false) {

                // if the position is closer to the beginning than before
                if ($pos < $smallestPos) {

                    // save this position
                    $smallestPos = $pos;

                    // also save this markup
                    $markupFound = $nextSymbol;
                }
            }
        }

        // if markup was not found
        if ($markupFound === null) {

            // no position to report
            return null;

            // markup was found
        } else {

            // we did find the markup, report the earliest one in the text
            return $smallestPos;
        }
    }


    /**
     * Strip out any defined markup symbols
     * @param string $text
     * 
     * @return string - the text without any markup symbols
     */
    public function stripMarkupSymbols($text) {
        
        // get all the symbols
        $symbols = $this->getSymbols();
        
        // go through each symbol
        foreach ($symbols AS $next) {
            
            // replace the next symbol with nothing
            $text = str_replace($next,"",$text);
        }
        
        // return the text without symbols
        return $text;
        
    }

    /**
     * Tests whether the string has any valid markup
     * @param string $text - the text in which to search
     * 
     * @return boolean
     */
    public function hasMarkup($text) {

        // get all the symbols
        $symbols = $this->getSymbols();

        // go through each symbol
        foreach ($symbols AS $next) {

            // search for markup in the text
            $pos = strpos($text,$next);

            // if something was found
            if ($pos !== false) { 
                
                // we found at least one markup
                return true;
                
            }
            
        }

        // went through all the symbols and didn't find a match
        return false;       
        
    }

 
    /**
     * Shorten the text by the length, but leaving any markup symbols
     * @param string $text
     * @param int $length
     *
     * @return string
     */
    public function shortenText($text, $length) {

        // if there is no text to shorten
        if (strlen($text) === 0) {
            
            // return the empty string
            return $text;
        }
        
        // get a copy of the text without the markup symbols
        $strippedText = $this->stripMarkupSymbols($text);

        // if the length provided is greater than the length
        // of the stripped text
        if ($length > strlen($strippedText)) {

            // simply make it exactly the length of the
            // stripped text, so all non-symbol
            // text is stripped, but all markup remains
            $length = strlen($strippedText);
            
        }

        // get the symbols
        $symbols = $this->getSymbols();

        // if there are no symbols
        if (count($symbols) === 0) {

            // Just return the shortened string
            return substr($text, 0, -$length);
        }

        // create an associative array to store the right most position
        // of each of the symbols
        $positions = $this->getSymbolPositions($text);

        // provide storage of any trailing symbols that might remain
        // e.g. This is **bold** would turn into to This** if it was 4 was the desired length
        // this allows the markup to run its course and leave things as expected
        $trailingSymbols = "";

        // initialize the counter
        $i = 0;

        // start with the end and keep taking off characters
        while ($i < $length) {
            
            // remember if a symbol was found
            $symbolFound = false;
            
            // go through each symbol
            foreach ($positions AS $symbol => $position) {

                // see if any of these position equals the last position
                if ($position === (strlen($text)-1)) {

                    // append the symbol to the trailing symbols string
                    $trailingSymbols = $symbol . $trailingSymbols;

                    // subtract it out of the text
                    $text = substr($text,0,-(strlen($symbol)));

                    // recalculate the positions of each symbol now that the string
                    // has changed, this could be more efficient, by only
                    // changing the one found, but in the interest of shared code...
                    $positions = $this->getSymbolPositions($text);
                    
                    // a symbol was found, get back to the top of the while
                    $symbolFound = true;
                    
                    // drop out of searching for symbols, if there is another
                    // symbol at the new end of it, we need to search all symbols
                    // for a match
                    continue;

                }

            }
        
            // if the last character is not a symbol
            if (!$symbolFound) {
                
                // if we get this far, then there is not a symbol at the end of the string
                // take off the last character
                $text = substr($text, 0, -1);

                // one more actual character was skipped
                ++$i;             
            }
            
        }
    
        // return the text and any straggling symbols
        return $text . $trailingSymbols;

    }

    /**
     * Justify text
     *
     * @param string $text - the text to justify
     * @param string | int $justification - if a string, it can be "left", "RIGHT", etc..., if an int, just needs to be a constant
     * @param int $width - the width to fill
     * @return string
     */
    public function justify($text, $justification, $width) {

        // try to make a string out of it
        $text = strval($text);

        // get the justification constant
        $justification = Justification::getJustificationConstant($justification);

        // if the width is not an integer or less than or equal to zero, or the string length is greater than the width
        if (!is_int($width) || $width <= 0) {

            // just return the text
            return $text;

        }

        // strip out the markup since they are invisible
        $strippedText = $this->stripMarkupSymbols($text);


        // if the text is actually wider than the width
        if (strlen($strippedText) > $width) {
            
            // ask the markup definition to shorten text but preserve markup symbols
            return $this->shortenText($text,strlen($strippedText) - $width);
            
            // justify the text
        } else {

            // calculate the space needed to widen to the width
            $delta = $width - strlen($strippedText);

            // if the justification is center
            if ($justification == Justification::CENTER) {

                // check if it is odd
                if ($delta % 2 !== 0) {

                    // left margin is one less than the right margin
                    $leftMargin = $delta / 2;
                    $rightMargin = ($delta + 1)/ 2;

                } else {

                    // both left and right margin are half of the actual width
                    $leftMargin = $rightMargin = $delta / 2;
                }

                // the padding is in the center
                return $this->pad($text,$leftMargin, $rightMargin);


                // if the justification is right
            } else if ($justification == Justification::RIGHT) {

                // the padding on the left and the text on the right
                return $this->pad($text, $delta, 0);

                // if the justification is left or poorly defined
            } else {

                // return the text on the left, and padding to achieve the width
                return $this->pad($text, 0, $delta);

            }

        }

    }

    /**
     * Pad the text with left and right padding, but place any first
     * and last markup beyond the padding, so it gets the same styling.
     *
     * @param string $text
     * @param int $leftPadding
     * @param int $rightPadding
     *
     * @return string
     */
    public function pad($text, $leftPadding, $rightPadding) {

        // initialize the returned text
        $returnedText = $text;
        
        // get the current symbols
        $symbols = $this->getSymbols();

        // if any left padding was defined
        if ($leftPadding > 0) {

            // first check if there is markup at the beginning
            $symbol = null;

            // go through each symbol
            foreach ($symbols AS $next) {
                
                // see if it is at the beginning of the string
                $pos = strpos($returnedText,$next);
                
                // if the position is zero
                if ($pos === 0) {
                    
                    // remember this symbol
                    $symbol = $next;
                    
                    // get out
                    break;
                }
            }
            
            // create the empty string to pad
            $padding = str_pad("",$leftPadding," ");

            // if a symbol is at the beginning of the string
            if ($symbol) {
                
                // extract the symbol from the beginning
                $returnedText = substr($returnedText, strlen($symbol));

                // prepend the symbol and add the left padding
                $returnedText = $symbol . $padding . $returnedText;
                
            // no symbol was found
            } else {
                
                // just prepend the left padding
                $returnedText = $padding . $returnedText;
            }
        }

        // if right padding is specified
        if ($rightPadding > 0) {
            
            // first check if there is markup at the beginning
            $symbol = null;

            // get the length of the returned string
            $length = strlen($returnedText);

            // go through the symbols
            foreach ($symbols AS $next) {
                
                // figure out if the next symbol is at the end of the string
                $pos = strrpos($returnedText,$next);
                
                // if it was found at the end of the string
                if ($pos === $length - strlen($next)) {
                    
                    // remember the symbol
                    $symbol = $next;
                    
                    // get out
                    break;
                }
            }

            // create the right padding
            $padding = str_pad("",$rightPadding," ");
            
            // if there is a symbol at the end
            if ($symbol) {
                // extract the symbol from the beginning
                $returnedText = substr($returnedText, 0, -strlen($symbol));

                // prepend the symbol and add the left padding
                $returnedText = $returnedText . $padding . $symbol;
                
            // there is no symbol
            } else {
                
                // just append the padding
                $returnedText .= $padding;
            }
        }

        // return the newly padded text
        return $returnedText;

    }

    /**
     * Check if the text at the index of the text is one of the symbols
     * @param string $text
     * @param int $index
     * @return null|string - return the symbol or null if not found
     */
    private function isSymbol($text, $index) {

        // get the current markup symbols
        $symbols = $this->getSymbols();

        // go through each symbol
        foreach ($symbols as $nextSymbol) {

            // if the first characters match...
            if ($text[$index] === $nextSymbol[0]) {

                // get a substring as long as the symbol from the original text
                if (($index + strlen($nextSymbol)) <= strlen($text)) {

                    // get the substring in the text that might match
                    $sub = substr($text,$index,strlen($nextSymbol));

                    // if those match, we have found a symbol in the original string
                    if ($sub === $nextSymbol) {

                        // add the symbol to the returned string
                        return $nextSymbol;
                        
                    }
                    // @codeCoverageIgnoreStart
                }
            }
        }
        // @codeCoverageIgnoreEnd

        return null;
    }
    /**
     * Merge together two strings, the original text which might have markup and
     * the wrapped version of it which has had the markup removed, but has been wrapped
     * 
     * @param string $originalText
     * @param string $wrappedText
     * 
     * @return string - the new string with the original text, but the newlines inserted
     *                  in the proper place
     */
    protected function merge($originalText, $wrappedText) {
        
        
        // the newly built string
        $returnedString = "";
        
        // the index for the wrapped text
        $wrappedTextIndex = 0;

        // the index for the original text
        $originalTextIndex = 0;
        
        // run until run to either the end of the original text or wrapped text
        while (($originalTextIndex < strlen($originalText)) && ($wrappedTextIndex < strlen($wrappedText))) {

            // if the characters are the same
            if ($originalText[$originalTextIndex] === $wrappedText[$wrappedTextIndex]) {

                // add it to the new string
                $returnedString .= $originalText[$originalTextIndex];
                
                // move past the character on both strings
                ++$wrappedTextIndex;
                ++$originalTextIndex;

            // the characters are different
            } else {

                // see if the next thing in the original text is a symbol
                $symbol = $this->isSymbol($originalText, $originalTextIndex);

                // a symbol was matched
                if ($symbol) {

                    $returnedString .= $symbol;
                    $originalTextIndex += strlen($symbol);
                    
                // if we did not find a symbol
                } else {
                    
                    // if the wrapped text has a new line
                    if ($wrappedText[$wrappedTextIndex] === "\n") {
                        
                        // add a newline character to the original text
                        $returnedString .= "\n";
                        
                        // if the original text has a blank line, then that can
                        // be skipped since the newline replaces the blank
                        if ($originalText[$originalTextIndex] === " ") {
                            
                            // skip over the blank
                            ++$originalTextIndex;
                        }

                        // skip over the \n
                        ++$wrappedTextIndex;
                    }
                }
            }
        }
        
        // if there is still leftover text in the original text
        if ($originalTextIndex < strlen($originalText)) {
            
            // append it to the returned string
            $returnedString .= substr($originalText,$originalTextIndex);           
        }

        // if there is still leftover text in the wrapped text
        if ($wrappedTextIndex < strlen($wrappedText)) {
            
            // append it to the returned string
            $returnedString .= substr($wrappedText,$wrappedTextIndex);           
        }

        // Yah!
        return $returnedString;

    }

    /**
     * Word wrap just the text with the width specified, ignoring
     * the space taken by the markup characters
     * .
     * The beginning offset is the placement of the first character
     * which might not be at 0.
     *
     * @param string $text
     * @param int $width
     * 
     * @return string - the same text, with newlines to wordwrap
     *                  ignoring but preserving the markup
     */
    public function wordwrap($text, $width) {

        // get rid of any straggling \n's
        $text = str_replace("\n","",$text);

        // strip off any markup symbols
        $strippedText = $this->stripMarkupSymbols($text);

        // now ask PHP to word wrap it, the last parameter $cut is set
        // to true, so any words that can't make it, will be unceremoniously
        // cut in half, only comes up if a word is wider than width
        $strippedText = wordwrap($strippedText, $width, "\n", true);


        // return the newly built string with newline characters
        // properly placed while ignoring and preserving the markup
        return $this->merge($text,$strippedText);

    }
    


}