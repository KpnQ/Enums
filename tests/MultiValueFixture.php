<?php

namespace Jac\Tests\Enums;

use Jac\Enums\AbstractEnum;

final class MultiValueFixture extends AbstractEnum
{
    /**
     * test default
     * @default
     */
    private const DEFAULT = '1';
    private const NO_DEFAULT = '1';

    /**
     * @deprecated
     */
    private const DEPRECATED = '2';
    private const NO_DEPRECATED = '2';

    /** @deprecated */
    private const ONLY_DEPRECATED = '3';
    /** @deprecated */
    private const ONLY_DEPRECATED_ = '3';

    private const NO_CONFIG = '4';
    private const NO_CONFIG_2 = '4';

    private const CONFIG_DEFAULT = '5';
    private const CONFIG_DEFAULT_2 = '5';

    private const __DEFAULT__ = array (
        '5' => 'CONFIG_DEFAULT'
    );
}