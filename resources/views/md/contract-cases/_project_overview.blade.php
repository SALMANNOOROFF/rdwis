<div class="cc-case-project-grid mb-3">
    <div class="cc-case-details">
        <div class="dg-sec-label"><i class="fas fa-file-contract"></i> Case Details</div>
        <div class="mb-3">
            <span class="cc-meta-label">Case ID & Date</span>
            <strong>#CC-{{ $case->ctc_id }}</strong><span class="text-muted ml-2">{{ $case->ctc_date ? \Carbon\Carbon::parse($case->ctc_date)->format('d M Y') : '—' }}</span>
        </div>
        <div class="mb-3">
            <span class="cc-meta-label">Division / Directorate</span>
            <strong>{{ $case->division_name }}</strong>
        </div>
        <div class="d-flex flex-wrap mb-3" style="gap: 18px;">
            <div><span class="cc-meta-label">Case Status</span><span class="badge badge-primary">{{ $case->ctc_status }}</span></div>
            <div><span class="cc-meta-label">Current Location</span><strong>{{ $case->current_office_name }}</strong></div>
        </div>
        <div class="cc-allocation-header"><span>Allocated Projects</span><span title="Active/current individuals assigned today; each person counted once">Already Hired</span></div>
        <div class="cc-allocation-list">
            @forelse($allocatedProjects as $allocation)
                <div class="cc-allocation-row" data-allocation-row="{{ $allocation->hed_id }}">
                    <div class="cc-allocation-name">
                        <button type="button" class="cc-project-select" data-project-panel="financial" data-head-id="{{ $allocation->hed_id }}"
                            data-panel-url="{{ route('contract-cases.project-panel', [$case->ctc_id, $allocation->hed_id]) }}" aria-pressed="false">
                            {{ $allocation->prj_code ?: $allocation->hed_code }}
                        </button>
                        <span class="small text-muted d-block">{{ $allocation->prj_name ?: $allocation->hed_name }}</span>
                    </div>
                    <strong class="cc-hired-count" title="Currently assigned active/current individuals">{{ $projectHiredCounts->get($allocation->hed_id, 0) }}</strong>
                    <div class="cc-project-actions">
                        @foreach(['financial' => ['fa-chart-pie', 'Financial Summary'], 'attachments' => ['fa-paperclip', 'Attachments'], 'milestones' => ['fa-flag-checkered', 'Milestones']] as $section => [$icon, $label])
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-project-panel="{{ $section }}"
                                data-head-id="{{ $allocation->hed_id }}" data-panel-url="{{ route('contract-cases.project-panel', [$case->ctc_id, $allocation->hed_id]) }}" aria-pressed="false">
                                <i class="fas {{ $icon }} mr-1"></i>{{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-muted my-3">{{ $case->is_hr_admin ? 'CSRF allocation — no financial head linked to this case yet.' : 'No project allocated to this case yet.' }}</p>
            @endforelse
        </div>
        <div class="small text-muted mt-2">Hired count: active/current individuals assigned as of {{ now()->format('d M Y') }}.</div>
    </div>
    <aside class="cc-project-panel" aria-label="Selected project details">
        <div id="contractProjectPanel" aria-live="polite" aria-busy="false">
            <span class="cc-meta-label">Project Financial Summary</span>
            <p class="text-muted my-3">{{ $allocatedProjects->isEmpty() ? 'A linked project is required to display its financial summary.' : 'Loading project financial summary…' }}</p>
        </div>
    </aside>
</div>
@push('scripts')
<script src="{{ asset('js/contract-case-projects.js') }}?v={{ filemtime(public_path('js/contract-case-projects.js')) }}" defer></script>
@endpush
