<?php
namespace php_require;

/*
    A Class that provides a nodejs style module loader so PHP can use npm.

    (This may not be a good idea!).
*/

class Module {

    /*
        Used as a semaphore while loading core modules required by php-require.
    */

    public static $coreModulesLoaded = false;

    /*
        List of core modules to load before php-require can be used.
    */

    public static $coreModules = array("php-path");

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

    private $paths = array();

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

        if (Module::$coreModulesLoaded === false) {

            /*
                This code block is only called once.
            */

            Module::$coreModulesLoaded = "loading";
            $this->loadNativeModules();
            Module::$coreModulesLoaded = true;
        }

        if (!$filename) {
            return;
        }

        $this->id = $filename;
        $this->parent = $parent;
        $this->filename = $filename;

        if (Module::$coreModulesLoaded === true) {

            /*
                We can only call Module::nodeModulePaths() once all core modules have loaded.
            */

            $this->paths = Module::nodeModulePaths(dirname($filename));
        }

        if ($parent) {
             array_push($parent->children, $this);
        }
    }

    /*
        Loads core modules.
    */

    private function loadNativeModules() {

        foreach (Module::$coreModules as $request) {
            $filename = __DIR__ . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . $request . ".php";
            $module = new Module($filename, $this);
            Module::$cache[$filename] = $module;
            $module->compile();
            $module->loaded = true;
        }
    }

    /*
        Resolve the module filename.

        Refactor required.
    */

    private static function resolveFilename($request, $parent) {

        $pathlib = Module::loadModule("php-path");
        $paths = array();

        if ($request[0] == DIRECTORY_SEPARATOR) {
            // If $request starts with a "/" then do nothing.
            $paths = array($request);
        } else if ($request[0] == ".") {
            // If $request starts with a "." then resolve it.
            $paths = array($pathlib->join($pathlib->dirname($parent->filename), $request));
        } else {
            // If request starts with neither then check the parent.
            foreach ($parent->paths as $path) {
                array_push($paths, $pathlib->join($path, $request));
            }
        }

        foreach ($paths as $root) {
            $abspath = $pathlib->normalize($root . ".php");
            if (file_exists($abspath)) {
                return $abspath;
            }
            $abspath = $pathlib->join($root, "index.php");
            if (file_exists($abspath)) {
                return $abspath;
            }
            $abspath = $pathlib->normalize($root);
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

        $pathlib = Module::loadModule("php-path");

        // guarantee that 'from' is absolute.
        $from = $pathlib->normalize($from);

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

    public static function loadModule($request, $parent=null, $isMain=false) {

        if (in_array($request, Module::$coreModules)) {
            $filename = __DIR__ . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . $request . ".php";
        } else {
            $filename = Module::resolveFilename($request, $parent);
        }

        if (isset(Module::$cache[$filename])) {
            return Module::$cache[$filename]->exports;
        }

        $module = new Module($filename, $parent);

        if ($isMain) {
            $module->id = ".";
        }

        Module::$cache[$filename] = $module;

        try {
            $module->load();
        } catch (\Exception $e) {
            unset(Module::$cache[$filename]);
            error_log("The module at " . $filename . " threw an Exception:\n" . $e);
        }

        return $module->exports;
    }

    /*
        Find the module file.
    */

    private function load() {

        $pathlib = Module::loadModule("php-path");

        if ($this->loaded) {
            throw new \Exception("the module " . $this->filename . " has already been loaded.");
        }

        $extension = $pathlib->extname($this->filename);

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
        $__dirname = dirname($this->filename);

        $fn = function ($__filename, $__dirname, &$exports, &$module, $require) {
            if (is_file($__filename)) {
                include($__filename);
            } else {
                // throw new \Exception("Module not found at: " . $__filename);
                error_log("php-require: Module not found at: " . $__filename);
            }
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
    $module->exports = json_decode($content, true);
};

/*
    Setup the first $require() function.
*/

$__filename = $_SERVER["DOCUMENT_ROOT"] . $_SERVER["SCRIPT_NAME"];
$__dirname = dirname($__filename);
$require = function ($request) use($__filename) {
    $parent = new Module($__filename, null);
    return Module::loadModule($request, $parent, true);
};
