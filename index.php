<?php
namespace php_require;

/*
    Import php-path so we can use it outside of php-require.
*/

use php_require\php_path\Path;
$module = new \stdClass();
require("php-path" . DIRECTORY_SEPARATOR . "index.php");

/*
    A Class that provides a nodejs style module loader so PHP can use npm.

    (This may not be a good idea!).
*/

class Module {

    /*
        Path object.
    */

    public static $pathlib = null;

    /*
        Cache of the loaded modules.
    */

    private static $cache = array();

    /*
        Map of supported extension loaders.
    */

    public static $extensions = array();

    /*
        Paths
    */

    private $paths = null;

    /*
        The actual module.
    */

    public $id = null;

    /*
        The actual module.
    */

    public $exports = array();

    /*
        Absolute path to the file.
    */

    public $filename = null;

    /*
        Modules parent module.
    */

    public $parent = null;

    /*
        Has this module been loaded.
    */

    public $loaded = false;

    /*
        Array of children;
    */

    public $children = array();

    /*
        Construct.
    */

    function __construct($filename, $parent) {

        if (Module::$pathlib === null) {
            Module::$pathlib = new Path();
        }

        $this->id = $filename;
        $this->parent = $parent;
        $this->filename = $filename;
        $this->paths = Module::nodeModulePaths(Module::$pathlib->dirname($filename));

        if ($parent) {
             array_push($parent->children, $this);
        }
    }

    /*
        Resolve the module filename.

        Refactor required.
    */

    private static function resolveFilename($request, $parent) {

        $paths = array();

        if ($request[0] == DIRECTORY_SEPARATOR) {
            // If $request starts with a "/" then do nothing.
            $paths = array($request);
        } else if ($request[0] == ".") {
            // If $request starts with a "." then resolve it.
            $paths = array(Module::$pathlib->join(Module::$pathlib->dirname($parent->filename), $request));
        } else {
            // If request starts with neither then check the parent.
            foreach ($parent->paths as $path) {
                array_push($paths, Module::$pathlib->join($path, $request));
            }
        }

        foreach ($paths as $root) {
            $abspath = Module::$pathlib->normalize($root . ".php");
            if (file_exists($abspath)) {
                return $abspath;
            }
            $abspath = Module::$pathlib->join($root, "index.php");
            if (file_exists($abspath)) {
                return $abspath;
            }
            $abspath = Module::$pathlib->normalize($root);
            if (file_exists($abspath)) {
                return $abspath;
            }
        }

        // If a file cannot be found return the original $request.

        return $request;
    }

    /*
        Generate a list of all possible paths modules could be found at.
    */

    private static function nodeModulePaths($from) {

        // guarantee that 'from' is absolute.
        $from = Module::$pathlib->normalize($from);

        // note: this approach *only* works when the path is guaranteed
        // to be absolute.  Doing a fully-edge-case-correct path.split
        // that works on both Windows and Posix is non-trivial.
        $paths = array();
        $parts = explode(DIRECTORY_SEPARATOR, $from);
        $pos = 0;

        foreach ($parts as $tip) {
            if ($tip != "node_modules") {
                $dir = implode(DIRECTORY_SEPARATOR, array_merge(array_slice($parts, 0, $pos + 1), array("node_modules")));
                array_push($paths, $dir);
            }
            $pos = $pos + 1;
        }

        return array_reverse($paths);
    }

    /*
        Load a module (this is the require function).
    */

    public static function loadModule($request, $parent, $isMain) {

        $filename = Module::resolveFilename($request, $parent);

        if (isset(Module::$cache[$filename])) {
            return Module::$cache[$filename]->exports;
        }

        $module = new Module($filename, $parent);

        if ($isMain) {
            $module->id = ".";
        }

        Module::$cache[$filename] = $module;

        $hadException = true;

        try {
            $module->load();
            $hadException = false;
        } catch (Exception $e) {
            if ($hadException) {
                unset(Module::$cache[$filename]);
            }
        }

        return $module->exports;
    }

    /*
        Find the module file.
    */

    private function load() {

        if ($this->loaded) {
            throw new Exception("the module " . $this->filename . " has already been loaded.");
        }

        $extension = Module::$pathlib->extname($this->filename);

        if (!isset(Module::$extensions[$extension])) {
            $extension = '.php';
        }

        call_user_func(Module::$extensions[$extension], $this, $this->filename);

        $this->loaded = true;
    }

    /*
        Compile the module file by requiring it in a closed scope.
    */

    public function compile() {

        $require = function ($request) {
            return Module::loadModule($request, $this, false);
        };

        $__filename = $this->filename;
        $__dirname = Module::$pathlib->dirname($this->filename);

        $fn = function ($__filename, $__dirname, &$exports, &$module, $require) {
            require($__filename);
        };

        $fn($__filename, $__dirname, $this->exports, $this, $require);
    }
}

/*
    Extensions loader implementations.
*/

Module::$extensions[".php"] = function ($module, $filename) {
    $module->compile();
};

Module::$extensions[".json"] = function ($module, $filename) {
    $content = file_get_contents($filename);
    $module->exports = $content;
};

/*
    Setup the first $require() function.
*/

Module::$pathlib = new Path();
$__filename = Module::$pathlib->join($_SERVER["DOCUMENT_ROOT"], $_SERVER["SCRIPT_NAME"]);
$__dirname = Module::$pathlib->dirname($__filename);
$require = function ($request) use($__filename) {
    $parent = new Module($__filename, null);
    return Module::loadModule($request, $parent, true);
};
