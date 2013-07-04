<?php

require("./fake-mocha.php");
require("./fake-asserts.php");
require("../../index.php");

describe("php-require", function () {

    it("should return true", function () {
        asserts()->equal(class_exists("Module"), true);
    });

    it("should return /fake", function () {
        $module = new Module("/fake", null);
        asserts()->equal($module->id, "/fake");
    });
});

describe("Module::dirname()", function () {

    it("should return a function", function () {
        asserts()->equal(method_exists("Module", "dirname"), true);
    });

    it("should return /my/dir", function () {
        $name = Module::dirname("/my/dir/name");
        asserts()->equal($name, "/my/dir");
    });

    it("should return /my/dir/name", function () {
        $name = Module::dirname("/my/dir/name/file.php");
        asserts()->equal($name, "/my/dir/name");
    });

    it("should return /my", function () {
        $name = Module::dirname("/my/dir");
        asserts()->equal($name, "/my");
    });

    it("should return .", function () {
        $name = Module::dirname("/my");
        asserts()->equal($name, ".");
    });

    it("should return .", function () {
        $name = Module::dirname("");
        asserts()->equal($name, ".");
    });

    it("should return .", function () {
        $name = Module::dirname("./");
        asserts()->equal($name, ".");
    });

    it("should return .", function () {
        $name = Module::dirname("/");
        asserts()->equal($name, ".");
    });
});

describe("Module::extname()", function () {

    it("should return a function", function () {
        asserts()->equal(method_exists("Module", "extname"), true);
    });

    it("should return .php", function () {
        $ext = Module::extname("/my/dir/name/file.php");
        asserts()->equal($ext, ".php");
    });

    it("should return null", function () {
        $ext = Module::extname("/my/dir");
        asserts()->equal($ext, null);
    });

    it("should return .json", function () {
        $ext = Module::extname("/my.old.json");
        asserts()->equal($ext, ".json");
    });
});

describe("Module::resolve()", function () {

    it("should return a function", function () {
        asserts()->equal(method_exists("Module", "resolve"), true);
    });

    it("should return my/dir/name/file.php", function () {
        $path = Module::resolve("my", "dir", "name", "file.php");
        asserts()->equal($path, "my/dir/name/file.php");
    });

    it("should return /my/dir/file.php", function () {
        $path = Module::resolve("/my", "/dir", "./file.php");
        asserts()->equal($path, "/my/dir/file.php");
    });

    it("should return /my/dir/dir/file.php", function () {
        $path = Module::resolve("/my/dir/", "/dir/", "/file.php");
        asserts()->equal($path, "/my/dir/dir/file.php");
    });

    it("should return /my/file.php", function () {
        $path = Module::resolve("/my/dir/../file.php");
        asserts()->equal($path, "/my/file.php");
    });

    it("should return /my/file.php", function () {
        $path = Module::resolve("/my", "dir", "../file.php");
        asserts()->equal($path, "/my/file.php");
    });

    it("should return /", function () {
        $path = Module::resolve("./");
        asserts()->equal($path, "/");
    });

    it("should return /", function () {
        $path = Module::resolve("../");
        asserts()->equal($path, "/");
    });

    it("should return /", function () {
        $path = Module::resolve("../../");
        asserts()->equal($path, "/");
    });
});

