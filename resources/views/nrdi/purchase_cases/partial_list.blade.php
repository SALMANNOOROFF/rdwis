@forelse($cases as $idx => $p)
@php 
    $statusClass = match(strtolower($p->pcs_status)) {
        'approved' => 'status-approved',
        'returned' => 'status-returned',
        default => ''
    };
    $statusIcon = match(strtolower($p->pcs_status)) {
        'under scrutiny' => 'fa-binoculars',
        'with md' => 'fa-user-tie',
        'with dg' => 'fa-user-shield',
        'with dfinance' => 'fa-file-invoice-dollar',
        'returned' => 'fa-undo',
        'approved' => 'fa-check-circle',
        default => 'fa-hourglass-half'
    };
@endphp
<tr class="hub-row div-row-{{ $p->pcs_unt_id }}">
    {{-- 1. Action Arrow --}}
    <td>
        <a href="{{ route($detailsRouteName, $p->pcs_id) }}" class="nav-arrow">
            <i class="fas fa-chevron-right"></i>
        </a>
    </td>

    {{-- 2. Title & Ref --}}
    <td>
        <div class="text-white font-weight-bold" style="font-size: 14px; letter-spacing: 0.3px;">{{ $p->pcs_title }}</div>
        <div class="text-ref" style="font-size: 11px;">Ref ID: {{ $p->pcs_id }}</div>
    </td>

    {{-- 3. Date --}}
    <td class="rajdhani text-muted" style="font-size: 12px; font-weight: 700;">
        {{ \Carbon\Carbon::parse($p->pcs_date)->format('d M, Y') }}
    </td>

    {{-- 4. Amount --}}
    <td class="text-right">
        <div class="text-amount">Rs. {{ number_format($p->pcs_price) }}</div>
    </td>

    {{-- 5. Status --}}
    <td class="text-center">
        <span class="status-pill {{ $statusClass }}">
            <i class="fas {{ $statusIcon }}"></i> {{ strtoupper($p->pcs_status) }}
        </span>
    </td>

    {{-- 6. Type Badge --}}
    <td class="text-right">
        <div class="type-badge type-{{ strtolower($p->pcs_type ?? 'ps') }}">{{ strtoupper(substr($p->pcs_type ?? 'PS', 0, 2)) }}</div>
    </td>
</tr>
@empty
<tr class="empty-row">
    <td colspan="6" class="text-center py-4 text-muted small rajdhani" style="opacity: 0.2; font-size: 16px;">
        No cases available.
    </td>
</tr>
@endforelse
