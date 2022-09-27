<?php

declare(strict_types=1);
/**
 * This file is part of model-uid-addon.
 *
 * @link     https://github.com/friendsofhyperf/model-uid-addon
 * @document https://github.com/friendsofhyperf/model-uid-addon/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use Hyperf\Utils\Str;
use Ramsey\Uuid\Codec\TimestampFirstCombCodec;
use Ramsey\Uuid\Generator\CombGenerator;
use Ramsey\Uuid\UuidFactory;
use Symfony\Component\Uid\Ulid;

if (! Str::hasMacro('ulid')) {
    Str::macro('ulid', function () {
        return new Ulid();
    });
}

if (! Str::hasMacro('orderedUuid')) {
    Str::macro('orderedUuid', function () {
        $factory = new UuidFactory();

        $factory->setRandomGenerator(new CombGenerator(
            $factory->getRandomGenerator(),
            $factory->getNumberConverter()
        ));

        $factory->setCodec(new TimestampFirstCombCodec(
            $factory->getUuidBuilder()
        ));

        return $factory->uuid4();
    });
}
