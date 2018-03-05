<?php

namespace Clio\Styling\Markup;

/**
 * This class is part of the Clio Open Source project, Clio.1happyplace.com
 * Copyright, Katie Ayres, katie@1happyplace.com
 *
 * Available through the MIT license
 * @license MIT
 *
 * The Justification class is a simple class allowing for a variety of ways
 * to specify justification, either Justification::LEFT, etc, or the word
 * "Left", "right", etc
 *
 */
class Justification
{
    /**
     * Justification constants
     */
    const NONE   = 200;
    const LEFT   = 201;
    const CENTER = 202;
    const RIGHT  = 203;

    /**
     * Used to look up strings that can be used instead of the justification constants
     * @var array
     */
    public static $justificationConstants =  [
        "NONE"   => 200,
        "LEFT"   => 201,
        "CENTER" => 202,
        "RIGHT"  => 203
    ];


    /**
     * Lookup table for the correct padding for a particular justification
     * @var array
     */
    public static $paddingLookup = [
        self::LEFT      => STR_PAD_RIGHT,
        self::CENTER    => STR_PAD_BOTH,
        self::RIGHT     => STR_PAD_LEFT,
        self::NONE      => STR_PAD_RIGHT];
    
    

    /**
     * Function to take a value and turn it into a justification constant
     *
     * @param string | int $value - if a string, it can be "left", "RIGHT", etc..., if an int, just needs to be a constant
     * @return int - the constant it represents or LEFT if it is garbage
     */
    public static function getJustificationConstant($value) {

        // if the value coming in, is a integer, ensure it is one of the constants
        if (is_int($value)) {

            // get the justification integer values
            $values = array_values(self::$justificationConstants);

            // if the integer submitted is one of those values, return it
            if (in_array($value, $values)) {

                // return the valid value
                return $value;
            }

        }
        // if the value coming in is a string
        else if (is_string($value)) {

            // trim the string
            $value = trim($value);

            // transform to uppercase
            $value = strtoupper($value);

            // if it is in the array of string values
            if (isset(self::$justificationConstants[$value])) {

                // return the matching integer value
                return self::$justificationConstants[$value];


            } else {

                // not a valid string
                return self::LEFT;
            }

        } else {

            // something went wrong, return left
            return self::LEFT;
        }

        return self::LEFT;
    }

 

}