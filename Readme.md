[![Build Status](https://travis-ci.com/KpnQ/Enums.svg?branch=main)](https://travis-ci.com/KpnQ/Enums)
[![psalm](https://shepherd.dev/github/KpnQ/Enums/coverage.svg)](https://shepherd.dev/githubKpnQ/Enums)
[![Coverage Status](https://coveralls.io/repos/github/KpnQ/Enums/badge.svg?branch=main)](https://coveralls.io/github/KpnQ/Enums?branch=main)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FKpnQ%2FEnums%2Fmain)](https://dashboard.stryker-mutator.io/reports/github.com/KpnQ/Enums/main)

# Enum implementation for PHP

## Why

To avoid to have to install the `SplEnum`, yet another extension...

Enums have a lot of benefits (in my own opinion):

- Allow type hinting
- Don't need to search in endless array definitions 
- Avoid misspelling of values
- Can be enrich with methods

In this implementation the constructor is made 
private to avoid duplicating instance of the enum

## Installation

```
composer require jac/php-enum
```

## How to use

For more complex example look at the __example__ directory

### Create an Enum

```php
use Jac\Enum\AbstractEnum;

/**
 * SendingStatus Enum
 * @method SendingStatus PROCESSING()
 * @method SendingStatus SENT()
 */
final class SendingStatus extends AbstractEnum
{
    private const PROCESSING = '1 - Processing';
    private const SENT = '2 - Sent';
}
```

### Usage

```php
function notifyUser(SendingStatus $status) {
    if ($status == SendingStatus::SENT()) {
        // your package have been sent
    }
}

$key = 'PROCESSING';
// Validate first the enum by inEnum
if (SendingStatus::inEnum($key)) {
    notifyUser(SendingStatus::$key());
}
```

### Enum having multiple keys with the same value[Multiple]

Instantiate an Enum from its value is possible calling the `Devise::EUR()` for example, 
and having multiple keys with the same value won't be harmful in that case. However, it
is different when we try to instantiate an Enum from its value. Let's take the following 
example:

```php
namespace App\Enums;

use Jac\Enum\AbstractEnum;

final class Devise extends AbstractEnum
{
    /**
     * @default
     */
    private const RMB = 'RMB'; // will be used due to the default tag
    private const CNY = 'RMB'; 

    private const EUR = 'EUR'; // will be used as the other key is deprecated
    /**
     * @deprecated
     */
    private const FRA = 'EUR';

    private const US_DOLLAR = 'USD'; // will be used because of the __DEFAULT__ configuration
    private const USD = 'USD';

    private const __DEFAULT__ = array(
        'USD' => 'US_DOLLAR'
    );
}
```

There is multiple ways a developer could use to help the builder decide which key should be 
preferred.
1. Use the `__DEFAULT__` constant which is a reverted key => value array. This method will first be checked before the following two
2. Use the `@default` tag in the php doc associated to the constant, in the above example, for the chinese devise (RMB, CNY),
in case we use `Devise::from('RMB')`, the key RMB is going to be choose.
3. Use the `@deprecated` to exclude values. When a constant is set as deprecated, then its priority is lowered and it will be returned if and only if no other options is found.

In case there is no configuration set for the default value to choose or if there is still multiple value available, a warning is triggered and one of the
found options is returned.

## API

Comparison of enums works with == and ===

- `__toString()` To display the current enum : EnumName::EnumKey::EnumValue (can be override)
- `getValue()` Return the value of the Enum (the value of the constant associated with the current key)
- `getKey()` Return the key of the the current Enum
- `equals(AbstractEnum|null):bool` Compare both enums using their values without taking the key into account.

Static method

- `enum(string, mixed)` Create an instance of the enum giving its key and its value, key are mandatory due to the possibility to have multiple value for the same key
- `from(mixed)` Create an enum from its value, see [Multiple] to understand more about its behavior
- `toArray()` return the list of key => value  of the enum
- `inEnum()` check if the parameter is either a key or a value
- `search(mixed): array` Search for all the keys having the given value
- `keyExists(string): bool` Check if the key exists in the enum
- `valueExists(mixed): bool` Check if at leas one of the keys have the given value
- `keys(): array`: The list of available keys
- `values(): array`: The list of available none unique values

## Serialization

The enum implements the `JsonSerializable` interface. By default it will return the value as a string

A formatter is available: `Jac\Enum\EnumJsonFormat` with several option implemented.

```php
use Jac\Enum\EnumJsonFormat;

echo json_decode(EnumJsonFormat::asKeyValue()->format(Devise::USD_DOLLAR()));
```

Will output
```json
{
    "USD_DOLLAR":"USD"
}
```

```php
use Jac\Enum\EnumJsonFormat;

echo json_decode(EnumJsonFormat::keyAndValueAsValues()->format(Devise::USD_DOLLAR()));
```
Will output
```json
{
    "App\\Enums\\Devise": {
        "key":"USD_DOLLAR",
        "value":"USD"
    }
}
```

## TODO

- [ ] Create a method search
- [ ] Split split validation for key and value
- [ ] Allow serialization/deserialization
- [ ] Allow to define default key when multiple key share the same value