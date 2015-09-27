<?php namespace Og\Support;

use Og\Collection;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
class Arr
{
    /**
     * Build a new array using a callback.
     *
     * @param  array    $array
     * @param  callable $callback
     *
     * @return array
     */
    public static function build($array, callable $callback)
    {
        $results = [];

        foreach ($array as $key => $value)
        {
            list($innerKey, $innerValue) = call_user_func($callback, $key, $value);

            $results[$innerKey] = $innerValue;
        }

        return $results;
    }

    /**
     * Collapse an array of arrays into a single array.
     *
     * @param  array|\ArrayAccess $array
     *
     * @return array
     */
    public static function collapse($array)
    {
        $results = [];

        foreach ($array as $values)
        {
            if ($values instanceof Collection)
            {
                $values = $values->copy();
            }

            $results = array_merge($results, $values);
        }

        return $results;
    }

    /**
     * Creates a copy of the array parameter with a numeric index.
     * ie: ['apple','orange','banana'] becomes [0]=>'apple', [1]=>'orange', [2]=>'banana'.
     *
     * @param $array
     *
     * @return array - result is a new array with added integer index.
     */
    static function convert_list_to_indexed_array($array)
    {
        $i = 0;
        $work = [];
        foreach ($array as $key => $field)
        {
            $work[$i++] = $field;
        }

        return $work;
    }

