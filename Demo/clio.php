<?php
require '../vendor/autoload.php';
use ANSI\Color\Color;
use ANSI\Color\Mode;
use Clio\Clio;
use Clio\Style\Style;

$clio = new Clio(Mode::RGB);

// clio usage to do the same thing with quick chaining<br>
$clio->line("Hello World!");

$clio->pause()->nl();

$clio = new Clio(Mode::RGB, "Antique White", "Royal Blue");

// clear the screen to fill with new colors
$clio->clearScreen();

$clio->line("Hello World!");
$clio->out("From Clio")->clear()->nl(2);
$clio->pause();


$clio = new Clio(Mode::RGB, "dimgray", "white");
$clio->clearScreen();

// set up the header style
$h1 = new Style();
$h1->setTextColor("lime")->setUnderscore();

// output a title and text
$clio->styleLine("Title",$h1);
$clio->out("Some regular text underneath the title")->nl(2);

// output another title and text
$clio->styleLine("Another Title",$h1);
$clio->out("More text...")->nl(2);

$clio->pause()->nl();

$danger = Color::orangered();
$OK = new Color("lime green");
        
$clio->colors("white",$danger)->display("An error has occurred")->nl();
$clio->fillColor($OK)->display("Everything is OK")->nl();

$clio->clear()->pause()->nl();

$clio = new Clio(Mode::RGB, "white","dimgray");
$clio->clearScreen();

// set up a header style
$h1 = (new Style())->setTextColor("Wheat")->setUnderscore(true);

// set up a warning and OK styles
$danger = (new Style())->setFillColor("Red");
$OK = (new Style())->setTextColor("springgreen");

// display clio.1happyplace.com
$clio->styleLine(str_pad("clio.1happyplace.com",75),$h1);
$clio->line("Last ping: 08/15/16");
$clio->out("Status: ")->style("Low Memory",$danger)->nl(2);

// display skippingscenes.com
$clio->styleLine(str_pad("skippingscenes.com",75),$h1);
$clio->line("Last ping: 08/15/16");
$clio->out("Status: ")->style("OK",$OK)->nl(2);

// clear the styling upon exit
$clio->clear(true);
