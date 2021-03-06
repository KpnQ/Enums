<?php

namespace Jac\Enums;

use JsonSerializable;

class EnumJsonFormat implements JsonSerializable
{
    private const AS_KEY_VALUE_OBJECT = 'keyValuesStrategy';
    private const AS_STRING = 'toStringStrategy';
    private const KEY_AND_VALUE_AS_OBJECT_VALUE = 'keyAndValueAsValueStrategy';

    /**
     * @readonly
     * @var string
     */
    private $function;

    /**
     * @var AbstractEnum|null
     */
    private $dataToFormat = null;

    /**
     * @var array<string, self>
     */
    private static $instances = array();

    private function __construct(string $function)
    {
        $this->function = $function;
    }

    /**
     * Will return the value as a string, it is the default implementation
     * for AbstractEnum
     * 
     * @example "enumValue"
     * @return self
     */
    public static function asString(): self
    {
        return self::getInstance(static::AS_STRING);
    }

    /**
     * Will return a json object where the key is the enum key 
     *      and the value the enum value
     * @example (see the format example)
     * {
     *      "ENUM_KEY": "enumValue"
     * }
     * @return self
     */
    public static function asKeyValue(): self
    {
        return self::getInstance(static::AS_KEY_VALUE_OBJECT);
    }

    /**
     * Will return a json object nested object as follow
     * {
     *      "Enum\Class": {
     *          "key" => "ENUM_KEY",
     *          "value" => "enumValue"
     *      }
     * }
     * 
     */
    public static function keyAndValueAsValues(): self
    {
        return self::getInstance(static::KEY_AND_VALUE_AS_OBJECT_VALUE);
    }

    /**
     * @param string $key
     * 
     * @return self
     */
    protected static function getInstance(string $key): self
    {
        if (isset(self::$instances[$key])) {
            return self::$instances[$key];
        }
        return self::$instances[$key] = new EnumJsonFormat($key);
    }

    /**
     * @example location description
     * 
     * @param AbstractEnum $enum
     * 
     * @return self
     */
    public function format(AbstractEnum $enum): self
    {
        $this->dataToFormat = $enum;
        return $this;
    }

    /**
     * @return string|null|array
     */
    final public function jsonSerialize()
    {
        if (null === $this->dataToFormat) {
            return null;
        }
        $formattedData = self::{$this->function}($this->dataToFormat);
        // Reset the passed data to avoid unexpected result
        // encoding in json without calling format
        $this->dataToFormat = null;
        return $formattedData;
    }
    
    /**
     * @param AbstractEnum $enum
     * @psalm-pure
     * @return mixed
     */
    private static function toStringStrategy(AbstractEnum $enum)
    {
        return $enum->getValue();
    }

    /**
     * @param AbstractEnum $enum
     * @psalm-pure
     * @return array
     */
    private static function keyValuesStrategy(AbstractEnum $enum): array
    {
        return array(
            $enum->getKey() => $enum->getValue()
        );
    }

    /**
     * @param AbstractEnum $enum
     * @psalm-pure
     * @return array
     */
    private static function keyAndValueAsValueStrategy(AbstractEnum $enum): array
    {
        return array(
            get_class($enum) => array(
                'key' => $enum->getKey(),
                'value' => $enum->getValue()
            )
        );
    }
}
