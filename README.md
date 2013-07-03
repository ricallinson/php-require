# Php-require

A class that provides a [nodejs style module](http://nodejs.org/api/modules.html) loader so [PHP](http://php.net/) can use [npm](https://npmjs.org/). This may not be a good idea!

Make a module __./math.php__;

    <?php
    $exports["sum"] = function ($a, $b) {
        return $a + $b;
    };
    ?>

Use the module;

    <?php
    require("./node_modules/npm-require/index.php");

    $math = $require("./math");
    $math["sum"](1, 1);
    ?>
