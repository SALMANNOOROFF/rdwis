@extends('welcome')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600&display=swap');

.scrutiny-hub { font-family: 'Inter', sans-serif; background: #080b0f; min-height: 100vh; color: #cbd5e0; }
.rajdhani { font-family: 'Rajdhani', sans-serif; letter-spacing: 0.5px; }

/* Compact Header */
.hub-header { background: #0f161e; padding: 12px 24px; border-bottom: 1px solid rgba(255,255,255,0.05); }
.hub-title { font-family: 'Rajdhani', sans-serif; font-size: 18px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 1px; margin: 0; }

/* Filter Bar */
.filter-bar { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 10px; }
.div-pill { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 4px; padding: 6px 14px; color: #8a96a3; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 6px; position: relative; }
.div-pill.active { background: #111a24; border-color: #3b82f6; color: #fff; box-shadow: 0 0 10px rgba(59, 130, 246, 0.2); }
.div-pill:hover:not(.active) { background: rgba(255,255,255,0.06); }
.red-dot { width: 8px; height: 8px; background: #ef4444; border-radius: 50%; position: absolute; top: -3px; right: -3px; box-shadow: 0 0 8px rgba(239, 68, 68, 0.9); }

/* Master Table Styling */
.hub-master-table { width: 100%; border-collapse: separate; border-spacing: 0 4px; table-layout: fixed; }

/* Sticky Header */
.sticky-header th { 
    position: sticky; 
    top: 0; 
    z-index: 100; 
    background: #0f161e; /* Match hub-header background */
    font-family: 'Rajdhani', sans-serif; 
    font-size: 11px; 
    text-transform: uppercase; 
    letter-spacing: 1.2px; 
    color: #4b5563; 
    padding: 12px 16px; 
    font-weight: 700;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

/* Section Group Headings */
.section-group-row { cursor: pointer; transition: background 0.2s; }
.section-group-row:hover { background: rgba(255,255,255,0.02); }
.section-group-cell { padding: 18px 16px 8px 16px !important; border-bottom: 1px solid rgba(255,255,255,0.1) !important; }

.section-title-wrap { display: flex; align-items: center; gap: 12px; }
.section-title { 
    font-family: 'Rajdhani', sans-serif; 
    font-size: 16px; 
    font-weight: 700; 
    color: #fff; 
    text-transform: uppercase; 
    letter-spacing: 1px;
}
.section-count {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
    font-size: 14px;
    font-weight: 800;
    padding: 2px 10px;
    border-radius: 4px;
    font-family: 'Rajdhani', sans-serif;
    border: 1px solid rgba(59, 130, 246, 0.2);
}
.toggle-icon { font-size: 14px; color: #64748b; transition: transform 0.3s; }
.collapsed .toggle-icon { transform: rotate(-90deg); }

/* Row Styling */
.hub-row { background: #0d1218; transition: background 0.2s; }
.hub-row:hover { background: #111a24; }
.hub-row td { padding: 10px 16px; border: none !important; vertical-align: middle; font-size: 14px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.hub-row td:first-child { border-radius: 6px 0 0 6px; width: 50px; text-align: center; }
.hub-row td:last-child { border-radius: 0 6px 6px 0; text-align: right; width: 120px; }

/* Navigation Arrow Button */
.nav-arrow { 
    width: 32px; 
    height: 32px; 
    border-radius: 6px; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    color: #3b82f6; 
    background: rgba(59, 130, 246, 0.08); 
    border: 1px solid rgba(59, 130, 246, 0.2); 
    transition: all 0.2s;
    text-decoration: none !important;
}
.nav-arrow:hover { background: #3b82f6; color: #fff; transform: scale(1.1); }

.type-badge { padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; color: #fff; text-transform: uppercase; }
.type-ps { background: rgba(59, 130, 246, 0.15); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.2); }
.type-pt { background: rgba(139, 92, 246, 0.15); color: #8b5cf6; border: 1px solid rgba(139, 92, 246, 0.2); }

.status-pill { background: rgba(245, 158, 11, 0.08); color: #f59e0b; padding: 4px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; border: 1px solid rgba(245, 158, 11, 0.15); display: inline-flex; align-items: center; gap: 4px; }

.text-amount { font-family: 'Rajdhani', sans-serif; font-size: 16px; font-weight: 700; color: #fff; }
.text-ref { font-size: 11px; color: #4b5563; font-weight: 500; }
</style>

<div class="content-wrapper scrutiny-hub">
    {{-- Header Area with Division Filter --}}
    <div class="hub-header">
        <h1 class="hub-title">Procurement Cases</h1>
        
        <div class="filter-bar">
            <div class="div-pill active" data-div="all">
                Show All
            </div>
            @foreach($unitNameMap as $id => $name)
                @php $pendingCount = $unitPendingMap[$id] ?? 0; @endphp
                @if($pendingCount > 0)
                <div class="div-pill" data-div="{{ $id }}">
                    {{ $name }}
                    <span class="red-dot"></span>
                </div>
                @endif
            @endforeach
        </div>
    </div>

    <div class="px-4 pb-5 mt-2">
        <table class="hub-master-table">
            <thead class="sticky-header">
                <tr>
                    <th style="width: 50px;"></th>
                    <th>Title / Description</th>
                    <th style="width: 120px;">Date</th>
                    <th style="width: 160px; text-align: right;">Amount</th>
                    <th style="width: 160px; text-align: center;">Status</th>
                    <th style="width: 100px; text-align: right;">Type</th>
                </tr>
            </thead>

            {{-- 1. PENDING ACTION SECTION --}}
            <tbody id="group-pending">
                <tr class="section-group-row" onclick="toggleGroup('pending')">
                    <td colspan="6" class="section-group-cell">
                        <div class="section-title-wrap">
                            <i class="fas fa-chevron-down toggle-icon" id="toggle-pending"></i>
                            <span class="section-title">Pending Action</span>
                            <span class="section-count" id="count-pending">{{ $pending->count() }}</span>
                        </div>
                    </td>
                </tr>
                @include('nrdi.purchase_cases.partial_list', ['cases' => $pending, 'type' => 'pending'])
            </tbody>

            {{-- 2. OPEN SECTION --}}
            <tbody id="group-open">
                <tr class="section-group-row" onclick="toggleGroup('open')">
                    <td colspan="6" class="section-group-cell">
                        <div class="section-title-wrap">
                            <i class="fas fa-chevron-down toggle-icon" id="toggle-open"></i>
                            <span class="section-title">Open</span>
                            <span class="section-count" id="count-open">{{ $open->count() }}</span>
                        </div>
                    </td>
                </tr>
                @include('nrdi.purchase_cases.partial_list', ['cases' => $open, 'type' => 'open'])
            </tbody>

            {{-- 3. CLOSE SECTION --}}
            <tbody id="group-closed">
                <tr class="section-group-row" onclick="toggleGroup('closed')">
                    <td colspan="6" class="section-group-cell">
                        <div class="section-title-wrap">
                            <i class="fas fa-chevron-down toggle-icon" id="toggle-closed"></i>
                            <span class="section-title">Close</span>
                            <span class="section-count" id="count-closed">{{ $closed->count() }}</span>
                        </div>
                    </td>
                </tr>
                @include('nrdi.purchase_cases.partial_list', ['cases' => $closed, 'type' => 'closed'])
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleGroup(type) {
    const group = document.getElementById('group-' + type);
    const rows = group.querySelectorAll('.hub-row, .empty-row');
    const toggle = document.getElementById('toggle-' + type);
    
    group.classList.toggle('collapsed');
    
    rows.forEach(row => {
        if (group.classList.contains('collapsed')) {
            row.style.setProperty('display', 'none', 'important');
        } else {
            // Respect the division filter if active
            const activePill = document.querySelector('.div-pill.active');
            const selectedDiv = activePill.getAttribute('data-div');
            
            if (selectedDiv === 'all' || row.classList.contains('div-row-' + selectedDiv) || row.classList.contains('empty-row')) {
                row.style.display = '';
            }
        }
    });
}

function updateDynamicCounts() {
    const groups = ['pending', 'open', 'closed'];
    groups.forEach(type => {
        const group = document.getElementById('group-' + type);
        const countSpan = document.getElementById('count-' + type);
        if (group && countSpan) {
            // Count rows belonging to this group that are NOT explicitly hidden by div filter
            // but we need to check if they are hidden by the group collapse too.
            // Actually, dynamic count should reflect what WOULD be visible if group were expanded.
            const totalVisibleInDiv = Array.from(group.querySelectorAll('.hub-row')).filter(r => {
                const activePill = document.querySelector('.div-pill.active');
                const selectedDiv = activePill.getAttribute('data-div');
                return selectedDiv === 'all' || r.classList.contains('div-row-' + selectedDiv);
            }).length;
            
            countSpan.textContent = totalVisibleInDiv;
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const pills = document.querySelectorAll('.div-pill');
    const rows = document.querySelectorAll('.hub-row');

    pills.forEach(pill => {
        pill.addEventListener('click', function() {
            pills.forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            const selectedDiv = this.getAttribute('data-div');
            
            // Iterate all groups
            ['pending', 'open', 'closed'].forEach(type => {
                const group = document.getElementById('group-' + type);
                const groupRows = group.querySelectorAll('.hub-row, .empty-row');
                const isCollapsed = group.classList.contains('collapsed');

                groupRows.forEach(row => {
                    if (selectedDiv === 'all') {
                        if (!isCollapsed) row.style.display = '';
                        else row.style.display = 'none';
                    } else {
                        if (!isCollapsed && (row.classList.contains('div-row-' + selectedDiv) || row.classList.contains('empty-row'))) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });
            });

            // Update Counts Dynamically
            updateDynamicCounts();
        });
    });
});
</script>
@endsection
