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
use PhpMyAdmin\SqlParser\Statements\InsertStatement;
use PhpMyAdmin\SqlParser\Statements\SelectStatement;
use PhpMyAdmin\SqlParser\Statements\UpdateStatement;

class SqlParser
{
    /**
     * @return array{operation:string,tables:string}
     */
    public static function parse(string $sql): array
    {
        if (empty($sql) || ! class_exists('PhpMyAdmin\SqlParser\Parser')) {
            return [
                'operation' => '',
                'tables' => '',
            ];
        }

        $parser = new \PhpMyAdmin\SqlParser\Parser($sql);
        $operation = $parser->list[0]->keyword;
        $table = [];

        $statement = $parser->statements[0];
        if ($statement instanceof SelectStatement) {
            foreach ($statement->from as $from) {
                $table[] = $from->table;
            }
            // join
            if (isset($statement->join)) {
                foreach ($statement->join as $join) {
                    if (! $join instanceof JoinKeyword) {
                        continue;
                    }
                    $table[] = $join->expr->table;
                }
            }
        } elseif ($statement instanceof UpdateStatement) {
            $table = array_column($statement->tables, 'table');
        } elseif ($statement instanceof InsertStatement) {
            $table[] = $statement->into->dest->table;
        }

        return [
            'operation' => $operation,
            'tables' => implode(',', $table),
        ];
    }
}
