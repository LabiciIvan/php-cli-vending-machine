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
			'price' => '20',
		],
		[
			'name' => 'Coca Zero',
			'price' => '10.99',
		],
		[
			'name' => 'Sprite',
			'price' => '10.80',
		],
		[
			'name' => 'Fanta',
			'price' => '11.90',
		],
		[
			'name' => 'Pepsi',
			'price' => '22.05',
		],
	],
	'B' => [
		[
			'name' => 'Snickers',
			'price' => '21.20',
		],
		[
			'name' => 'Chocolate Bar',
			'price' => '15.99',
		],
		[
			'name' => 'Mars',
			'price' => '17.88',
		],
		[
			'name' => 'Cotton Candy',
			'price' => '16.50',
		],
		[
			'name' => 'Lion bar',
			'price' => '20.01',
		],
	],
	'C' => [
		[
			'name' => 'Lays Chips',
			'price' => '20',
		],
		[
			'name' => 'Chop Chips',
			'price' => '10.99',
		],
		[
			'name' => 'Pop Corn',
			'price' => '10.66',
		],
		[
			'name' => 'Salted Chips',
			'price' => '22.20',
		],
		[
			'name' => 'Salted Rice Cakes',
			'price' => '10.99',
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
			'price' => '11.99',
		],
		[
			'name' => 'Ice Tea Lemon',
			'price' => '11.59',
		],
		[
			'name' => 'Lemon Tea',
			'price' => '11.80',
		],
		[
			'name' => 'Black Tea',
			'price' => '12.99',
		],
		[
			'name' => 'Black Ice Tea',
			'price' => '12.50',
		],
	],
];

function askFor(string $name): string
{
    echo $name;

    $received = trim(fgets(STDIN));

    return $received;
}

function notify(string $message = null, bool $extra = false): void
{
    if (isset($message)) {
        echo $message;
    }

    if ($extra) {
        echo PHP_EOL;
    }
}

function checkArgument(string $argument): bool
{
    return (file_exists($argument) ? true : false);
}

function printReceipt(): void
{
    $arg = func_get_args();

    $fields = [
        '1x',
        'cost',
        'payed',
        'change',
    ];

    for ($i = 0; $i < count($arg); ++$i) {
        $print_width = 50 - (strlen($fields[$i]) + strlen($arg[$i]));

        echo $fields[$i];

        for ($j = 0; $j < $print_width; ++$j) {
            echo ".";
        }

        echo $arg[$i] . PHP_EOL;
    }
}

function processApplication(array $arguments, array $items): void
{
    notify('Vending machine started.' . PHP_EOL, true);

    if (isset($arguments[1]) && checkArgument($arguments[1])) {
        $items = json_decode(file_get_contents($arguments[1]), true);

        notify('Items list updated !' . PHP_EOL, true);
    } else {
        notify('No new items provided, continue with defaults.' . PHP_EOL, true);
    }

    do {
        $row = askFor('Pick a row : ');

        $column = askFor('Pick a column : ');

        notify(null, true);

        if (isset($items[$row][$column])) {
            notify("Picked items is : {$items[$row][$column]['name']}" . PHP_EOL);

            $confirmation = askFor('Choose another ? (y/n) ');

            notify(null, true);

            if ($confirmation === 'y') {
                $user_not_decided = true;
            } elseif ($confirmation === 'n') {
                $user_not_decided = false;
            } else {
                notify('Unknown command, please pick again.' . PHP_EOL, true);
            }
        } else {
            notify('Item not available, choose another one.' . PHP_EOL, true);

            $user_not_decided = true;
        }

    } while ($user_not_decided);

    $confirmation = askFor("Pay {$items[$row][$column]['price']} ? (y/n) ");

    notify(null, true);

    if ($confirmation === 'n') {
        notify('Hope to see you next time.');
        exit;
    }

    $payed = 0;

    do {
        notify("Amount inserted : {$payed}", true);

        $inserted = askFor("Insert money : ");

        $payed += (int)$inserted;
    } while ($payed <= $items[$row][$column]['price']);

    notify('Thank you for your purchase.', true);
    notify('Please wait for your selection...' . PHP_EOL, true);

    sleep(2);

    notify("Take your receipt." . PHP_EOL, true);

    printReceipt(
        $items[$row][$column]['name'],
        $items[$row][$column]['price'],
        (string)$payed,
        (string)($payed - $items[$row][$column]['price']));
}

processApplication($argv, $items);