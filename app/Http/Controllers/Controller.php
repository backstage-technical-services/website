<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Package\WebDevTools\Laravel\Traits\AuthorisesGates;
use Package\WebDevTools\Laravel\Traits\ChecksPaginationPage;
use Package\WebDevTools\Laravel\Traits\UsesAjax;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ChecksPaginationPage, UsesAjax, AuthorisesGates;
}
