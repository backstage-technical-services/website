<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use App\Models\Resources\Resource;
use App\Models\Resources\Category;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Handle a GET request.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function getSearch(Request $request)
    {
        // Parse the search
        $search   = $this->parseSearchRequest($request);
        $query    = isset($search['query']) ? $search['query'] : null;
        $category = isset($search['category']) ? $search['category'] : null;
        $tags     = isset($search['tag']) ? $search['tag'] : [];

        if($query == null && $category == null && empty($tags)) {
            return $this->viewForm();
        } else {
            return $this->viewResults($query, $category, $tags);
        }
    }

    /**
     * View the index page.
     * @return \Illuminate\View\View
     */
    private function viewForm()
    {
        return view('resources.search.index');
    }

    /**
     * Process the search query and show the results.
     * @param       $query
     * @param       $category
     * @param array $tags
     * @return $this
     */
    private function viewResults($query, $category, array $tags)
    {
        // Decode the search term
        $query = urldecode($query);

        // Search using the query and category
        $resources = Resource::select('resources.*');
        $resources = $query ? $resources->search($query) : $resources->orderBy('title');
        $resources = $category ? $resources->inCategory($category) : $resources;
        $resources = $tags ? $resources->withTags($tags) : $resources;

        // Access and paginate
        $resources = $resources->accessible()
                               ->paginate(20);
        $this->checkPage($resources);
        $resources->appends(request()->only('query', 'category', 'tag'));

        // Render the view
        return view('resources.search.results')->with([
            'resources' => $resources,
            'search'    => (object) [
                'query'    => $query,
                'category' => $category,
                'tags'     => $tags,
            ],
            'category'  => Category::where('slug', $category)->first(),
        ]);
    }

    /**
     * Process the submitted search form.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSearch(Request $request)
    {
        if(!$request->get('query') && !$request->get('category') && !$request->get('tag')) {
            return redirect()->route('resource.search');
        } else {
            return redirect()->route('resource.search', $this->parseSearchRequest($request));
        }
    }

    /**
     * Parse a search request, looking for additional categories and tags.
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    private function parseSearchRequest(Request $request)
    {
        // Get the query, category and tags from the request
        $query    = $request->get('query') ?: null;
        $category = $request->get('category') ?: null;
        $tags     = $request->get('tag') ?: [];

        // Initialise the parsed array
        $params = ['query' => $query];
        if($category) {
            $params['category'] = $category;
        }
        if($tags) {
            foreach($tags as $tag) {
                @$params['tag'][] = $tag;
            }
        }

        // Look for a category in the query
        preg_match('/category:([a-z0-9-]+)/i', $query, $matches);
        if(count($matches) > 0) {
            $params['category'] = $matches[1];
            $query              = trim(str_replace($matches[0], '', $query));
        }

        // Look for any tags in the query
        preg_match_all('/tag:([a-z0-9-]+)/i', $query, $matches);
        if(count($matches[0]) > 0) {
            foreach($matches[1] as $i => $tag) {
                @$params['tag'][] = $tag;
                $query = trim(str_replace($matches[0][$i], '', $query));
            }
        }

        // Set the query
        $params['query'] = $query;

        return $params;
    }
}
