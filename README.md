# Npm-require

A class that provides a nodejs style module loader so PHP can use npm (This may not be a good idea!).

Make a module __./math.php__;

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

## Ways to Use Modules

### Attributes

    $exports["newline"] = "\n";
    $exports["return"] = "\r\n";

### Anonymous Functions

    $exports["fn"] = function () {
        return 1;
    };

### Objects

    class MyClass {
        public function fn() {
            return 1;
        }
    }

    $exports["obj"] = new MyClass();

### Factories

    class MyClass {
        public function fn() {
            return 1;
        }
    }

    $module->exports = function () {
        return new MyClass();
    };
