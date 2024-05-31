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
use PhpMyAdmin\SqlParser\Parser as BaseSqlParser;
use PhpMyAdmin\SqlParser\Statements\InsertStatement;
use PhpMyAdmin\SqlParser\Statements\SelectStatement;
use PhpMyAdmin\SqlParser\Statements\UpdateStatement;

class SqlParser
{
    /**
     * @return array{operation:string,table:string,tables:string[]}
     */
    public static function parse(string $sql): array
    {
        if (! class_exists(BaseSqlParser::class) || empty($sql)) {
            return [
                'operation' => '',
                'table' => '',
                'tables' => [],
            ];
        }

        $parser = new BaseSqlParser($sql);
        $operation = $parser->list[0]->keyword;
        $tables = [];

        $statement = $parser->statements[0];
        if ($statement instanceof SelectStatement) {
            foreach ($statement->from as $from) {
                $tables[] = $from->table;
            }
            // join
            if (isset($statement->join)) {
                foreach ($statement->join as $join) {
                    if (! $join instanceof JoinKeyword) {
                        continue;
                    }
                    $tables[] = $join->expr->table;
                }
            }
        } elseif ($statement instanceof UpdateStatement) {
            $tables = array_column($statement->tables, 'table');
        } elseif ($statement instanceof InsertStatement) {
            $tables[] = $statement->into->dest->table;
        }

        return [
            'operation' => $operation,
            'table' => $tables[0] ?? '',
            'tables' => $tables,
        ];
    }
}
