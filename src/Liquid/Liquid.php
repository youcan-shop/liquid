<?php

/*
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace YouCan\Liquid;

/**
 * Liquid for PHP.
 */
class Liquid
{
    /**
     * We cannot make settings constants, because we cannot create compound
     * constants in PHP (before 5.6).
     *
     * @var array configuration array
     */
    public static $config = [
        // The method is called on objects when resolving variables to see
        // if a given property exists.
        'HAS_PROPERTY_METHOD'          => 'field_exists',

        // This method is called on object when resolving variables when
        // a given property exists.
        'GET_PROPERTY_METHOD'          => 'get',

        // Separator between filters.
        'FILTER_SEPARATOR'             => '\|',

        // Separator for arguments.
        'ARGUMENT_SEPARATOR'           => ',',

        // Separator for argument names and values.
        'FILTER_ARGUMENT_SEPARATOR'    => ':',

        // Separator for variable attributes.
        'VARIABLE_ATTRIBUTE_SEPARATOR' => '.',

        // Allow template names with extension in include and extends tags.
        'INCLUDE_ALLOW_EXT'            => false,

        // Suffix for include files.
        'INCLUDE_SUFFIX'               => 'liquid',

        // Prefix for include files.
        'INCLUDE_PREFIX'               => '_',

        // Whitespace control.
        'WHITESPACE_CONTROL'           => '-',

        // Tag start.
        'TAG_START'                    => '{%',

        // Tag end.
        'TAG_END'                      => '%}',

        // Variable start.
        'VARIABLE_START'               => '{{',

        // Variable end.
        'VARIABLE_END'                 => '}}',

        // Variable name.
        'VARIABLE_NAME'                => '[a-zA-Z_][a-zA-Z_0-9.-]*',

        'QUOTED_STRING'                 => '(?:"[^"]*"|\'[^\']*\')',
        'QUOTED_STRING_FILTER_ARGUMENT' => '"[^"]*"|\'[^\']*\'',

        // Automatically escape any variables unless told otherwise by a "raw" filter
        'ESCAPE_BY_DEFAULT'             => false,

        // The name of the key to use when building pagination query strings e.g. ?page=1
        'PAGINATION_REQUEST_KEY'        => 'page',

        // The name of the context key used to denote the current page number
        'PAGINATION_CONTEXT_KEY'        => 'page',

        // Whenever variables from $_SERVER should be directly available to templates
        'EXPOSE_SERVER'                 => false,

        // $_SERVER variables whitelist - exposed even when EXPOSE_SERVER is false
        'SERVER_SUPERGLOBAL_WHITELIST'  => [
            'HTTP_ACCEPT',
            'HTTP_ACCEPT_CHARSET',
            'HTTP_ACCEPT_ENCODING',
            'HTTP_ACCEPT_LANGUAGE',
            'HTTP_CONNECTION',
            'HTTP_HOST',
            'HTTP_REFERER',
            'HTTP_USER_AGENT',
            'HTTPS',
            'REQUEST_METHOD',
            'REQUEST_URI',
            'SERVER_NAME',
        ],
    ];

    /**
     * Get a configuration setting.
     *
     * @param string $key setting key
     *
     * @return string
     */
    public static function get($key)
    {
        // backward compatibility
        if ($key === 'ALLOWED_VARIABLE_CHARS') {
            return substr(self::$config['VARIABLE_NAME'], 0, -1);
        }
        if (array_key_exists($key, self::$config)) {
            return self::$config[$key];
        }
        // This case is needed for compound settings
        switch ($key) {
            case 'QUOTED_FRAGMENT':
                return '(?:' . self::get('QUOTED_STRING') . '|(?:\[\s*' . self::get('QUOTED_STRING') . '\s*\]|[^\s,\|\'"])+)';
            case 'VARIABLE_PATH':
                return self::get('VARIABLE_NAME') . '(?:\[(?:\s*' . self::get('QUOTED_STRING') . '\s*|[^\[\]\s]+)\][a-zA-Z_0-9.-]*)*';
            case 'TAG_ATTRIBUTES':
                return '/(\w+)\s*\:\s*(' . self::get('QUOTED_FRAGMENT') . ')/';
            case 'TOKENIZATION_REGEXP':
                return '/(' . self::$config['TAG_START'] . '.*?' . self::$config['TAG_END'] . '|' . self::$config['VARIABLE_START'] . '.*?' . self::$config['VARIABLE_END'] . ')/s';
            default:
                return null;
        }
    }

    /**
     * Changes/creates a setting.
     *
     * @param string $key
     * @param string $value
     */
    public static function set($key, $value)
    {
        // backward compatibility
        if ($key === 'ALLOWED_VARIABLE_CHARS') {
            $key = 'VARIABLE_NAME';
            $value .= '+';
        }
        self::$config[$key] = $value;
    }

    /**
     * Flatten a multidimensional array into a single array. Does not maintain keys.
     *
     * @param array $array
     *
     * @return array
     */
    /**
     * @param mixed $value
     *
     * @return int|float
     */
    public static function number($value)
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            $value = (string) $value;
        }

        if (!is_string($value)) {
            return 0;
        }

        if (preg_match('/^\s*-?\d+\.\d+\s*$/', $value)) {
            return (float) $value;
        }

        return preg_match('/^\s*[-+]?\d+/', $value, $m) ? (int) $m[0] : 0;
    }

    public static function formatFloat(float $value): string
    {
        $text = json_encode($value, JSON_PRESERVE_ZERO_FRACTION);

        if (!str_contains($text, 'e')) {
            return $text;
        }

        [$mantissa, $exponent] = explode('e', $text);

        return (str_contains($mantissa, '.') ? $mantissa : $mantissa . '.0') . 'e' . ($exponent[0] === '-' ? '-' : '+') . str_pad(ltrim($exponent, '+-'), 2, '0', STR_PAD_LEFT);
    }

    /**
     * @param int|float $left
     * @param int|float $right
     *
     * @return int|float
     */
    public static function arithmetic($left, $right, string $operator)
    {
        if (is_int($left) && is_int($right)) {
            return match ($operator) {
                '+' => $left + $right,
                '-' => $left - $right,
                '*' => $left * $right,
            };
        }

        [$a, $b] = [self::decimal($left), self::decimal($right)];

        if ($a === null || $b === null) {
            return match ($operator) {
                '+' => $left + $right,
                '-' => $left - $right,
                '*' => $left * $right,
            };
        }

        return (float) match ($operator) {
            '+' => bcadd($a, $b, 40),
            '-' => bcsub($a, $b, 40),
            '*' => bcmul($a, $b, 40),
        };
    }

    private static function decimal(int|float $value): ?string
    {
        $text = is_int($value) ? (string) $value : json_encode($value);

        return preg_match('/^-?\d+(\.\d+)?$/', $text) ? $text : null;
    }

    public static function shortName(object $object): string
    {
        return substr(strrchr('\\' . get_class($object), '\\'), 1);
    }

    public static function arrayFlatten($array)
    {
        $return = [];

        foreach ($array as $element) {
            if (is_array($element)) {
                $return = array_merge($return, self::arrayFlatten($element));
            } else {
                $return[] = $element;
            }
        }

        return $return;
    }
}
