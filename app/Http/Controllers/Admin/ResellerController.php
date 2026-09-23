<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResetResellerPasswordRequest;
use App\Http\Requests\Admin\StoreResellerRequest;
use App\Http\Requests\Admin\UpdateResellerRequest;
use App\Models\User;
use App\Services\ResellerAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ResellerController extends Controller
{
    public function __construct(private readonly ResellerAccountService $resellerAccountService) {}

    public function index(): View
    {
        $resellers = User::query()
            ->where('utype', User::TYPE_RESELLER)
            ->with('resellerProfile')
            ->latest()
            ->paginate(15);

        return view('admin.resellers.index', compact('resellers'));
    }

    public function create(): View
    {
        return view('admin.resellers.create');
    }

    public function store(StoreResellerRequest $request): RedirectResponse
    {
        $reseller = $this->resellerAccountService->create($request->validated());

        return redirect()
            ->route('admin.resellers.edit', $reseller)
            ->with('status', __('Reseller account created. Share the temporary password securely.'));
    }

    public function edit(User $reseller): View
    {
        $this->assertReseller($reseller);

        return view('admin.resellers.edit', [
            'reseller' => $reseller->load('resellerProfile'),
        ]);
    }

    public function update(UpdateResellerRequest $request, User $reseller): RedirectResponse
    {
        $this->assertReseller($reseller);

        $this->resellerAccountService->update($reseller, $request->validated());

        return redirect()
            ->route('admin.resellers.edit', $reseller)
            ->with('status', __('Reseller account updated.'));
    }

    public function suspend(User $reseller): RedirectResponse
    {
        $this->assertReseller($reseller);

        $this->resellerAccountService->suspend($reseller);

        return redirect()
            ->route('admin.resellers.index')
            ->with('status', __('Reseller account suspended.'));
    }

    public function reactivate(User $reseller): RedirectResponse
    {
        $this->assertReseller($reseller);

        $this->resellerAccountService->reactivate($reseller);

        return redirect()
            ->route('admin.resellers.index')
            ->with('status', __('Reseller account reactivated.'));
    }

    public function resetPassword(ResetResellerPasswordRequest $request, User $reseller): RedirectResponse
    {
        $this->assertReseller($reseller);

        $this->resellerAccountService->resetTemporaryPassword($reseller, $request->validated('password'));

        return redirect()
            ->route('admin.resellers.edit', $reseller)
            ->with('status', __('Temporary password reset. Share it securely; it is not stored in plaintext.'));
    }

    public function destroy(User $reseller): RedirectResponse
    {
        $this->assertReseller($reseller);

        $this->resellerAccountService->deleteIfSafe($reseller);

        return redirect()
            ->route('admin.resellers.index')
            ->with('status', __('Reseller account deleted.'));
    }

    private function assertReseller(User $reseller): void
    {
        abort_unless($reseller->isReseller(), 404);
    }
}
