<?php

namespace Package\WebDevTools\Laravel\Traits;

trait CorrectsPaginatorPath
{
    /**
     * Create the path to use for pagination.
     *
     * @param array $customQuery
     * @param bool  $includeQuery
     *
     * @return string
     */
    public function paginatorPath(array $customQuery = null, $includeQuery = true)
    {
        // Get the request and query
        $request = request();
        $route = $request->route();
        $query = $request->query();

        // Remove the page element
        if (isset($query['page'])) {
            unset($query['page']);
        }

        if ($route->getName()) {
            $parameters = array_merge($route->parameters(), $includeQuery ? $query : [], $customQuery ?: []);

            return route($route->getName(), $parameters);
        } else {
            $query = array_merge($includeQuery ? $query : [], $customQuery ?: []);

            return $request->url() . count($query) ? '?' . http_build_query($query) : '';
        }
    }
}
