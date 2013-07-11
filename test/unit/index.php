<?php
use php_require\Module;

require("../index.php");

describe("php-require", function () {

    it("should return true", function () {
        assert(class_exists("php_require\Module"));
    });

    it("should return /fake", function () {
        $module = new Module("/fake", null);
        assert($module->id === "/fake");
    });
});

describe("Module::dirname()", function () {

    it("should return a function", function () {
        assert(method_exists("php_require\Module", "dirname"));
    });

    it("should return /my/dir", function () {
        $name = Module::dirname("/my/dir/name");
        assert($name === "/my/dir");
    });

    it("should return /my/dir/name", function () {
        $name = Module::dirname("/my/dir/name/file.php");
        assert($name === "/my/dir/name");
    });

    it("should return /my", function () {
        $name = Module::dirname("/my/dir");
        assert($name === "/my");
    });

    it("should return .", function () {
        $name = Module::dirname("/my");
        assert($name === "/");
    });

    it("should return .", function () {
        $name = Module::dirname("");
        assert($name === ".");
    });

    it("should return .", function () {
        $name = Module::dirname("./");
        assert($name === ".");
    });

    it("should return .", function () {
        $name = Module::dirname("/");
        assert($name === "/");
    });
});

describe("Module::extname()", function () {

    it("should return a function", function () {
        assert(method_exists("php_require\Module", "extname"));
    });

    it("should return .php", function () {
        $ext = Module::extname("/my/dir/name/file.php");
        assert($ext === ".php");
    });

    it("should return null", function () {
        $ext = Module::extname("/my/dir");
        assert($ext === null);
    });

    it("should return .json", function () {
        $ext = Module::extname("/my.old.json");
        assert($ext === ".json");
    });
});

describe("Module::resolve()", function () {

    it("should return a function", function () {
        assert(method_exists("php_require\Module", "resolve"));
    });

    it("should return my/dir/name/file.php", function () {
        $path = Module::resolve("my", "dir", "name", "file.php");
        assert($path === "my/dir/name/file.php");
    });

    it("should return /my/dir/file.php", function () {
        $path = Module::resolve("/my", "/dir", "./file.php");
        assert($path === "/my/dir/file.php");
    });

    it("should return /my/dir/dir/file.php", function () {
        $path = Module::resolve("/my/dir/", "/dir/", "/file.php");
        assert($path === "/my/dir/dir/file.php");
    });

    it("should return /my/file.php", function () {
        $path = Module::resolve("/my/dir/../file.php");
        assert($path === "/my/file.php");
    });

    it("should return /my/file.php", function () {
        $path = Module::resolve("/my", "dir", "../file.php");
        assert($path === "/my/file.php");
    });

    it("should return /", function () {
        $path = Module::resolve("./");
        assert($path === "/");
    });

    it("should return /", function () {
        $path = Module::resolve("../");
        assert($path === "/");
    });

    it("should return /", function () {
        $path = Module::resolve("../../");
        assert($path === "/");
    });
});

describe("Module::resolveFilename()", function () {

    /*
        This is REALLY weird but handy.
    */

    $method = new ReflectionMethod("php_require\Module", "resolveFilename");
    $method->setAccessible(true);

    $parent = new Module(Module::resolve(__DIR__, "../../fixtures/node_modules/math/index.php"), null);

    it("should return a function", function () {
        assert(method_exists("php_require\Module", "resolveFilename"));
    });

    it("should return /path/not/found", function () use ($method, $parent) {
        // $paths = Module::resolveFilename("/path/not/found");
        $path = $method->invoke(new Module(null, null), "/path/not/found", $parent);
        assert($path === "/path/not/found");
    });

    it("should return /php-require/test/fixtures/node_modules/math/index.php", function () use ($method, $parent) {
        $request = Module::resolve(__DIR__, "../../fixtures/node_modules/math");
        // $paths = Module::resolveFilename($request);
        $path = $method->invoke(new Module(null, null), $request, $parent);
        assert(strrpos($path, "/php-require/test/fixtures/node_modules/math/index.php") !== false);
    });

    it("should return /php-require/test/fixtures/node_modules/math/index.php", function () use ($method, $parent) {
        $request = Module::resolve(__DIR__, "../../fixtures/node_modules/math/index");
        // $paths = Module::resolveFilename($request);
        $path = $method->invoke(new Module(null, null), $request, $parent);
        assert(strrpos($path, "/php-require/test/fixtures/node_modules/math/index.php") !== false);
    });

    it("should return /php-require/test/fixtures/node_modules/math/index.php", function () use ($method, $parent) {
        $request = Module::resolve(__DIR__, "../../fixtures/node_modules/math/index.php");
        // $paths = Module::resolveFilename($request);
        $path = $method->invoke(new Module(null, null), $request, $parent);
        assert(strrpos($path, "/php-require/test/fixtures/node_modules/math/index.php") !== false);
    });

    it("should return /php-require/test/fixtures/node_modules/math/index.php", function () use ($method, $parent) {
        $request = Module::resolve(__DIR__, "../../fixtures/node_modules/tester/refect");
        // $paths = Module::resolveFilename($request);
        $path = $method->invoke(new Module(null, null), $request, $parent);
        assert(strrpos($path, "/php-require/test/fixtures/node_modules/tester/refect.php") !== false);
    });

    it("should return /php-require/test/fixtures/config.json", function () use ($method, $parent) {
        $request = Module::resolve(__DIR__, "../../fixtures/config.json");
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

    $parent = new Module(Module::resolve(__DIR__, "../../fixtures/node_modules/math/index.php"), null);

    it("should return a function", function () {
        assert(method_exists("php_require\Module", "loadModule"));
    });

    it("should return a Closure", function () use ($parent) {
        $module = Module::loadModule("math", $parent, false);
        assert(get_class($module["sum"]) === "Closure");
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
        $module = new Module(Module::resolve(__DIR__, "../../fixtures/node_modules/tester/refect.php"), null);
        assert(count($module->exports) === 0);
        $module->compile();
        assert(count($module->exports) === 2);

        assert($module->exports["filename"] === $module->filename);
        assert($module->exports["dirname"] === Module::dirname($module->filename));
    });
});
