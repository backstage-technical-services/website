<?php

namespace App\Http\Middleware;

use bnjns\LaravelNotifications\Facades\Notify;
use Closure;

class AddNotifications
{
    /**
     * Add the default notifications.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Javascript
        Notify::warning('We use javascript to improve the user experience and make things more interactive - things may not work if you have javascript turned off.')
              ->title('Uh oh! No javascript!')
              ->enclose('noscript')
              ->permanent();

        // Cookie policy
        if (!isset($_COOKIE['CookiePolicyAccepted'])) {
            Notify::info('We use cookies to enhance your user experience; to see what data we collect, simply go to our [privacy policy](/page/privacy-policy). By continuing to use this site, or by closing this message, you agree with our use of cookies.')
                  ->title('Cookie policy')
                  ->attribute('id', 'cookie-policy-msg')
                  ->permanent();
        }

        return $next($request);
    }
}
