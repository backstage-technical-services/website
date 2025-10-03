<?php

namespace App\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;
use Laravel\Socialite\Contracts\Factory;
use SocialiteProviders\Keycloak\Provider;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    private readonly Provider $keycloak;

    public function __construct(
        private readonly AuthFactory $auth,
        private readonly AuthService $service,
        Factory $socialite,
    ) {
        $this->keycloak = $socialite->driver('keycloak');
    }

    /**
     * This is responsible for initiating the authentication flow by redirecting the user to the Keycloak IdP. If the
     * user is already logged in then they are simply redirected to the member dashboard.
     *
     * @return Response
     */
    function redirect(): Response
    {
        if ($this->auth->guard()->check()) {
            return redirect('/members');
        }

        return $this->keycloak->redirect();
    }

    function callback(): Response
    {
        $user = $this->service->login();

        return $user ? redirect('/members') : redirect('/');
    }

    /**
     * This is responsible for logging the user out of both the application and Keycloak. If the user is not logged in,
     * we just skip this and redirect them to the home page.
     *
     * @param Request $request
     * @return Response
     */
    function logout(Request $request): Response
    {
        return $this->service->logout($request) ? redirect($this->service->getKeycloakLogoutUrl()) : redirect('/');
    }
}
