<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope;

class EntryType
{
    public const CACHE = 'cache';

    public const COMMAND = 'command';

    /**
     * @deprecated since v3.1, use `\FriendsOfHyperf\Telescope\EntryType::SCHEDULED_TASK` instead, will be removed in v3.2
     */
    public const SCHEDULE = 'schedule';

    public const DUMP = 'dump';

    public const EVENT = 'event';

    public const EXCEPTION = 'exception';

    public const JOB = 'job';

    public const LOG = 'log';

    public const MAIL = 'mail';

    public const MODEL = 'model';

    public const NOTIFICATION = 'notification';

    public const QUERY = 'query';

    public const REDIS = 'redis';

    public const REQUEST = 'request';

    public const SCHEDULED_TASK = 'schedule';

    public const GATE = 'gate';

    public const VIEW = 'view';

    public const SERVICE = 'service';

    public const CLIENT_REQUEST = 'client_request';
}
