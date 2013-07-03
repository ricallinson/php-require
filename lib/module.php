<?php

/*
    A Class that provides a nodejs style module loader so PHP to use npm.

    This may not be a good idea!
*/

class Module {

    /*
        Map of supported extension loaders.
    */

    public static $extensions = [];

    /*
        The actual module.
    */

    public $id = null;

    /*
        The actual module.
    */

    public $exports = [];

    /*
        Absolute path to the file.
    */

    public $filename = null;

    /*
        Cache of the loaded modules.
    */

    private static $cache = [];

    /*
        Modules parent module.
    */

    private $parent = null;

    /*
        Has this module been loaded.
    */

    private $loaded = false;

    /*
        Array of children;
    */

    private $children = [];

    /*
        Paths
    */

    public $paths = null;

    /*
        Construct.
    */

    function __construct($filename, $parent) {

        $this->id = $filename;
        $this->parent = $parent;
        $this->filename = $filename;
        $this->paths = Module::nodeModulePaths(Module::dirname($filename));

        if ($parent) {
             array_push($parent->children, $this);
        }
    }

    /*
        Returns the directory name of the given path.
    */

    public static function dirname($path) {

        $result = explode(DIRECTORY_SEPARATOR, $path);

        if (!$result[0] && !$result[1]) {
            // No dirname whatsoever
            return '.';
        }

        return implode(DIRECTORY_SEPARATOR, array_slice($result, 0, - 1));
    }

    /*
        Returns the extension of the given $path.
    */

    public static function extname($path) {
        return ".php";
    }

    /*
        Given an array of path segments returns a complete path.

        Refactor required.
    */

    public static function resolve(/* func_get_args() */) {

        $args = func_get_args();
        $root = $args[0][0] == DIRECTORY_SEPARATOR ? DIRECTORY_SEPARATOR : "";
        $parts = explode(DIRECTORY_SEPARATOR, join(DIRECTORY_SEPARATOR, $args));
        $paths = [];

        foreach($parts as &$part) {
            if ($part == ".") {
                $part = null;
            }
        }

        $parts = array_values(array_filter($parts));

        for ($i = 0; $i < count($parts); $i++) {
            $arg = $parts[$i];
            if ($arg == "..") {
                $arg = null;
                if ($i > 0) {
                    $paths[$i - 1] = null;
                }
            } else if ($arg == ".") {
                $arg = null;
            }
            array_push($paths, $arg);
        }

        $paths = array_filter($paths);

        $abspath = $root . join(DIRECTORY_SEPARATOR, $paths);

        return $abspath;
    }

    /*
        Resolve the module filename.

        Refactor required.
    */

    private static function resolveFilename($request, $parent) {

        $paths = [];

        if ($request[0] == DIRECTORY_SEPARATOR) {
            // If $request starts with a "/" then do nothing.
            $paths = [$request];
        } else if ($request[0] == ".") {
            // If $request starts with a "." then resolve it.
            $paths = [Module::resolve(Module::dirname($parent->filename), $request)];
        } else {
            // If request starts with neither then check the parent.
            foreach ($parent->paths as $path) {
                array_push($paths, Module::resolve($path, $request));
            }
        }

        foreach ($paths as $root) {
            $abspath = Module::resolve($root . ".php");
            if (file_exists($abspath)) {
                return $abspath;
            }
            $abspath = Module::resolve($root, "index.php");
            if (file_exists($abspath)) {
                return $abspath;
            }
            $abspath = Module::resolve($root);
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
        $from = Module::resolve($from);

        // note: this approach *only* works when the path is guaranteed
        // to be absolute.  Doing a fully-edge-case-correct path.split
        // that works on both Windows and Posix is non-trivial.
        $paths = [];
        $parts = explode(DIRECTORY_SEPARATOR, $from);
        $pos = 0;

        foreach ($parts as $tip) {
            if ($tip != "node_modules") {
                $dir = implode(DIRECTORY_SEPARATOR, array_merge(array_slice($parts, 0, $pos + 1), ["node_modules"]));
                array_push($paths, $dir);
            }
            $pos = $pos + 1;
        }

        return array_reverse($paths);
    }

    /*
        Load a module.
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

        $extension = Module::extname($this->filename) || '.php';

        if (!isset(Module::$extensions[$extension])) {
            $extension = '.php';
        }

        call_user_func(Module::$extensions[$extension], $this, $this->filename);

        $this->loaded = true;
    }

    /*
        Uses "eval" to compile the given $content.
    */

    public function compile($content, $filename) {

        // Remove <?php
        $content = str_replace("<?php", "", $content);

        $require = function ($request) {
            return Module::loadModule($request, $this, false);
        };

        $__filename = $filename;
        $__dirname = Module::dirname($filename);

        $content = 'return function ($__filename, $__dirname, &$exports, &$module, $require) {' . $content . '};';

        $fn = eval($content);

        $fn($__filename, $__dirname, $this->exports, $this, $require);
    }
}

/*
    Extensions loader implementations.
*/

Module::$extensions[".php"] = function ($module, $filename) {
    $content = file_get_contents($filename);
    $module->compile($content, $filename);
};

/*
    Setup standard globals.
*/

$__filename = Module::resolve($_SERVER["DOCUMENT_ROOT"], $_SERVER["SCRIPT_NAME"]);
$__dirname = Module::dirname($__filename);
$require = function ($request) use($__filename) {
    $parent = new Module($__filename, null);
    return Module::loadModule($request, $parent, true);
};
