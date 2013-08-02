<?php
namespace php_require\php_path;

class Path {

    public $sep = DIRECTORY_SEPARATOR;

    public $delimiter = PATH_SEPARATOR;

    public function normalize($path) {

        if ($path[0]) {
            $root = $path[0] == $this->sep ? $this->sep : "";
        } else {
            $root = "";
        }
        
        $parts = explode($this->sep, $path);

        foreach($parts as &$part) {
            if ($part == ".") {
                $part = null;
            }
        }

        $parts = array_values(array_filter($parts));
        $build = array();

        while (count($parts) > 0) {
            $arg = array_shift($parts);
            if ($arg == "..") {
                array_pop($build);
            } else if ($arg != ".") {
                array_push($build, $arg);
            }
        }

        $abspath = $root . join($this->sep, $build);

        return $abspath ? $abspath : "/";
    }

    public function join(/* func_get_args */) {
        return $this->normalize(implode($this->sep, func_get_args()));
    }

    public function resolve() {
        throw new \Exception("Not implemented yet.");
    }

    public function isAbsolute() {
        throw new \Exception("Not implemented yet.");
    }

    public function relative() {
        throw new \Exception("Not implemented yet.");
    }

    public function dirname($path) {
        
        $dirname = dirname($path);

        return $dirname ? $dirname : ".";
    }

    public function basename($path, $ext="") {
        return basename($path, $ext);
    }

    public function extname($path) {
        $parts = explode($this->sep, $path);
        $last = array_pop($parts);
        if (strrpos($last, ".") === false) {
            return null;
        }
        $filename = explode(".", $last);
        return "." . $filename[count($filename)-1];
    }
}

$module->exports = new Path();
