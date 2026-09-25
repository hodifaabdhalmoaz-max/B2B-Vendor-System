@push('style')
<style>
    .b2b-reservations h3, .b2b-reservations dd { overflow-wrap: anywhere; }
    .b2b-reservations .wg-box, .b2b-reservations .table-responsive { min-width: 0; max-width: 100%; }
    .b2b-reservations table { min-width: 900px; }
    .b2b-reservations th { white-space: nowrap; }
    .b2b-reservations .tf-button { min-height: 44px; height: auto; }
    .b2b-reservations a:focus-visible, .b2b-reservations button:focus-visible,
    .b2b-reservations input:focus-visible, .b2b-reservations .table-responsive:focus-visible {
        outline: 2px solid var(--Main); outline-offset: 3px;
    }
</style>
@endpush
