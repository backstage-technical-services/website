<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request as RequestFacade;

class Request extends FormRequest
{
    /**
     * Variable to store the name of the current route.
     *
     * @var string
     */
    protected $route;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function __construct()
    {
        parent::__construct();
        $this->route = RequestFacade::route()->getName();
    }
}
