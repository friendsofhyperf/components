<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\AccessLog;

use Carbon\Carbon;
use FriendsOfHyperf\AccessLog\Formatter\AccessLogFormatter;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Stringable\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

use function Hyperf\Support\env;

class Handler
{
    public function __construct(protected ConfigInterface $config, protected LoggerInterface $logger, protected AccessLogFormatter $formatter)
    {
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function process($request, $response)
    {
        if (! $this->isEnable($request)) {
            return;
        }

        [$message, $context] = $this->getMessageAndContext($request, $response);

        $this->logger->info($message, $context);
    }

    /**
     * @param mixed $request
     */
    protected function isEnable($request): bool
    {
        if (! $this->config->get('access_log.enable', true)) {
            return false;
        }

        if (! $request || ! $request instanceof ServerRequestInterface) {
            return false;
        }

        if (Str::contains($request->getHeaderLine('user-agent'), (array) $this->config->get('access_log.ignore_user_agents', []))) {
            return false;
        }

        if (Str::is((array) $this->config->get('access_log.ignore_paths', []), $request->getUri()->getPath())) {
            return false;
        }

        return true;
    }

    protected function getMessageAndContext(ServerRequestInterface $request, ResponseInterface $response): array
    {
        $context = $this->buildContext($request, $response);
        $group = $this->config->get('access_log.logger.group', 'default');
        $formatterClass = $this->config->get(sprintf('logger.%s.formatter.class', $group));
        $message = '';

        if ($formatterClass != AccessLogFormatter::class) {
            $message = $this->formatter->format(compact('context'));
            $context = [];
        }

        return [$message, $context];
    }

    protected function buildContext(ServerRequestInterface $request, ResponseInterface $response): array
    {
        $serverParams = $request->getServerParams();
        $requestPathWithQueryString = $request->getUri()->getQuery() ? $request->getUri()->getPath() . '?' . $request->getUri()->getQuery() : $request->getUri()->getPath();

        return [
            'host' => $serverParams['host'] ?? env('APP_NAME', 'hyperf'),
            'remote_addr' => $request->getHeaderLine('x-real-ip') ?: $serverParams['remote_addr'],
            'time_local' => Carbon::now()->format($this->config->get('access_log.logger.time_format', 'd/M/Y:H:i:s O')),
            'request' => sprintf(
                '%s %s %s',
                $request->getMethod(),
                $requestPathWithQueryString,
                $serverParams['server_protocol']
            ),
            'status' => $response->getStatusCode(),
            'body_bytes_sent' => $response->getBody()->getSize(),
            'http_referer' => $request->getHeaderLine('referer') ?? '-',
            'http_user_agent' => $request->getHeaderLine('user-agent') ?? '-',
            'http_x_forwarded_for' => $request->getHeaderLine('x-forwarded-for') ?? '-',
            'request_time' => number_format(microtime(true) - $serverParams['request_time_float'], 3, '.', ''),
            'upstream_response_time' => '-',
            'upstream_addr' => '-',
        ];
    }
}
