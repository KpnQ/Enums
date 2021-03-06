# Enum implementation for PHP

## Why

To avoid to have to install the `SplEnum`, yet another extension...

Enums have a lot of benefits (in my own opinion):

- Allow type hinting
- Don't need to search in endless array definitions 
- Avoid misspelling of the value
- Can be enrich with methods
- ...

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

## API

Comparison of enums works with == and ===

- `__toString()` To display the current enum : EnumName::EnumKey::EnumValue (can be override)
- `getValue()` Return the value of the Enum (the value of the constant associated with the current key)
- `getKey()` Return the key of the the current Enum

Static method

- `enum(string, mixed)` Create an instance of the enum giving its key and its value, key are mandatory due to the possibility to have multiple value for the same key
- `toArray()` return the list of key => value  of the enum
- `inEnum()` check if the parameter is either a key or a value

## TODO

- [ ] Create a method search
- [ ] Split split validation for key and value
- [ ] Allow serialization/deserialization
- [ ] Allow to define default key when multiple key share the same value