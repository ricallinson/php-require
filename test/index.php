<?php
use php_require\php_tester\Tester;
use php_require\php_tester\SimpleRenderer;

/*
	We have to fake some stuff as we can't use "php-require" (because we are testing it).
*/

class FakeRequire {
	public $exports = array();
}

$module = new FakeRequire();

require(__DIR__ . "../../php-tester/lib/tester.php");

$tester = $module->exports;

require(__DIR__ . "../../php-tester/lib/simple.php");

$renderer = $module->exports;

// Active assert and make it quiet
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 0);
assert_options(ASSERT_QUIET_EVAL, 1);
assert_options(ASSERT_CALLBACK, function ($file, $line, $code) {
    throw new Exception();
});

function describe($text, $fn) {
    Tester::describe($text, $fn);
}

function it($text, $fn) {
    Tester::it($text, $fn);
}

$tester->renderer($renderer);
$errors = $tester->run(getcwd() . DIRECTORY_SEPARATOR . "unit");
exit($errors);
