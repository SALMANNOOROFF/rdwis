@extends('welcome')

@section('content')
<div class="content-wrapper">
    <div class="content-header pb-2">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h1 class="m-0 font-weight-bold" style="color:var(--rd-text1); letter-spacing: -0.5px;">
                        <i class="fas fa-book mr-2 text-warning"></i> Books Inventory
                    </h1>
                    <p class="text-muted mb-0 small">Departmental libraries and borrowing requests</p>
                </div>
                <div>
                    <a href="{{ route('training.books.create') }}" class="btn btn-warning btn-sm px-3 shadow-sm font-weight-bold" style="border-radius:6px;">
                        <i class="fas fa-plus-circle mr-2"></i> Buy New Book
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            {{-- Search & Filters --}}
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="d-flex bg-dark p-2 border border-secondary" style="border-radius: 12px; background: rgba(0,0,0,0.2) !important;">
                        <div class="input-group input-group-sm mr-2" style="max-width: 300px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-transparent border-0 text-muted"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" class="form-control bg-transparent border-0 text-white" placeholder="Search by title, author or ISBN...">
                        </div>
                        <select class="form-control form-control-sm bg-dark border-secondary text-white mr-2" style="width: 150px;">
                            <option>All Departments</option>
                            <option>IT</option>
                            <option>PMO</option>
                            <option>Strategy</option>
                        </select>
                        <button class="btn btn-outline-secondary btn-sm"><i class="fas fa-filter mr-1"></i> Filter</button>
                    </div>
                </div>
            </div>

            {{-- Books List (Compact) --}}
            <div class="card shadow-sm border-0" style="background:var(--rd-surface); border-radius:12px; border: 1px solid rgba(255,255,255,0.05) !important;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 text-white" style="border-collapse: separate; border-spacing: 0;">
                            <thead>
                                <tr class="extra-small text-uppercase tracking-wider text-muted" style="background: rgba(0,0,0,0.2);">
                                    <th class="py-3 pl-4 border-0" style="width: 50px;">#</th>
                                    <th class="py-3 border-0">Book Details</th>
                                    <th class="py-3 border-0">Department</th>
                                    <th class="py-3 border-0" style="width: 200px;">Availability</th>
                                    <th class="py-3 border-0">ISBN</th>
                                    <th class="py-3 pr-4 border-0 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($books as $book)
                                    <tr class="book-row" style="transition: all 0.2s ease;">
                                        <td class="py-3 pl-4 border-top border-secondary align-middle">
                                            <div class="icon-box-sm" style="background: {{ $book['color'] }}15; color: {{ $book['color'] }}; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">
                                                <i class="{{ $book['icon'] }}"></i>
                                            </div>
                                        </td>
                                        <td class="py-3 border-top border-secondary align-middle text-white">
                                            <div class="font-weight-bold mb-0" style="font-size: 0.95rem;">{{ $book['title'] }}</div>
                                            <div class="extra-small text-muted" style="font-size: 0.7rem;">by {{ $book['author'] }}</div>
                                        </td>
                                        <td class="py-3 border-top border-secondary align-middle">
                                            <span class="badge badge-dark border border-secondary extra-small px-2 py-1" style="color:var(--rd-text2)">{{ $book['department'] }}</span>
                                        </td>
                                        <td class="py-3 border-top border-secondary align-middle">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1 mr-3">
                                                    @php $percent = ($book['available'] / $book['copies']) * 100; @endphp
                                                    <div class="progress" style="height: 4px; background: rgba(255,255,255,0.05); border-radius: 2px;">
                                                        <div class="progress-bar {{ $percent > 20 ? 'bg-warning' : 'bg-danger' }}" role="progressbar" style="width: {{ $percent }}%"></div>
                                                    </div>
                                                </div>
                                                <span class="extra-small font-weight-bold {{ $book['available'] > 0 ? 'text-warning' : 'text-danger' }}">
                                                    {{ $book['available'] }} / {{ $book['copies'] }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="py-3 border-top border-secondary align-middle">
                                            <code class="text-info extra-small">{{ $book['isbn'] }}</code>
                                        </td>
                                        <td class="py-3 pr-4 border-top border-secondary align-middle text-right">
                                            <button class="btn btn-warning btn-xs px-3 btn-borrow py-1 font-weight-bold" data-id="{{ $book['id'] }}" data-name="{{ $book['title'] }}" style="border-radius: 4px; font-size: 0.75rem;">
                                                <i class="fas fa-hand-holding mr-1"></i> Borrow
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

{{-- Borrow Modal --}}
<div class="modal fade" id="borrowModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="background: #1a1a27; border-radius: 20px;">
            <div class="modal-header border-0 text-center d-block pt-4">
                <div class="icon-circle mb-3 bg-warning-soft mx-auto" style="width:60px; height:60px; background:rgba(243,156,18,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-book-reader text-warning fa-2x"></i>
                </div>
                <h4 class="modal-title font-weight-bold w-100 text-white" id="modalBookName">Clean Code</h4>
                <p class="text-muted small mt-2">Request to borrow from holding department</p>
            </div>
            <div class="modal-body px-4 pb-4">
                <div id="borrowForm">
                    <div class="form-group">
                        <label class="extra-small text-uppercase text-muted">Borrowing Duration (Days)</label>
                        <select class="form-control bg-dark border-secondary text-white">
                            <option>7 Days</option>
                            <option>15 Days</option>
                            <option>30 Days</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="extra-small text-uppercase text-muted">Justification</label>
                        <textarea class="form-control bg-dark border-secondary text-white" rows="2" placeholder="e.g. Research for upcoming project..."></textarea>
                    </div>
                    <button type="button" id="submitBorrow" class="btn btn-warning btn-block py-2 font-weight-bold" style="border-radius:10px;">
                        Send Borrow Request
                    </button>
                </div>
                <div id="borrowSuccess" class="d-none animated fadeIn text-center py-3">
                    <div class="alert alert-success bg-success-soft border-0 mb-4" style="background:rgba(40,167,69,0.1); color:#28a745;">
                        <i class="fas fa-check-circle mr-2"></i> Request Sent Successfully!
                    </div>
                    <p class="text-muted small">Holding department has been notified. You will receive a notification once approved for pickup.</p>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<style>
    :root {
        --rd-surface: #1e1e2d;
        --rd-text1: #ffffff;
        --rd-text2: #a2a3b7;
    }
    .extra-small { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 4px; }
    .book-row:hover { 
        background: rgba(255,255,255,0.02) !important;
    }
    .animated { animation-duration: 0.5s; }
</style>

<script>
$(document).ready(function() {
    $('.btn-borrow').click(function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        $('#modalBookName').text(name);
        $('#borrowForm').removeClass('d-none');
        $('#borrowSuccess').addClass('d-none');
        $('#borrowModal').modal('show');
    });

    $('#submitBorrow').click(function() {
        $(this).html('<i class="fas fa-spinner fa-spin mr-2"></i> Sending...');
        setTimeout(() => {
            $('#borrowForm').addClass('d-none');
            $('#borrowSuccess').removeClass('d-none');
        }, 1500);
    });
});
</script>
@endsection
