<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Translation\Translator;
use Symfony\Component\HttpFoundation\Response;

/** Root JSON labels for admin reservations and their navigation links. */
class AdminReservationTranslations
{
    public function handle(Request $request, Closure $next): Response
    {
        $original = app('translator');
        $loader = clone $original->getLoader();
        $loader->addJsonPath(base_path('lang'));
        $translator = new Translator($loader, app()->getLocale());
        $translator->setFallback($original->getFallback());
        if (! $request->routeIs('admin.b2b.reservations.*')) {
            // Other admin screens only need the new navigation label. Preserve
            // their existing translation behavior (including variant management).
            $navigationLabel = $translator->get('B2B Reservations');
            $translator = clone $original;
            $translator->load('*', '*', app()->getLocale());
            $translator->addLines(['*.B2B Reservations' => $navigationLabel], app()->getLocale());
        }
        app()->instance('translator', $translator);

        try {
            return $next($request);
        } finally {
            app()->instance('translator', $original);
        }
    }
}
