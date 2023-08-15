<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\FastPaginate;

use Closure;
use Hyperf\Database\Query\Expression;
use Hyperf\Paginator\Paginator;

use function FriendsOfHyperf\Helpers\filled;
use function Hyperf\Collection\collect;

/**
 * @mixin \Hyperf\Database\Model\Builder
 */
class FastPaginate
{
    public function fastPaginate()
    {
        /* @phpstan-ignore-next-line */
        return $this->paginate('paginate', fn ($items, $paginator) => $this->paginator(
            $this->getModel()->newCollection($items),
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            $paginator->getOptions()
        ));
    }

    public function simpleFastPaginate()
    {
        /* @phpstan-ignore-next-line */
        return $this->paginate('simplePaginate', fn ($items, $paginator) => $this->simplePaginator(
            $this->getModel()->newCollection($items),
            $paginator->perPage(),
            $paginator->currentPage(),
            $paginator->getOptions()
        )->hasMorePagesWhen($paginator->hasMorePages()));
    }

    /**
     * @param mixed $builder
     * @return array
     * @throws QueryIncompatibleWithFastPagination
     */
    public static function getInnerSelectColumns($builder)
    {
        $base = $builder->getQuery();
        $model = $builder->newModelInstance();
        $key = $model->getKeyName();
        $table = $model->getTable();

        // Collect all of the `orders` off of the base query and pluck
        // the column out. Based on what orders are present, we may
        // have to include certain columns in the inner query.
        $orders = collect($base->orders)
            ->pluck('column')
            ->filter()
            ->map(fn ($column) => $base->grammar->wrap($column));

        return collect($base->columns)
            ->filter(function ($column) use ($orders, $base) {
                $column = $column instanceof Expression ? $column->getValue($base->grammar) : $base->grammar->wrap($column);
                foreach ($orders as $order) {
                    // If we're ordering by this column, then we need to
                    // keep it in the inner query.
                    if (str_contains($column, "as {$order}")) {
                        return true;
                    }
                }

                // Otherwise we don't.
                return false;
            })
            ->each(function ($column) use ($base) {
                if ($column instanceof Expression) {
                    $column = $column->getValue($base->grammar);
                }

                if (str_contains($column, '?')) {
                    throw new QueryIncompatibleWithFastPagination();
                }
            })
            ->prepend("{$table}.{$key}")
            ->unique()
            ->values()
            ->toArray();
    }

    protected function paginate(string $paginationMethod, Closure $paginatorOutput)
    {
        return function ($perPage = null, $columns = ['*'], $pageName = 'page', $page = null) use ($paginationMethod, $paginatorOutput) {
            /** @var \Hyperf\Database\Model\Builder $this */
            $base = $this->getQuery();
            // Havings, groups, and unions don't work well with this paradigm, because
            // we are counting on each row of the inner query to return a primary key
            // that we can use. When grouping, that's not always the case.
            if (filled($base->havings) || filled($base->groups) || filled($base->unions)) {
                return $this->{$paginationMethod}($perPage, $columns, $pageName, $page);
            }

            $model = $this->newModelInstance();
            $key = $model->getKeyName();
            $table = $model->getTable();

            // Apparently some databases allow for offset 0 with no limit and some people
            // use it as a hack to get all records. Since that defeats the purpose of
            // fast pagination, we'll just return the normal paginator in that case.
            // https://github.com/hammerstonedev/fast-paginate/issues/39
            if ($perPage === -1) {
                return $this->{$paginationMethod}($perPage, $columns, $pageName, $page);
            }

            try {
                $innerSelectColumns = FastPaginate::getInnerSelectColumns($this);
            } catch (QueryIncompatibleWithFastPagination $e) {
                return $this->{$paginationMethod}($perPage, $columns, $pageName, $page);
            }

            // This is the copy of the query that becomes
            // the inner query that selects keys only.
            /** @var Paginator $paginator */
            $paginator = $this->clone()
                // Only select the primary key, we'll get the full
                // records in a second query below.
                ->select($innerSelectColumns)
                // We don't need eager loads for this cloned query, they'll
                // remain on the query that actually gets the records.
                // (withoutEagerLoads not available on Laravel 8.)
                ->setEagerLoads([])
                ->{$paginationMethod}($perPage, ['*'], $pageName, $page);

            // Get the key values from the records on the current page without mutating them.
            $ids = $paginator->getCollection()->map->getRawOriginal($key)->toArray();

            if (in_array($model->getKeyType(), ['int', 'integer'])) {
                /* @phpstan-ignore-next-line */
                $this->query->whereIntegerInRaw("{$table}.{$key}", $ids);
            } else {
                /* @phpstan-ignore-next-line */
                $this->query->whereIn("{$table}.{$key}", $ids);
            }

            // The $paginator is full of records that are primary keys only. Here,
            // we create a new paginator with all of the *stats* from the index-
            // only paginator, but the *items* from the outer query.
            $items = $this->simplePaginate($perPage, $columns, $pageName, 1)->getCollection();

            return Closure::fromCallable($paginatorOutput)->call($this, $items, $paginator);
        };
    }
}
