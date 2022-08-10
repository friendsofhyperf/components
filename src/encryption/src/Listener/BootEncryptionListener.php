<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace Friendsofhyperf\Encryption\Listener;

use Friendsofhyperf\Encryption\Contract\Encrypter as EncrypterInterface;
use Friendsofhyperf\Encryption\Contract\StringEncrypter;
use Friendsofhyperf\Encryption\Encrypter;
use Friendsofhyperf\Encryption\KeyParser;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Opis\Closure\SerializableClosure;
use Psr\Container\ContainerInterface;

#[Listener]
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
