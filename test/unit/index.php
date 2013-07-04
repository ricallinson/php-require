<?php

require("./fake-mocha.php");
require("./fake-asserts.php");
require("../../index.php");

describe("php-require", function () {

    it("should return true", function () {
        asserts()->equal(class_exists("Module"), true);
    });
});

describe("Module::dirname()", function () {

    it("should return a function", function () {
        asserts()->equal(method_exists("Module", "dirname"), "function");
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
        asserts()->equal(method_exists("Module", "extname"), "function");
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
        asserts()->equal(method_exists("Module", "resolve"), "function");
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

    it("should return a function", function () {
        asserts()->equal(method_exists("Module", "resolveFilename"), "function");
    });
});

describe("Module::nodeModulePaths()", function () {

    /*
        This is REALLY weird.
    */

    $method = new ReflectionMethod("Module", "nodeModulePaths");
    $method->setAccessible(true);

    it("should return a function", function () {
        asserts()->equal(method_exists("Module", "nodeModulePaths"), "function");
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

    it("should return a function", function () {
        asserts()->equal(method_exists("Module", "loadModule"), "function");
    });
});

describe("module->load()", function () {

    it("should return a function", function () {
        asserts()->equal(method_exists("Module", "load"), "function");
    });
});

describe("module->compile()", function () {

    it("should return a function", function () {
        asserts()->equal(method_exists("Module", "compile"), "function");
    });
});
