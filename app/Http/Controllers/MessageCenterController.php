<?php

namespace App\Http\Controllers;

use App\Services\MessageCenterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageCenterController extends Controller
{
    public function __construct(private MessageCenterService $messageCenterService) {}

    public function index(Request $request): View
    {
        return view('messages.index', $this->messageCenterService->overview($request));
    }

    public function section(Request $request, string $section): View
    {
        abort_unless($this->messageCenterService->isValidSection($section), 404);

        return view('messages.section', $this->messageCenterService->section($request, $section));
    }

    public function clear(Request $request): RedirectResponse
    {
        $request->session()->put('messages_cleared_at', now()->toIso8601String());

        return back()->with('status', 'تم حذف الرسائل الحالية.');
    }
}
