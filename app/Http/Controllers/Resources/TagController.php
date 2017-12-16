<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use App\Models\Resources\Tag;
use bnjns\LaravelNotifications\Facades\Notify;
use bnjns\WebDevTools\Laravel\Traits\CreatesSlugs;
use Illuminate\Http\Request;

class TagController extends Controller
{
    use CreatesSlugs;

    /**
     * View the list of categories.
     *
     * @return $this
     */
    public function index()
    {
        // Authorise
        $this->authorize('index', Tag::class);

        // Get the list of categories
        $tags = Tag::orderBy('name', 'ASC')
                   ->paginate(20);
        $this->checkPage($tags);
        return view('resources.tags.index')->with('tags', $tags);
    }

    /**
     * Store a new category.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Authorise
        $this->requireAjax();
        $this->authorize('create', Tag::class);

        // Create the slug
        $this->createSlug($request);

        // Validate
        $fields = ['name', 'slug'];
        $this->validate(
            $request,
            Tag::getValidationRules($fields),
            Tag::getValidationMessages($fields)
        );

        // Create
        Tag::create(clean($request->only($fields)));
        Notify::success('Tag created');
        return $this->ajaxResponse('Tag created');
    }

    /**
     * Update an existing category.
     *
     * @param                          $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        // Authorise
        $this->requireAjax();
        $tag = Tag::findOrFail($id);
        $this->authorize('update', $tag);

        // Create the slug
        $this->createSlug($request);

        // Validate
        $fields        = ['name', 'slug'];
        $rules         = Tag::getValidationRules($fields);
        $rules['slug'] .= ",{$id},id";
        $this->validate(
            $request,
            $rules,
            Tag::getValidationMessages($fields)
        );

        // Update
        $tag->update(clean($request->only($fields)));
        Notify::success('Tag updated');
        return $this->ajaxResponse('Tag updated');
    }

    /**
     * Delete an existing category.
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Authorise
        $this->requireAjax();
        $tag = Tag::findOrFail($id);
        $this->authorize('delete', $tag);

        // Delete
        $tag->delete();
        Notify::success('Tag deleted');
        return $this->ajaxResponse('Tag deleted');
    }

    /**
     * Ensure that the request contains a slug field.
     *
     * @param \Illuminate\Http\Request $request
     */
    private function createSlug(Request $request)
    {
        return $request->merge([
            'slug' => $this->slugify($request),
        ]);
    }
}
