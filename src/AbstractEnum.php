<?php

namespace Jac\Enums;

use JsonSerializable;
use ReflectionClass;
use ReflectionClassConstant;

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
     * @var int|string
     */
    private $value;

    /**
     * @readonly
     * @var string
     */
    private $key;

    /**
     * @var array<string,array<string,int|string>>
     */
    protected static $keyValueMapCache;

    /**
     * @var array<string,array<string,static>>
     */
    protected static $instances;

    /**
     * @var array<string,array<int|string,string>>
     */
    protected static $multiValueDefault;


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
     * @param int|string $value : value of the const 
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
     * Lookup for the right enum name from the value
     * In case multiple keys have the same value, it will try
     * to find the best option 
     *      1. Look for the value in the __DEFAULT__ Enum constant to find the default key to use
     *      2. Use the phpDoc of each constant to find either a @\default or a at least one which is not @\deprecated
     *      3. Use one the keys
     * @param int|string $value
     * 
     * @return self
     */
    final public static function from($value): self
    {
        $keys = static::search($value);
        if (empty($keys)) {
            throw new InvalidEnumException("Unable to find a valid key from $value");
        }

        if (sizeof($keys) === 1) {
            return self::cachedInitialization($keys[0]);
        }

        $defaultKey = self::searchDefaultKey($keys, $value);
        return self::cachedInitialization($defaultKey);
    }


    /**
     * @param array $keys
     * @param int|string $value
     * 
     * @return string
     */
    final private static function searchDefaultKey(array $keys, $value): string
    {
        if (isset(static::$multiValueDefault[static::class][$value])) {
            return static::$multiValueDefault[static::class][$value];
        }

        /** 
         * Lookup in the __DEFAULT__ constant array if it is set 
         */
        $classRef = new ReflectionClass(static::class);
        $defaultsConfig = $classRef->getConstant('__DEFAULT__');
        if (isset($defaultsConfig[$value])) {
            return static::$multiValueDefault[static::class][$value] = $defaultsConfig[$value];
        }

        /**
         * Parse the php doc to look up for the best scenario
         * First try to find a constant with the @default doc
         * Second exclude from best scenario the @\deprecated ones
         * Third count all non excluded keys
         */
        $defaultKey = '';
        $deprecatedKey = '';
        $eligibleKeyCount = 0;
        foreach ($keys as $key) {
            $constant = new ReflectionClassConstant(static::class, $key);
            $phpDoc = $constant->getDocComment() ?: '';
            if (preg_match('/@default(\s|\n)/', $phpDoc)) {
                return static::$multiValueDefault[static::class][$value] = $key;
            }

            if (preg_match('/@deprecated(\s|\n)/', $phpDoc)) {
                $deprecatedKey = $key;
                continue;
            }
            $eligibleKeyCount++;
            $defaultKey = $key;
        }

        /**
         * Have to use a deprecated key: trigger a compile warning
         */
        if (empty($defaultKey)) {
            user_error("An enum set as deprecated has been used for '$deprecatedKey' => '$value'", E_USER_WARNING);
            return $deprecatedKey;
        }

        /**
         * When not all but one is deprecated, we trigger the warning
         */
        if ($eligibleKeyCount > 1) {
            user_error("More than one key where found despite analysis", E_USER_WARNING);
        }
        static::$multiValueDefault[static::class][$value] = $defaultKey;
        return $defaultKey;
    }

    /**
     * @psalm-pure
     * @param int|string $value
     * 
     * @return array
     */
    final public static function search($value): array
    {
        return array_keys(static::toArray(), $value, true);
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
     * @param int|string $data Either the name of the const or its value
     * 
     * @return bool
     */
    final public static function inEnum($data): bool
    {
        if ($data === '__DEFAULT__') {
            return false;
        }

        if (is_string($data) && static::keyExists($data)) {
            return true;
        }
        return static::valueExists($data);
    }

    /**
     * Look into constants' name if it exists
     * @psalm-pure
     * @param string $key
     * @return bool
     */
    final public static function keyExists(string $key): bool
    {
        return array_key_exists($key, static::toArray());
    }

    /**
     * Search if a key exists for the given value
     * @psalm-pure
     * @param int|string $value the value to search
     * @return bool
     */
    final public static function valueExists($value): bool
    {
        return false === empty(static::search($value));
    }

    /**
     * List of available keys
     * 
     * @return array
     */
    final public static function keys(): array
    {
        return array_keys(static::toArray());
    }

    /**
     * List of available values, might not be unique
     * 
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
     * @return array<string,int|string>
     */
    final public static function toArray(): array
    {
        if (isset(static::$keyValueMapCache[static::class])) {
            return static::$keyValueMapCache[static::class];
        }
        /** @psalm-suppress ImpureMethodCall no side-effect due to static::class */
        $enumReflection = new ReflectionClass(static::class);
        /** @psalm-suppress ImpureMethodCall no side-effect */
        $keyValue = $enumReflection->getConstants();
        if (isset($keyValue['__DEFAULT__'])) {
            unset($keyValue['__DEFAULT__']);
        }
        return static::$keyValueMapCache[static::class] = $keyValue;
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

    /**
     * Compare both enum by value whereas == and === 
     * will also use the key to check for equality
     * 
     * @param AbstractEnum|null $enum
     * 
     * @return bool
     */
    public function equals(?AbstractEnum $enum = null): bool
    {
        return $enum !== null
            && $enum->getValue() === $this->getValue()
            && static::class === get_class($enum);
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
     * @return int|string
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
     * 
     * @return int|string
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
     * @param int|string $value
     * Disable magic set to avoid mutations
     */
    final public function __set($name, $value)
    {
        return;
    }

    /**
     * @param string $name
     * 
     * @return int|string|null
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
     * @param array $properties
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
