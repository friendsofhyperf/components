<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\AccessLog\Formatter;

use Monolog\Formatter\FormatterInterface;
use Monolog\LogRecord;

class AccessLogFormatter implements FormatterInterface
{
    public const SIMPLE_FORMAT = "%host% %remote_addr% [%time_local%] \"%request%\" %status% %body_bytes_sent% \"%http_referer%\" \"%http_user_agent%\" \"%http_x_forwarded_for%\" %request_time% %upstream_response_time% %upstream_addr%\n";

    public function formatBatch(array $records): string
    {
        $message = '';
        foreach ($records as $record) {
            $message .= $this->format($record);
        }

        return $message;
    }

    public function format(array|LogRecord $record)
    {
        $context = (array) ($record['context'] ?? []);

        return preg_replace_callback('/%(\w+)%/', fn ($matches) => $context[$matches[1]] ?? '-', self::SIMPLE_FORMAT);
    }
}
