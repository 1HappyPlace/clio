<?php

use Clio\Styling\Markup\Justification;
use PHPUnit\Framework\TestCase;

class JustificationTest extends TestCase
{




    public function setUp(): void
    {


    }

    public function tearDown(): void
    {

    }


    /**
     * static function getJustificationConstant($justification)
     *
     * Function to take a value and turn it into a justification constant
     *
     * param string | int $justification - if a string, it can be "left", "RIGHT", etc..., if an int, just needs to be a constant
     * return int - the constant it represents or LEFT if it is garbage
     */
    public function test_getJustificationConstant() {

        // straight up justification constants
        $answer = Justification::getJustificationConstant(Justification::NONE);
        $this->assertSame(Justification::NONE,$answer);

        $answer = Justification::getJustificationConstant(Justification::LEFT);
        $this->assertSame(Justification::LEFT,$answer);

        // an integer that is CENTER
        $answer = Justification::getJustificationConstant(202);
        $this->assertSame(Justification::CENTER,$answer);


        // valid strings
        $answer = Justification::getJustificationConstant("None ");
        $this->assertSame(Justification::NONE,$answer);

        $answer = Justification::getJustificationConstant("center");
        $this->assertSame(Justification::CENTER,$answer);

        $answer = Justification::getJustificationConstant("RIGHT");
        $this->assertSame(Justification::RIGHT,$answer);

        $answer = Justification::getJustificationConstant("   leFt   ");
        $this->assertSame(Justification::LEFT,$answer);


        // invalid input, which produces left justification
        // an integer that is not any color constant
        $answer = Justification::getJustificationConstant(1214);
        $this->assertSame(Justification::LEFT,$answer);

        // an integer that is not any color constant
        $answer = Justification::getJustificationConstant(0);
        $this->assertSame(Justification::LEFT,$answer);


        // an string that is not any justification constant
        $answer = Justification::getJustificationConstant("ce nter");
        $this->assertSame(Justification::LEFT,$answer);

        // an string that is not any color constant
        $answer = Justification::getJustificationConstant("junk");
        $this->assertSame(Justification::LEFT,$answer);

        // outright invalid types
        $answer = Justification::getJustificationConstant(null);
        $this->assertSame(Justification::LEFT,$answer);

        $answer = Justification::getJustificationConstant(new \stdClass());
        $this->assertSame(Justification::LEFT,$answer);

        $answer = Justification::getJustificationConstant(["green"]);
        $this->assertSame(Justification::LEFT,$answer);

        $answer = Justification::getJustificationConstant(1.0);
        $this->assertSame(Justification::LEFT,$answer);
    }



 

}


