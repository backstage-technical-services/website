<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use App\Models\Training\Category;
use Package\Notifications\Facades\Notify;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * CategoryController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * View the list of training categories.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->authorize('index', Category::class);
        return view('training.categories.index')->with([
            'categories' => Category::orderBy('name', 'ASC')->get(),
        ]);
    }

    /**
     * Create a new training category.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->requireAjax();
        $this->authorize('create', Category::class);

        $this->validate($request, [
            'name' => 'required|unique:training_categories,name',
        ], [
            'name.required' => 'Please enter the category name',
            'name.unique'   => 'A category with that name already exists',
        ]);

        Category::create([
            'name' => clean($request->get('name')),
        ]);
        Notify::success('Category created');
        return $this->ajaxResponse(true);
    }

    /**
     * Update the details of a training category.
     *
     * @param                          $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        $this->requireAjax();
        $category = Category::findOrFail($id);
        $this->authorize('update', $category);

        $this->validate($request, [
            'name' => 'required|unique:training_categories,name,' . $id . ',id',
        ], [
            'name.required' => 'Please enter the category name',
            'name.unique'   => 'A category with that name already exists',
        ]);

        $category->update([
            'name' => clean($request->get('name')),
        ]);
        Notify::success('Category updated');
        return $this->ajaxResponse(true);
    }

    /**
     * Delete a training category.
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $this->requireAjax();
        $category = Category::findOrFail($id);
        $this->authorize('delete', $category);

        $category->delete();
        Notify::success('Category deleted');
        return $this->ajaxResponse(true);
    }
}
