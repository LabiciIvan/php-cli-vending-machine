<?php

/**
 * This is a simple implementation of a CLI vending machine.
 * 
 * Allows to pick an items from the list or update the list with a json object
 * which might contain new items.
 */

declare(strict_types=1);

namespace Machine\Vending;

function askFor(string $name): string
{
    echo $name;

    $received = trim(fgets(STDIN));

    return $received;
}

askFor('Insert a column : ');