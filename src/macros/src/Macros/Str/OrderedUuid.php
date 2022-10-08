<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Macros\Macros\Str;

use FriendsOfHyperf\Macros\Foundation\UuidContainer;
use Ramsey\Uuid\Codec\TimestampFirstCombCodec;
use Ramsey\Uuid\Generator\CombGenerator;
use Ramsey\Uuid\UuidFactory;

class OrderedUuid
{
    public function __invoke()
    {
        return static function () {
            if (UuidContainer::$uuidFactory) {
                return call_user_func(UuidContainer::$uuidFactory);
            }

            $factory = new UuidFactory();

            $factory->setRandomGenerator(new CombGenerator(
                $factory->getRandomGenerator(),
                $factory->getNumberConverter()
            ));

            $factory->setCodec(new TimestampFirstCombCodec(
                $factory->getUuidBuilder()
            ));

            return $factory->uuid4();
        };
    }
}
