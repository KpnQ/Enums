<?php

namespace Jac\Tests\Enums;

use Jac\Enums\AbstractEnum;

class EnumFixtureDiff extends AbstractEnum
{
    private const ENUM_1 = 'first';

    private const ENUM_INT = 10;

    private const ENUM_3 = 'three';

    private const ENUM_THREE = 'three';

    public static function enum1(): self 
    {
        return self::enum('ENUM_1', self::ENUM_1);
    }
}