<?php namespace Og\Support;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

class Str
{
    /**
     * Converts a CamelCase string to underscore_case
     * ie:  CamelCase -> camel_case
     *      MyCAMELCase -> my_camel_case
     *      etc.
     *
     * @param        $input
     * @param string $delimiter
     *
     * @return string
     * This code was posted by <a href='http://stackoverflow.com/users/18393/cletus'>cletus</a>
     * on Stack Overflow.
     */
    static function camel_to_snakecase($input, $delimiter = '_')
    {
        preg_match_all(
            '!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!',
            $input,
            $matches
        );
        $ret = $matches[0];
        foreach ($ret as &$match)
        {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }

        return implode($delimiter, $ret);
    }

    /**
     * @param $needles
     * @param $haystack
     *
     * @return bool
     */
    static function contains($needles, $haystack)
    {
        foreach ((array) $needles as $needle)
            if ($needle != '' && strpos($haystack, $needle) !== FALSE)
                return TRUE;

        return FALSE;
    }

    /**
     * Escape HTML entities in a string.
     *
     * @param  string $value
     *
     * @return string
     */
    static function e($value)
    {
        return htmlentities($value, ENT_QUOTES, 'UTF-8', FALSE);
    }

    /**
     * A simple readable json encode static function for PHP 5.3+ (and licensed under GNU/AGPLv3 or GPLv3)
     *
     * @author   bohwaz <http://php.net/manual/en/function.json-encode.php#102091>
     *
     * @param     $data
     * @param int $indent
     *
     * @return string
     */
    static function encode_readable_json($data, $indent = 0)
    {
        $_escape = function ($str) { return preg_replace("!([\b\t\n\r\f\"\\'])!", "\\\\\\1", $str); };

        $out = '';

        foreach ($data as $key => $value)
        {
            $out .= str_repeat("\t", $indent + 1);
            $out .= "\"" . $_escape((string) $key) . "\": ";

            if (is_object($value) || is_array($value))
            {
                $out .= "\n";
                $out .= self::encode_readable_json($value, $indent + 1);
            }
            elseif (is_bool($value))
            {
                $out .= $value ? 'true' : 'false';
            }
            elseif (is_null($value))
            {
                $out .= 'null';
            }
            elseif (is_string($value))
            {
                $out .= "\"" . $_escape($value) . "\"";
            }
            else
            {
                $out .= $value;
            }

            $out .= ",\n";
        }

        if ( ! empty($out))
        {
            $out = substr($out, 0, -2);
        }

        $out = str_repeat("\t", $indent) . "{\n" . $out;
        $out .= "\n" . str_repeat("\t", $indent) . "}";

        return $out;
    }

    /**
     * @param $needle
     * @param $haystack
     *
     * @return bool
     */
    static function endsWith($needle, $haystack)
    {
        // search forward starting from end minus needle length characters
        return
            empty($needle)
            or (($temp = strlen($haystack) - strlen($needle)) >= 0
                and strpos($haystack, $needle, $temp) !== FALSE);
    }

    /**
     * @param string $name  name and extension part of file path
     * @param array  $paths array of folders to search
     *
     * @return string complete path to file
     */
    static function file_in_path($name, Array $paths)
    {
        $file_path = FALSE;

        foreach ($paths as $path)
        {
            if (file_exists($path . $name))
            {
                $file_path = $path . $name;
                break;
            }
        }

        return $file_path;
    }

    /**
     * Format given string to valid URL string
     *
     * @param string $string
     *
     * @return string URL-safe string
     */
    static function format_for_url($string)
    {
        // Allow only alphanumerics, underscores and dashes
        $string = preg_replace('/([^a-zA-Z0-9_\-]+)/', '-', strtolower($string));

        // Replace extra spaces and dashes with single dash
        $string = preg_replace('/\s+/', '-', $string);
        $string = preg_replace('|-+|', '-', $string);

        // Trim extra dashes from beginning and end
        $string = trim($string, '-');

        return $string;
    }

    /**
     * @param int    $length
     * @param string $preface
     *
     * @return string 32 character (16 bytes) string - unique for each run
     */
    static function generate_token($length = 16, $preface = '')
    {
        $bh = bin2hex(openssl_random_pseudo_bytes($length));

        return "{$preface}{$bh}";
    }

    /**
     * html special chars helper
     *
     * @param      $string
     * @param bool $double_encode
     *
     * @return string
     */
    static function h($string, $double_encode = TRUE)
    {
        return htmlspecialchars($string, ENT_COMPAT | ENT_HTML5, 'UTF-8', $double_encode);
    }

    /**
     * Pseudonym for `contains()`.
     *
     * @param string $needles
     * @param string $haystack
     *
     * @return bool
     */
    static function has($needles, $haystack)
    {
        return self::contains($needles, $haystack);
    }

