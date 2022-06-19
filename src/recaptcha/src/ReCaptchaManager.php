<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ReCaptcha;

use Hyperf\Contract\ConfigInterface;
use ReCaptcha\ReCaptcha;
use RuntimeException;
use TypeError;

class ReCaptchaManager
{
    /**
     * @var ReCaptcha[]
     */
    protected $container = [];

    /**
     * @var ConfigInterface
     */
    protected $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $version
     * @throws TypeError
     * @throws RuntimeException
     * @return ReCaptcha
     */
    public function get(?string $version = null)
    {
        if (isset($this->container[$version])) {
            return $this->container[$version];
        }

        if (! $this->config->has('recaptcha')) {
            throw new RuntimeException('Not publish yet, please run \'php bin/hyperf.php vendor:publish friendsofhyperf/recaptcha\'');
        }

        $version = $version ?? $this->config->get('recaptcha.default', 'v3');

        return $this->container[$version] = make(ReCaptcha::class, [
            'secret' => $this->config->get(sprintf('recaptcha.%s.secret_key', $version), ''),
        ]);
    }
}
