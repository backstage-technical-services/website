<?php

namespace Package\WebDevTools\Laravel\Traits;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

trait CorrectsDistinctPagination
{
    /**
     * A custom paginate method for when using the distinct() method. This fixes the incorrect 'total' reported by the default paginate.
     *
     * @param         $query
     * @param int     $perPage
     * @param array   $columns
     * @param string  $pageName
     *
     * @return LengthAwarePaginator
     */
    public function scopeDistinctPaginate($query, $perPage = 15, array $columns = ['*'], $pageName = 'page')
    {
        // Get the results
        $results = $query
            ->distinct()
            ->forPage(request()->input($pageName, 1), $perPage)
            ->get($columns);

        // Now do a count on an unlimited version of the previous query
        $query->limit(static::count())->offset(0);
        $count = DB::select('SELECT COUNT(*) FROM (' . $query->toSql() . ') src;', $query->getBindings())[0]
            ->{'COUNT(*)'};

        // Create the paginator
        return new LengthAwarePaginator($results, $count, $perPage, Paginator::resolveCurrentPage(), [
            'path' => Paginator::resolveCurrentPath(),
        ])->appends(request()->except('page'));
    }
}
