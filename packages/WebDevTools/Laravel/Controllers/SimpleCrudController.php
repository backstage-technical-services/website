<?php

namespace Package\WebDevTools\Laravel\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use InvalidArgumentException;

class SimpleCrudController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Define the name of the key used in HTTP requests.
     *
     * @var string
     */
    protected $keyName = 'id';

    /**
     * Set the full class name of the model to use.
     *
     * @var string
     */
    protected $modelClass = '';

    /**
     * Set the short-hand name of the model, used in the views.
     *
     * @var string
     */
    protected $modelName = '';

    /**
     * Set the view directory.
     *
     * @var string
     */
    protected $viewPrefix = '';

    /**
     * Set which methods should be performed over an AJAX request.
     *
     * @var array
     */
    protected $requireAjax = [
        'index'  => false,
        'create' => false,
        'store'  => true,
        'view'   => false,
        'edit'   => false,
        'update' => true,
        'delete' => true,
    ];

    /**
     * Define the validation rules.
     *
     * @var array
     */
    protected $validationRules = [];

    /**
     * Define the validation messages.
     *
     * @var array
     */
    protected $validationMessages = [];

    /**
     * Define the attributes to use when creating and updating.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * When constructing the controller, check the attributes.
     */
    public function __construct()
    {
        if (!$this->keyName || !$this->modelClass || !$this->modelName || !$this->viewPrefix) {
            throw new InvalidArgumentException('Required Controller attributes not set up.');
        }
    }

    /**
     * View the list of existing models.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->verifyAjax('index');
        $this->authorise('index');
        return view($this->viewPrefix . '.index');
    }

    /**
     * View the form to create a new model.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->verifyAjax('create');
        $this->authorise('create');
        return view($this->viewPrefix . '.create');
    }

    /**
     * Process the request and store the new model instance.
     *
     * @return array
     */
    public function store()
    {
        $this->verifyAjax('store');
        $this->authorise('create');

        $request = request();
        $this->validateWith($this->makeValidator($request, null), $request);

        $result = call_user_func([$this->modelClass, 'create'], $this->getAttributes($request, null));

        return [
            is_object($result),
            is_object($result) ? $result : null,
        ];
    }

    /**
     * View an existing model instance.
     *
     * @param $key
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function view($key)
    {
        $this->verifyAjax('view');
        $this->authorise('view');
        return view($this->viewPrefix . '.view', [
            $this->modelName => $this->getModelFromDatabase($key),
        ]);
    }

    /**
     * View the form to edit an existing model instance.
     *
     * @param $key
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($key)
    {
        $this->verifyAjax('edit');
        $this->authorise('edit');
        return view($this->viewPrefix . '.edit', [
            $this->modelName => $this->getModelFromDatabase($key),
        ]);
    }

    /**
     * Update an existing model instance.
     *
     * @param $key
     *
     * @return array
     */
    public function update($key)
    {
        $this->verifyAjax('update');
        $this->authorise('edit');
        $model = $this->getModelFromDatabase($key);

        $request = request();
        $this->validateWith($this->makeValidator($request, $model), $request);

        $status = $model->update($this->getAttributes($request, $model));
        return [
            $status,
            $model,
        ];
    }

    /**
     * A RESTful pseudonym for the delete method.
     *
     * @param $key
     *
     * @return array
     */
    public function destroy($key)
    {
        return $this->delete($key);
    }

    /**
     * Delete a model instance.
     *
     * @param $key
     *
     * @return array
     */
    public function delete($key)
    {
        $this->verifyAjax('delete');
        $this->authorise('delete');

        $model  = $this->getModelFromDatabase($key);
        $status = $model->delete();

        return [
            $status,
            $model,
        ];
    }

    /**
     * Restore a soft-deleted model.
     *
     * @param $key
     *
     * @return array
     */
    public function restore($key)
    {
        if (!method_exists($this->modelClass, 'restore')) {
            return;
        }

        $this->verifyAjax('delete');
        $this->authorise('delete');

        $model  = call_user_func_array([$this->modelClass, 'where'], [$this->keyName, $key])->withTrashed()->firstOrFail();
        $status = $model->restore();

        return [
            $status,
            $model,
        ];
    }

    /**
     * Make the validator instance for the store and update methods.
     *
     * @param \Illuminate\Http\Request $request
     * @param null                     $model
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function makeValidator(Request $request, $model = null)
    {
        return validator(
            $request->all(),
            $this->getValidationRules($request, $model),
            $this->getValidationMessages($request, $model)
        );
    }

    /**
     * Get the validation rules for the request.
     *
     * @param \Illuminate\Http\Request $request
     * @param                          $model
     *
     * @return array
     */
    protected function getValidationRules(Request $request, $model = null)
    {
        return $this->validationRules;
    }

    /**
     * Get the validation messages for the request.
     *
     * @param \Illuminate\Http\Request $request
     * @param                          $model
     *
     * @return array
     */
    protected function getValidationMessages(Request $request, $model = null)
    {
        return $this->validationMessages;
    }

    /**
     * Test whether authorisation should be used for the method.
     *
     * @param                          $method
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function shouldAuthorise($method, Request $request)
    {
        return true;
    }

    /**
     * Automatically authorise the request, if necessary.
     *
     * @param $method
     *
     * @return void
     */
    protected function authorise($method)
    {
        if ($this->shouldAuthorise($method, request())) {
            $this->authorize($method, $this->modelClass);
        }
    }

    /**
     * Test whether the request should be made by AJAX.
     *
     * @param $method
     *
     * @return array|bool|mixed
     */
    protected function requiresAjax($method)
    {
        if (is_array($this->requireAjax) && isset($this->requireAjax[$method])) {
            return $this->requireAjax[$method];
        } else if (is_bool($this->requireAjax)) {
            return $this->requireAjax;
        } else {
            return false;
        }
    }

    /**
     * Automatically that the request method (AJAX or not) is valid.
     *
     * @param $method
     *
     * @return void
     */
    protected function verifyAjax($method)
    {
        if ($this->requiresAjax($method) && (!request()->ajax() || in_array($method, ['index', 'create', 'view', 'edit']))) {
            app()->abort(404);
        }
    }

    /**
     * Get the model instance from the database.
     *
     * @param $key
     *
     * @return mixed
     */
    protected function getModelFromDatabase($key)
    {
        return call_user_func_array([$this->modelClass, 'where'], [$this->keyName, $key])->firstOrFail();
    }

    /**
     * Get the attributes to use for storing and updating.
     *
     * @param \Illuminate\Http\Request $request
     * @param                          $model
     *
     * @return mixed
     */
    protected function getAttributes(Request $request, $model = null)
    {
        return clean($request->only($this->attributes));
    }
}