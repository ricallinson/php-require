<?php

class FakeAsserts { 

    public function equal($got, $should) {
        if ($got != $should) {
            throw new Exception("Got [" . $got . "] should have been [" . $should . "]");
        }
    }
}

function asserts() {
    return new FakeAsserts();
}
