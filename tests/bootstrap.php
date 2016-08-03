<?php

function loadFixture($file)
{
    $expected = file_get_contents(__DIR__ . '/fixtures/expected/' . $file . '.php.fixture');
    $target = file_get_contents(__DIR__ . '/fixtures/target/' . $file . '.php.fixture');

    return [$expected, $target];
}
