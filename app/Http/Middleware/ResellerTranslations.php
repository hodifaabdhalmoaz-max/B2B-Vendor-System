<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Translation\Translator;
use Symfony\Component\HttpFoundation\Response;

/** Load the portal's root JSON dictionaries without changing legacy storefront/admin translations. */
class ResellerTranslations
{
    public function handle(Request $request, Closure $next): Response
    {
        $original = app('translator');
        $loader = clone $original->getLoader();
        $loader->addJsonPath(base_path('lang'));
        $portal = new Translator($loader, app()->getLocale());
        $portal->setFallback($original->getFallback());
        app()->instance('translator', $portal);

        try {
            return $next($request);
        } finally {
            // Also isolate translations across repeated requests in tests/long-lived workers.
            app()->instance('translator', $original);
        }
    }
}
