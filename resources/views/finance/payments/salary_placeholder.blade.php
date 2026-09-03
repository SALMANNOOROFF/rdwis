@extends('welcome')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap');

    .salary-placeholder {
        font-family: 'Inter', sans-serif;
        background: var(--rd-bg) !important;
        min-height: 85vh;
        color: var(--rd-text1);
        padding-top: 40px;
        padding-bottom: 50px;
    }

    .rajdhani {
        font-family: 'Rajdhani', sans-serif;
        letter-spacing: 0.5px;
    }

    .card-cyber {
        background: var(--rd-surface);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        max-width: 650px;
        margin: 0 auto;
    }
</style>

<div class="content-wrapper salary-placeholder px-4 text-center">
    <div class="card card-cyber p-5">
        <div class="mb-4">
            <div class="mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; border-radius: 50%; background: rgba(168, 85, 247, 0.12); color: #c084fc; font-size: 36px; border: 1px solid rgba(168, 85, 247, 0.3);">
                <i class="fas fa-users-cog"></i>
            </div>
            <span class="badge badge-secondary px-3 py-1 rajdhani" style="font-size: 13px;">FUTURE MODULE</span>
            <h2 class="text-white rajdhani font-weight-bold mt-2 mb-2">Salary Orders (HR Payroll) Module</h2>
            <p class="text-muted small">
                Salary Order commitments (<code>cmt_type = 'Sa'</code>) are linked to monthly payroll requisitions in <code>hr.salreqs</code> and <code>fin.salorders</code>. This module will be activated in a subsequent release.
            </p>
        </div>

        <div class="pt-3 border-top border-secondary">
            <a href="{{ route('fin.commitments.landing') }}" class="btn btn-outline-info px-4 rajdhani font-weight-bold">
                <i class="fas fa-arrow-left mr-2"></i> Back to Commitments Hub
            </a>
        </div>
    </div>
</div>
@endsection
