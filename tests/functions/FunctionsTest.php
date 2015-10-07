<?php

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

/**
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class FunctionsTest extends PHPUnit_Framework_TestCase
{
    /** @var FunSets */
    private $funSets;

    function test_ContainsIsImplemented()
    {
        // We characterize a set by its contains() function. It is the basic function of a set.
        $set = $this->funSets;

        $element = function ($element) { return TRUE; };
        $this->assertTrue($set->contains($element, 'a zillion'));
    }

    function test_FilterContainsOnlyElementsThatMatchConditionFunction()
    {
        $set = $this->funSets;

        $u12 = $this->createUnionWithElements(1, 2);
        $u123 = $set->union($u12, $set->singletonSet(3));

        // Filtering rule, find elements greater than 1 (meaning 2 and 3)
        $condition = function ($elem) { return $elem > 1; };

        // Filtered set
        $filteredSet = $set->filter($u123, $condition);

        // Verify filtered set does not contain 1
        $this->assertFalse($set->contains($filteredSet, 1), "Should not contain 1");
        // Check it contains 2 and 3
        $this->assertTrue($set->contains($filteredSet, 2), "Should contain 2");
        $this->assertTrue($set->contains($filteredSet, 3), "Should contain 3");
    }

    private function createUnionWithElements($elem1, $elem2)
    {
        $set = $this->funSets;

        $s1 = $set->singletonSet($elem1);
        $s2 = $set->singletonSet($elem2);

        return $set->union($s1, $s2);
    }

    function test_ForAllCorrectlyTellsIfAllElementsSatisfyCondition()
    {
        $u123 = $this->createUnionWith123();

        $higherThanZero = function ($elem) { return $elem > 0; };
        $higherThanOne = function ($elem) { return $elem > 1; };
        $higherThanTwo = function ($elem) { return $elem > 2; };

        $this->assertTrue($this->funSets->forAll($u123, $higherThanZero));
        $this->assertFalse($this->funSets->forAll($u123, $higherThanOne));
        $this->assertFalse($this->funSets->forAll($u123, $higherThanTwo));
    }

    private function createUnionWith123()
    {
        return $this->funSets->union(
            $this->createUnionWithElements(1, 2),
            $this->funSets->singletonSet(3)
        );
    }

    function test_SingletonSetContainsSingleElement()
    {
        // A singleton set is characterize by a function which passed to contains will return true for the single element
        // passed as its parameter. In other words, a singleton is a set with a single element.
        $set = $this->funSets;

        $singleton = $set->singletonSet(10.5);
        $this->assertTrue($set->contains($singleton, 10.5));
        $this->assertFalse($set->contains($singleton, 10.6));
    }

    function test_UnionContainsAllElements()
    {
        // A union is characterized by a function which gets 2 sets as parameters and contains all the provided sets
        $set = $this->funSets;

        # the real data `set (1,2)` exists only in this $union function,
        # and is therefore perfectly immutable.
        $immutableSet = $set->union(
            $set->singletonSet(1),
            $set->singletonSet(2)
        );

        // Now, check that both 1 and 2 are part of the union
        $this->assertTrue($set->contains($immutableSet, 1));
        $this->assertTrue($set->contains($immutableSet, 2));
        // ... and that it does not contain 3
        $this->assertFalse($set->contains($immutableSet, 3));
    }

    protected function setUp()
    {
        $this->funSets = new FunSets();
    }
}

/**
 * Functional Sets Example
 *
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
class FunSets
{
    private $bound = 10;

    # applies a function to the element (atomic?) value 
    /**
     * @param $s1
     * @param $s2
     *
     * @return Closure
     */
    public function diff($s1, $s2)
    {
        return function ($otherElem) use ($s1, $s2)
        {
            return $this->contains($s1, $otherElem) && ! $this->contains($s2, $otherElem);
        };
    }

    /**
     * @param $set
     * @param $elem
     *
     * @return mixed
     */
    public function contains($set, $elem)
    {
        return $set($elem);
    }

    /**
     * @param $set
     * @param $condition
     *
     * @return Closure
     */
    public function filter($set, $condition)
    {
        return function ($otherElem) use ($set, $condition)
        {
            if ($condition($otherElem))
                return $this->contains($set, $otherElem);

            return FALSE;
        };
    }

    /**
     * @param $set
     * @param $condition
     *
     * @return bool
     */
    public function forAll($set, $condition)
    {
        return $this->forAllIterator(-$this->bound, $set, $condition);
    }

    /**
     * @param $currentValue
     * @param $set
     * @param $condition
     *
     * @return bool
     */
    private function forAllIterator($currentValue, $set, $condition)
    {
        if ($currentValue > $this->bound)
            return TRUE;
        elseif ($this->contains($set, $currentValue))
            return $condition($currentValue) && $this->forAllIterator($currentValue + 1, $set, $condition);
        else
            return $this->forAllIterator($currentValue + 1, $set, $condition);
    }

    /**
     * @param $s1
     * @param $s2
     *
     * @return Closure
     */
    public function intersect($s1, $s2)
    {
        return function ($otherElem) use ($s1, $s2)
        {
            return $this->contains($s1, $otherElem) && $this->contains($s2, $otherElem);
        };
    }

    /**
     * @param $set
     * @param $action
     *
     * @return Closure
     */
    public function map($set, $action)
    {
        return function ($currentElem) use ($set, $action)
        {
            return $this->exists($set, function ($elem) use ($currentElem, $action)
            {
                return $currentElem == $action($elem);
            });
        };
    }

    /**
     * @param $set
     * @param $condition
     *
     * @return bool
     */
    public function exists($set, $condition)
    {
        return $this->existsIterator(-$this->bound, $set, $condition);
    }

    /**
     * @param $currentValue
     * @param $set
     * @param $condition
     *
     * @return bool
     */
    private function existsIterator($currentValue, $set, $condition)
    {
        if ($currentValue > $this->bound)
            return FALSE;
        elseif ($this->contains($set, $currentValue))
            return $condition($currentValue) || $this->existsIterator($currentValue + 1, $set, $condition);
        else
            return $this->existsIterator($currentValue + 1, $set, $condition);
    }

    /**
     * @param $elem
     *
     * @return Closure
     */
    public function singletonSet($elem)
    {
        # return the singleton set function

        /**
         * @param $otherElem
         *
         * @return bool
         */
        return function ($otherElem) use ($elem)
        {
            # return true if the passed element matches 
            return $elem == $otherElem;
        };
    }

    /**
     * @param $s1
     * @param $s2
     *
     * @return Closure
     */
    public function union($s1, $s2)
    {
        return function ($otherElem) use ($s1, $s2)
        {
            return $this->contains($s1, $otherElem) || $this->contains($s2, $otherElem);
        };
    }
}
