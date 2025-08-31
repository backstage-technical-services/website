<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use App\Http\Requests\Resources\ResourceRequest;
use App\Models\Resources\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Package\Notifications\Facades\Notify;
use Package\SearchTools\SearchTools;
use Package\WebDevTools\Laravel\Traits\CorrectsPaginatorPath;

class ResourceController extends Controller
{
    use CorrectsPaginatorPath;

    /**
     * View the list of resources.
     *
     * @param SearchTools $searchTools
     *
     * @return $this
     */
    public function index(SearchTools $searchTools)
    {
        // Authorise
        $this->authorize('index', Resource::class);

        // Start the retrieval
        $resources = Resource::select('resources.*')->orderBy('title', 'ASC');

        // Allow filtering by category or access
        $filter = $searchTools->filter();
        if ($filter) {
            if (preg_match('/^category:(.*)$/', $filter, $matches)) {
                $resources = $resources->inCategory($matches[1]);
            } elseif (preg_match('/^access:(.*)$/', $filter, $matches)) {
                $resources = $resources->where('access', $matches[1]);
            }
        }

        $resources = $resources->paginate(20)->withPath($this->paginatorPath());
        $this->checkPage($resources);

        // Render
        return view('resources.index')->with('resources', $resources);
    }

    /**
     * View the form to create a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->authorize('create', Resource::class);
        return view('resources.create');
    }

    /**
     * Process the form and save the resource
     *
     * @param \App\Http\Requests\Resources\ResourceRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ResourceRequest $request)
    {
        // Create the resource
        $resource = Resource::create([
            'title' => clean($request->get('title')),
            'description' => $request->has('description') ? clean($request->get('description')) : null,
            'category_id' => $request->has('category_id') ? $request->get('category_id') : null,
            'event_id' => $request->has('event_id') ? $request->get('event_id') : null,
            'type' => $request->get('type'),
            'href' => null,
            'access' => $request->get('access'),
        ]);

        // Set the tags
        $resource->tags()->sync($request->has('tags') ? $request->get('tags') : []);

        // Set the source
        if ($resource->isFile()) {
            File::makeDirectory($resource->getPath(), 0775, true);
            $resource->reissue($request->file('file'), 'Initial issue');
        } elseif ($resource->isGDoc()) {
            $resource->update([
                'href' => clean($request->get('drive_id')),
            ]);
        }

        Log::info("User {$request->user()->id} created resource {$resource->id}");
        Notify::success('Resource created');
        return redirect()->route('resource.view', ['id' => $resource->id]);
    }

    /**
     * View a resource.
     *
     * @param $id
     *
     * @return \Illuminate\View\View
     */
    public function view($id)
    {
        $resource = $this->getResourceWithAccessCheck($id);
        return view('resources.view')->with('resource', $resource);
    }

    /**
     * View the contents of a resource.
     *
     * @param                          $id
     *
     * @return mixed
     */
    public function stream($id)
    {
        return $this->getResourceWithAccessCheck($id)->checkIssueNum()->stream();
    }

    /**
     * Download a resource.
     *
     * @param                          $id
     *
     * @return string
     */
    public function download($id)
    {
        return $this->getResourceWithAccessCheck($id)->checkIssueNum()->download();
    }

    /**
     * View the form to edit a resource.
     *
     * @param $id
     *
     * @return $this
     */
    public function edit($id)
    {
        $resource = Resource::findOrFail($id);
        $this->authorize('update', $resource);
        return view('resources.edit')->with('resource', $resource);
    }

    /**
     * Process the form and update a resource.
     *
     * @param                                              $id
     * @param \App\Http\Requests\Resources\ResourceRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($id, ResourceRequest $request)
    {
        $resource = Resource::findOrFail($id);

        // Update
        $resource->update([
            'title' => clean($request->get('title')),
            'description' => $request->has('description') ? clean($request->get('description')) : null,
            'category_id' => $request->has('category_id') ? $request->get('category_id') : null,
            'event_id' => $request->has('event_id') ? $request->get('event_id') : null,
            'access' => $request->get('access'),
        ]);

        // Set the tags
        $resource->tags()->sync($request->has('tags') ? $request->get('tags') : []);

        Log::info("User {$request->user()->id} updated resource $id");
        Notify::success('Resource saved');
        return redirect()->route('resource.view', ['id' => $resource->id]);
    }

    /**
     * View the form to issue a new version.
     *
     * @param $id
     *
     * @return \Illuminate\View\View
     */
    public function issueForm($id)
    {
        // Authorise
        $resource = Resource::findOrFail($id);
        $this->authorize('update', $resource);

        // Check the resource can be issued
        if (!$resource->isIssuable()) {
            return redirect()->route('resource.view', ['id' => $id]);
        }

        return view('resources.issue')->with('resource', $resource);
    }

    /**
     * Submit the form and create a new issue.
     *
     * @param                          $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function issue($id, Request $request)
    {
        // Authorise
        $resource = Resource::findOrFail($id);
        $this->authorize('update', $resource);

        // Check the resource can be issued
        if (!$resource->isIssuable()) {
            return redirect()->route('resource.view', ['id' => $id]);
        }

        // Validate
        $this->validate(
            $request,
            [
                'file' => 'required|' . ResourceRequest::FILE_RULES,
                'reason' => 'required',
            ],
            ResourceRequest::FILE_MESSAGES + ['reason.required' => 'Please enter a reason for the new issue'],
        );

        // Create the new issue
        $newIssue = $resource->reissue($request->file('file'), $request->get('reason'));

        Log::info("User {$request->user()->id} created issue {$newIssue->issue} on resource $id");
        Notify::success('Issue submitted');
        return redirect()->route('resource.view', ['id' => $id]);
    }

    /**
     * View a resource's history.
     *
     * @param $id
     *
     * @return \Illuminate\View\View
     */
    public function history($id)
    {
        $resource = $this->getResourceWithAccessCheck($id);

        // Check the resource can be issued
        if (!$resource->isIssuable()) {
            return redirect()->route('resource.view', ['id' => $id]);
        }

        return view('resources.history')->with('resource', $resource);
    }

    /**
     * Delete a resource.
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Authorise
        $resource = Resource::findOrFail($id);
        $this->authorize('delete', $resource);

        $resource->delete();

        Log::info('User ' . request()->user()->id . " deleted resource $id");

        Notify::success('Resource deleted');
        return $this->ajaxResponse('Resource deleted');
    }

    /**
     * Retrieve a resource, checking the user can access it.
     *
     * @param $id
     *
     * @return \App\Models\Resources\Resource
     */
    private function getResourceWithAccessCheck($id)
    {
        // Get the resource
        $resource = Resource::findOrFail($id);

        // Check the access privileges
        if (!$resource->canAccess(Auth::user())) {
            app()->abort(403);
        }

        // Check the source is accessible
        if ($resource->isFile() && !file_exists($resource->getFullPath())) {
            app()->abort(404);
        }

        return $resource;
    }
}
