<?php
/**
 * Project: doxygen-php-filters
 * Author:  Alex Schickedanz (AbcAeffchen)
 * Date:    05.03.2015
 * License: GPL v2.0
 */

// Get the input
$source = file_get_contents($argv[1]);

// make traits to classes
$regexp = '#trait([\s]+[\S]+[\s]*){#';
$replace = 'class$1{';
$source = preg_replace($regexp, $replace, $source);

// use traits by extending them (classes that not extending a class)
$regexp = '#class([\s]+[\S]+[\s]*)(implements[\s]+[\S]+[\s]*)?{[\s]+use([^;]+);#';
$replace = 'class$1 extends $3 $2 {';
$source = preg_replace($regexp, $replace, $source);

// use traits by extending them (classes that already extending a class)
$regexp = '#class([\s]+[\S]+[\s]+extends[\s]+[\S]+[\s]*)(implements[\s]+[\S]+[\s]*)?{[\s]+use([^;]+);#';
$replace = 'class$1, $3 $2{';
$source = preg_replace($regexp, $replace, $source);

// Output
echo $source;
