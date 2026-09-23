@props(['rating' => 0, 'count' => 0])

@php
    $fullStars = floor($rating);
    $hasHalf = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($hasHalf ? 1 : 0);
@endphp

<div class="product-card__review d-flex align-items-center">
    <div class="reviews-group d-flex">
        @for($i = 0; $i < $fullStars; $i++)
        <svg class="review-star" viewBox="0 0 9 9" xmlns="http://www.w3.org/2000/svg"><use href="#icon_star" /></svg>
        @endfor
        @if($hasHalf)
        <svg class="review-star" viewBox="0 0 9 9" xmlns="http://www.w3.org/2000/svg" style="opacity: 0.5"><use href="#icon_star" /></svg>
        @endif
        @for($i = 0; $i < $emptyStars; $i++)
        <svg class="review-star" viewBox="0 0 9 9" xmlns="http://www.w3.org/2000/svg" style="opacity: 0.2"><use href="#icon_star" /></svg>
        @endfor
    </div>
    @if($count > 0)
    <span class="reviews-note text-lowercase text-secondary ms-1">({{ $count }} تقييم)</span>
    @else
    <span class="reviews-note text-lowercase text-secondary ms-1">لا توجد تقييمات</span>
    @endif
</div>
