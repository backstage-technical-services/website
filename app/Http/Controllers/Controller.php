<?php

namespace App\Http\Controllers;

use bnjns\WebDevTools\Laravel\Traits\AuthorisesGates;
use bnjns\WebDevTools\Laravel\Traits\ChecksPaginationPage;
use bnjns\WebDevTools\Laravel\Traits\UsesAjax;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ChecksPaginationPage, UsesAjax, AuthorisesGates;
}
