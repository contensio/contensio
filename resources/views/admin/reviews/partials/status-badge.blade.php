{{--
 | Contensio — review status badge.
 | Usage: @include('contensio::admin.reviews.partials.status-badge', ['status' => $item->review_status])
--}}
@php
    $reviewStatus = $status ?? null;
    if (! $reviewStatus) return;

    [$badgeClass, $badgeLabel] = match($reviewStatus) {
        'pending'       => ['bg-amber-50 text-amber-700 border border-amber-200',   'Pending Review'],
        'approved'      => ['bg-green-50 text-green-700 border border-green-200',   'Approved'],
        'soft_rejected' => ['bg-orange-50 text-orange-700 border border-orange-200','Revision Requested'],
        'hard_rejected' => ['bg-red-50 text-red-700 border border-red-200',         'Rejected'],
        default         => [null, null],
    };
@endphp
@if($badgeClass)
<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $badgeClass }}">
    {{ $badgeLabel }}
</span>
@endif
