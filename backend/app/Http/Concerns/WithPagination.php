<?php

namespace App\Http\Concerns;

use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;

trait WithPagination
{
    protected function perPage(): int
    {
        $perPage = request()->integer('per_page', 20);

        return max(1, min(100, $perPage));
    }

    protected function currentPage(): int
    {
        return request()->integer('page', 1);
    }

    protected function paginate(EloquentBuilder|QueryBuilder|Relation $query): LengthAwarePaginator
    {
        return $query->paginate(
            perPage: $this->perPage(),
            page: $this->currentPage(),
        );
    }

    protected function simplePaginate(EloquentBuilder|QueryBuilder|Relation $query): Paginator
    {
        return $query->simplePaginate(
            perPage: $this->perPage(),
            page: $this->currentPage(),
        );
    }

    protected function paginationParams(): array
    {
        return [
            'per_page' => $this->perPage(),
            'page'     => $this->currentPage(),
        ];
    }

    public static function paginationQueryParams(): array
    {
        return [
            'per_page' => [
                'type'        => 'integer',
                'description' => 'Ilość elementów na stronę (1-100).',
                'default'     => 20,
                'example'     => 10,
            ],
            'page' => [
                'type'        => 'integer',
                'description' => 'Numer strony.',
                'default'     => 1,
                'example'     => 2,
            ],
        ];
    }
}
