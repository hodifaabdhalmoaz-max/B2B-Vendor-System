<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Services\ResellerPortalService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, ResellerPortalService $portal): View
    {
        return view('reseller.index', $portal->dashboard($request->user()->resellerProfile));
    }
}
