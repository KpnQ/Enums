<?php

namespace Jac\Example\Enums;

use Jac\Enums\AbstractEnum;

/**
 * @psalm-immutable
 * 
 * @method static TicketStatus PENDING()
 * @method static TicketStatus FINISHED()
 * @method static TicketStatus TERMINATED()
 * 
 */
final class TicketStatus extends AbstractEnum
{
    private const NEW_STATUS = 'new';

    private const PENDING = 'pending';

    private const FINISHED = 'finished';

    private const TERMINATED = 'finished';

    /**
     * Override the name of the constructor, but
     * TicketStatus::NEW_STATUS() could be call
     * 
     * @psalm-pure
     * @return self
     */
    public static function NEW(): self
    {
        return self::enum('NEW_STATUS', self::NEW_STATUS);
    }
}
