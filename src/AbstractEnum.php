<?php

namespace Jac\Enum;

use InvalidArgumentException;
use JsonSerializable;
use ReflectionClass;
use Serializable;

abstract class AbstractEnum implements JsonSerializable {
    private $value;

    private $key;

    protected static $keyValueMapCache;

    protected static $instances;

    /**
     * Set as private to avoid construction outside 
     * of the __callStatic or enum method
     * 
     * @param string $key The const to be used
     * @param mixed $value
     */
    private function __construct(string $key, $value)
    {
        $this->value = $value;
        $this->key = $key;
    }

    /**
     * @param string 
     * @param mixed : The true type of $value must be scalar as PHP doesn't allow 
     *                objects in const
     */
    final public static function enum(string $key, $value) 
    {
        if (false === static::inEnum($value)) {
            throw new InvalidArgumentException(
                "The value '$value' doesn't exists in the enum" . static::class
            );
        }    

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
     * @see ...
     * 
     * @uses static::enum(string $key, $value)
     */
    final public static function __callStatic($name, $arguments)
    {
        return static::enum($name, $arguments);
    }

    /**
     * Determine if the given value is valid
     * 
     * @param mixed $data Either the name of the const or its value
     */
    final public static function inEnum($data): bool
    {
        foreach (static::toArray() as $name => $value) {
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
     * @return array<constants, values>
     */
    final public static function toArray(): array
    {
        if (isset(static::$keyValueMapCache[static::class])) {
            return static::$keyValueMapCache[static::class];
        }
        try {
            $enumReflection = new ReflectionClass(static::class);
            return static::$keyValueMapCache[static::class] = $enumReflection->getConstants();
        } catch (ReflectionExceptionn $e) {
            trigger_error("Unable to load enum: {$e->getMessage()}", E_USER_WARNING);
            return array();
        }
    }

    /********************************
     *           GETTERS
     ********************************/

    final public function getKey(): string {
        return $this->key;
    }

    final public function getValue() {
        return $this->value;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return $this->value;
    }

    /**
     * Disable cloning
     */
    final private function __clone()
    {
    }

    /**
     * Disable magic set to avoid mutations
     */
    final public function __set($name, $value)
    {
        return;
    }

    /**
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

    final public function __set_state($properties)
    {
        return;
    }
}