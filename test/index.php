<?php
use php_require\php_tester\Tester;

/*
	Report everything.
*/

error_reporting(E_ALL);
ini_set('display_errors', 'on');

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

/*
	The code below is 99% a copy paste from php-tester.
*/

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

/*
    Get params from the CLI
*/

$dir = getcwd() . DIRECTORY_SEPARATOR . "unit";
// $renderer = "simple";

/*
    Load the renderer to use.
*/

// $renderer = $require(__DIR__. DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . $renderer);
$tester->renderer($renderer);

/*
    If xdebug is NOT installed.
*/

if (!function_exists("xdebug_start_code_coverage")) {
    $errors = $tester->run($dir);
    exit($errors);
}

/*
    If xdebug IS installed.
*/

xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);

$errors = $tester->run($dir);

$files = xdebug_get_code_coverage();

$called = 0;
$missed = 0;

foreach ($files as $file => $lines) {
    if (strpos($file, $dir) !== false) {
        foreach ($lines as $num => $line) {
            // echo $num . ": " . $line . "\n";
            if ($line === 1) {
                $called++;
            } else if ($line === -1) {
                $missed++;
            }
        }
    }
}

xdebug_stop_code_coverage(true);

echo("Code covergae: " . (100 - (($called + $missed / 100) * $missed)) . "%\n\n");

exit($errors);
