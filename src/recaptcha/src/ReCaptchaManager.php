<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ReCaptcha;

use Hyperf\Contract\ConfigInterface;
use ReCaptcha\ReCaptcha;
use RuntimeException;
use TypeError;

use function Hyperf\Support\make;

class ReCaptchaManager
{
    /**
     * @var array<string, ReCaptcha>
     */
    protected array $instances = [];

    public function __construct(protected ConfigInterface $config)
    {
    }

    /**
     * @throws TypeError
     * @throws RuntimeException
     */
    public function get(?string $version = null): ReCaptcha
    {
        if (isset($this->instances[$version])) {
            return $this->instances[$version];
        }

        if (! $this->config->has('recaptcha')) {
            throw new RuntimeException('Not publish yet, please run \'php bin/hyperf.php vendor:publish friendsofhyperf/recaptcha\'');
        }

        $version ??= (string) $this->config->get('recaptcha.default', 'v3');
        $secret = $this->config->get(sprintf('recaptcha.%s.secret_key', $version), '');

        return $this->instances[$version] = make(ReCaptcha::class, [
            'secret' => $secret,
        ]);
    }
}
