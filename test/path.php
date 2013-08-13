<?php
use php_require\php_path\Path;

/*
    Now we test it.
*/

describe("php-path", function () {

    describe("path->normalize()", function () {

        it("should return my/dir/name/file.php", function () {
            $path = new Path();
            $str = $path->normalize("my/dir/name/file.php");
            assert($str === "my/dir/name/file.php");
        });

        it("should return /my/dir/file.php", function () {
            $path = new Path();
            $str = $path->normalize("/my//dir/./file.php");
            assert($str === "/my/dir/file.php");
        });

        it("should return /my/dir/dir/file.php", function () {
            $path = new Path();
            $str = $path->normalize("/my/dir///dir///file.php");
            assert($str === "/my/dir/dir/file.php");
        });

        it("should return /my/file.php", function () {
            $path = new Path();
            $str = $path->normalize("/my/dir/../file.php");
            assert($str === "/my/file.php");
        });

        it("should return ./my/file.php", function () {
            $path = new Path();
            $str = $path->normalize("./my/dir/../file.php");
            assert($str === "./my/file.php");
        });

        it("should return ./", function () {
            $path = new Path();
            $str = $path->normalize("./");
            assert($str === "./");
        });

        it("should return /", function () {
            $path = new Path();
            $str = $path->normalize("../");
            assert($str === "/");
        });

        it("should return /", function () {
            $path = new Path();
            $str = $path->normalize("../../");
            assert($str === "/");
        });

        it("should return /foo/bar/baz/asdf", function () {
            $path = new Path();
            $str = $path->normalize("/foo/bar//baz/asdf/quux/..");
            assert($str === "/foo/bar/baz/asdf");
        });

        it("should return /User/name/php", function () {
            $path = new Path();
            $str = $path->normalize("/User/name/php/module/sub/../..");
            assert($str === "/User/name/php");
        });
    });

    describe("path->dirname()", function () {

        it("should return /my/dir", function () {
            $path = new Path();
            $name = $path->dirname("/my/dir/name");
            assert($name === "/my/dir");
        });

        it("should return /my/dir/name", function () {
            $path = new Path();
            $name = $path->dirname("/my/dir/name/file.php");
            assert($name === "/my/dir/name");
        });

        it("should return /my", function () {
            $path = new Path();
            $name = $path->dirname("/my/dir");
            assert($name === "/my");
        });

        it("should return .", function () {
            $path = new Path();
            $name = $path->dirname("/my");
            assert($name === "/");
        });

        it("should return .", function () {
            $path = new Path();
            $name = $path->dirname("");
            assert($name === ".");
        });

        it("should return .", function () {
            $path = new Path();
            $name = $path->dirname("./");
            assert($name === ".");
        });

        it("should return .", function () {
            $path = new Path();
            $name = $path->dirname("/");
            assert($name === "/");
        });
    });
    
    describe("path->join()", function () {

        it("should return /foo/bar/baz/asdf", function () {
            $path = new Path();
            $ext = $path->join("/foo", "bar", "baz/asdf", "quux", "..");
            assert($ext === "/foo/bar/baz/asdf");
        });
    });

    describe("path->resolve()", function () {

        it("should return [true]", function () {
            $path = new Path();
            $error = false;
            try {
                $path->resolve();
            } catch (Exception $e) {
                $error = true;
            }
            assert($error);
        });
    });

    describe("path->isAbsolute()", function () {

        it("should return [true]", function () {
            $path = new Path();
            $error = false;
            try {
                $path->isAbsolute();
            } catch (Exception $e) {
                $error = true;
            }
            assert($error);
        });
    });

    describe("path->relative()", function () {

        it("should return [true]", function () {
            $path = new Path();
            $error = false;
            try {
                $path->relative();
            } catch (Exception $e) {
                $error = true;
            }
            assert($error);
        });
    });

    describe("path->extname()", function () {

        it("should return .php", function () {
            $path = new Path();
            $ext = $path->extname("/my/dir/name/file.php");
            assert($ext === ".php");
        });

        it("should return null", function () {
            $path = new Path();
            $ext = $path->extname("/my/dir");
            assert($ext === null);
        });

        it("should return .json", function () {
            $path = new Path();
            $ext = $path->extname("/my.old.json");
            assert($ext === ".json");
        });
    });

    describe("path->basename()", function () {

        it("should return file.php", function () {
            $path = new Path();
            $ext = $path->basename("/my/dir/name/file.php");
            assert($ext === "file.php");
        });

        it("should return dir", function () {
            $path = new Path();
            $ext = $path->basename("/my/dir");
            assert($ext === "dir");
        });

        it("should return my.old.json", function () {
            $path = new Path();
            $ext = $path->basename("/my.old.json");
            assert($ext === "my.old.json");
        });

        it("should return my.old", function () {
            $path = new Path();
            $ext = $path->basename("/my.old.json", ".json");
            assert($ext === "my.old");
        });
    });
});
