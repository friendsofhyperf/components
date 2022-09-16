<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Http\Client;

use Hyperf\Utils\Traits\Macroable;
use OutOfBoundsException;

class ResponseSequence
{
    use Macroable;

    /**
     * The responses in the sequence.
     *
     * @var array
     */
    protected $responses;

    /**
     * Indicates that invoking this sequence when it is empty should throw an exception.
     *
     * @var bool
     */
    protected $failWhenEmpty = true;

    /**
     * The response that should be returned when the sequence is empty.
     *
     * @var \GuzzleHttp\Promise\PromiseInterface
     */
    protected $emptyResponse;

    /**
     * Create a new response sequence.
     */
    public function __construct(array $responses)
    {
        $this->responses = $responses;
    }

    /**
     * Get the next response in the sequence.
     *
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function __invoke()
    {
        if ($this->failWhenEmpty && count($this->responses) === 0) {
            throw new OutOfBoundsException('A request was made, but the response sequence is empty.');
        }

        if (! $this->failWhenEmpty && count($this->responses) === 0) {
            return value($this->emptyResponse ?? Factory::response());
        }

        return array_shift($this->responses);
    }

    /**
     * Push a response to the sequence.
     *
     * @param array|string $body
     * @return $this
     */
    public function push($body = '', int $status = 200, array $headers = [])
    {
        $body = is_array($body) ? json_encode($body) : $body;

        return $this->pushResponse(
            Factory::response($body, $status, $headers)
        );
    }

    /**
     * Push a response with the given status code to the sequence.
     *
     * @return $this
     */
    public function pushStatus(int $status, array $headers = [])
    {
        return $this->pushResponse(
            Factory::response('', $status, $headers)
        );
    }

    /**
     * Push response with the contents of a file as the body to the sequence.
     *
     * @return $this
     */
    public function pushFile(string $filePath, int $status = 200, array $headers = [])
    {
        $string = file_get_contents($filePath);

        return $this->pushResponse(
            Factory::response($string, $status, $headers)
        );
    }

    /**
     * Push a response to the sequence.
     *
     * @param mixed $response
     * @return $this
     */
    public function pushResponse($response)
    {
        $this->responses[] = $response;

        return $this;
    }

    /**
     * Make the sequence return a default response when it is empty.
     *
     * @param \Closure|\GuzzleHttp\Promise\PromiseInterface $response
     * @return $this
     */
    public function whenEmpty($response)
    {
        $this->failWhenEmpty = false;
        $this->emptyResponse = $response;

        return $this;
    }

    /**
     * Make the sequence return a default response when it is empty.
     *
     * @return $this
     */
    public function dontFailWhenEmpty()
    {
        return $this->whenEmpty(Factory::response());
    }

    /**
     * Indicate that this sequence has depleted all of its responses.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return count($this->responses) === 0;
    }
}