describe("Module::resolveFilename()", function () {

    /*
        This is REALLY weird but handy.
    */

    $method = new ReflectionMethod("Module", "resolveFilename");
    $method->setAccessible(true);

    $parent = new Module(Module::resolve(__DIR__, "../../fixtures/node_modules/math/index.php"), null);

    it("should return a function", function () {
        asserts()->equal(method_exists("Module", "resolveFilename"), true);
    });

    it("should return /path/not/found", function () use ($method, $parent) {
        // $paths = Module::resolveFilename("/path/not/found");
        $path = $method->invoke(new Module(null, null), "/path/not/found", $parent);
        asserts()->equal($path, "/path/not/found");
    });

    it("should return /php-require/test/fixtures/node_modules/math/index.php", function () use ($method, $parent) {
        $request = Module::resolve(__DIR__, "../../fixtures/node_modules/math");
        // $paths = Module::resolveFilename($request);
        $path = $method->invoke(new Module(null, null), $request, $parent);
        asserts()->equal(strrpos($path, "/php-require/test/fixtures/node_modules/math/index.php") !== false, true);
    });

    it("should return /php-require/test/fixtures/node_modules/math/index.php", function () use ($method, $parent) {
        $request = Module::resolve(__DIR__, "../../fixtures/node_modules/math/index");
        // $paths = Module::resolveFilename($request);
        $path = $method->invoke(new Module(null, null), $request, $parent);
        asserts()->equal(strrpos($path, "/php-require/test/fixtures/node_modules/math/index.php") !== false, true);
    });

    it("should return /php-require/test/fixtures/node_modules/math/index.php", function () use ($method, $parent) {
        $request = Module::resolve(__DIR__, "../../fixtures/node_modules/math/index.php");
        // $paths = Module::resolveFilename($request);
        $path = $method->invoke(new Module(null, null), $request, $parent);
        asserts()->equal(strrpos($path, "/php-require/test/fixtures/node_modules/math/index.php") !== false, true);
    });

    it("should return /php-require/test/fixtures/node_modules/math/index.php", function () use ($method, $parent) {
        $request = Module::resolve(__DIR__, "../../fixtures/node_modules/tester/refect");
        // $paths = Module::resolveFilename($request);
        $path = $method->invoke(new Module(null, null), $request, $parent);
        asserts()->equal(strrpos($path, "/php-require/test/fixtures/node_modules/tester/refect.php") !== false, true);
    });

    it("should return /php-require/test/fixtures/config.json", function () use ($method, $parent) {
        $request = Module::resolve(__DIR__, "../../fixtures/config.json");
        // $paths = Module::resolveFilename($request);
        $path = $method->invoke(new Module(null, null), $request, $parent);
        asserts()->equal(strrpos($path, "/php-require/test/fixtures/config.json") !== false, true);
    });

    it("should return /php-require/test/fixtures/node_modules/math/index.php", function () use ($method, $parent) {
        // $paths = Module::resolveFilename("./index");
        $path = $method->invoke(new Module(null, null), "./index", $parent);
        asserts()->equal(strrpos($path, "/php-require/test/fixtures/node_modules/math/index.php") !== false, true);
    });

    it("should return /php-require/test/fixtures/node_modules/math/index.php", function () use ($method, $parent) {
        // $paths = Module::resolveFilename("math");
        $path = $method->invoke(new Module(null, null), "math", $parent);
        asserts()->equal(strrpos($path, "/php-require/test/fixtures/node_modules/math/index.php") !== false, true);
    });
});

describe("Module::nodeModulePaths()", function () {

    /*
        This is REALLY weird but handy.
    */

    $method = new ReflectionMethod("Module", "nodeModulePaths");
    $method->setAccessible(true);

    it("should return a function", function () {
        asserts()->equal(method_exists("Module", "nodeModulePaths"), true);
    });

    it("should return a list of 5 paths", function () use ($method) {
        // $paths = Module::nodeModulePaths("/find/from/this/path");
        $paths = $method->invoke(new Module(null, null), "/find/from/this/path");
        asserts()->equal(count($paths), 5);
    });

    it("should return a /find/from/this/path/node_modules", function () use ($method) {
        // $paths = Module::nodeModulePaths("/find/from/this/path");
        $paths = $method->invoke(new Module(null, null), "/find/from/this/path");
        asserts()->equal($paths[0], "/find/from/this/path/node_modules");
    });

    it("should return a /node_modules", function () use ($method) {
        // $paths = Module::nodeModulePaths("/find/from/this/path");
        $paths = $method->invoke(new Module(null, null), "/find/from/this/path");
        asserts()->equal($paths[4], "/node_modules");
    });
});

describe("Module::loadModule()", function () {

    $parent = new Module(Module::resolve(__DIR__, "../../fixtures/node_modules/math/index.php"), null);

    it("should return a function", function () {
        asserts()->equal(method_exists("Module", "loadModule"), true);
    });

    it("should return a Closure", function () use ($parent) {
        $module = Module::loadModule("math", $parent, false);
        asserts()->equal(get_class($module["sum"]), "Closure");
    });
});

describe("module->load()", function () {

    it("should return a function", function () {
        asserts()->equal(method_exists("Module", "load"), true);
    });
});

describe("module->compile()", function () {

    it("should return a function", function () {
        asserts()->equal(method_exists("Module", "compile"), true);
    });

    it("should return 2", function () {
        $module = new Module(Module::resolve(__DIR__, "../../fixtures/node_modules/tester/refect.php"), null);
        asserts()->equal(count($module->exports), 0);
        $module->compile();
        asserts()->equal(count($module->exports), 2);

        asserts()->equal($module->exports["filename"], $module->filename);
        asserts()->equal($module->exports["dirname"], Module::dirname($module->filename));
    });
});
