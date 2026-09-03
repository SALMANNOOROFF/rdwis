@extends('welcome')

@section('content')
<div class="content-wrapper pt-3" style="background: #f1f5f9; min-height: 100vh;">
    <div class="container-fluid px-lg-4">

        {{-- TOP HEADER BAR --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <div>
                <h2 class="m-0 font-weight-bold text-dark" style="font-family:'Rajdhani',sans-serif; letter-spacing:0.5px; font-size: 26px;">
                    <i class="fas fa-ticket-alt text-primary mr-2"></i> TICKET {{ $ticket->tkt_ref }}
                </h2>
                <p class="text-muted small mb-0 mt-0.5">
                    Filed on {{ $ticket->tkt_created_at->format('d-M-Y \a\t H:i') }} ({{ $ticket->tkt_created_at->diffForHumans() }})
                </p>
            </div>
            <div>
                <a href="{{ route('support.tickets.index') }}" class="btn btn-outline-secondary btn-sm font-weight-bold px-3 py-1.5 rounded-pill">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Helpdesk
                </a>
            </div>
        </div>

        {{-- FLASH MESSAGES --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-3" role="alert" style="border-radius: 10px; background: #dcfce7; color: #15803d;">
                <i class="fas fa-check-circle mr-2"></i> <strong>Success!</strong> {{ session('success') }}
                <button type="button" class="close text-success" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-3" role="alert" style="border-radius: 10px; background: #fee2e2; color: #b91c1c;">
                <i class="fas fa-exclamation-triangle mr-2"></i> <strong>Error:</strong> {{ session('error') }}
                <button type="button" class="close text-danger" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            {{-- LEFT COLUMN: TICKET DETAILS & CONVERSATION TIMELINE --}}
            <div class="col-lg-8">
                {{-- Ticket Overview Card --}}
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: #ffffff;">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge badge-primary px-3 py-1 font-weight-bold" style="font-size: 12px; background: #0284c7;">
                                {{ $ticket->tkt_ref }}
                            </span>
                            @php
                                $badgeClass = match($ticket->tkt_type) {
                                    'Complaint' => 'badge-danger',
                                    'Suggestion' => 'badge-success',
                                    'Bug Report' => 'badge-warning',
                                    default => 'badge-info'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} font-weight-bold px-2.5 py-1" style="font-size: 11px;">
                                {{ strtoupper($ticket->tkt_type) }}
                            </span>
                            <span class="badge badge-light border text-dark font-weight-bold" style="font-size: 11px;">
                                {{ $ticket->tkt_module }}
                            </span>
                            @if($ticket->tkt_is_apex)
                                <span class="badge badge-warning text-dark font-weight-bold" style="font-size: 11px;">
                                    <i class="fas fa-crown mr-1"></i> APEX DIRECTIVE
                                </span>
                            @endif
                        </div>
                        <div>
                            @php
                                $stClass = match($ticket->tkt_status) {
                                    'Open' => 'badge-warning text-dark',
                                    'In Progress' => 'badge-primary text-white',
                                    'Returned' => 'badge-danger text-white',
                                    'Resolved', 'Closed' => 'badge-success text-white',
                                    default => 'badge-secondary'
                                };
                            @endphp
                            <span class="badge {{ $stClass }} font-weight-bold px-3 py-1.5" style="font-size: 12px; border-radius: 6px;">
                                {{ $ticket->tkt_status }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <h4 class="font-weight-bold text-dark mb-2" style="font-size: 18px;">
                            {{ $ticket->tkt_subject }}
                        </h4>
                        <div class="text-muted small mb-3">
                            Initiated by <strong>{{ $ticket->tkt_user_name }}</strong> ({{ $ticket->tkt_unt_name ?? $ticket->tkt_user_role }})
                        </div>
                        <div class="p-3 bg-light rounded border text-dark mb-3" style="white-space: pre-wrap; font-size: 14px; line-height: 1.6;">
                            {{ $ticket->tkt_description }}
                        </div>

                        @if($ticket->tkt_attachment)
                            <div class="mt-2">
                                <a href="{{ \App\Facades\FileStorage::url($ticket->tkt_attachment) }}" target="_blank" class="btn btn-outline-primary btn-sm font-weight-bold" style="border-radius: 6px;">
                                    <i class="fas fa-paperclip mr-1"></i> View / Download Primary Attachment
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Activity Timeline --}}
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: #ffffff;">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="mb-0 font-weight-bold text-dark" style="font-size: 15px;">
                            <i class="fas fa-history text-primary mr-2"></i> Activity Log & Audit Trail
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        @forelse($ticket->activities as $act)
                            @php
                                $isResolverAct = in_array($act->act_action, ['Resolved', 'In Progress', 'Returned']);
                                $bg = $isResolverAct ? 'background:#ecfdf5; border-left:4px solid #10b981;' : 'background:#f8fafc; border-left:4px solid #0284c7;';
                            @endphp
                            <div class="p-3 rounded mb-3 border shadow-2xs" style="{{ $bg }}">
                                <div class="d-flex justify-content-between align-items-center mb-1.5">
                                    <span class="font-weight-bold text-dark" style="font-size: 13px;">
                                        <i class="fas fa-user-circle mr-1 text-primary"></i> {{ $act->act_user_name }} 
                                        <span class="text-muted font-weight-normal">({{ $act->act_user_role }})</span>
                                    </span>
                                    <span class="badge badge-light border text-muted" style="font-size: 10.5px;">
                                        {{ $act->act_action }} • {{ $act->act_created_at->format('d-M-Y H:i') }}
                                    </span>
                                </div>
                                <div class="text-dark small" style="white-space: pre-wrap; font-size: 13px;">
                                    {{ $act->act_message }}
                                </div>
                                @if($act->act_attachment)
                                    <div class="mt-2">
                                        <a href="{{ \App\Facades\FileStorage::url($act->act_attachment) }}" target="_blank" class="btn btn-xs btn-outline-secondary font-weight-bold" style="font-size: 11px;">
                                            <i class="fas fa-paperclip mr-1"></i> Attachment
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center text-muted py-3">No activity recorded yet.</div>
                        @endforelse

                        {{-- Reply Form --}}
                        <div class="mt-4 pt-3 border-top">
                            <h6 class="font-weight-bold text-dark mb-2" style="font-size: 13.5px;">
                                <i class="fas fa-reply mr-1 text-primary"></i> Post Message / Clarification
                            </h6>
                            <form action="{{ route('support.tickets.reply', $ticket->tkt_id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group mb-2">
                                    <textarea name="message" rows="3" class="form-control" placeholder="Type your comment, update, or clarification here..." required style="border-radius: 8px; font-size: 13px;"></textarea>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <input type="file" name="attachment" style="font-size: 12px;">
                                    <button type="submit" class="btn btn-primary font-weight-bold px-4 py-1.5 shadow-xs" style="border-radius: 6px; font-size: 13px; background: #0284c7;">
                                        <i class="fas fa-paper-plane mr-1"></i> Send Reply
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: STATUS DESK (FOR RESOLVERS) OR SUMMARY (FOR USERS) --}}
            <div class="col-lg-4">
                @if($isResolver)
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: #ffffff; border-top: 4px solid #10b981 !important;">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h5 class="mb-0 font-weight-bold text-dark" style="font-size: 15px;">
                                <i class="fas fa-shield-alt text-success mr-2"></i> SO IT / Resolver Action Desk
                            </h5>
                        </div>
                        <div class="card-body p-3">
                            <form action="{{ route('support.tickets.status', $ticket->tkt_id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group mb-3">
                                    <label class="small text-muted font-weight-bold text-uppercase">Resolution Action</label>
                                    <select name="status" class="form-control font-weight-bold" required style="border-radius: 6px; font-size: 13px; height: 40px;">
                                        <option value="In Progress" {{ $ticket->tkt_status == 'In Progress' ? 'selected' : '' }}>⏳ Mark In Progress</option>
                                        <option value="Returned" {{ $ticket->tkt_status == 'Returned' ? 'selected' : '' }}>⚠️ Return to User (Needs Info)</option>
                                        <option value="Resolved" {{ in_array($ticket->tkt_status, ['Resolved', 'Open']) ? 'selected' : '' }}>✅ Resolve Ticket (Completed)</option>
                                        <option value="Rejected">❌ Reject / Close</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="small text-muted font-weight-bold text-uppercase">Attach Solution File (Optional)</label>
                                    <input type="file" name="attachment" class="form-control-file" style="font-size: 12px;">
                                </div>
                                <div class="form-group mb-3">
                                    <label class="small text-muted font-weight-bold text-uppercase">Resolution Remarks / Decision Note <span class="text-danger">*</span></label>
                                    <textarea name="resolution_note" rows="4" class="form-control text-dark" placeholder="State how the ticket was solved or what clarification is needed..." required style="border-radius: 8px; font-size: 13px;">{{ $ticket->tkt_resolution_note }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-success btn-block font-weight-bold py-2 shadow-sm" style="border-radius: 8px; font-size: 13.5px;">
                                    <i class="fas fa-check-circle mr-1"></i> UPDATE TICKET STATUS
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: #ffffff;">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h5 class="mb-0 font-weight-bold text-dark" style="font-size: 15px;">
                                <i class="fas fa-info-circle text-primary mr-2"></i> Resolution Summary
                            </h5>
                        </div>
                        <div class="card-body p-3">
                            @if(in_array($ticket->tkt_status, ['Resolved', 'Closed']))
                                <div class="text-center py-3">
                                    <i class="fas fa-check-circle text-success fa-3x mb-2"></i>
                                    <h6 class="font-weight-bold text-success">Ticket Resolved</h6>
                                    <p class="text-muted small mb-2">
                                        Solved by <strong>{{ $ticket->tkt_solved_by_name ?? 'SO IT' }}</strong> on {{ $ticket->tkt_solved_at ? $ticket->tkt_solved_at->format('d-M-Y H:i') : $ticket->tkt_updated_at->format('d-M-Y H:i') }}
                                    </p>
                                    @if($ticket->tkt_resolution_note)
                                        <div class="p-3 bg-light border rounded text-dark small text-left mt-3">
                                            <strong>Resolution Note:</strong><br>
                                            {{ $ticket->tkt_resolution_note }}
                                        </div>
                                    @endif
                                </div>
                            @elseif($ticket->tkt_status === 'Returned')
                                <div class="alert alert-warning border-0 p-3 mb-0" style="border-radius: 8px;">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> <strong>Action Required:</strong> SO IT has requested additional clarification from you. Please use the reply box to clarify.
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-hourglass-half text-warning fa-2x mb-2"></i>
                                    <h6 class="font-weight-bold text-dark">Under Review</h6>
                                    <p class="text-muted small mb-0">SO IT Support Desk is currently processing this ticket.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
