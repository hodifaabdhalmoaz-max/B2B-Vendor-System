<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reseller\StoreReservationRequest;
use App\Services\ResellerPortalService;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function __construct(private readonly ResellerPortalService $portal) {}

    public function store(StoreReservationRequest $request)
    {
        $reservation = $this->portal->submit($request->user()->resellerProfile, $request->items(), $request->validated('idempotency_key'), $request->validated('notes'));

        return redirect()->route('reseller.reservations.show', $reservation->id)->with('status', __('Reservation submitted.'));
    }

    public function index(Request $request)
    {
        return view('reseller.reservations', ['reservations' => $this->portal->history($request->user()->resellerProfile->id)]);
    }

    public function show(Request $request, int $reservation)
    {
        return view('reseller.reservation', ['reservation' => $this->portal->detail($request->user()->resellerProfile->id, $reservation)]);
    }

    public function cancel(Request $request, int $reservation)
    {
        $this->portal->cancel($request->user()->resellerProfile, $reservation);

        return redirect()->route('reseller.reservations.show', $reservation)->with('status', __('Reservation cancelled.'));
    }
}
