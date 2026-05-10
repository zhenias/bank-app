<?php

namespace App\Http\Concerns;

use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;

#[QueryParameter('per_page', description: 'Number of items per page.', type: 'int', default: 10, example: 20)]
#[QueryParameter('page', description: 'Current page number.', type: 'int', default: 1, example: 2)]
trait WithPagination
{
    /**
     * Get the number of items per page from request.
     * Default: 20, Max: 100, Min: 1.
     *
     * @query per_page integer
     */
    protected function perPage(): int
    {
        $perPage = request()->integer('per_page', 20);

        return max(1, min(100, $perPage));
    }

    /**
     * Get the current page from request.
     */
    protected function currentPage(): int
    {
        return request()->integer('page', 1);
    }

    /**
     * Paginate a query with all request parameters.
     */
    protected function paginate($query): LengthAwarePaginator
    {
        return $query->paginate(
            perPage: $this->perPage(),
            page: $this->currentPage(),
        );
    }

    /**
     * Simple paginate (only next/prev, no total count).
     */
    protected function simplePaginate(Builder $query): Paginator
    {
        return $query->simplePaginate(
            perPage: $this->perPage(),
            page: $this->currentPage(),
        );
    }

    /**
     * Get pagination parameters as array.
     */
    protected function paginationParams(): array
    {
        return [
            'per_page' => $this->perPage(),
            'page' => $this->currentPage(),
        ];
    }

    /**
     * Register pagination query parameters for Scramble documentation.
     * Call this in controller's __construct or method docblock.
     */
    public static function paginationQueryParams(): array
    {
        return [
            'per_page' => [
                'type' => 'integer',
                'description' => 'Ilość elementów na stronę (1-100).',
                'default' => 20,
                'example' => 10,
            ],
            'page' => [
                'type' => 'integer',
                'description' => 'Numer strony.',
                'default' => 1,
                'example' => 2,
            ],
        ];
    }
}
