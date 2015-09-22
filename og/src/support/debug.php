<?php

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Support\Arr;

/**
 * @param bool $raw
 *
 * @return string
 */
function elapsed_time($raw = FALSE)
{
    return ! $raw
        ? sprintf("%8.1f ms", (microtime(TRUE) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000)
        : (microtime(TRUE) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000;
}

/**
 * @param $index
 *
 * @return string
 */
function location_from_backtrace($index = 2)
{
    $file = '';
    $line = 0;
    $dbt = debug_backtrace();

    if (isset($dbt[$index]['file']))
    {
        $file = basename($dbt[$index]['file']);
        $line = $dbt[$index]['line'];
    }

    $function = isset($dbt[$index]['function']) ? $dbt[$index]['function'] : '';
    $class = isset($dbt[$index]['class']) ? $dbt[$index]['class'] : '';

    return "$file:$line -> $class::$function";
}

/**
 * @param     $var
 * @param int $depth
 *
 * @return mixed
 */
function idump($var = NULL, $depth = 12)
{
    \Tracy\Debugger::$maxDepth = $depth;

    $args = func_get_args();
    Arr::forget(func_num_args() - 1, $args);

    $dumper = defined('PHPUNIT_TESTS__') ? 'Tracy\Dumper::toTerminal' : 'Tracy\Debugger::dump';

    array_map($dumper, $args);

    return $var;
}

/**
 * @param null $value
 * @param int  $depth
 */
function expose($value = NULL, $depth = 8)
{
    $guard = location_from_backtrace(1);

    $trace = ['probe' => $guard, 'trace' => [], 'target' => $value];
    $fence = 2;

    $lfb = location_from_backtrace($fence);
    while ($guard !== $lfb)
    {
        $trace['trace'][$fence] = $lfb;
        $guard = $lfb;
        ++$fence;
        $lfb = location_from_backtrace($fence);
    }

    idump($trace, $depth);
}

/**
 * dump and die with backtrace
 *
 * @param     $value
 * @param int $depth
 */
function ddump($value = NULL, $depth = 8)
{
    expose($value, $depth);
    if ( ! defined('PHPUNIT_TESTS__'))
        exit(1);
}

/**
 * @param $error_type
 *
 * @return string
 */
function readable_error_type($error_type)
{
    switch ($error_type)
    {
        case E_ERROR: // 1 //
            return 'E_ERROR';
        case E_WARNING: // 2 //
            return 'E_WARNING';
        case E_PARSE: // 4 //
            return 'E_PARSE';
        case E_NOTICE: // 8 //
            return 'E_NOTICE';
        case E_CORE_ERROR: // 16 //
            return 'E_CORE_ERROR';
        case E_CORE_WARNING: // 32 //
            return 'E_CORE_WARNING';
        case E_COMPILE_ERROR: // 64 //
            return 'E_COMPILE_ERROR';
        case E_COMPILE_WARNING: // 128 //
            return 'E_COMPILE_WARNING';
        case E_USER_ERROR: // 256 //
            return 'E_USER_ERROR';
        case E_USER_WARNING: // 512 //
            return 'E_USER_WARNING';
        case E_USER_NOTICE: // 1024 //
            return 'E_USER_NOTICE';
        case E_STRICT: // 2048 //
            return 'E_STRICT';
        case E_RECOVERABLE_ERROR: // 4096 //
            return 'E_RECOVERABLE_ERROR';
        case E_DEPRECATED: // 8192 //
            return 'E_DEPRECATED';
        case E_USER_DEPRECATED: // 16384 //
            return 'E_USER_DEPRECATED';
    }

    return "UNKNOWN_ERROR";
}
    
