<?php

function loadFixture($file)
{
    $expected = file_get_contents(__DIR__ . '/fixtures/replaced/' . $file . '.php');
    $origin = file_get_contents(__DIR__ . '/fixtures/origin/' . $file . '.php');

    return [$expected, $origin];
}
