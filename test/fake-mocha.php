<?php

class FakeMocha {

    static private $group = array();

    static private $current = "";

    static private $errors = array();

    static public function renderStart() {
        print("\nRunning Tests\n\n");
    }

    static public function renderSuccess() {
        print(".");
    }

    static public function renderError() {
        print("|");
    }

    static public function renderEnd($errors) {

        foreach ($errors as $error) {
            print("\n\n");
            print($error[0] . ": " . $error[1] . "\n\n");
            print("\t" . $error[2]->getMessage() . "\n");
            print("\n");
            print($error[2]->getTraceAsString() . "\n");
            print("\n");
        }

        print("\n\n");
    }

    static public function describe($text, $fn) {
        self::$group[$text] = $fn;
    }

    static public function it($text, $fn) {
        try {
            $fn();
            self::renderSuccess();
        } catch (Exception $err) {
            array_push(self::$errors, array(self::$current, $text, $err));
            self::renderError();
        }
    }

    static public function run() {

        self::$errors = array();

        self::renderStart();

        foreach (self::$group as $text => $fn) {

            self::$current = $text;
            try {
                $fn();
            } catch (Exception $err) {
                array_push(self::$errors, $err);
                self::renderError();
            }
            self::$current = "";
        }

        self::renderEnd(self::$errors);

        exit(count(self::$errors));
    }
}

function describe($text, $fn) {
    FakeMocha::describe($text, $fn);
}

function it($text, $fn) {
    FakeMocha::it($text, $fn);
}

require("./unit/index.php");

FakeMocha::run();
