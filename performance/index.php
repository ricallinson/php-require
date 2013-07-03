<?php

/*
	For Quick debuging.
*/

error_reporting(E_ALL);
ini_set('display_errors', 'on');
header('Content-type: text/plain');

/*
    Measurment function.
*/

$cycle = 5000000;

function measure($last) {
	if ($last) {
		return microtime(true) - $last;
	}
	return microtime(true);
}

/*
	Standard PHP Class.
*/

$start = measure(null);

require_once("./class.php");

$class = new PhpClass();

for ($i = 0; $i < $cycle; $i++) {
	$class->fn();
}

print(round(measure($start), 5) . "\n");

/*
	Nodejs style module with PhpClass.
*/

$start = measure(null);

require_once("../lib/module.php");

$module = $require("./php-module");

for ($i = 0; $i < $cycle; $i++) {
	$module->fn();
}

print(round(measure($start), 5) . "\n");

/*
	Nodejs style module Function.
*/

$start = measure(null);

require_once("../lib/module.php");

$module = $require("./fn-module");

for ($i = 0; $i < $cycle; $i++) {
	$module["fn"]();
}

print(round(measure($start), 5) . "\n");

echo "Done\n";
