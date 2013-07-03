# Php-require

A Class that provides a nodejs style module loader so PHP can use npm (This may not be a good idea!).

    <?php
    require("../lib/module.php");
    $math = $require("math");
    $math["sum"](1, 2)
    ?>
