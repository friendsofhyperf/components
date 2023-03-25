<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Http\Logger\Writer;

use Carbon\Carbon;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DefaultLogWriter implements LogWriter
{
    public function __construct(private LoggerFactory $loggerFactory, private ConfigInterface $config)
    {
    }

    public function logRequest(ServerRequestInterface $request, ResponseInterface $response): void
    {
        $group = (string) $this->config->get('http_logger.log_group', 'default');
        $name = (string) $this->config->get('http_logger.log_name', 'http');
        $level = (string) $this->config->get('http_logger.log_level', 'info');
        $message = $this->formatMessage($request, $response);

        $this->loggerFactory->get($name, $group)->log($level, $message);
    }

    public function formatMessage(ServerRequestInterface $request, ResponseInterface $response): string
    {
        $context = $this->getContext($request, $response);

        return preg_replace_callback('/%(\w+)%/', fn ($matches) => $context[$matches[1]] ?? '-', (string) $this->config->get('http_logger.log_format', ''));
    }

    protected function getContext(ServerRequestInterface $request, ResponseInterface $response): array
    {
        $serverParams = $request->getServerParams();
        $requestPathWithQueryString = $request->getUri()->getQuery() ? $request->getUri()->getPath() . '?' . $request->getUri()->getQuery() : $request->getUri()->getPath();

        return [
            'host' => $serverParams['host'] ?? env('APP_NAME', 'hyperf'),
            'remote_addr' => $request->getHeaderLine('x-real-ip') ?: $serverParams['remote_addr'] ?? '',
            'time_local' => Carbon::now()->format($this->config->get('http_logger.log_time_format', 'd/M/Y:H:i:s O')),
            'request' => sprintf(
                '%s %s %s',
                $request->getMethod(),
                $requestPathWithQueryString,
                $serverParams['server_protocol'] ?? ''
            ),
            'status' => $response->getStatusCode(),
            'body_bytes_sent' => $response->getBody()->getSize(),
            'http_referer' => $request->getHeaderLine('referer') ?? '-',
            'http_user_agent' => $request->getHeaderLine('user-agent') ?? '-',
            'http_x_forwarded_for' => $request->getHeaderLine('x-forwarded-for') ?? '-',
            'request_time' => number_format(microtime(true) - ($serverParams['request_time_float'] ?? microtime(true)), 3, '.', ''),
            'upstream_response_time' => '-',
            'upstream_addr' => '-',
        ];
    }
}
