<?php

namespace Jac\Enums;

use JsonSerializable;
use ReflectionClass;
use ReflectionException;

/**
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
     * @var array<string, array<string,AbstractEnum>>
     */
    protected static $instances;


    /**
     * Set as private to avoid construction outside 
     * of the __callStatic or enum method
     * 
     * @param string $key The const to be used
     * @param mixed $value
     */
    final private function __construct(string $key, $value)
    {
        $this->value = $value;
        $this->key = $key;
    }

    /**
     * @param string $key
     * @param mixed $value : The true type of $value must be scalar as PHP doesn't allow 
     *                objects in const
     */
    final public static function enum(string $key, $value): self
    {
        if (false === static::inEnum($value) || false === static::inEnum($key)) {
            throw new InvalidEnumException(
                "The couple '$key' '$value' doesn't exists in the enum " . static::class
            );
        }

        return self::cachedInitialization($key, $value);
    }

    /**
     * @param string $key
     * @param mixed $value
     * 
     * @return self
     */
    protected static function cachedInitialization(string $key, $value): self
    {
        if (isset(static::$instances[static::class][$key])) {
            return static::$instances[static::class][$key];
        }

        return static::$instances[static::class][$key] = new static(
            $key,
            $value
        );
    }

    /**
     * Allow to create an enum by calling the name of a constant
     * as an enum
     * 
     * @param string $name
     * @param mixed $arguments
     * 
     * @see ...
     * 
     * @uses static::enum(string $key, $value)
     */
    final public static function __callStatic($name, $arguments)
    {
        if (self::inEnum($name)) {
            return self::cachedInitialization($name, static::toArray()[$name]);
        }
        throw new InvalidEnumException(
            "The constant '$name' doesn't exists in the enum " . static::class
        );
    }

    /**
     * Determine if the given value is valid using either key or value
     * The type is used to compare
     *
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
     * Return a Map of available keys => values
     * for the called enum
     * 
     * @return array<string, mixed>
     */
    final public static function toArray(): array
    {
        if (isset(static::$keyValueMapCache[static::class])) {
            return static::$keyValueMapCache[static::class];
        }
        $enumReflection = new ReflectionClass(static::class);
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
     * @psalm-mutation-free
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return $this->value;
    }

    /**
     * Disable cloning, actually tested
     * @codeCoverageIgnore 
     */
    final private function __clone()
    {
    }

    /**
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
     * @param mixed $properties
     * 
     * @return self
     */
    final public static function __set_state($properties)
    {
        if (static::inEnum($properties['key'])) {
            return static::{$properties['key']}();
        }

        throw new InvalidEnumException("Unable to set state from " . $properties['key']);
    }
}
