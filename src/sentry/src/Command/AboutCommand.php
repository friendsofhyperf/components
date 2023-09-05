<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Command;

use FriendsOfHyperf\Sentry\Version;
use Hyperf\Command\Command;
use Sentry\Client;
use Sentry\State\HubInterface;

use function Hyperf\Collection\collect;
use function Hyperf\Support\make;

class AboutCommand extends Command
{
    protected ?string $name = 'sentry:about';

    protected string $description = 'Show what Sentry SDK is used in this project and if it is enabled';

    public function handle()
    {
        $hub = make(HubInterface::class);
        $options = $this->getSentryOptions($hub);
        $this->table(
            ['Option', 'Value'],
            collect($options)->map(fn ($value, $key) => [$key, $value])->toArray()
        );
    }

    protected function getSentryOptions(HubInterface $hub): array
    {
        $client = $hub->getClient();

        if ($client === null) {
            return [
                'Enabled' => '<fg=red;options=bold>NOT CONFIGURED</>',
                'Hyperf SDK Version' => Version::SDK_VERSION,
                'PHP SDK Version' => Client::SDK_VERSION,
            ];
        }

        $options = $client->getOptions();

        // Note: order is not important since Hyperf orders these alphabetically
        return [
            'Enabled' => $options->getDsn() ? '<fg=green;options=bold>YES</>' : '<fg=red;options=bold>MISSING DSN</>',
            'Environment' => $options->getEnvironment() ?: '<fg=yellow;options=bold>NOT SET</>',
            'Hyperf SDK Version' => Version::SDK_VERSION,
            'PHP SDK Version' => Client::SDK_VERSION,
            'Release' => $options->getRelease() ?: '<fg=yellow;options=bold>NOT SET</>',
            'Sample Rate Errors' => $this->formatSampleRate($options->getSampleRate()),
            'Sample Rate Performance Monitoring' => $this->formatSampleRate($options->getTracesSampleRate(), $options->getTracesSampler() !== null),
            'Sample Rate Profiling' => $this->formatSampleRate($options->getProfilesSampleRate()),
            'Send Default PII' => $options->shouldSendDefaultPii() ? '<fg=green;options=bold>ENABLED</>' : '<fg=yellow;options=bold>DISABLED</>',
        ];
    }

    private function formatSampleRate(?float $sampleRate, bool $hasSamplerCallback = false): string
    {
        if ($hasSamplerCallback) {
            return '<fg=green;options=bold>CUSTOM SAMPLER</>';
        }

        if ($sampleRate === null) {
            return '<fg=yellow;options=bold>NOT SET</>';
        }

        return number_format($sampleRate * 100) . '%';
    }
}
