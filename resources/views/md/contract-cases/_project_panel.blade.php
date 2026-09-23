<div class="cc-project-panel-heading">
    <div>
        <span class="cc-meta-label">{{ ['financial' => 'Project Financial Summary', 'attachments' => 'Project Attachments', 'milestones' => 'Project Milestones'][$section] }}</span>
        <strong>{{ $allocation->prj_code ?: $allocation->hed_code }}</strong>
        <div class="small text-muted">{{ $allocation->prj_name ?: $allocation->hed_name }}</div>
    </div>
    @if($allocation->hed_prj_id)
        <a href="{{ route('projects.financial_view', $allocation->hed_prj_id) }}{{ $section === 'attachments' ? '#tab-docs' : ($section === 'milestones' ? '#tab-milestones' : '') }}"
           target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary cc-full-report">Full report <i class="fas fa-external-link-alt ml-1"></i></a>
    @endif
</div>
@if($section === 'financial')
    <div class="cc-financial-values">
        @foreach([
            'allocation' => 'Total Allocation',
            'rdw_share' => 'RDW Share',
            'pcc_share' => 'Project Cost Centre Share',
            'acc_received' => 'Received',
            'acc_expenditure' => 'Expenditure',
            'acc_commitments' => 'Commitments',
            'acc_in_process' => 'In Process',
            'balance' => 'Balance',
            'available' => 'Available Funds',
            'can_be_spent' => 'Can Be Spent',
        ] as $field => $label)
            <div class="cc-financial-row {{ in_array($field, ['available', 'can_be_spent']) ? 'cc-financial-total' : '' }}">
                <span>{{ $label }}</span>
                <strong class="{{ ($financial->$field ?? 0) < 0 ? 'text-danger' : '' }}">{{ number_format($financial->$field ?? 0, 2) }}</strong>
            </div>
        @endforeach
    </div>
    <div class="small text-muted mt-2">Amounts in PKR · Selected project only</div>
@elseif($section === 'attachments')
    @forelse($attachments as $attachment)
        <a class="cc-project-document" href="{{ route('universal.attachment.view', ['module' => 'prj', 'id' => $attachment->jat_id]) }}"
           target="_blank" rel="noopener" data-project-document data-document-title="{{ $attachment->jat_type ?: 'Project attachment' }}">
            <i class="fas fa-paperclip mr-2"></i>{{ $attachment->jat_type ?: 'Project attachment' }}<i class="fas fa-eye ml-auto"></i>
        </a>
    @empty
        <p class="text-muted my-3">No project attachments uploaded.</p>
    @endforelse
@else
    <div class="cc-milestone-list">
        @forelse($milestones as $milestone)
            <div class="cc-milestone-item">
                <strong>{{ $milestone->msn_desc }}</strong>
                <div class="d-flex justify-content-between small mt-1">
                    <span>{{ $milestone->msn_status ?: 'No status' }}</span>
                    <span>Target: {{ $milestone->msn_targetdt ? \Carbon\Carbon::parse($milestone->msn_targetdt)->format('d M Y') : '—' }}</span>
                </div>
            </div>
        @empty
            <p class="text-muted my-3">No milestones recorded for this project.</p>
        @endforelse
    </div>
@endif
