<?php

declare(strict_types=1);
/**
 * This file is part of http-client.
 *
 * @link     https://github.com/friendsofhyperf/http-client
 * @document https://github.com/friendsofhyperf/http-client/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Http\Client;

class RequestException extends HttpClientException
{
    /**
     * The response instance.
     *
     * @var Response
     */
    public $response;

    /**
     * Create a new exception instance.
     */
    public function __construct(Response $response)
    {
        parent::__construct($this->prepareMessage($response), $response->status());

        $this->response = $response;
    }

    /**
     * Prepare the exception message.
     *
     * @return string
     */
    protected function prepareMessage(Response $response)
    {
        $message = "HTTP request returned status code {$response->status()}";

        $summary = class_exists(\GuzzleHttp\Psr7\Message::class)
            ? \GuzzleHttp\Psr7\Message::bodySummary($response->toPsrResponse())
            : \GuzzleHttp\Psr7\get_message_body_summary($response->toPsrResponse());

        return is_null($summary) ? $message : $message .= ":\n{$summary}\n";
    }
}
