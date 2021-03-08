<?php

namespace Jac\Tests\Enums;

use Jac\Enums\AbstractEnum;

final class EnumFixture extends AbstractEnum
{
    private const ENUM_1 = 'first';

    private const ENUM_2 = 'second';

    private const ENUM_SECOND = 'second';

    private const ENUM_INT = 10;

    public static function enum1(): self 
    {
        return self::enum('ENUM_1', self::ENUM_1);
    }

    public static function enum2(): self 
    {
        return self::enum('ENUM_2', self::ENUM_2);
    }

    public static function enumSecond(): self 
    {
        return self::enum('ENUM_SECOND', self::ENUM_SECOND);
    }

    private const __DEFAULT__ = array('empty' => '');
}