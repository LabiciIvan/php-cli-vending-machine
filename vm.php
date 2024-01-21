<?php

/**
 * This is a simple implementation of a CLI vending machine.
 * 
 * Allows to pick an items from the list or update the list with a json object
 * which might contain new items.
 */

declare(strict_types=1);

namespace Machine\Vending;

$items = [
	"A" => [
		[
			'name' => 'Coca Cola',
			'price' => '2',
		],
		[
			'name' => 'Coca Zero',
			'price' => '1.99',
		],
		[
			'name' => 'Sprite',
			'price' => '1.80',
		],
		[
			'name' => 'Fanta',
			'price' => '1.90',
		],
		[
			'name' => 'Pepsi',
			'price' => '2.05',
		],
	],
	'B' => [
		[
			'name' => 'Snickers',
			'price' => '2.20',
		],
		[
			'name' => 'Chocolate Bar',
			'price' => '1.99',
		],
		[
			'name' => 'Mars',
			'price' => '1.88',
		],
		[
			'name' => 'Cotton Candy',
			'price' => '1.50',
		],
		[
			'name' => 'Lion bar',
			'price' => '2.01',
		],
	],
	'C' => [
		[
			'name' => 'Lays Chips',
			'price' => '2',
		],
		[
			'name' => 'Chop Chips',
			'price' => '1.99',
		],
		[
			'name' => 'Pop Corn',
			'price' => '1.66',
		],
		[
			'name' => 'Salted Chips',
			'price' => '2.20',
		],
		[
			'name' => 'Salted Rice Cakes',
			'price' => '0.99',
		],
	],
	'D' => [
		[
			'name' => 'Expresso',
			'price' => '5',
		],
		[
			'name' => 'Machiato',
			'price' => '3.99',
		],
		[
			'name' => 'Latte Coffe',
			'price' => '4.99',
		],
		[
			'name' => 'Americano',
			'price' => '2.89',
		],
		[
			'name' => 'Double Expresso',
			'price' => '5.99',
		],
	],
	'E' => [
		[
			'name' => 'Fruits Tea',
			'price' => '1.99',
		],
		[
			'name' => 'Ice Tea Lemon',
			'price' => '1.59',
		],
		[
			'name' => 'Lemon Tea',
			'price' => '1.80',
		],
		[
			'name' => 'Black Tea',
			'price' => '2.99',
		],
		[
			'name' => 'Black Ice Tea',
			'price' => '2.50',
		],
	],
];

function askFor(string $name): string
{
    echo $name;

    $received = trim(fgets(STDIN));

    return $received;
}

function notify(string $message, bool $extra = false): void
{
    echo $message;

    if ($extra) {
        echo PHP_EOL;
    }
}

function checkArgument(string $argument, bool $exists = false): bool
{
    if ($exists) {
        return (file_exists($argument) ? true : false);
    }

    return false;
}

function processApplication(array $arguments, array $items): void
{
    notify('Vending machine started' . PHP_EOL, true);

    if (isset($arguments[1]) && checkArgument($arguments[1], true)) {
        $items = json_decode(file_get_contents($arguments[1]), true);

        notify('Items list updated !' . PHP_EOL, true);
    } else {
        notify('No items provided, continue with defaults' . PHP_EOL, true);
    }

    $user_not_decided = true;

    do {
        $row = askFor('Pick a row : ');

        $column = askFor('Pick a column : ');

        if (isset($items[$row][$column])) {
            notify("Picked items is : {$items[$row][$column]['name']}" . PHP_EOL);

            $confirmation = askFor('continue ? (y/n) ');

            if ($confirmation === 'y' || $confirmation === 'n') {
                $user_not_decided = ($confirmation === 'n' ? true : false);
            } else {
                notify('Unknown command, please pick again.' . PHP_EOL, true);
            }
        } else {
            notify('Item not available, choose another one .' . PHP_EOL, true);
        }

    } while ($user_not_decided);
}

processApplication($argv, $items);