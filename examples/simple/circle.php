<?php
$PI = pi();

$exports["area"] = function ($r) use ($PI) {
    return $PI * $r * $r;
};

$exports["circumference"] = function ($r) use ($PI) {
    return 2 * $PI * $r;
};