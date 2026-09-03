@extends('welcome')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap');

    .commitments-landing {
        font-family: 'Inter', sans-serif;
        background: var(--rd-bg) !important;
        min-height: 85vh;
        color: var(--rd-text1);
        padding-top: 25px;
        padding-bottom: 50px;
    }

    .rajdhani {
        font-family: 'Rajdhani', sans-serif;
        letter-spacing: 0.5px;
    }

    .hub-card {
        background: var(--rd-surface);
        border: 1px solid rgba(255, 255, 255, 0.07);
        border-radius: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none !important;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
    }

    .hub-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: transparent;
        transition: all 0.3s ease;
    }

    .hub-card.active-card:hover {
        transform: translateY(-5px);
        border-color: rgba(0, 191, 255, 0.4);
        box-shadow: 0 12px 30px rgba(0, 191, 255, 0.12);
    }

    .hub-card.active-card:hover::before {
        background: linear-gradient(90deg, #00BFFF, #3b82f6);
    }

    .hub-card.disabled-card {
        opacity: 0.75;
        border-style: dashed;
        cursor: not-allowed;
    }

    .icon-box {
        width: 68px;
        height: 68px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
    }

    .icon-purchase {
        background: rgba(0, 191, 255, 0.12);
        color: #00BFFF;
        border: 1px solid rgba(0, 191, 255, 0.25);
    }

    .icon-salary {
        background: rgba(147, 51, 234, 0.12);
        color: #a855f7;
        border: 1px solid rgba(147, 51, 234, 0.25);
    }

    .stat-pill {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 8px;
        padding: 6px 14px;
        font-size: 13px;
    }
</style>

<div class="content-wrapper commitments-landing px-4">
    <div class="mb-5">
        <span class="badge badge-primary px-3 py-1 mb-2 rajdhani" style="font-size: 12px; letter-spacing: 1px;">FINANCE DISBURSEMENT MODULE</span>
        <h2 class="text-white rajdhani font-weight-bold mb-1 display-5">Financial Commitments & Disbursements</h2>
        <p class="text-muted mb-0">Manage and settle post-approval commitments for sanctioned procurement cases and payroll requisitions.</p>
    </div>

    <div class="row g-4">
        <!-- 1. Purchase Cases Commitments Card -->
        <div class="col-lg-6 mb-4">
            <a href="{{ route('fin.payments.index') }}" class="hub-card active-card p-4">
                <div>
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div class="icon-box icon-purchase">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <span class="badge badge-success px-3 py-2 rajdhani font-weight-bold" style="font-size: 13px;">ACTIVE MODULE</span>
                    </div>
                    
                    <h3 class="text-white rajdhani font-weight-bold mb-2">Purchase Case Commitments</h3>
                    <p class="text-muted small mb-4" style="line-height: 1.6;">
                        View and settle open financial liabilities created from approved Purchase Cases (Material, Licenses, Stationery, Works, Services, etc.). Record multi-installment disbursements, tax deductions, and update commitment remarks.
                    </p>
                </div>

                <div class="pt-3 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                    <div class="d-flex gap-2">
                        <div class="stat-pill">
                            <span class="text-warning font-weight-bold mr-1">{{ number_format($openPurchaseCount) }}</span>
                            <span class="text-muted">Awaited</span>
                        </div>
                        <div class="stat-pill">
                            <span class="text-success font-weight-bold mr-1">{{ number_format($closedPurchaseCount) }}</span>
                            <span class="text-muted">Paid / Closed</span>
                        </div>
                    </div>
                    <span class="text-info rajdhani font-weight-bold d-flex align-items-center">
                        Open Hub <i class="fas fa-arrow-right ml-2"></i>
                    </span>
                </div>
            </a>
        </div>

        <!-- 2. Salary Orders Commitments Card (Stub / Future) -->
        <div class="col-lg-6 mb-4">
            <div class="hub-card disabled-card p-4">
                <div>
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div class="icon-box icon-salary">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <span class="badge badge-secondary px-3 py-2 rajdhani font-weight-bold" style="font-size: 12px; background: rgba(255,255,255,0.1);">DEFERRED / UNDER DEV</span>
                    </div>
                    
                    <h3 class="text-white rajdhani font-weight-bold mb-2 text-muted">Salary Orders (HR Payroll)</h3>
                    <p class="text-muted small mb-4" style="line-height: 1.6;">
                        Disbursements against monthly payroll requisitions (<code class="text-muted">fin.salorders</code> &amp; <code class="text-muted">cmt_type = 'Sa'</code>). Single-shot settlement flow closing salary orders and employee requisitions simultaneously.
                    </p>
                </div>

                <div class="pt-3 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                    <div class="stat-pill">
                        <span class="text-muted">Tied to HR Salreqs Subsystem</span>
                    </div>
                    <span class="text-muted rajdhani font-weight-bold">
                        <i class="fas fa-lock mr-1"></i> Disabled
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
