<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Util;

use PhpMyAdmin\SqlParser\Components\JoinKeyword;
use PhpMyAdmin\SqlParser\Statements\SelectStatement;

class SqlParser
{
    public function parse(string $sql): array
    {
        $parser = new \PhpMyAdmin\SqlParser\Parser($sql);

        $operate = $parser->list[0]->keyword;
        $table = [];

        $statement = $parser->statements[0];
        if ($statement instanceof SelectStatement) {
            $table[] = $statement->from[0]->table;
            // join
            if (isset($statement->join)) {
                foreach ($statement->join as $join) {
                    if (! $join instanceof JoinKeyword) {
                        continue;
                    }
                    $table[] = $join->expr->table;
                }
            }
        }

        return [
            'operate' => $operate,
            'table' => $table,
        ];
    }
}
