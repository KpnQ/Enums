<?php

namespace Jac\Enums;

use Exception;

/**
 * Throw for the Jac\Enums\AbstractEnum
 * wen the given enum constructor parameters
 * do not exist.
 */
class InvalidEnumException extends Exception
{
}