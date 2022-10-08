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

use Hyperf\Utils\Str;

class Excerpt
{
    public function __invoke()
    {
        return static function ($text, $phrase = '', $options = []) {
            $radius = $options['radius'] ?? 100;
            $omission = $options['omission'] ?? '...';

            preg_match('/^(.*?)(' . preg_quote((string) $phrase) . ')(.*)$/iu', (string) $text, $matches);

            if (empty($matches)) {
                return null;
            }

            $startStr = ltrim($matches[1]);
            $start = Str::of(mb_substr($matches[1], max(mb_strlen($startStr, 'UTF-8') - $radius, 0), $radius, 'UTF-8'))->ltrim();
            $start = $start->unless(
                (function ($startWithRadius) use ($startStr) { return $startWithRadius->exactly($startStr); })($start),
                function ($startWithRadius) use ($omission) { return $startWithRadius->prepend($omission); },
            );

            $endStr = rtrim($matches[3]);
            $end = Str::of(mb_substr($endStr, 0, $radius, 'UTF-8'))->rtrim();
            $end = $end->unless(
                (function ($endWithRadius) use ($endStr) { return $endWithRadius->exactly($endStr); })($end),
                function ($endWithRadius) use ($omission) { return $endWithRadius->append($omission); },
            );

            return $start->append($matches[2], $end)->__toString();
        };
    }
}
