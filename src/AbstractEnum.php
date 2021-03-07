<?php

namespace Jac\Enums;

use JsonSerializable;
use ReflectionClass;

/**
 * Base class for Enum, to create a new enum:
 *  - Implement this class
 *  - Define your constants 
 * Enjoy ! 
 * 
 * @link https://github.com/KpnQ/Enums.git for more details
 * @licence http://www.opensource.org/licenses/mit-license.php 
 *      MIT (see the LICENSE file)
 * 
 * 
 * @example ../example/TicketStatus.php 
 * 
 * @psalm-immutable
 */
abstract class AbstractEnum implements JsonSerializable
{

    /**
     * @readonly
     * @var mixed
     */
    private $value;

    /**
     * @readonly
     * @var string
     */
    private $key;

    /**
     * @var array<string, array<string, mixed>>
     */
    protected static $keyValueMapCache;

    /**
     * @var array<string, array<string, static>>
     */
    protected static $instances;


    /**
     * Set as private to avoid construction outside 
     * of the __callStatic or enum method
     * 
     * @internal Create the singleton instance
     * @param string $key The const to be used
     */
    final private function __construct(string $key)
    {
        $this->value = static::toArray()[$key];
        $this->key = $key;
    }

    /**
     * Constructor 
     * 
     * @param string $key : name of the constants of the enum
     * @param mixed $value : value of the const 
     *      The true type of $value must be scalar 
     *      as PHP doesn't allow objects in const
     * 
     * @throws InvalidEnumException
     * 
     * @psalm-pure
     * @psalm-suppress ImpureStaticProperty
     * @psalm-return static
     */
    final public static function enum(string $key, $value): self
    {
        if (false === static::inEnum($value) ||  false === static::inEnum($key)) {
            throw new InvalidEnumException(
                "The couple '$key' '$value' doesn't exists in the enum " . static::class
            );
        }

        return self::cachedInitialization($key);
    }

    /**
     * @param mixed $value
     * 
     * @return string|false
     */
    final public static function search($value)
    {
        /** @var string|false */
        return array_search($value, static::toArray(), true);
    }

    /**
     * Helper method to retrieve instances in cache
     * 
     * @param string $key
     * 
     * @psalm-pure
     * @psalm-suppress ImpureStaticProperty
     * @psalm-return static
     */
    final private static function cachedInitialization(string $key): self
    {
        if (isset(static::$instances[static::class][$key])) {
            return static::$instances[static::class][$key];
        }

        return static::$instances[static::class][$key] = new static(
            $key
        );
    }

    /**
     * Allow to create an enum by calling the name of a constant
     * as an enum
     * 
     * @param string $name
     * @param mixed $arguments
     * @psalm-pure
     * @psalm-suppress ImpureStaticProperty
     * @psalm-return static
     * 
     * @throws InvalidEnumException when the enum value is not valid
     * 
     * @uses static::enum(string $key, $value)
     */
    final public static function __callStatic($name, $arguments)
    {
        if (self::inEnum($name)) {
            return self::cachedInitialization($name);
        }
        throw new InvalidEnumException(
            "The constant '$name' doesn't exists in the enum " . static::class
        );
    }

    /**
     * Determine if the given value is valid using either key or value
     * The type is used to compare so : 
     *      if the enum value is (int)1 then '1' won't match
     * 
     * @psalm-pure
     * @psalm-suppress ImpureStaticProperty
     * @param mixed $data Either the name of the const or its value
     * 
     * @return bool
     */
    final public static function inEnum($data): bool
    {
        $validData = static::toArray();
        foreach ($validData as $name => $value) {
            if ($name === $data || $data === $value) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array
     */
    final public static function keys(): array
    {
        return array_keys(static::toArray());
    }

    /**
     * @return array
     */
    final public static function values(): array
    {
        return array_values(static::toArray());
    }


    /**
     * Return a Map of available keys => values
     * for the called enum
     * 
     * @psalm-pure
     * @psalm-suppress ImpureStaticProperty
     * @return array<string, mixed>
     */
    final public static function toArray(): array
    {
        if (isset(static::$keyValueMapCache[static::class])) {
            return static::$keyValueMapCache[static::class];
        }
        /** @psalm-suppress ImpureMethodCall no side-effect due to static::class */
        $enumReflection = new ReflectionClass(static::class);
        /** @psalm-suppress ImpureMethodCall no side-effect */
        return static::$keyValueMapCache[static::class] = $enumReflection->getConstants();
    }

    /**
     * @psalm-mutation-free
     * @return string
     */
    public function __toString()
    {
        return static::class
            . '::' . $this->key
            . '::' . $this->value;
    }

    /********************************
     *           GETTERS
     ********************************/

    /**
     * @psalm-mutation-free
     * @return string
     */
    final public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @psalm-mutation-free
     * @return mixed
     */
    final public function getValue()
    {
        return $this->value;
    }

    /**
     * Serialized as string
     * To customize the serialization :
     * @see https://github.com/KpnQ/Enums/blob/main/src/EnumJsonFormat.php
     * 
     * 
     * @psalm-mutation-free
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return $this->value;
    }

    /************************************
     *          MAGIC METHODS
     ************************************/

    /**
     * Disable cloning, actually tested
     * @codeCoverageIgnore 
     */
    final private function __clone()
    {
    }

    /**
     * @psalm-mutation-free
     * @param string $name
     * @param mixed $value
     * Disable magic set to avoid mutations
     */
    final public function __set($name, $value)
    {
        return;
    }

    /**
     * @param string $name
     * 
     * Can be used to access 'value' and 'key'
     * But should prefer the official getters
     */
    final public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        return;
    }

    /**
     * Avoid to create a new instance from a var_export,
     * this will rebuild through the key attribute
     * 
     * @param mixed $properties
     * 
     * @throws InvalidEnumException
     * 
     * @return self
     */
    final public static function __set_state($properties)
    {
        if (static::inEnum($properties['key'])) {
            return static::{$properties['key']}();
        }

        throw new InvalidEnumException("Unable to set state from '" . $properties['key'] . "'");
    }
}
