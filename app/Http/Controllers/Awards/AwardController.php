<?php

namespace App\Http\Controllers\Awards;

use App\Http\Controllers\Controller;
use App\Models\Awards\Award;
use Package\Notifications\Facades\Notify;
use Illuminate\Http\Request;

class AwardController extends Controller
{
    /**
     * View the list of awards.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->authorize('index', Award::class);
        $awards = Award::suggested()->get()
                       ->merge(Award::approved()->get());
        return view('awards.awards.index')->with('awards', $awards);
    }

    /**
     * Store a new award.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Authorise
        $this->requireAjax();
        $this->authorize('create', Award::class);

        // Validate
        $fields = ['name', 'description', 'recurring'];
        $this->validate($request, Award::getValidationRules($fields), Award::getValidationMessages($fields));

        // Create the award
        Award::create([
            'name'         => clean($request->get('name')),
            'description'  => clean($request->get('description')),
            'suggested_by' => null,
            'recurring'    => (bool)$request->get('recurring'),
        ]);

        Notify::success('Award created');
        return $this->ajaxResponse('Award created');
    }

    /**
     * Update an award's details.
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
        $award = Award::findOrFail($id);
        $this->authorize('update', $award);

        // Validate
        $fields = ['name', 'description', 'recurring'];
        $this->validate($request, Award::getValidationRules($fields), Award::getValidationMessages($fields));

        // Update the award
        $award->update([
            'name'        => clean($request->get('name')),
            'description' => clean($request->get('description')),
            'recurring'   => (bool)$request->get('recurring'),
        ]);

        Notify::success('Award updated');
        return $this->ajaxResponse('Award updated');
    }

    /**
     * Approve an award.
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function approve($id)
    {
        // Authorise
        $this->requireAjax();
        $award = Award::findOrFail($id);
        $this->authorize('update', $award);

        // Update
        $award->update([
            'suggested_by' => null,
        ]);
        Notify::success('Award approved');
        return $this->ajaxResponse('Award approved');
    }

    /**
     * Suggest new awards.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function suggest(Request $request)
    {
        // Authorise
        $this->requireAjax();
        $this->authorize('suggest', Award::class);

        // Validate
        $fields = ['name', 'description'];
        $this->validate($request, Award::getValidationRules($fields), Award::getValidationMessages($fields));

        // Create the award
        Award::create([
            'name'         => clean($request->get('name')),
            'description'  => clean($request->get('description')),
            'suggested_by' => $request->user()->id,
            'recurring'    => false,
        ]);

        Notify::success('Award suggested');
        return $this->ajaxResponse('Award suggested');
    }

    /**
     * Delete an award.
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Authorise
        $this->requireAjax();
        $award = Award::findOrFail($id);
        $this->authorize('delete', $award);

        // Delete
        $award->delete();
        Notify::success('Award deleted');
        return $this->ajaxResponse('Award deleted');
    }
}