@extends('welcome')

@section('content')
<div class="content-wrapper">
    <section class="content-header pb-2">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h1 class="font-weight-bold" style="color:var(--rd-text1);">Raise Training Case</h1>
                    <p class="text-muted mb-0 small">Initiate a new training request (Domestic, In-House, or Online)</p>
                </div>
                <div class="header-actions">
                    <a href="{{ route('training.books.create') }}" class="btn btn-primary btn-sm mr-2 shadow-sm">
                        <i class="fas fa-book mr-1"></i> Raise Book Case
                    </a>
                    <a href="{{ route('training.index') }}" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-list mr-1"></i> View Queue
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0" style="background:var(--rd-surface); border-radius:12px;">
                <div class="card-body p-4">
                    <form id="trainingForm" action="{{ route('training.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="extra-small text-uppercase text-muted">Training Title / Topic</label>
                                <input type="text" name="title" class="form-control form-control-sm bg-dark border-secondary text-white" placeholder="e.g. Advanced Cybersecurity Workshop" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="extra-small text-uppercase text-muted">Training Category</label>
                                <select name="category" class="form-control form-control-sm bg-dark border-secondary text-white">
                                    <option value="in-house">In-House Physical</option>
                                    <option value="domestic">Domestic Physical (Traveling)</option>
                                    <option value="online">Local / International Online</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="extra-small text-uppercase text-muted">Hiring Justification & Purpose</label>
                                <textarea name="justification" class="form-control form-control-sm bg-dark border-secondary text-white" rows="4" placeholder="Explain the requirement..."></textarea>
                            </div>
                        </div>
                        <div class="text-right mt-4">
                            <button type="submit" class="btn btn-info btn-sm px-4 font-weight-bold shadow-sm">
                                <i class="fas fa-paper-plane mr-2"></i> Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<style>
    :root {
        --rd-surface: #1e1e2d;
    }
    .extra-small { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.5px; }
</style>
@endsection
