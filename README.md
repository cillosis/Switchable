Switchable
==========

PHP Library for Split Testing. It allows you to take multiple items or objects and split among them based on a percentage. Here is an example splitting two items:

    include_once "Switchable.class.php";

    // First item to switch
    $site1 = new stdClass();
    $site1->object = "http://www.github.com"; //I am some type of string/number/array/object. I might be from a database result.
    $site1->split = 33.33;

    // Second item to switch
    $site2 = new stdClass();
    $site2->object = "http://www.jeremyharris.me"; //I am some type of string/number/array/object. I might be from a database result.
    $site2->split = 66.66;

    // Create Switchable Object
    $switch = new Switchable();

    // Add our items to Switchable
    $switch->addItem( $site1->object, $site1->split );
    $switch->addItem( $site2->object, $site2->split );

    // If we have items, lets switch on them
    if ( $switch->hasItems() ) {

        // Returns object of type "Item"
        $random = $switch->getSplitValue();

        echo("We are randomly visiting URL: " . $random->object);

    }

This library has other features such as:

* Third parameter to addItem() function which is array of additional data/parameters you wish to pass with it and get back when random selection complete.
* Full support for float-based percentages such as 32.69%.
* Fallback in instances where splits may unexpectedly not add up to 100%, we just pick a random value rather than dying. 

If you have any questions or suggestions, please direct them to contact@jeremyharris.me. Thanks!
