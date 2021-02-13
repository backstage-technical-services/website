<?php

namespace Package\WebDevTools\Laravel\Traits;

use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;

trait ChecksPaginationPage
{
    /**
     * Redirect to page 1 if the paginator is empty.
     *
     * @param LengthAwarePaginator $paginator
     *
     * @return void
     */
    protected function checkPage(LengthAwarePaginator $paginator)
    {
        $request = request();
        if ((int)$request->input('page') > 1 && $paginator->count() == 0) {
            $query = array_merge($request->except('page'), ['page' => 1]);

            if ($request->route()->getName()) {
                $query    = array_merge($request->route()->parameters(), $query);
                $location = route($request->route()->getName(), $query);
            } else {
                $location = $request->url() . '?' . http_build_query($query);
            }
            
            app()->abort(Response::HTTP_FOUND, '', ['Location' => $location]);
        }
    }
}
