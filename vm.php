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

processApplication($items);


function printLine(string $msg): void
{
    echo $msg;
}

function getUserInput(): mixed
{
    return trim(fgets(STDIN));
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

function selectItem($items): array
{
    $user_not_decided = true;

    do {
        printLine('Pick a row : ');

        $row = getUserInput();

        printLine('Pick a column : ');

        $column = getUserInput();

        $item = $items[$row][$column] ?? null;

        if (isset($item)) {
            printLine(
                sprintf(
                    "You've picked %s, do you want to change it ?",
                    $item['name']
                    )
            );

            $confirmation = getUserInput();

            if ($confirmation === 'y') {
                $user_not_decided = true;
            } elseif ($confirmation === 'n') {
                $user_not_decided = false;
            } else {
                printLine('Unknown command, please pick again.' . PHP_EOL, true);
            }
        } else {
            printLine('Item not available, choose another one.' . PHP_EOL, true);

            $user_not_decided = true;
        }

    } while ($user_not_decided);
    return [];
}

function payItem(): void
{
    $payed = 0;

    do {
        printLine("Amount inserted : {$payed}", true);

        $inserted = getUserInput("Insert money : ");

        $payed += (int)$inserted;
    } while ($payed <= $items[$row][$column]['price']);
}

function updateItems(): mixed
{
    printLine(sprintf("Do you want to update items ? %s", PHP_EOL));

    $response = getUserInput();

    if (strtolower($response) !== 'y') {
        return false;
    }

    printLine(sprintf("Enter path to the items : %s", PHP_EOL));

    $items_path = getUserInput();

    if (!file_exists($items_path)) {
        printLine(
            sprintf("Items not found under path: %s %s", $items_path, PHP_EOL)
        );

        return false;
    }

    if (!$items = file_get_contents($items_path)) {
        printLine(sprintf("Could not read items content. %s", PHP_EOL));

        return false;
    };

    $items = json_decode($items, true, JSON_ERROR_SYNTAX);
    
    if (json_last_error() === JSON_ERROR_SYNTAX) {
        printLine(sprintf("Error occured while decoding the JSON. %s", PHP_EOL));

        return false;
    }

    return $items;
}

function processApplication(array $items): void
{
    printLine(sprintf("Vending machine started.%s", PHP_EOL));

    if (($returned_items = updateItems()) === false) {
        printLine(sprintf("Will continue with default items. %s", PHP_EOL));
    }

    $items = ($returned_items !== false ? $returned_items : $items);

    selectItem($items);

    payItem();

    printLine('Thank you for your purchase.', true);
    printLine('Please wait for your selection...' . PHP_EOL, true);

    sleep(2);

    printLine("Take your receipt." . PHP_EOL, true);

    printReceipt(
        $items[$row][$column]['name'],
        $items[$row][$column]['price'],
        (string)$payed,
        (string)($payed - $items[$row][$column]['price']));
}
