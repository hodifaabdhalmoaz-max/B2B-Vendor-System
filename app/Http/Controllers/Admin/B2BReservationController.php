<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterB2BReservationsRequest;
use App\Models\Reservation;
use App\Services\AdminReservationService;
use App\Services\ReservationService;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class B2BReservationController extends Controller
{
    public function __construct(private readonly AdminReservationService $queries, private readonly ReservationService $reservations) {}

    public function index(FilterB2BReservationsRequest $request): View
    {
        return view('admin.b2b.reservations.index', $this->queries->index($request->validated()));
    }

    public function show(Reservation $reservation): View
    {
        return view('admin.b2b.reservations.show', $this->queries->show($reservation));
    }

    public function confirm(Request $request, Reservation $reservation): RedirectResponse
    {
        return $this->perform($reservation, fn () => $this->reservations->confirm($reservation, $request->user()->id), __('Reservation confirmed.'));
    }

    public function preparing(Request $request, Reservation $reservation): RedirectResponse
    {
        return $this->perform($reservation, fn () => $this->reservations->markPreparing($reservation, $request->user()->id), __('Reservation marked preparing.'));
    }

    public function ship(Request $request, Reservation $reservation): RedirectResponse
    {
        return $this->perform($reservation, fn () => $this->reservations->markShipped($reservation, $request->user()->id), __('Reservation marked shipped.'));
    }

    public function complete(Request $request, Reservation $reservation): RedirectResponse
    {
        return $this->perform($reservation, fn () => $this->reservations->complete($reservation, $request->user()->id), __('Reservation completed.'));
    }

    public function cancel(Request $request, Reservation $reservation): RedirectResponse
    {
        return $this->perform($reservation, fn () => $this->reservations->cancel($reservation, $request->user()->id), __('Reservation cancelled.'));
    }

    public function expire(Request $request, Reservation $reservation): RedirectResponse
    {
        return $this->perform($reservation, function () use ($request, $reservation): void {
            if (! $this->reservations->expire($reservation, $request->user()->id)) {
                throw ValidationException::withMessages(['status' => __('Reservation was not expired: it is not due or has already been processed.')]);
            }
        }, __('Reservation expired.'));
    }

    /** Keep feedback on the detail page even for stale requests without a Referer. */
    private function perform(Reservation $reservation, Closure $operation, string $message): RedirectResponse
    {
        $response = redirect()->route('admin.b2b.reservations.show', $reservation);
        try {
            $operation();
        } catch (ValidationException $exception) {
            return $response->withErrors($exception->errors());
        } catch (QueryException $exception) {
            report($exception);

            return $response->withErrors(['reservation' => __('Unable to process the reservation. Refresh its details and try again.')]);
        }

        return $response->with('status', $message);
    }
}
