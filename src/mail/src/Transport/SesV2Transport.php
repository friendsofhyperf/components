<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Mail\Transport;

use Aws\Exception\AwsException;
use Aws\SesV2\SesV2Client;
use Stringable;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Message;

use function Hyperf\Collection\collect;

class SesV2Transport extends AbstractTransport implements Stringable
{
    public function __construct(
        protected SesV2Client $ses,
        protected array $options = [],
    ) {
        parent::__construct();
    }

    /**
     * Get the string representation of the transport.
     */
    public function __toString(): string
    {
        return 'ses-v2';
    }

    /**
     * Get the Amazon SES V2 client for the SesV2Transport instance.
     */
    public function ses(): SesV2Client
    {
        return $this->ses;
    }

    /**
     * Get the transmission options being used by the transport.
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Set the transmission options being used by the transport.
     */
    public function setOptions(array $options): array
    {
        return $this->options = $options;
    }

    protected function doSend(SentMessage $message): void
    {
        $options = $this->options;

        if ($message->getOriginalMessage() instanceof Message) {
            foreach ($message->getOriginalMessage()->getHeaders()->all() as $header) {
                if ($header instanceof MetadataHeader) {
                    $options['EmailTags'][] = ['Name' => $header->getKey(), 'Value' => $header->getValue()];
                }
            }
        }

        try {
            $result = $this->ses->sendEmail(
                array_merge(
                    $options,
                    [
                        'Source' => $message->getEnvelope()->getSender()->toString(),
                        'Destination' => [
                            'ToAddresses' => collect($message->getEnvelope()->getRecipients()) // @phpstan-ignore-line
                                ->map
                                ->toString()
                                ->values()
                                ->all(),
                        ],
                        'Content' => [
                            'Raw' => [
                                'Data' => $message->toString(),
                            ],
                        ],
                    ]
                )
            );
        } catch (AwsException $e) {
            $reason = $e->getAwsErrorMessage() ?? $e->getMessage();

            throw new TransportException(
                sprintf('Request to AWS SES V2 API failed. Reason: %s.', $reason),
                (int) $e->getCode(),
                $e
            );
        }

        $messageId = $result->get('MessageId');

        $message->getOriginalMessage()->getHeaders()->addHeader('X-Message-ID', $messageId); // @phpstan-ignore-line
        $message->getOriginalMessage()->getHeaders()->addHeader('X-SES-Message-ID', $messageId); // @phpstan-ignore-line
    }
}
