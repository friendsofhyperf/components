<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Encryption\Listener;

use FriendsOfHyperf\Encryption\Contract\Encrypter as EncrypterInterface;
use FriendsOfHyperf\Encryption\Contract\StringEncrypter;
use FriendsOfHyperf\Encryption\Encrypter;
use FriendsOfHyperf\Encryption\KeyParser;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Container;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Opis\Closure\SerializableClosure;
use Psr\Container\ContainerInterface;

/**
 * @property Container $container
 */
class BootEncryptionListener implements ListenerInterface
{
    public function __construct(private ContainerInterface $container, private ConfigInterface $config, private KeyParser $parser)
    {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        $this->registerOpisSecurityKey();
        $this->registerAlias();
    }

    protected function registerAlias()
    {
        if (! $this->config->get('encryption.key')) {
            return;
        }

        $this->container->set(EncrypterInterface::class, $this->container->get(Encrypter::class));
        $this->container->set(StringEncrypter::class, $this->container->get(Encrypter::class));
    }

    /**
     * Configure Opis Closure signing for security.
     */
    protected function registerOpisSecurityKey(): void
    {
        $config = $this->config->get('encryption', []);

        if (! class_exists(SerializableClosure::class) || empty($config['key'])) {
            return;
        }

        SerializableClosure::setSecretKey($this->parser->parseKey($config));
    }
}
