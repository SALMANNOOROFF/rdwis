@extends('welcome')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap');

.under-dev-page {
    font-family: 'Inter', sans-serif;
    min-height: calc(100vh - 120px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 30px 15px;
}

.rajdhani {
    font-family: 'Rajdhani', sans-serif;
}

.dev-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
    max-width: 620px;
    width: 100%;
    padding: 40px 30px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.dev-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: linear-gradient(90deg, #0284c7, #06b6d4, #10b981);
}

.icon-wrapper {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(2, 132, 199, 0.08);
    color: #0284c7;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 34px;
    margin-bottom: 20px;
    border: 2px dashed rgba(2, 132, 199, 0.3);
}

.dev-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #fef3c7;
    color: #b45309;
    border: 1px solid #fde68a;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.5px;
    margin-bottom: 15px;
}
</style>

<div class="content-wrapper">
    <div class="container-fluid py-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary rajdhani font-weight-bold px-3" style="border-radius: 8px;">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
            <span class="text-muted small rajdhani font-weight-bold">
                <i class="fas fa-shield-alt mr-1 text-primary"></i> RDWIS 2.0 PERSONNEL SYSTEM
            </span>
        </div>

        <div class="under-dev-page">
            <div class="dev-card">
                <div class="icon-wrapper">
                    <i class="fas fa-tools"></i>
                </div>

                <div>
                    <span class="dev-badge rajdhani">
                        <i class="fas fa-hammer"></i> MODULE UNDER DEVELOPMENT
                    </span>
                </div>

                <h2 class="rajdhani font-weight-bold text-dark mb-1" style="font-size: 28px;">
                    {{ $title ?? 'Module Under Development' }}
                </h2>

                <p class="text-primary font-weight-bold rajdhani mb-3" style="font-size: 15px; letter-spacing: 0.5px;">
                    <i class="fas fa-id-badge mr-1"></i> {{ $category ?? 'Human Resources Management' }}
                </p>

                <p class="text-muted mb-4" style="font-size: 14px; line-height: 1.6;">
                    {{ $description ?? 'This module is currently being configured and integrated into the RDWIS 2.0 platform. All records, authorizations, and roster services will be accessible shortly.' }}
                </p>

                <div class="p-3 mb-4 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0; font-size: 12.5px; color: #475569;">
                    <i class="fas fa-info-circle text-info mr-1"></i> For priority access or data requirements for this unit, please coordinate with HR Directorate.
                </div>

                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('dashboard') }}" class="btn btn-primary rajdhani font-weight-bold px-4 py-2" style="border-radius: 8px;">
                        <i class="fas fa-home mr-1"></i> Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
