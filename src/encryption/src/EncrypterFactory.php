<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/encryption.
 *
 * @link     https://github.com/friendsofhyperf/encryption
 * @document https://github.com/friendsofhyperf/encryption/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace Friendsofhyperf\Encryption;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class EncrypterFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var array $config */
        $config = $container->get(ConfigInterface::class)->get('encryption', []);
        /** @var KeyParser $parser */
        $parser = $container->get(KeyParser::class);

        return new Encrypter($parser->parseKey($config), $config['cipher']);
    }
}
