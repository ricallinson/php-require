<?php

/*
	For Quick debuging.
*/

error_reporting(E_ALL);
ini_set('display_errors', 'on');
header('Content-type: text/plain');

/*
    Require the "php-require" module loader.
*/

require("../index.php");

/*
    $require modules.
*/

$text = $require("text");
$math = $require("math");

$text["print"]("Filename: " . $__filename);
$text["print"]("Dirname: " . $__dirname);
$text["print"]("Sum: " . $math["sum"](1, 2));
$text["print"]("Fib: " . $math["fib"](8));
$text["print"]("Span: " . $text["html"]["span"]("span"));
$text["print"]("Bold: " . $text["html"]["bold"]("bold"));

var_dump($require("./config.json"));
