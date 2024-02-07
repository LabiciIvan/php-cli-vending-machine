<?php

/**
 * This is a simple implementation of a CLI vending machine.
 * 
 * Allows to pick an items from the list or update the list with a json object
 * which might contain new items.
 */

declare(strict_types=1);

const ANSWERS = [
    'y' => 1,
    'n' => 0,
];

const ITEMS = [
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
    ]
];

processApplication(ITEMS);

function processApplication(array $items): void
{
    printLine('Vending machine started.');

    $itemsUpdated = updateItems();

    $items = $itemsUpdated ?? $items;

    while (true) {
        printLine('Welcome!');

        $item = selectItem($items);

        if (!keepItemOrChange($item['name'])) {
            continue;
        }

        $receipt = payment($item['name'], (int)$item['price']);

        try {
            printReceipt(
                [
                    'name' => $item['name'],
                    'cost' => $item['price'],
                    'paid' => $receipt['paid'],
                    'change' => $receipt['change'],
                ],
                20
            );
        } catch (Exception $e) {
            printLine($e->getMessage());
            exit;
        }
    }
}

function printLine(string $msg): void
{
    echo $msg . PHP_EOL;
}

function updateItems(): ?array
{
    $answering = true;

    while ($answering) {
        printLine('Update items list?');

        printLine('[y = yes | n = no]:');

        $decision = getUserInput();

        if (!isset(ANSWERS[strtolower($decision)])) {
            printLine('Unknown command!');
            continue;
        }

        break;
    }

    if (strtolower($decision) === 'n') {
        printLine('Items not updated.');
        return null;
    }

    while ($answering) {
        printLine('Insert path to items:');

        $path = getUserInput();

        if ($path && file_exists($path)) {
            break;
        }

        printLine('Items not found!');

        while ($answering) {
            printLine('Try again?');

            printLine('[y = yes | n = no]:');

            $decision = getUserInput();

            if (!isset(ANSWERS[strtolower($decision)])) {
                printLine('Unknown command!');
                continue;
            }

            if (strtolower($decision) === 'y') {
                break;
            }

            printLine('Items not updated.');

            return null;
        }
    }

    $items = file_get_contents($path, true);

    if ($items === false) {
        printLine('Can\'t read items.');
        return null;
    };

    try {
        $items = json_decode($items, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        printLine('An error occurred:');

        printLine($e->getMessage());

        return null;
    }

    printLine('Items updated.');

    return $items;
}

function selectItem(array $items): array
{
    $keyPosition = 0;
    $keys = array_keys($items);

    $controls = [
        '>' => 'NEXT',
        '<' => 'BACK',
    ];

    while (true) {
        $row = $keys[$keyPosition];

        printLine('Pick an item.');

        displayItems($items[$row], $row, 20);

        printLine(sprintf('| > = NEXT | < = BACK | %s0, %s1 = ITEM |', $row, $row));

        $userAction = getUserInput();

        $isControl = (strlen($userAction) === 1 ? true : false);

        $isItem = !$isControl;

        if ($isControl && !isset($controls[$userAction])) {
            printLine('Unknown command!');
            continue;
        }

        if ($isControl) {
            $lastKeyPosition = count($keys) - 1;
            switch ($controls[$userAction]) {
                case 'NEXT':
                    $keyPosition = ($keyPosition === $lastKeyPosition ? 0 : ++$keyPosition);
                    break;

                case 'BACK':
                    $keyPosition = ($keyPosition === 0 ? $lastKeyPosition : --$keyPosition);
                    break;
            }

            continue;
        }

        if ($isItem) {
            list($itemRow, $itemColumn) = str_split($userAction, 1);

            if (!isset($items[$itemRow][$itemColumn]) || (strlen($userAction) > 2)) {
                printLine('Item not found!');
                continue;
            }

            return $items[$itemRow][$itemColumn];
        }
    }
}

function displayItems(array $items, string $row, int $width): void
{
    $square = $lineTopBottom = sprintf('+%s+%s', str_repeat('-', $width), PHP_EOL);

    $lengthItems = count($items);
    $lengthRowColumnSpace = 5;

    for ($i = 0; $i < $lengthItems; ++$i) {
        $itemName = $items[$i]['name'];
        $lengthSpacing = strlen($itemName) + $lengthRowColumnSpace;
        $filler = ($width > $lengthSpacing ? $width - $lengthSpacing : 0);

        if ($lengthSpacing > $width) {
            $nameWidth = $width - $lengthRowColumnSpace;
            $itemName = substr($itemName, 0, $nameWidth);
        }

        $square .= sprintf('| %s.%s %s%s|%s', $row, $i, $itemName, str_repeat(' ', $filler), PHP_EOL);
    }

    $square .= $lineTopBottom . PHP_EOL;

    printLine($square);
}

function keepItemOrChange(string $itemName): bool
{
    while (true) {
        printLine(sprintf('You picked: %s.', $itemName));
        printLine('Keep it?');
        printLine('| y = yes | n = no |');

        $willKeep = getUserInput();

        if (!isset(ANSWERS[$willKeep])) {
            printLine('Unknown command!');
            continue;
        }

        if ($willKeep === 'y') {
            return true;
        }

        return false;
    }
}

function getUserInput(): ?string
{
    $input = fgets(STDIN);

    return $input === false ? null : trim($input);
}

/**
 * @return array{paid:string,change:string}
 */
function payment(string $itemName, int $itemPrice): array
{
    $paid = 0;
    $acceptedAmount = array_flip(['10', '20', '50']);

    while ($itemPrice > $paid) {
        printLine(sprintf('%s is $%s.', $itemName, $itemPrice));
        printLine(sprintf('Credit: %s.', $paid));
        printLine('Insert amount:');

        $amount = getUserInput();

        if (!isset($acceptedAmount[$amount])) {
            printLine(sprintf('Accepted amounts: %s', implode(', ', array_keys($acceptedAmount))));
            continue;
        }

        $paid += $amount;
    }

    $change = $paid - $itemPrice;

    return [
        'paid' => number_format($paid, 2, '.'),
        'change' => number_format($change, 2, '.'),
    ];
}

/**
 * @param array{name:string,cost:string,paid:string,change:string} $receipt
 * @throws RuntimeException If a field is missing or too short space for field value
 */
function printReceipt(array $receipt, int $width = 30, int $minDistance = 3): void
{
    $error = [];
    $receiptFields = array_flip(['name', 'cost', 'paid', 'change']);

    foreach ($receiptFields as $field => $value) {
        if (!isset($receipt[$field])) {
            $error[] = $field;
        }
    }

    if ($error) {
        $fields = implode(', ', array_keys(array_flip($error)));
        throw new RuntimeException(sprintf('Missing receipt fields: %s.', $fields));
    }

    $receipt = array_intersect_key($receipt, $receiptFields);

    $receiptOutput = '';

    foreach ($receipt as $field => $value) {
        $repeat = $minDistance;
        $lengthDistanceField = strlen($field) + $minDistance;
        $lengtFieldDistanceValue = strlen($field) + $minDistance + strlen($value);

        if ($lengthDistanceField >= $width) {
            throw new RuntimeException(sprintf('Not enought room for field\'s `%s` value.', $field));
        }

        if ($lengtFieldDistanceValue < $width) {
            $repeat = $minDistance + ($width - $lengtFieldDistanceValue);
        }

        if ($lengtFieldDistanceValue > $width) {
            $shortenValue = $width - $lengthDistanceField;
            $value = substr($value, 0, $shortenValue);
        }

        $receiptOutput .= sprintf('%s%s%s%s', $field, str_repeat('.', $repeat), $value, PHP_EOL);
    }

    printLine($receiptOutput);
}