    /**
     * @param integer|string      $key
     * @param null|string|integer $default
     *
     * @return int|string
     */
    static function http_code($key, $default = NULL)
    {
        static $statusTexts = [
            000 => 'Unknown Error',
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',            // RFC2518
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',          // RFC4918
            208 => 'Already Reported',      // RFC5842
            226 => 'IM Used',               // RFC3229
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Reserved',
            307 => 'Temporary Redirect',
            308 => 'Permanent Redirect',    // RFC7238
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot',                                               // RFC2324
            422 => 'Unprocessable Entity',                                        // RFC4918
            423 => 'Locked',                                                      // RFC4918
            424 => 'Failed Dependency',                                           // RFC4918
            425 => 'Reserved for WebDAV advanced collections expired proposal',   // RFC2817
            426 => 'Upgrade Required',                                            // RFC2817
            428 => 'Precondition Required',                                       // RFC6585
            429 => 'Too Many Requests',                                           // RFC6585
            431 => 'Request Header Fields Too Large',                             // RFC6585
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates (Experimental)',                      // RFC2295
            507 => 'Insufficient Storage',                                        // RFC4918
            508 => 'Loop Detected',                                               // RFC5842
            510 => 'Not Extended',                                                // RFC2774
            511 => 'Network Authentication Required',                             // RFC6585
        ];

        if (is_integer($key))
            return isset($statusTexts[$key]) ? "$key " . $statusTexts[$key] : $default;

        $key = strtolower($key);
        foreach ($statusTexts as $code => $text)
        {
            if (self::startsWith($key, strtolower($text)))
                return $code;
        }

        return $default;
    }

    /**
     * @param $class_name       - name of the class
     * @param $suffix_to_remove - suffix to strip from class name
     *
     * @return string
     */
    static function name_from_class($class_name, $suffix_to_remove = 'Controller')
    {
        return strtolower(self::remove_namespace($class_name, $suffix_to_remove));
    }

    /**
     * @param $path
     *
     * @return string
     */
    static function normalize_path($path)
    {
        //expose($path);
        return self::stripTrailing('/', realpath($path)) . '/';
    }

    /**
     * This static function lifted from PHP docs.
     * Parse class name into namespace and class_name
     *
     * @param $name
     *
     * @return array
     */
    static function parse_class_name($name)
    {
        $namespace = array_slice(explode('\\', $name), 0, -1);

        return [
            'namespace' => $namespace,
            'class_name' => join('', array_slice(explode('\\', $name), -1)),
            'namespace_path' => implode('\\', $namespace),
            'namespace_base' => isset($namespace[0]) ? $namespace[0] : '',
        ];
    }

    /**
     * Determine if a given string matches a given pattern.
     *
     * @param  string $value
     *
     * @param  string $pattern
     *
     * @return bool
     */
    static function pattern_matches($value, $pattern)
    {
        if ($pattern == $value)
            return TRUE;

        $pattern = preg_quote($pattern, '#');

        // Asterisks are translated into zero-or-more regular expression wildcards
        // to make it convenient to check if the strings starts with the given
        // pattern such as "library/*", making any string check convenient.
        $pattern = str_replace('\*', '.*', $pattern) . '\z';

        return (bool) preg_match('#^' . $pattern . '#', $value);
    }

    /**
     * Determine if a given string matches a given pattern.
     *
     * @param  string $pattern
     * @param  string $value
     *
     * @return bool
     */
    public static function is($pattern, $value)
    {
        if ($pattern == $value)
        {
            return TRUE;
        }

        $pattern = preg_quote($pattern, '#');

        // Asterisks are translated into zero-or-more regular expression wildcards
        // to make it convenient to check if the strings starts with the given
        // pattern such as "library/*", making any string check convenient.
        $pattern = str_replace('\*', '.*', $pattern) . '\z';

        return (bool) preg_match('#^' . $pattern . '#', $value);
    }
    
    /**
     * @param string $class_name
     * @param string $class_suffix
     *
     * @return mixed
     */
    static function remove_namespace($class_name, $class_suffix = NULL)
    {
        $segments = explode('\\', $class_name);
        $class = $segments[(sizeof($segments) - 1)];
        if ( ! is_null($class_suffix))
        {
            $class = str_ireplace($class_suffix, '', $class);
        }

        return $class;
    }

    /**
     * @param $string
     *
     * @return mixed
     */
    static function remove_quotes($string)
    {
        return str_replace(['"', "'"], ["", ""], $string);
    }

    /**
     * @param string $slug
     *
     * @return string
     */
    static function slug_to_title($slug)
    {
        return ucwords(str_replace('-', ' ', $slug));
    }

    /**
     * Converts a underscore_case string to CamelCase.
     *
     * @param $string   string to be converted.
     *
     * @return string   converted string
     */
    static function snakecase_to_camelcase($string)
    {
        $string = ucwords(str_replace(['_', '.'], ' ', $string));

        return lcfirst(str_replace(' ', '', $string));
    }

    /**
     * Converts underscores to spaces and capitalizes first letter of each word
     *
     * @param string $word
     * @param string $space
     *
     * @return string
     */
    static function snakecase_to_heading($word, $space = ' ')
    {
        $prep = ucwords(str_replace('_', ' ', $word));

        return ucwords(str_replace(' ', $space, $prep));
    }

    /**
     * @param $needle
     * @param $haystack
     *
     * @return bool
     */
    static function startsWith($needle, $haystack)
    {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }

    /**
     * @param $characters
     * @param $string
     *
     * @return string
     */
    static function stripTrailing($characters, $string)
    {
        return self::endsWith($characters, $string) ? rtrim($string, $characters) : $string;
    }

    /**
     * Truncates a string to a certain length & adds a "..." to the end
     *
     * @param        $string
     * @param string $endlength
     * @param string $end
     *
     * @return string
     */
    static function truncate($string, $endlength = "30", $end = "...")
    {
        $strlen = strlen($string);
        if ($strlen > $endlength)
        {
            $trim = $endlength - $strlen;
            $string = substr($string, 0, $trim);
            $string .= $end;
        }

        return $string;
    }

}