    /**
     * Recursively copy object properties to an array.
     *
     * @note Only copies properties accessible in the current scope.
     *
     * @param $obj
     *
     * @return array
     */
    static function copy_object($obj)
    {
        $result = [];
        foreach (get_object_vars($obj) as $key => $value)
        {
            if (is_object($value))
            {
                $value = self::copy_object($value);
            }
            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * @param      $array
     * @param      $key
     * @param null $default
     *
     * @return mixed|null
     */
    static function deep_query(&$array, $key, $default = NULL)
    {
        # if the value is an associative array, then merge it into the container
        if (self::is_assoc($key))
        {
            if (count($key) > 0)
            {
                // Merge given config settings over any previous ones (if $value is array)
                $array = self::merge_recursive_replace($array, $key);
            }
        }
        elseif (is_string($key))
        {
            //$array = $this->container;

            # handle compound indexes
            if (strpos($key, '.') !== FALSE)
            {
                $data_value = $array;
                $valueParts = explode('.', $key);
                foreach ($valueParts as $valuePart)
                {
                    if (isset($data_value[$valuePart]))
                    {
                        $data_value = $data_value[$valuePart];
                    }
                }
            }

            # handle simple indexes
            else
            {
                $data_value = NULL;
                //if ($this->has($key))
                if (array_key_exists($key, $array))
                {
                    $data_value = $array[$key];
                }
                else
                {
                    $data_value = value($default);
                }
            }

            return $data_value;
        }

        return NULL;
    }

    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param  array  $array
     * @param  string $prepend
     *
     * @return array
     */
    static function dict($array, $prepend = '')
    {
        $results = [];

        foreach ($array as $key => $value)
        {
            if (is_array($value))
            {
                $results = array_merge($results, self::dict($value, $prepend . $key . '.'));
            }
            else
            {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }

    /**
     * Divide an array into two arrays. One with keys and the other with values.
     *
     * @param  array $array
     *
     * @return array
     */
    public static function divide($array)
    {
        return [array_keys($array), array_values($array)];
    }

    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param  array  $array
     * @param  string $prepend
     *
     * @return array
     */
    public static function dot($array, $prepend = '')
    {
        $results = [];

        foreach ($array as $key => $value)
        {
            if (is_array($value))
            {
                $results = array_merge($results, static::dot($value, $prepend . $key . '.'));
            }
            else
            {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }

    /**
     * Get all of the given array except for a specified array of items.
     *
     * @param  array|string $keys
     * @param  array        $array
     *
     * @return array
     */
    static function except($keys, $array)
    {
        return array_diff_key($array, array_flip((array) $keys));
    }

    /**
     * Expand a dot-notated key/value to an array.
     * i.e.: key:'this.thing', value:'exists' -> ['this'=>['thing'=>'exists']]
     *
     * @param $key
     * @param $value
     *
     * @return array
     */
    static function expand_notated($key, $value)
    {
        # extract dot-notation array structures and build the query array
        $params = [];
        Arr::from_notation($key, $params, $value);

        # first element is the base key
        $key = array_keys($params)[0];

        # the result is the value array (or element)
        $value = array_shift($params);

        return [$key, $value];
    }

    /**
     * Extracts a value from an array of associated arrays.
     * ie:
     *    $records = [
     *      'George' => ['age' => 26, 'gender' => 'Male'],
     *      'Lois'   => ['age' => 32, 'gender' => 'Female'],
     *      ];
     *    `array_extract_list('age', $records)` returns `[26,32]`
     *
     * @param string $find_key
     * @param array  $array
     *
     * @return array
     */
    static function extract_list($find_key, $array)
    {
        $result = [];
        foreach ($array as $element)
        {
            # replaces non-matched items with NULL as a place-keeper.
            $result[] = isset($element[$find_key]) ? $element[$find_key] : NULL;
        }

        return $result;
    }

    /**
     * @param $obj
     * @param $array
     *
     * @return mixed
     */
    static function fill_object($obj, $array)
    {
        foreach ($array as $property => $value)
        {
            $obj->$property = $value;
        }

        return $obj;
    }

    /**
     * Return the first element in an array passing a given truth test.
     *
     * @param  array    $array
     * @param  callable $callback
     * @param  mixed    $default
     *
     * @return mixed
     */
    public static function first($array, callable $callback, $default = NULL)
    {
        foreach ($array as $key => $value)
        {
            if (call_user_func($callback, $key, $value))
            {
                return $value;
            }
        }

        return value($default);
    }

    /**
     * Flatten a multi-dimensional array into a single level.
     *
     * @param  array $array
     *
     * @return array
     */
    public static function flatten($array)
    {
        $return = [];

        array_walk_recursive($array, function ($x) use (&$return) { $return[] = $x; });

        return $return;
    }

    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param  array|string $keys
     *
     * @param  array        $array
     */
    static function forget($keys, &$array)
    {
        $original = &$array;

        foreach ((array) $keys as $key)
        {
            $parts = explode('.', $key);

            while (count($parts) > 1)
            {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part]))
                {
                    $array = &$array[$part];
                }
            }

            unset($array[array_shift($parts)]);

            // clean up after each pass
            $array = &$original;
        }
    }

    /**
     * @param string $dot_path
     * @param array  $target_array
     * @param string $target_value
     *
     * @return string
     */
    static function from_notation($dot_path, &$target_array, $target_value = NULL)
    {
        $result_array = &$target_array;

        foreach (explode('.', $dot_path) as $step)
        {
            $result_array = &$result_array[$step];
        }

        return $target_value ? $result_array = $target_value : $result_array;
    }

    /**
     * Return an array with the object name as the key.
     *
     * @param object $object
     * @param mixed  $value
     *
     * @return array|null
     */
    static function generate_object_value_hash($object, $value)
    {
        if (is_object($object))
        {
            $class = get_class($object);

            return [$class => $value];
        }

        return NULL;
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  string $key
     * @param  array  $array
     * @param  mixed  $default
     *
     * @return mixed
     */
    static function get($key, $array, $default = NULL)
    {
        if (is_null($key))
            return $array;

        if (isset($array[$key]))
            return $array[$key];

        foreach (explode('.', $key) as $segment)
        {
            if ( ! is_array($array) || ! array_key_exists($segment, $array))
            {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * General purpose array[index] validation which avoids errors.
     * Returns NULL on error.
     *
     * @param $the_array
     * @param $index
     *
     * @return bool|string|integer|object|array
     */
    static function get_array_value_safely($index, $the_array)
    {
        if (isset($the_array[$index]))
        {
            return $the_array[$index];
        }
        else
        {
            return NULL;
        }
    }

    /**
     * Check if an item exists in an array using "dot" notation.
     *
     * @param  array  $array
     * @param  string $key
     *
     * @return bool
     */
    public static function has($array, $key)
    {
        if (empty($array) || is_null($key))
        {
            return FALSE;
        }

        if (array_key_exists($key, $array))
        {
            return TRUE;
        }

        foreach (explode('.', $key) as $segment)
        {
            if ( ! is_array($array) || ! array_key_exists($segment, $array))
            {
                return FALSE;
            }

            $array = $array[$segment];
        }

        return TRUE;
    }

    /**
     * Insert an key/value pair before a given key in an array.
     *
     * @param array  $originalKey   - the key into the working array
     * @param array  $originalArray - the working array
     * @param string $insertKey     - the key to insert before the working array[key] element
     * @param string $insertValue   - the value of the array[key] to insert
     *
     * @return array
     */
    static function insert_before_key($originalKey, $originalArray, $insertKey, $insertValue)
    {
        $newArray = [];
        $inserted = FALSE;

        foreach ($originalArray as $key => $value)
        {
            if ( ! $inserted && $key === $originalKey)
            {
                $newArray[$insertKey] = $insertValue;
                $inserted = TRUE;
            }
            $newArray[$key] = $value;
        }

        return $newArray;
    }

    /**
     * Type-checked pseudonym for is_assoc().   
     *
     * @param  array $array
     *
     * @return bool
     */
    public static function isAssoc(array $array)
    {
        return static::is_assoc($array);
    }

    /**
     * Determines if an array is an associative array.<br>
     * ie:<br>
     * <ul>
     *    <li>Single dimension array: <pre>array("dog","cat","etc") == FALSE</pre>
     *    <li>Associative array: <pre>array("animal"=>"dog", "place"=>"123 East") == TRUE</pre>
     *
     * @package SupportLoader
     * @module  arrays
     *
     * @param $array - The array to test
     *
     * @return bool   - returns TRUE if an associative array
     */
    static function is_assoc($array)
    {
        return is_array($array) ? (bool) ! (array_values($array) === $array) : FALSE;

    }

    /**
     * Return the last element in an array passing a given truth test.
     *
     * @param  array    $array
     * @param  callable $callback
     * @param  mixed    $default
     *
     * @return mixed
     */
    public static function last($array, callable $callback, $default = NULL)
    {
        return static::first(array_reverse($array), $callback, $default);
    }

    /**
     * Converts a associative array of key,value pairs to SQL query comparisons.
     * ie: ['a' => 4, 'b' => 'open'] -> [0 => "a=`4`", 1 => "b=`open`"]
     *
     * @param array $array
     *
     * @return array|null
     */
    static function make_compare_list(array $array)
    {
        $list = [];

        if (Arr::is_assoc($array))
        {
            foreach ($array as $key => $value)
                $list[] = $key . '=`' . $value . '`';

            return $list;
        }

        return NULL;
    }

    /**
     * Merges any number of arrays of any dimensions, the later overwriting
     * previous keys, unless the key is numeric, in which case, duplicated
     * values will not be added. The arrays to be merged are passed as
     * arguments to the function.
     *
     * @package SupportLoader
     * @module  arrays
     *
     * @param null $key
     * @param null $value
     *
     * @return array - Resulting array, once all have been merged
     */
    static function merge_recursive_replace($key, $value)
    {
        // Holds all the arrays passed
        $params = func_get_args();

        // First array is used as the base, everything else overwrites on it
        $return = array_shift($params);

        // Merge all arrays on the first array
        foreach ($params as $array)
            foreach ($array as $key => $value)
                if (isset($return[$key]) && is_array($value) && is_array($return[$key]))
                    $return[$key] = Arr::merge_recursive_replace($return[$key], $value);
                else
                    $return[$key] = $value;

        return $return;
    }

    /**
     * Explodes a string on multiple delimiters.
     *
     * @origin <http://php.net/manual/en/function.explode.php#111307>
     *
     * @param string $string
     * @param array  $delimiters
     * @param bool   $trim
     *
     * @return array
     */
    static function multi_explode($string, array $delimiters, $trim = FALSE)
    {
        $ready = str_replace($delimiters, $delimiters[0], $string);
        $launch = explode($delimiters[0], $ready);

        return $trim ? str_replace(' ', '', $launch) : $launch;
    }

    /**
     * Typecast an object to an array.
     *
     * @param $object
     *
     * @return array
     */
    static function object_to_array($object)
    {
        return (array) $object;
    }

    /**
     * Get a subset of the items from the given array.
     *
     * @param  array        $array
     * @param  array|string $keys
     *
     * @return array
     */
    public static function only($array, $keys)
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }

    /**
     * Convert to useful array style from HTML Form input style. Useful for matching up
     * input arrays without having to increment a number in field names<br><br>
     * Input an array like this:<pre>
     * ["name"]  =>  [0] => "Google", [1] => "Yahoo!"
     * ["url"]  =>  [0] => "http://www.google.com", [1] => "http://www.yahoo.com"</pre>
     * And you will get this:<pre>
     * [0]  =>  ["name"] => "Google", ["url"] => "http://www.google.com"
     * [1]  =>  ["name"] => "Yahoo!", ["url"] => "http://www.yahoo.com"</pre>
     *
     * @package SupportLoader
     * @module  arrays
     *
     * @param array $input - a reference to an associative array.
     *
     * @return array - the array flipped.
     */
    static function pivot_array_on_index(array $input)
    {
        $output = [];
        foreach ($input as $key => $val)
        {
            foreach ($val as $key2 => $val2)
            {
                $output[$key2][$key] = $val2;
            }
        }

        return $output;
    }

    /**
     * Get a value from the array, and remove it.
     *
     * @param  string $key
     * @param  array  $array
     * @param  mixed  $default
     *
     * @return mixed
     */
    static function pull($key, &$array, $default = NULL)
    {
        $value = self::get($key, $array, $default);

        self::forget($key, $array);

        return $value;
    }

    /**
     * Retrieves the value of an array element or object property with the given key or property name.
     * If the key does not exist in the array or object, the default value will be returned instead.
     * The key may be specified in a dot format to retrieve the value of a sub-array or the property
     * of an embedded object. In particular, if the key is `x.y.z`, then the returned value would
     * be `$array['x']['y']['z']` or `$array->x->y->z` (if `$array` is an object). If `$array['x']`
     * or `$array->x` is neither an array nor an object, the default value will be returned.
     * Note that if the array already has an element `x.y.z`, then its value will be returned
     * instead of going through the sub-arrays.
     * Below are some usage examples,
     * ~~~
     * // working with array
     * $username = array_query($_POST, 'username');
     * // working with object
     * $username = array_query($user, 'username');
     * // working with anonymous function
     * $fullName = array_query($user, static function ($user, $defaultValue) {
     *     return $user->firstName . ' ' . $user->lastName;
     * });
     * // using dot format to retrieve the property of embedded object
     * $street = array_query($users, 'address.street');
     * ~~~
     *
     * @param string|\Closure $key     key name of the array element, or property name of the object,
     *                                 or an anonymous static function returning the value. The anonymous function
     *                                 signature should be:
     *                                 `function($array, $defaultValue)`.
     * @param array|object    $array   array or object to extract value from
     * @param mixed           $default the default value to be returned if the specified array key does not exist.
     *                                 Not used when getting value from an object.
     *
     * @return mixed the value of the element if found, default value otherwise
     */
    static function query($key, $array, $default = NULL)
    {
        if ($key instanceof \Closure)
            return $key($array, $default);

        if (is_array($array) && array_key_exists($key, $array))
            return $array[$key];

        if (($pos = strrpos($key, '.')) !== FALSE)
        {
            $array = self::query(substr($key, 0, $pos), $array, $default);
            $key = substr($key, $pos + 1);
        }

        if (is_object($array))
            return $array->$key;

        if (is_array($array))
            return array_key_exists($key, $array) ? $array[$key] : $default;

        return $default;
    }

    /**
     * Converts a string of space or tab delimited words as an array.
     * Multiple whitespace between words is converted to a single space.
     *
     * @param        $words
     * @param string $delimiter
     *
     * @return array
     */
    static function s_to_a($words, $delimiter = ' ')
    {
        return explode($delimiter, preg_replace('/\s+/', ' ', $words));
    }

    /**
     * Converts a string in the form "<key>:<value," to an associative array.
     *
     * @param $tuples - the formatted string representation of a key/value array.
     *
     * @return array  - the constructed array
     */
    static function s_to_aa($tuples)
    {
        $array = self::s_to_a($tuples, ',');
        $result = [];

        foreach ($array as $tuple)
        {
            $ra = explode(':', $tuple);

            $key = trim($ra[0]);
            $value = trim($ra[1]);

            $result[$key] = is_numeric($value) ? intval($value) : $value;
        }

        return $result;
    }

    /**
     * Typecast an array to an object.
     *
     * @param $array
     *
     * @return object
     */
    static function to_object($array)
    {
        return (object) $array;
    }

    /**
     * @param array $symbols
     *
     * @return array
     */
    static function transform_array_hash(array $symbols)
    {
        $transform = [];
        foreach ($symbols as $var)
            $transform[$var] = NULL;

        return $transform;
    }

}

