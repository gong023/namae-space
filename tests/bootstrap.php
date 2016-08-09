<?php

function loadFixture($file)
{
    $expected = file_get_contents(__DIR__ . '/fixtures/replaced/' . $file . '.php.fixture');
    $origin = file_get_contents(__DIR__ . '/fixtures/origin/' . $file . '.php.fixture');

    return [$expected, $origin];
}
