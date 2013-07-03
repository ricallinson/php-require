# Npm-require

A class that provides a nodejs style module loader so PHP can use npm (This may not be a good idea!).

Make a module __./math__;

	<?php
	$exports["sum"] = function ($a, $b) {
		return $a + $b;
	};
	?>

Use the module;

    <?php
    require("./npm-require.php");
    $math = $require("./math");
    $math["sum"](1, 1);
    ?>

