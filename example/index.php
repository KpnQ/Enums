<?php

require __DIR__ . '\..\vendor\autoload.php';

use Jac\Enums\EnumJsonFormat;
use Jac\Example\Enums\TicketStatus;

/**
 * @psalm-pure
 *
 * @return TicketStatus
 */
function getEnumFromStatic(): TicketStatus
{
    return TicketStatus::PENDING();
}

// Objects are equals even by type
if (TicketStatus::PENDING() === getEnumFromStatic()) {
    echo "Pending\n\n";
}

$var = TicketStatus::TERMINATED();
// to string display the full details, could be override
echo $var . "\n";
// access the value ...
echo $var->getValue() . "\n";
// ... or the key
echo $var->getKey() . "\n";

// Not the same key, but the same value
echo "\nIs finished same than terminated despite them having the same value? \n";
/** @psalm-suppress ForbiddenCode **/
var_dump(TicketStatus::FINISHED() === TicketStatus::TERMINATED());

$var = TicketStatus::NEW();
echo "\nFormat the json object";
echo '<pre>';
/** @psalm-suppress ForbiddenCode **/
var_dump(json_decode(
    json_encode(
        EnumJsonFormat::keyAndValueAsValues()->format($var)
    )
));
echo '</pre>';