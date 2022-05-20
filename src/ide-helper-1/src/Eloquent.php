<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://github.com/friendsofhyperf/ide-helper
 * @document https://github.com/friendsofhyperf/ide-helper/blob/master/README.md
 * @contact  huangdijia@gmail.com
 * @license  https://github.com/friendsofhyperf/ide-helper/blob/master/LICENSE
 */
namespace FriendsOfHyperf\IdeHelper;

class Eloquent
{
    public static function make()
    {
        $alias = new Alias('\\Hyperf\\DbConnection\\Model\\Model', 'Eloquent');
        $alias->addClass('\\Hyperf\\Database\\Model\\Builder');
        $alias->addClass('\\Hyperf\\Database\\Query\\Builder');

        $block = "namespace { \r\n";
        $block .= "  class Eloquent extends \\Hyperf\\DbConnection\\Model\\Model { \r\n";
        foreach ($alias->getMethods() as $method) {
            $return = $method->shouldReturn() ? 'return ' : '';
            $block .= '    ' . trim($method->getDocComment('    ')) . "\r\n";
            $block .= "    public static function {$method->getName()}({$method->getParamsWithDefault()}){ \r\n";
            if ($method->isInstanceCall()) {
                $block .= "        /** @var {$method->getRoot()} \$instance */ \r\n";
            }
            $block .= "        {$return} {$method->getRootMethodCall()}; \r\n";
            $block .= "     } \r\n";
        }
        $block .= "  } \r\n}";
        return $block;
    }
}
