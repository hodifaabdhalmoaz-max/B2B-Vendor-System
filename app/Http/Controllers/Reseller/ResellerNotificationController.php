<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Services\ResellerNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResellerNotificationController extends Controller
{
    public function __construct(private readonly ResellerNotificationService $notifications) {}

    public function index(Request $request): View
    {
        return view('reseller.notifications', ['notifications' => $this->notifications->listing($request->user())]);
    }

    public function read(Request $request, string $notification): RedirectResponse
    {
        $this->notifications->markRead($request->user(), $notification);

        return back()->with('status', __('Notification marked as read.'));
    }

    public function readAll(Request $request): RedirectResponse
    {
        $this->notifications->markAllRead($request->user());

        return back()->with('status', __('All notifications marked as read.'));
    }
}
