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

function selectItem(array $items): array
{
    $user_not_decided = true;

    do {
        printLine('Pick a row : ');

        $row = getUserInput();

        printLine('Pick a column : ');

        $column = getUserInput();

        $item = $items[$row][$column] ?? null;

        if (!isset($item)) {
            printLine(sprintf("Item not available, pick another! %s",PHP_EOL));
            continue;
        }

        printLine(sprintf("You've picked %s, do you want to change it ?", $item['name']));

        $to_change = getUserInput();

        $user_not_decided = ($to_change === 'y' ? true : false);

    } while ($user_not_decided);

    return $item;
}

function payItem(array $item): array
{
    $receipt = [
        'price'     => $item['price'],
        'name'      => $item['name'],
        'change'    => null,
        'inserted'  => 0,
    ];

    printLine(sprintf("You must pay : %d %s", $receipt['price'], PHP_EOL));

    $not_payed = true;

    do {
        printLine(sprintf("Inserted amount : %d %s", $receipt['inserted'], PHP_EOL));

        printLine(sprintf("Insert money : "));

        $amount = (int)getUserInput();

        if (($amount !== 5) && ($amount !== 10) && ($amount !== 50)) {
            printLine(sprintf("You can insert only %d, %d, %d values. %s", 5, 10, 50, PHP_EOL));
            continue;
        }

        $receipt['inserted'] += $amount;

        if ($receipt['inserted'] > $receipt['price']) {
            $not_payed = false;

            $receipt['change'] = $receipt['inserted'] - $receipt['price'];
        }

    } while ($not_payed);

    return $receipt;
}

function printReceipt(array $receipt, $width_included = null): void
{
    $width = (!$width_included ? strlen($receipt['name']) * 5 : $width_included);

    $receipt_message = "";

    foreach ($receipt as $key => $value) {

        $limit = $width - (strlen((string)$value) + strlen((string)$key));

        $receipt_symbol = str_repeat(".", $limit);

        $receipt_message .= sprintf("%s%s%s%s", $key, $receipt_symbol, $value, PHP_EOL);
    }

    echo $receipt_message;
}

function processApplication(array $items): void
{
    printLine(sprintf("Vending machine started.%s", PHP_EOL));

    if (($returned_items = updateItems()) === false) {
        printLine(sprintf("Will continue with default items. %s", PHP_EOL));
    }

    $items = ($returned_items !== false ? $returned_items : $items);

    $item = selectItem($items);

    $receipt = payItem($item);

    printReceipt($receipt);
}
