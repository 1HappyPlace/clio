#Clio

Developing a quick and clear user interface for PHP command line programs can be difficult. And yet, command
line programming can be useful, particularly with non-public facing software that you need to spin up.

Clio was created to help in development of usable PHP command line interface (CLI) programs. Although
there are no graphic capabilities for the command line, there are some things working in our favor.
Monospaced fonts line up nicely and can be used to present data in a succinct way.  For some terminal 
emulators (PHPStorm for one), you can use the full 24 bit RGB coloring, also making for interesting options.

The Clio project begins with the [Clio Class](http://clio.1happyplace.com/clio/clio.html), the heart and soul 
of the architecture.  In fact, you can just use Clio and be on your way if your needs aren't complicated. 
It is easy to send out text to the terminal, occasionally bold or underscore text and use a set of colors to 
highlight information. 

If you wish to get more complex, there are a set of classes that support that allow for easy styling. Namely:

* [Color Class](http://clio.1happyplace.com/clio/color.html) - allows you to select a color, and depending on your terminal's color capability, the class
              will automatically command the terminal to show the closest color available.
* [Style Class](http://clio.1happyplace.com/clio/style.html) - allows you to create a style, that is a combination of bolding and underscoring and the text and fill colors, allowing
         for consistent styling.
* [Paragraph Class](http://clio.1happyplace.com/clio/style.html) - create a class to style and display all paragraphs consistently
* [Title Class](http://clio.1happyplace.com/clio/style.html) - create a class to style and display all headings consistently
* [Menu Class](http://clio.1happyplace.com/clio/style.html) - use a fast and intuitive menuing system
* [Paragraph Class](http://clio.1happyplace.com/clio/style.html) - create a class to style and display all paragraphs consistently
* [Lists Class](http://clio.1happyplace.com/clio/style.html) - display ordered and unordered lists
* [Table Class](http://clio.1happyplace.com/clio/style.html) - create tables for easy data display

        
         
**Be sure to read [Getting Started with Clio](http:clio.1happyplace.com/getting-started/clio.html)** and running the **[Demo](https://github.com/1HappyPlace/Clio-demo)**!
