<?php


namespace Clio\Widgets\Lists;


/**
 * Class UnorderedList - creates a list that has bullets
 *
 * @package Clio\HTML\Lists
 */
class UnorderedList extends BulletedList
{

    /**
     * The bullet character for a list
     * @var string
     */
    protected $defaultBullet = "-";

    /**
     * The bullet character for a list
     * @var string
     */
    protected $bullet = "-";
    
    /**
     * Set the bullet string for lists
     *
     * @param $str
     * @return $this
     */
    public function setBullet($str) {

        // store the bullet, if it is null..
        if (is_null($str)) {

            // store the default bullet
            $this->bullet = $this->defaultBullet;

        // something was sent in
        } else {

            // store it
            $this->bullet = $str;
        }

        return $this;
    }
    

    /**
     * Display a list item
     * @param $text - the item
     * @return $this
     */
    public function li($text) {
        
        // draw the list item
        $this->drawListItem($this->bullet . " ", $text);

        // chaining
        return $this;
    }



}