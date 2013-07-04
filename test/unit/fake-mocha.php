<?php

class FakeMocha {

    static private $stack = array();

    static private $error = false;

    static public function renderSuccess() {
        print(".");
    }

    static public function renderError($group, $test, $err) {
        print("\n");
        print($group . ": " . $test . "\n\n");
        print("\t" . $err->getMessage() . "\n");
        print("\n");
        print($err->getTraceAsString() . "\n");
        print("\n");
    }

    static public function describe($text, $fn) {
        array_push(self::$stack, $text);
        try {
            $fn();
        } catch (Exception $err) {
            self::renderError($text, null, $err);
        }
        array_pop(self::$stack);
    }

    static public function it($text, $fn) {
        try {
            $fn();
            self::renderSuccess();
        } catch (Exception $err) {
            self::$error = true;
            self::renderError(self::$stack[count(self::$stack)-1], $text, $err);
        }
    }
}

function describe($text, $fn) {
    FakeMocha::describe($text, $fn);
}

function it($text, $fn) {
    FakeMocha::it($text, $fn);
}
