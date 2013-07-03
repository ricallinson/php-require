# Php-require

A [PHP](http://php.net/) class that provides a [nodejs style module](http://nodejs.org/api/modules.html) loader so [PHP](http://php.net/) can use [npm](https://npmjs.org/). This may not be a good idea!

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

That's all there is to it. For a complete example with dependences checkout the [php-require-example](https://github.com/ricallinson/php-require-example) project.

## Modules

This section is a modified version of the [nodejs modules api](http://nodejs.org/api/modules.html) documentation.

__php-require__ has a simple module loading system.  In __php-require__, files and modules are in
one-to-one correspondence.  As an example, `foo.php` loads the module
`circle.php` in the same directory.

The contents of `foo.php`:
    
    <?php
    require("../../index.php");
    $circle = $require('./circle.php');
    print('The area of a circle of radius 4 is ' . $circle["area"](4));

The contents of `circle.php`:

    <?php
    $PI = pi();

    $exports["area"] = function ($r) use ($PI) {
        return $PI * $r * $r;
    };

    $exports["circumference"] = function ($r) use ($PI) {
        return 2 * $PI * $r;
    };

The module `circle.php` has exported the functions `area()` and
`circumference()`.  To export an object, add to the special `$exports`
object.

Note that `$exports` is a reference to `$module->exports` making it suitable
for augmentation only. If you are exporting a single item such as a
constructor you will want to use `$module->exports` directly instead.

    <?php
    class MyConstructor {
        function __construct() {
            
        }
    }

    // BROKEN: Does not modify exports
    $exports = new MyConstructor();

    // exports the constructor properly
    $module->exports = new MyConstructor();

Variables local to the module will be private. In this example the variable `$PI` is 
private to `circle.php`. However, as in this example, if you are using an anonymous function
you will still have to use the `use()` keyword to import the variables into the function scope.

Any class or function declared in a module will be globally assessable.

The module system is implemented in the `$require("php-require")` module.

## File Modules

If the exact filename is not found, then node will attempt to load the
required filename with the added extension of `.php`.

A module prefixed with `'/'` is an absolute path to the file.  For
example, `$require('/home/marco/foo.php')` will load the file at
`/home/marco/foo.php`.

A module prefixed with `'./'` is relative to the file calling `$require()`.
That is, `circle.php` must be in the same directory as `foo.php` for
`$require('./circle')` to find it.

Without a leading '/' or './' to indicate a file, the module is loaded 
from a `node_modules` folder.

If the given path does not exist, `$require()` will produce a fatal 
_E_COMPILE_ERROR_ level error.

## Loading from `node_modules` Folders

If the module identifier passed to `$require()` does not begin 
with `'/'`, `'../'`, or `'./'`, then __php_require__ starts at the
parent directory of the current module, and adds `/node_modules`, and
attempts to load the module from that location.

If it is not found there, then it moves to the parent directory, and so
on, until the root of the tree is reached.

For example, if the file at `'/home/ry/projects/foo.php'` called
`$require('bar.php')`, then node would look in the following locations, in
this order:

* `/home/ry/projects/node_modules/bar.php`
* `/home/ry/node_modules/bar.php`
* `/home/node_modules/bar.php`
* `/node_modules/bar.php`

This allows programs to localize their dependencies, so that they do not
clash.

## Folders as Modules

It is convenient to organize programs and libraries into self-contained
directories, and then provide a single entry point to that library.
There are three ways in which a folder may be passed to `$require()` as
an argument.

__START OF NOT IMPLEMENTED__

The first is to create a `package.json` file in the root of the folder,
which specifies a `main` module.  An example package.json file might
look like this:

    { "name" : "some-library",
      "main" : "./lib/some-library.js" }

If this was in a folder at `./some-library`, then
`$require('./some-library')` would attempt to load
`./some-library/lib/some-library.php`.

This is the extent of __php_require__'s awareness of package.json files.

__END OF NOT IMPLEMENTED__

If there is no package.json file present in the directory, then __php_require__
will attempt to load an `index.php` file out of that
directory.  For example, if there was no package.json file in the above
example, then `$require('./some-library')` would attempt to load:

* `./some-library/index.php`

## Caching

Modules are cached after the first time they are loaded.  This means
(among other things) that every call to `$require('foo')` will get
exactly the same object returned, if it would resolve to the same file.

Multiple calls to `$require('foo')` may not cause the module code to be
executed multiple times.  This is an important feature.  With it,
"partially done" objects can be returned, thus allowing transitive
dependencies to be loaded even when they would cause cycles.

If you want to have a module execute code multiple times, then export a
function, and call that function.

### Module Caching Caveats

Modules are cached based on their resolved filename.  Since modules may
resolve to a different filename based on the location of the calling
module (loading from `node_modules` folders), it is not a *guarantee*
that `$require('foo')` will always return the exact same object, if it
would resolve to different files.

## The `module` Object

<!-- type=var -->
<!-- name=module -->

* {Object}

In each module, the `module` free variable is a reference to the object
representing the current module.  In particular
`module.exports` is accessible via the `exports` module-global.
`module` isn't actually a global but rather local to each module.

### module->exports

* {Array}

The `$module->exports` object is created by the Module system as a PHP array, 
you can add items to the array as needed. Sometimes this is not
acceptable, many want their module to be an instance of some class or to be a variable. To do this
assign the desired export value to `$module->exports`.

    $module->exports["item"] = "My Item";

or;

    $module->exports = new MyClass();

or;

    $module->exports = "My Variable";

### module->id

* {String}

The identifier for the module. Typically this is the fully resolved
filename.


### module->filename

* {String}

The fully resolved filename to the module.


### module->loaded

* {Boolean}

Whether or not the module is done loading, or is in the process of
loading.


### module->parent

* {Module Object}

The module that required this one.


### module->children

* {Array}

The module objects required by this one.
