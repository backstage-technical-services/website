<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use App\Models\Resources\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Package\Notifications\Facades\Notify;
use Package\WebDevTools\Laravel\Traits\CreatesSlugs;

class CategoryController extends Controller
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
        $this->authorize('index', Category::class);

        // Get the list of categories
        $categories = Category::orderBy('name', 'ASC')
                              ->paginate(20);
        $this->checkPage($categories);
        return view('resources.categories.index')->with('categories', $categories);
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
        $this->authorize('create', Category::class);

        // Create the slug
        $this->createSlug($request);

        // Validate
        $fields = ['name', 'slug', 'flag'];
        $this->validate(
            $request,
            Category::getValidationRules($fields),
            Category::getValidationMessages($fields)
        );

        // Create
        $category = Category::create(clean($request->only($fields)));

        Log::info("User {$request->user()->id} created resource category {$category->id}");
        Notify::success('Category created');
        return $this->ajaxResponse('Category created');
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
        $category = Category::findOrFail($id);
        $this->authorize('update', $category);

        // Create the slug
        $this->createSlug($request);

        // Validate
        $fields        = ['name', 'slug', 'flag'];
        $rules         = Category::getValidationRules($fields);
        $rules['slug'] .= ",{$id},id";
        $this->validate(
            $request,
            $rules,
            Category::getValidationMessages($fields)
        );

        // Update
        $category->update(clean($request->only($fields)));

        Log::info("User {$request->user()->id} updated resource category $id");
        Notify::success('Category updated');
        return $this->ajaxResponse('Category updated');
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
        $category = Category::findOrFail($id);
        $this->authorize('delete', $category);

        // Delete
        $category->delete();

        Log::info("User " . request()->user()->id . " deleted resource category $id");
        Notify::success('Category deleted');
        return $this->ajaxResponse('Category deleted');
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
