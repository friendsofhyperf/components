<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\MySqlGrammarAddon\Aspect;

use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

class MySqlGrammarAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\Database\Schema\Grammars\MySqlGrammar::compileColumnListing',
        'Hyperf\Database\Schema\Grammars\MySqlGrammar::compileColumns',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return match ($proceedingJoinPoint->getReflectMethod()->getName()) {
            'compileColumnListing' => str_replace(
                ', `column_comment` as `column_comment`,',
                ', binary `column_comment` as `column_comment`,',
                $proceedingJoinPoint->process()
            ),
            'compileColumns' => str_replace(
                ', `column_comment`',
                ', binary `column_comment`',
                $proceedingJoinPoint->process()
            ),
            default => $proceedingJoinPoint->process()
        };
    }
}
