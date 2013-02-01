<?php

/**
* Switchable
*
* Perform percentage based splits. Useful in various split-testing endeavors.
*
* @author       Jeremy Harris <contact@jeremyharris.me>
* @license      MIT <http://opensource.org/licenses/MIT>
*
* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal 
* in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies 
* of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR 
* A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN 
* ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

class Switchable
{

    /**
    * @var array    Items to split between.
    */
    private $_items = array();

    /**
    * @var Item     Last item selected from split test
    */
    private $_last = null;

    /**
    * __construct()
    *
    * Allows array of items as parameter for object initialization.
    *
    * @param array  Items to assign to split test.
    */
    public function __construct($values = null)
    {
        if(is_array($values))
        {
            foreach($values as $value)
            {
                // Get values 
                $object = isset($value['object']) ? trim($value['object']) : "";
                $split = isset($value['split']) ? floatval($value['split']) : 0.00;
                $params = isset($value['params']) ? $value['params'] : null;

                // Add item to split test
                $this->addItem($object, $split, $params);
            }
        }       
    }

    /**
    * addItem()
    *
    * Add item to switchable object.
    *
    * @param Mixed      Item to be included in split test.
    * @param Integer    Percentage link is to be split on
    * @param Float
    */
    public function addItem($object, $split, $params = null)
    {
        // Create new item
        $item = new Item($object, $split, $params);

        // Add to split test array
        array_push($this->_items, $item);

        // Free memory
        $item = null;
    }

    /**
    * calculateSplitRanges()
    *
    * Calculate split ranges based on number of items and their split percentages.
    */
    public function calculateSplitRanges()
    {
        
        // Sort array of associative arrays by percentage with custom sorting function
        usort($this->_items, array($this, "_itemSort"));

        // Set starting percent
        $prev = 0.00;

        // Calculate low/high ranges for each item
        foreach($this->_items as $item)
        {
            $item->low_range = $prev + ($prev > 0.00 ? 0.01 : 0.00) ;
            $item->high_range = $item->split + $prev;

            // If high range 0.01 off from 100, adjust to 100
            if($item->high_range == 99.99) { $item->high_range = 100.00; }

            // If low range 0.01 off from 0, adjust to 0
            if($item->low_range == 0.01) { $item->low_range = 0.00; }

            $prev = $item->high_range;
        }

    }

    /**
    * getSplitValue()
    *
    * Get random value based on split percentage.
    *
    * @return mixed     Random value based on split percentage.
    */
    public function getSplitValue()
    {
        // Create random number
        $rand = $this->generateFloat(0.00, 100.00, 2);

        // Calculate number ranges for percentages
        $this->calculateSplitRanges();

        // Loop through values and check if meets random based on percentage.
        foreach($this->_items as $item)
        {
            // Check if offer is randomly selected
            if($rand >= $item->low_range && $rand <= $item->high_range)
            {
                // Save this item before we return it
                $this->_last = $item;
                
                // We found the random value in the split.
                return $item;
            }
        }

        // If we have reached this point, something went wrong and nothing matched. Lets just
        // return a random item or fail with false.
        $rand = mt_rand(0, count($this->_items)-1);
        return isset($this->_items[$rand]) ? $this->_items[$rand] : false;
    }

    /**
    * hasItems()
    *
    * Check if we have item to be split on
    *
    * @return Boolean   True if count of items array is > 0
    */
    public function hasItems()
    {
        if(count($this->_items) > 0)
            return true;

        return false;
    }

    /**
    * generateFloat()
    *
    * Generate random float value in number range with range of decimals
    *
    * @param Float      Starting range
    * @param Float      Ending range
    * @param Integer    Decimal places
    */
    public function generateFloat($minValue, $maxValue, $decimals)
    {
        $powerTen = pow(10, $decimals);
        return mt_rand($minValue * $powerTen, $maxValue * $powerTen) / $powerTen;
    }

    /**
    * _itemSort()
    *
    * Custom Item object sort on Split value
    *
    * @param Item       First Item object in comparison
    * @param Item       Second Item object in comparison
    * @return Boolean   Sort value, is First split higher than Second split?
    */
    private function _itemSort($a, $b)
    {
        if($a instanceof Item && $b instanceof Item)
            return $a->split > $b->split;
    }
}

/**
* Item 
*
* Data container for item being split tested.
*/
class Item
{
    /**
    * @var string       object result of split test
    */
    public $object = null;

    /**
    * @var int          Number from 1 to 100 for split percentage
    */
    public $split = null;

    /**
    * @var mixed        params data you may want to store with an item
    */
    public $params = null;

    /**
    * @var int          Lower match on random based on split percentage
    */
    public $low_range = null;

    /**
    * @var int          Upper match on random based on split percentage
    */
    public $high_range = null;

    /**
    * __construct()
    *
    * Initialize data item
    *
    * @param string     object result for splitting
    * @param int        Percentage to split (1 to 100)
    */
    public function __construct($object = "", $split = 0, $params = null)
    {
        $this->object = $object;
        $this->split = $split;
        $this->params = $params;
    }
}

?>