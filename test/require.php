<?php
use php_require\Module;
use php_require\php_path\Path;

// require("../index.php");

/*
    This is done to prime "php-path".
*/

$prime = new Module(null, null);

describe("php-require", function () {

    it("should return [true]", function () {
        assert(class_exists("php_require\Module"));
    });

    it("should return [/fake]", function () {
        $module = new Module("/fake", null);
        assert($module->id === "/fake");
    });

    it("should return [DIRECTORY_SEPARATOR]", function () {
        global $require;
        $module = $require("php-path");
        assert($module->sep === DIRECTORY_SEPARATOR);
    });

    it("should return []", function () {
        $module = new Module(null, null);
        assert(get_class($module) === "php_require\Module");
    });
});

describe("Module::resolveFilename()", function () {

    /*
        This is REALLY weird but handy.
    */

    $method = new ReflectionMethod("php_require\Module", "resolveFilename");
    $method->setAccessible(true);

    $pathlib = new Path();
    $parent = new Module($pathlib->join(__DIR__, "./fixtures/node_modules/math/index.php"), null);

    it("should return a function", function () {
        assert(method_exists("php_require\Module", "resolveFilename"));
    });

    it("should return /path/not/found", function () use ($method, $parent) {
        // $paths = Module::resolveFilename("/path/not/found");
        $path = $method->invoke(new Module(null, null), "/path/not/found", $parent);
        assert($path === "/path/not/found");
    });

    it("should return /php-require/test/fixtures/node_modules/math/index.php", function () use ($method, $parent, $pathlib) {
        $request = $pathlib->join(__DIR__, "./fixtures/node_modules/math");
        // $paths = Module::resolveFilename($request);
        $path = $method->invoke(new Module(null, null), $request, $parent);
        assert(strrpos($path, "/php-require/test/fixtures/node_modules/math/index.php") !== false);
    });

    it("should return /php-require/test/fixtures/node_modules/math/index.php", function () use ($method, $parent, $pathlib) {
        $request = $pathlib->join(__DIR__, "./fixtures/node_modules/math/index");
        // $paths = Module::resolveFilename($request);
        $path = $method->invoke(new Module(null, null), $request, $parent);
        assert(strrpos($path, "/php-require/test/fixtures/node_modules/math/index.php") !== false);
    });

    it("should return /php-require/test/fixtures/node_modules/math/index.php", function () use ($method, $parent, $pathlib) {
        $request = $pathlib->join(__DIR__, "./fixtures/node_modules/math/index.php");
        // $paths = Module::resolveFilename($request);
        $path = $method->invoke(new Module(null, null), $request, $parent);
        assert(strrpos($path, "/php-require/test/fixtures/node_modules/math/index.php") !== false);
    });

    it("should return /php-require/test/fixtures/node_modules/math/index.php", function () use ($method, $parent, $pathlib) {
        $request = $pathlib->join(__DIR__, "./fixtures/node_modules/tester/refect");
        // $paths = Module::resolveFilename($request);
        $path = $method->invoke(new Module(null, null), $request, $parent);
        assert(strrpos($path, "/php-require/test/fixtures/node_modules/tester/refect.php") !== false);
    });

    it("should return /php-require/test/fixtures/config.json", function () use ($method, $parent, $pathlib) {
        $request = $pathlib->join(__DIR__, "./fixtures/config.json");
        // $paths = Module::resolveFilename($request);
        $path = $method->invoke(new Module(null, null), $request, $parent);
        assert(strrpos($path, "/php-require/test/fixtures/config.json") !== false);
    });

    it("should return /php-require/test/fixtures/node_modules/math/index.php", function () use ($method, $parent) {
        // $paths = Module::resolveFilename("./index");
        $path = $method->invoke(new Module(null, null), "./index", $parent);
        assert(strrpos($path, "/php-require/test/fixtures/node_modules/math/index.php") !== false);
    });

    it("should return /php-require/test/fixtures/node_modules/math/index.php", function () use ($method, $parent) {
        // $paths = Module::resolveFilename("math");
        $path = $method->invoke(new Module(null, null), "math", $parent);
        assert(strrpos($path, "/php-require/test/fixtures/node_modules/math/index.php") !== false);
    });
});

describe("Module::nodeModulePaths()", function () {

    /*
        This is REALLY weird but handy.
    */

    $method = new ReflectionMethod("php_require\Module", "nodeModulePaths");
    $method->setAccessible(true);

    it("should return a function", function () {
        assert(method_exists("php_require\Module", "nodeModulePaths"));
    });

    it("should return a list of 5 paths", function () use ($method) {
        // $paths = Module::nodeModulePaths("/find/from/this/path");
        $paths = $method->invoke(new Module(null, null), "/find/from/this/path");
        assert(count($paths) === 5);
    });

    it("should return a /find/from/this/path/node_modules", function () use ($method) {
        // $paths = Module::nodeModulePaths("/find/from/this/path");
        $paths = $method->invoke(new Module(null, null), "/find/from/this/path");
        assert($paths[0] === "/find/from/this/path/node_modules");
    });

    it("should return a /node_modules", function () use ($method) {
        // $paths = Module::nodeModulePaths("/find/from/this/path");
        $paths = $method->invoke(new Module(null, null), "/find/from/this/path");
        assert($paths[4] === "/node_modules");
    });
});

describe("Module::loadModule()", function () {

    $pathlib = new Path();
    $parent = new Module($pathlib->join(__DIR__, "./fixtures/node_modules/math/index.php"), null);

    it("should return a function", function () {
        assert(method_exists("php_require\Module", "loadModule"));
    });

    it("should return a Closure", function () use ($parent) {
        $module = Module::loadModule("math", $parent, false);
        assert(get_class($module["sum"]) === "Closure");
    });

    it("should return [string] as main", function () use ($parent) {
        $module = Module::loadModule("tester/refect", $parent, true);
        assert($module["module"]->id === ".");
    });

    it("should return [2] after catching an exception", function () use ($parent) {
        $module = Module::loadModule("tester/error", $parent);
        assert(count($module) === 0);
    });

    it("should return [] after reading json", function () use ($parent) {
        $module = Module::loadModule("tester/json.json", $parent);
        assert($module["key"] === "val");
    });
});

describe("module->load()", function () {

    it("should return a function", function () {
        assert(method_exists("php_require\Module", "load"));
    });
});

describe("module->compile()", function () {

    it("should return a function", function () {
        assert(method_exists("php_require\Module", "compile"));
    });

    it("should return 2", function () {

        $pathlib = new Path();
        $module = new Module($pathlib->join(__DIR__, "./fixtures/node_modules/tester/refect.php"), null);
        assert(count($module->exports) === 0);
        $module->compile();
        assert(count($module->exports) === 3);

        assert($module->exports["filename"] === $module->filename);
        assert($module->exports["dirname"] === $pathlib->dirname($module->filename));
    });
});
