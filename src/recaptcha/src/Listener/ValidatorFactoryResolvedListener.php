<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/recaptcha.
 *
 * @link     https://github.com/friendsofhyperf/recaptcha
 * @document https://github.com/friendsofhyperf/recaptcha/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ReCaptcha\Listener;

use FriendsOfHyperf\ReCaptcha\ReCaptchaManager;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Event\ValidatorFactoryResolved;

class ValidatorFactoryResolvedListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            ValidatorFactoryResolved::class,
        ];
    }

    public function process(object $event)
    {
        /** @var ValidatorFactoryInterface $validatorFactory */
        $validatorFactory = $event->validatorFactory;

        $validatorFactory->extend('recaptcha', function ($attribute, $value, $parameters, $validator) {
            [$action, $score, $hostname, $version] = [$parameters[0] ?? '', (float) $parameters[1] ?? 0.34, $parameters[2] ?? '', $parameters[3] ?? 'v3'];

            $recaptcha = ReCaptchaManager::get($version);

            if ($action) {
                $recaptcha->setExpectedAction($action);
            }

            if ($score) {
                $recaptcha->setScoreThreshold($score);
            }

            if ($hostname) {
                $recaptcha->setExpectedHostname($hostname);
            }

            /** @var RequestInterface $request */
            $request = ApplicationContext::getContainer()->get(RequestInterface::class);

            return $recaptcha->verify($value, $request->server('remote_addr'));
        });
    }
}
