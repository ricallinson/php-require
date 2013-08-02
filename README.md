# Php-require

[![Build Status](https://secure.travis-ci.org/ricallinson/php-require.png?branch=master)](http://travis-ci.org/ricallinson/php-require)

A [PHP](http://php.net/) class that provides a [nodejs style module](http://nodejs.org/api/modules.html) loader so [PHP](http://php.net/) can use [npm](https://npmjs.org/).

## Exmaple

Make a module __./math.php__;

    <?php
    namespace php_require\my_module;

    $exports["sum"] = function ($a, $b) {
        return $a + $b;
    };
    ?>

Use the module;

    <?php
    require("../node_modules/php-require/index.php");

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
    require("../node_modules/php-require/index.php");

    $circle = $require('./circle.php');
    print('The area of a circle of radius 4 is ' . $circle["area"](4));

The contents of `circle.php`:

    <?php
    namespace php_require\circle;

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
    namespace php_require\my_class;

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

__WARNING:__ Any class or function declared in a module will be globally assessable. As in the example it is recommended for you to use a `namespace` to isolate any classes or functions defined in the module.

The module system is implemented in the `$require("php-require")` module.

## Core Modules

__Php-require__ has several modules packaged with it. These modules are described in greater detail elsewhere.

The core modules are pulled into __Php-require__ via it's `package.json` file.

Core modules are always preferentially loaded if their identifier is passed to require(). For instance, require('php-path') will always return the built in `php-path` module, even if there is a file by that name.

## File Modules

If the exact filename is not found, then __php-require__ will attempt to load the
required filename with the added extension of `.php`.

A module prefixed with `'/'` is an absolute path to the file.  For
example, `$require('/home/marco/foo.php')` will load the file at
`/home/marco/foo.php`.

A module prefixed with `'./'` is relative to the file calling `$require()`.
That is, `circle.php` must be in the same directory as `foo.php` for
`$require('./circle')` to find it.

Without a leading '/' or './' to indicate a file, the module is loaded 
from a `node_modules` folder.

If the given path does not exist, `$require()` will produce an _E_WARNING_ level error which will allow the script to continue.

## Loading from `node_modules` Folders

If the module identifier passed to `$require()` does not begin 
with `'/'`, `'../'`, or `'./'`, then __php_require__ starts at the
parent directory of the current module, and adds `/node_modules`, and
attempts to load the module from that location.

If it is not found there, then it moves to the parent directory, and so
on, until the root of the tree is reached.

For example, if the file at `'/home/ry/projects/foo.php'` called
`$require('bar.php')`, then __php-require__ would look in the following locations, in
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

# Php-path

This is a copy of the [nodejs path](http://nodejs.org/api/path.html) module for use with [php-require](https://github.com/ricallinson/php-require).

    Stability: 3 - Stable

This module contains utilities for handling and transforming file
paths.  Almost all these methods perform only string transformations.
The file system is not consulted to check whether paths are valid.

Use `require('path')` to use this module.  The following methods are provided:

## path.normalize(p)

Normalize a string path, taking care of `'..'` and `'.'` parts.

When multiple slashes are found, they're replaced by a single one;
when the path contains a trailing slash, it is preserved.
On Windows backslashes are used.

Example:

    path.normalize('/foo/bar//baz/asdf/quux/..')
    // returns
    '/foo/bar/baz/asdf'

## path.join([path1], [path2], [...])

Join all arguments together and normalize the resulting path.

Arguments must be strings.  In v0.8, non-string arguments were
silently ignored.  In v0.10 and up, an exception is thrown.

Example:

    path.join('/foo', 'bar', 'baz/asdf', 'quux', '..')
    // returns
    '/foo/bar/baz/asdf'

    path.join('foo', {}, 'bar')
    // throws exception
    TypeError: Arguments to path.join must be strings

## [TBD] path.resolve([from ...], to)

Resolves `to` to an absolute path.

If `to` isn't already absolute `from` arguments are prepended in right to left
order, until an absolute path is found. If after using all `from` paths still
no absolute path is found, the current working directory is used as well. The
resulting path is normalized, and trailing slashes are removed unless the path
gets resolved to the root directory. Non-string arguments are ignored.

Another way to think of it is as a sequence of `cd` commands in a shell.

    path.resolve('foo/bar', '/tmp/file/', '..', 'a/../subfile')

Is similar to:

    cd foo/bar
    cd /tmp/file/
    cd ..
    cd a/../subfile
    pwd

The difference is that the different paths don't need to exist and may also be
files.

Examples:

    path.resolve('/foo/bar', './baz')
    // returns
    '/foo/bar/baz'

    path.resolve('/foo/bar', '/tmp/file/')
    // returns
    '/tmp/file'

    path.resolve('wwwroot', 'static_files/png/', '../gif/image.gif')
    // if currently in /home/myself/node, it returns
    '/home/myself/node/wwwroot/static_files/gif/image.gif'

## [TBD] path.isAbsolute(path)

Determines whether `path` is an absolute path. An absolute path will always
resolve to the same location, regardless of the working directory.

Posix examples:

    path.isAbsolute('/foo/bar') // true
    path.isAbsolute('/baz/..')  // true
    path.isAbsolute('qux/')     // false
    path.isAbsolute('.')        // false

Windows examples:

    path.isAbsolute('//server')  // true
    path.isAbsolute('C:/foo/..') // true
    path.isAbsolute('bar\\baz')   // false
    path.isAbsolute('.')         // false

## [TBD] path.relative(from, to)

Solve the relative path from `from` to `to`.

At times we have two absolute paths, and we need to derive the relative
path from one to the other.  This is actually the reverse transform of
`path.resolve`, which means we see that:

    path.resolve(from, path.relative(from, to)) == path.resolve(to)

Examples:

    path.relative('C:\\orandea\\test\\aaa', 'C:\\orandea\\impl\\bbb')
    // returns
    '..\\..\\impl\\bbb'

    path.relative('/data/orandea/test/aaa', '/data/orandea/impl/bbb')
    // returns
    '../../impl/bbb'

## path.dirname(p)

Return the directory name of a path.  Similar to the Unix `dirname` command.

Example:

    path.dirname('/foo/bar/baz/asdf/quux')
    // returns
    '/foo/bar/baz/asdf'

## path.basename(p, [ext])

Return the last portion of a path.  Similar to the Unix `basename` command.

Example:

    path.basename('/foo/bar/baz/asdf/quux.html')
    // returns
    'quux.html'

    path.basename('/foo/bar/baz/asdf/quux.html', '.html')
    // returns
    'quux'

## path.extname(p)

Return the extension of the path, from the last '.' to end of string
in the last portion of the path.  If there is no '.' in the last portion
of the path or the first character of it is '.', then it returns
an empty string.  Examples:

    path.extname('index.html')
    // returns
    '.html'

    path.extname('index.')
    // returns
    '.'

    path.extname('index')
    // returns
    ''

## path.sep

The platform-specific file separator. `'\\'` or `'/'`.

An example on *nix:

    'foo/bar/baz'.split(path.sep)
    // returns
    ['foo', 'bar', 'baz']

An example on Windows:

    'foo\\bar\\baz'.split(path.sep)
    // returns
    ['foo', 'bar', 'baz']

## path.delimiter

The platform-specific path delimiter, `;` or `':'`.

An example on *nix:

    console.log(process.env.PATH)
    // '/usr/bin:/bin:/usr/sbin:/sbin:/usr/local/bin'

    process.env.PATH.split(path.delimiter)
    // returns
    ['/usr/bin', '/bin', '/usr/sbin', '/sbin', '/usr/local/bin']

An example on Windows:

    console.log(process.env.PATH)
    // 'C:\Windows\system32;C:\Windows;C:\Program Files\nodejs\'

    process.env.PATH.split(path.delimiter)
    // returns
    ['C:\Windows\system32', 'C:\Windows', 'C:\Program Files\nodejs\']
