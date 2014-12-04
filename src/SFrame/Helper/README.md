# Helper
Useful libraries.

# Installation
```php
composer require "sframe/helper:dev-master"
```

# Arr
Array helper, extended from laravel Illuminate\Support\Arr

# Arr Methods
```php
Arr::
    // Add an element to an array using "dot" notation if it doesn't exist.
    add($array, $key, $value)

    // Build a new array using a callback.
    build($array, Closure $callback)

    // Divide an array into two arrays. One with keys and the other with values.
    divide($array)

    // Flatten a multi-dimensional associative array with dots.
    dot($array, $prepend = '')

    // Get all of the given array except for a specified array of items.
    except($array, $keys)

    // Fetch a flattened array of a nested array element.
    fetch($array, $key)

    // Return the first element in an array passing a given truth test.
    first($array, $callback, $default = null)

    // Return the last element in an array passing a given truth test.
    last($array, $callback, $default = null)

    // Flatten a multi-dimensional array into a single level.
    flatten($array)

    // Remove one or many array items from a given array using "dot" notation.
    forget(&$array, $keys)

    // Get an item from an array using "dot" notation.
    get($array, $key, $default = null)

    // Get a subset of the items from the given array.
    only($array, $keys)

    // Pluck an array of values from an array.
    pluck($array, $value, $key = null)

    // Get a value from the array, and remove it.
    pull(&$array, $key, $default = null)

    // Set an array item to a given value using "dot" notation.
    set(&$array, $key, $value)

    // Sort the array using the given Closure.
    sort($array, Closure $callback)

    // Filter the array using the given Closure.
    where($array, Closure $callback)
```


# Str
String helper, extended from laravel Illuminate\Support\Str

#Str Methods
```php
Str::
    // Transliterate a UTF-8 value to ASCII.
    ascii($value)

    // Convert a value to camel case.
    camel($value)

    // Determine if a given string contains a given substring.
    contains($haystack, $needles)

    // Determine if a given string ends with a given substring.
    endsWith($haystack, $needles)

    // Cap a string with a single instance of a given value.
    finish($value, $cap)

    // Determine if a given string matches a given pattern.
    is($pattern, $value)

    // Return the length of the given string.
    length($value)

    // Limit the number of characters in a string.
    limit($value, $limit = 100, $end = '...')

    // Convert the given string to lower-case.
    lower($value)

    // Limit the number of words in a string.
    words($value, $words = 100, $end = '...')

    // Parse a Class@method style callback into class and method.
    parseCallback($callback, $default)

    // Get the plural form of an English word.
    plural($value, $count = 2)

    // Generate a more truly "random" alpha-numeric string.
    random($length = 16)

    // Generate a "random" alpha-numeric string.
    quickRandom($length = 16)

    // Convert the given string to upper-case.
    upper($value)

    // Convert the given string to title case.
    title($value)

    // Get the singular form of an English word.
    singular($value)

    // Generate a URL friendly "slug" from a given string.
    slug($title, $separator = '-')

    // Convert a string to snake case.
    snake($value, $delimiter = '_')

    // Determine if a given string starts with a given substring.
    startsWith($haystack, $needles)

    // Convert a value to studly caps case.
    studly($value)
```
