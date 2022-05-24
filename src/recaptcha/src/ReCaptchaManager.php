<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/recaptcha.
 *
 * @link     https://github.com/friendsofhyperf/recaptcha
 * @document https://github.com/friendsofhyperf/recaptcha/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ReCaptcha;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\ApplicationContext;
use ReCaptcha\ReCaptcha;
use RuntimeException;
use TypeError;

class ReCaptchaManager
{
    /**
     * @var array
     */
    protected static $recaptchas = [];

    /**
     * @param string $version
     * @throws TypeError
     * @throws RuntimeException
     * @return ReCaptcha
     */
    public static function get(string $version = null)
    {
        if (! isset(self::$recaptchas[$version])) {
            /** @var ConfigInterface $config */
            $config = ApplicationContext::getContainer()->get(ConfigInterface::class);

            if (! $config->has('recaptcha')) {
                throw new RuntimeException('Not publish yet, please run \'php bin/hyperf.php vendor:publish friendsofhyperf/recaptcha\'');
            }

            $version = $version ?? $config->get('recaptcha.default', 'v3');

            self::$recaptchas[$version] = make(ReCaptcha::class, ['secret' => $config->get('recaptcha.' . $version . '.secret_key', '')]);
        }

        return self::$recaptchas[$version];
    }
}
