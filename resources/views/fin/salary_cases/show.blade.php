{{-- resources/views/fin/salary_cases/show.blade.php --}}
@extends('welcome')

@section('content')
<div class="content-wrapper px-3 py-3" style="background: #f1f5f9; min-height: 90vh;">
    {{-- Top Case Banner Bar --}}
    <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px; background: #ffffff;">
        <div class="card-body py-2.5 px-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
                <div class="d-flex align-items-center flex-wrap" style="gap: 12px;">
                    <a href="{{ route('salary_cases.index') }}" class="btn btn-sm btn-outline-secondary font-weight-bold" style="border-radius: 6px;">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Cases
                    </a>
                    <h4 class="mb-0 font-weight-bold text-dark rajdhani" style="font-size: 19px; letter-spacing: 0.5px;">
                        <span class="text-primary">{{ $case->soc_minute_no ?: 'Minute --' }}</span> &bull; Case of {{ \Carbon\Carbon::createFromFormat('Y-m', $case->soc_month)->format('F Y') }}
                    </h4>
                    <span class="badge badge-light border text-dark font-weight-bold px-2 py-1" style="font-size: 11.5px;">
                        Month: {{ \Carbon\Carbon::createFromFormat('Y-m', $case->soc_month)->format('F Y') }}
                    </span>
                </div>

                <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                    @if($isFinance)
                        <a href="{{ route('salary_cases.print', $case->soc_id) }}" target="_blank" class="btn btn-sm btn-dark font-weight-bold" style="border-radius: 6px;">
                            <i class="fas fa-print mr-1"></i> Print Minute
                        </a>
                        <a href="{{ route('salary_cases.export_word', $case->soc_id) }}" class="btn btn-sm btn-outline-success font-weight-bold" style="border-radius: 6px;">
                            <i class="fas fa-file-word mr-1"></i> Save as Word (.doc)
                        </a>
                    @endif
                    @php
                        $rawStatus = trim($case->soc_status ?? '');
                        $displayStatus = in_array(strtolower($rawStatus), ['draft', 'pending action']) ? 'Pending Action' : $rawStatus;
                        $statusBadge = match(strtolower($displayStatus)) {
                            'pending action', 'pending' => 'badge-warning text-dark font-weight-bold',
                            'under review', 'under approval' => 'badge-info text-white font-weight-bold',
                            'approved' => 'badge-success font-weight-bold',
                            'returned', 'cancelled', 'rejected' => 'badge-danger font-weight-bold',
                            default => 'badge-secondary font-weight-bold',
                        };
                    @endphp
                    <span class="badge {{ $statusBadge }} px-3 py-1.5" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.4px;">
                        Status: {{ $displayStatus }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm py-2 px-3 small border-0 mb-3" style="background: #f0fdf4; color: #16a34a; border-left: 4px solid #16a34a !important;">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close text-success" data-dismiss="alert">&times;</button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm py-2 px-3 small border-0 mb-3" style="background: #fef2f2; color: #dc2626; border-left: 4px solid #dc2626 !important;">
            <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
            <button type="button" class="close text-danger" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{-- Main Dual-Pane Row --}}
    <div class="row">
        {{-- ========================================================= --}}
        {{-- LEFT PANE: OFFICIAL MINUTE SHEET (65% Width)             --}}
        {{-- ========================================================= --}}
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card border-0 shadow-sm" style="border-radius: 8px; background: #ffffff;">
                <div class="card-header bg-white border-bottom py-2.5 px-4 d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
                    <span class="text-uppercase font-weight-bold rajdhani" style="font-size: 13.5px; letter-spacing: 1px; color: #475569;">
                        <i class="fas fa-file-alt text-primary mr-1.5"></i> OFFICIAL MINUTE SHEET &bull; CASE OF {{ strtoupper(\Carbon\Carbon::createFromFormat('Y-m', $case->soc_month)->format('F Y')) }}
                    </span>
                    @if($canEdit)
                        <div class="d-flex align-items-center" style="gap: 8px;">
                            <button type="button" class="btn btn-sm btn-outline-info font-weight-bold shadow-sm px-2.5 py-1" style="font-size: 11.5px; border-radius: 5px;" onclick="if(confirm('Re-sync table and paragraphs with all salary orders for {{ $case->soc_month }}?')) { document.getElementById('syncOrdersForm').submit(); }" title="Fetch all salary orders of this month into this Minute Case">
                                <i class="fas fa-sync-alt mr-1"></i> Re-sync from Orders
                            </button>
                            <span class="badge badge-warning text-dark font-weight-bold px-2 py-1" style="font-size: 11px;">
                                <i class="fas fa-pencil-alt mr-1"></i> Editable Draft
                            </span>
                            <button type="submit" form="draftMinuteForm" class="btn btn-sm btn-primary font-weight-bold shadow-sm px-3 py-1" style="font-size: 12px; border-radius: 5px; background: #0f172a; border-color: #0f172a;">
                                <i class="fas fa-save mr-1.5 text-warning"></i> Save as Draft
                            </button>
                        </div>
                    @endif
                </div>

                <div class="card-body p-4 p-md-5" style="background: #ffffff; color: #0f172a; font-family: 'Times New Roman', Times, serif; font-size: 15px; line-height: 1.6;">
                    @if($canEdit)
                        <form id="draftMinuteForm" method="POST" action="{{ route('salary_cases.update_draft', $case->soc_id) }}">
                            @csrf
                            @method('PATCH')
                    @endif

                    {{-- Minute Header --}}
                    <div class="text-center mb-4">
                        @if($canEdit)
                            <div class="d-inline-block">
                                <label class="small text-muted d-block font-sans-serif mb-0" style="font-family: Arial, sans-serif; font-size: 11px;">EDIT MINUTE NUMBER</label>
                                <input type="text" name="soc_minute_no" class="form-control form-control-sm text-center font-weight-bold" value="{{ $case->soc_minute_no }}" style="font-family: 'Times New Roman', serif; font-size: 18px; width: 180px; text-decoration: underline; border: 1px dashed #94a3b8; margin: 0 auto;">
                            </div>
                        @else
                            <h4 class="font-weight-bold mb-0" style="font-size: 20px; text-decoration: underline;">
                                {{ $case->soc_minute_no }}
                            </h4>
                        @endif
                    </div>

                    {{-- Paragraph 1: Auto Summary / Editable --}}
                    <div class="mb-4" style="text-align: justify;">
                        @if($canEdit)
                            <div class="p-3 border rounded bg-light mb-2" style="border-left: 3px solid #3b82f6 !important; font-family: Arial, sans-serif;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="font-weight-bold text-dark small mb-0">
                                        <i class="fas fa-edit text-primary mr-1"></i> PARAGRAPH 1 (SUMMARY & ENCLOSURE) - EDITABLE
                                    </label>
                                    <span class="badge badge-primary text-white font-weight-bold" style="font-size: 10px;">Draft Editing</span>
                                </div>
                                <div class="d-flex align-items-center mb-2" style="gap: 10px;">
                                    <span class="small font-weight-bold text-muted">Enclosure Ref:</span>
                                    <input type="text" name="soc_encl_ref" class="form-control form-control-sm" style="max-width: 180px;" value="{{ $case->soc_encl_ref }}">
                                </div>
                                <textarea name="soc_para1_text" class="form-control" rows="3" style="font-family: 'Times New Roman', serif; font-size: 15px; border-radius: 4px;">{{ $case->soc_para1_text ?: ("1. {$case->soc_total_due_emps} employees are due for salary. Salaries of {$case->soc_total_paid_emps} employees are being paid for the month of " . \Carbon\Carbon::createFromFormat('Y-m', $case->soc_month)->format('M y') . " against projects mentioned below. {$case->soc_split_salaries_count} salaries are split into {$case->soc_split_payments_count} payments. Salary sheet is placed at " . ($case->soc_encl_ref ?: 'Encl 40-A/F') . ".") }}</textarea>
                            </div>
                        @else
                            <p class="mb-0" style="font-size: 15px;">
                                {!! nl2br(e($case->soc_para1_text ?: ("1. {$case->soc_total_due_emps} employees are due for salary. Salaries of {$case->soc_total_paid_emps} employees are being paid for the month of " . \Carbon\Carbon::createFromFormat('Y-m', $case->soc_month)->format('M y') . " against projects mentioned below. {$case->soc_split_salaries_count} salaries are split into {$case->soc_split_payments_count} payments. Salary sheet is placed at " . ($case->soc_encl_ref ?: 'Encl 40-A/F') . "."))) !!}
                            </p>
                        @endif
                    </div>

                    {{-- Grouped Projects Table (Exact Match with Official Sheet / Editable in Draft) --}}
                    @if($canEdit)
                        <div class="p-3 border rounded bg-light mb-4" style="border-left: 3px solid #6366f1 !important; font-family: Arial, sans-serif;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <label class="font-weight-bold text-dark small mb-0">
                                        <i class="fas fa-table mr-1" style="color: #6366f1;"></i> PROJECTS TABLE (BOX) - FULLY EDITABLE
                                    </label>
                                    <div class="text-muted" style="font-size: 11px;">
                                        Edit Paid From heads, payments count, amounts, remaining balances, and remarks. Totals calculate live.
                                    </div>
                                </div>
                                <div class="d-flex align-items-center" style="gap: 6px;">
                                    <button type="button" class="btn btn-xs btn-outline-info font-weight-bold shadow-sm" onclick="if(confirm('Re-sync table and paragraphs from current salary orders?')) { document.getElementById('syncOrdersForm').submit(); }" title="Reload project heads and amounts directly from salary orders">
                                        <i class="fas fa-sync-alt mr-1"></i> Re-sync Orders
                                    </button>
                                    <button type="button" class="btn btn-xs btn-primary font-weight-bold shadow-sm" onclick="addMinuteTableRow()">
                                        <i class="fas fa-plus mr-1"></i> Add Project Row
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0 bg-white" id="editableMinuteTable" style="border: 1.5px solid #000; font-size: 12.5px;">
                                    <thead style="background: #f8fafc; border-bottom: 2px solid #000;">
                                        <tr style="color: #000; font-weight: bold;">
                                            <th style="width: 22%; border: 1px solid #000; padding: 6px;">Division Group</th>
                                            <th style="width: 22%; border: 1px solid #000; padding: 6px;">Paid From</th>
                                            <th style="width: 11%; border: 1px solid #000; padding: 6px; text-align: center;">Payments</th>
                                            <th style="width: 17%; border: 1px solid #000; padding: 6px; text-align: right;">Amount (PKR)</th>
                                            <th style="width: 15%; border: 1px solid #000; padding: 6px; text-align: right;">Remaining</th>
                                            <th style="width: 9%; border: 1px solid #000; padding: 6px;">Remarks</th>
                                            <th style="width: 4%; border: 1px solid #000; padding: 6px; text-align: center;">Act</th>
                                        </tr>
                                    </thead>
                                    <tbody id="minuteTableBody">
                                        @php
                                            $rowIndex = 0;
                                            $calcPayments = 0;
                                            $calcAmount = 0;
                                            $divisionOptions = [
                                                'Center',
                                                'Command, Control and Communication (C3)',
                                                'AI and Immersive Technology Division',
                                                'Under Water Tech Division',
                                                'Sensors and UAV Technology Division',
                                                'Radar and EW Division',
                                                'Ex System Division',
                                            ];
                                        @endphp
                                        @foreach($minuteTable as $divGroup => $heads)
                                            @foreach($heads as $head)
                                                @php
                                                    $calcPayments += (int)$head['payments'];
                                                    $calcAmount += (float)$head['amount'];
                                                @endphp
                                                <tr class="minute-table-row">
                                                    <td style="border: 1px solid #000; padding: 3px 5px;">
                                                        <select name="table_rows[{{ $rowIndex }}][division_group]" class="form-control form-control-sm" style="font-size: 11.5px; height: 28px; padding: 2px 4px;">
                                                            @foreach($divisionOptions as $dOpt)
                                                                <option value="{{ $dOpt }}" {{ (trim($divGroup) === trim($dOpt) || str_starts_with($divGroup, explode(' ', $dOpt)[0])) ? 'selected' : '' }}>
                                                                    {{ $dOpt }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td style="border: 1px solid #000; padding: 3px 5px;">
                                                        <input type="text" name="table_rows[{{ $rowIndex }}][paid_from]" class="form-control form-control-sm font-weight-bold" value="{{ $head['paid_from'] }}" style="font-size: 12px; height: 28px; padding: 2px 5px;" required>
                                                    </td>
                                                    <td style="border: 1px solid #000; padding: 3px 5px;">
                                                        <input type="number" name="table_rows[{{ $rowIndex }}][payments]" class="form-control form-control-sm text-center row-payments-input" value="{{ $head['payments'] }}" min="0" oninput="recalcMinuteTotals()" style="font-size: 12px; height: 28px; padding: 2px 4px;" required>
                                                    </td>
                                                    <td style="border: 1px solid #000; padding: 3px 5px;">
                                                        <input type="number" step="any" name="table_rows[{{ $rowIndex }}][amount]" class="form-control form-control-sm text-right row-amount-input font-mono" value="{{ $head['amount'] }}" oninput="recalcMinuteTotals()" style="font-size: 12px; height: 28px; padding: 2px 5px; font-family: 'Courier New', monospace;" required>
                                                    </td>
                                                    <td style="border: 1px solid #000; padding: 3px 5px;">
                                                        <input type="number" step="any" name="table_rows[{{ $rowIndex }}][remaining]" class="form-control form-control-sm text-right row-remaining-input font-mono" value="{{ $head['remaining'] }}" style="font-size: 12px; height: 28px; padding: 2px 5px; font-family: 'Courier New', monospace;">
                                                    </td>
                                                    <td style="border: 1px solid #000; padding: 3px 5px;">
                                                        <input type="text" name="table_rows[{{ $rowIndex }}][remarks]" class="form-control form-control-sm" value="{{ $head['remarks'] ?? '' }}" style="font-size: 11px; height: 28px; padding: 2px 4px;">
                                                    </td>
                                                    <td style="border: 1px solid #000; padding: 3px 5px; text-align: center;">
                                                        <button type="button" class="btn btn-xs btn-outline-danger py-0 px-1" onclick="removeMinuteTableRow(this)" title="Delete Row" style="height: 24px; line-height: 22px;">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @php $rowIndex++; @endphp
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr style="background: #f8fafc; font-weight: bold; border-top: 2px solid #000;">
                                            <td colspan="2" style="border: 1px solid #000; padding: 6px 8px; font-weight: bold; font-size: 13.5px;">
                                                Total
                                            </td>
                                            <td id="tableTotalPayments" style="border: 1px solid #000; padding: 6px 8px; text-align: center; font-size: 13.5px;">
                                                {{ $calcPayments }}
                                            </td>
                                            <td id="tableTotalAmount" style="border: 1px solid #000; padding: 6px 8px; text-align: right; font-size: 13.5px; font-family: 'Courier New', monospace;">
                                                {{ number_format($calcAmount) }}
                                            </td>
                                            <td colspan="3" style="border: 1px solid #000; padding: 6px 8px;"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @else
                        {{-- Grouped Projects Table (Official Clean Bordered Display) --}}
                        <div class="table-responsive mb-4" style="font-family: Arial, sans-serif;">
                            <table class="table table-bordered table-sm mb-0" style="border: 1.5px solid #000; font-size: 13px;">
                                <thead style="background: #f8fafc; border-bottom: 2px solid #000;">
                                    <tr style="color: #000; font-weight: bold;">
                                        <th style="border: 1px solid #000; padding: 6px 8px; width: 34%;">Paid From</th>
                                        <th style="border: 1px solid #000; padding: 6px 8px; width: 14%; text-align: center;">Payments</th>
                                        <th style="border: 1px solid #000; padding: 6px 8px; width: 22%; text-align: right;">Amount (PKR)</th>
                                        <th style="border: 1px solid #000; padding: 6px 8px; width: 20%; text-align: right;">Remaining</th>
                                        <th style="border: 1px solid #000; padding: 6px 8px; width: 10%;">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $grandPayments = 0;
                                        $grandAmount = 0;
                                    @endphp
                                    @foreach($minuteTable as $divisionGroup => $heads)
                                        <tr style="background: #f1f5f9;">
                                            <td colspan="5" style="border: 1px solid #000; padding: 4px 8px; font-weight: bold; text-decoration: underline; color: #000; font-size: 13.5px;">
                                                {{ $divisionGroup }}
                                            </td>
                                        </tr>

                                        @foreach($heads as $head)
                                            @php
                                                $grandPayments += (int)$head['payments'];
                                                $grandAmount += (float)$head['amount'];
                                            @endphp
                                            <tr>
                                                <td style="border: 1px solid #000; padding: 4px 8px; font-weight: 600; padding-left: 18px;">
                                                    {{ $head['paid_from'] }}
                                                </td>
                                                <td style="border: 1px solid #000; padding: 4px 8px; text-align: center; font-weight: 500;">
                                                    {{ $head['payments'] }}
                                                </td>
                                                <td style="border: 1px solid #000; padding: 4px 8px; text-align: right; font-family: 'Courier New', monospace; font-weight: 600;">
                                                    {{ number_format($head['amount']) }}
                                                </td>
                                                <td style="border: 1px solid #000; padding: 4px 8px; text-align: right; font-family: 'Courier New', monospace; color: {{ ($head['remaining'] ?? 0) < 0 ? '#dc2626' : '#000' }};">
                                                    {{ number_format($head['remaining'] ?? 0) }}
                                                </td>
                                                <td style="border: 1px solid #000; padding: 4px 8px;">
                                                    {{ $head['remarks'] ?? '' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach

                                    <tr style="background: #f8fafc; font-weight: bold; border-top: 2px solid #000;">
                                        <td style="border: 1px solid #000; padding: 6px 8px; font-weight: bold; font-size: 14px;">
                                            Total
                                        </td>
                                        <td style="border: 1px solid #000; padding: 6px 8px; text-align: center; font-size: 14px;">
                                            {{ $grandPayments }}
                                        </td>
                                        <td style="border: 1px solid #000; padding: 6px 8px; text-align: right; font-size: 14px; font-family: 'Courier New', monospace;">
                                            {{ number_format($grandAmount) }}
                                        </td>
                                        <td style="border: 1px solid #000; padding: 6px 8px;"></td>
                                        <td style="border: 1px solid #000; padding: 6px 8px;"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @endif

                    {{-- Paragraph 2: Held Salaries (Fully Editable in Draft Mode) --}}
                    <div class="mb-4" style="text-align: justify;">
                        @if($canEdit)
                            <div class="p-3 border rounded bg-light mb-2" style="border-left: 3px solid #f59e0b !important; font-family: Arial, sans-serif;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="font-weight-bold text-dark small mb-0">
                                        <i class="fas fa-edit text-warning mr-1"></i> PARAGRAPH 2 (HELD SALARIES & REMARKS) - EDITABLE
                                    </label>
                                    <span class="badge badge-warning text-dark font-weight-bold" style="font-size: 10px;">Draft Editing</span>
                                </div>
                                <div class="small text-muted mb-2 font-italic">
                                    You can write and edit custom reasons for held salaries, contract expiries, or notes:
                                </div>
                                <textarea name="soc_para2_text" class="form-control" rows="3" style="font-family: 'Times New Roman', serif; font-size: 15px; border-radius: 4px;">{{ $case->soc_para2_text }}</textarea>
                            </div>
                        @else
                            <p class="mb-0" style="font-size: 15px;">
                                {!! nl2br(e($case->soc_para2_text)) !!}
                            </p>
                        @endif
                    </div>

                    {{-- Paragraph 3: Recommendation with Words / Editable --}}
                    <div class="mb-4" style="text-align: justify;">
                        @if($canEdit)
                            <div class="p-3 border rounded bg-light mb-2" style="border-left: 3px solid #10b981 !important; font-family: Arial, sans-serif;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="font-weight-bold text-dark small mb-0">
                                        <i class="fas fa-edit text-success mr-1"></i> PARAGRAPH 3 (RECOMMENDATION & CHANNEL) - EDITABLE
                                    </label>
                                    <span class="badge badge-success text-white font-weight-bold" style="font-size: 10px;">Draft Editing</span>
                                </div>
                                <div class="d-flex align-items-center mb-2" style="gap: 10px;">
                                    <span class="small font-weight-bold text-muted">Payment Channel:</span>
                                    <input type="text" name="soc_payment_channel" class="form-control form-control-sm" style="max-width: 200px;" value="{{ $case->soc_payment_channel }}">
                                </div>
                                <textarea name="soc_para3_text" id="socPara3Input" class="form-control" rows="3" style="font-family: 'Times New Roman', serif; font-size: 15px; border-radius: 4px;">{{ $case->soc_para3_text ?: ("3. Foregoing in view, approval of Rs. " . number_format($case->soc_total_amount) . " (" . app(\App\Services\SalaryCaseService::class)->numberToWords((int)$case->soc_total_amount) . ") for salaries of {$case->soc_total_paid_emps} employees for month of " . \Carbon\Carbon::createFromFormat('Y-m', $case->soc_month)->format('M y') . " may please be accorded for release of the payment through {$case->soc_payment_channel}.") }}</textarea>
                            </div>
                        @else
                            <p class="mb-0" style="font-size: 15px;">
                                {!! nl2br(e($case->soc_para3_text ?: ("3. Foregoing in view, approval of Rs. " . number_format($case->soc_total_amount) . " (" . app(\App\Services\SalaryCaseService::class)->numberToWords((int)$case->soc_total_amount) . ") for salaries of {$case->soc_total_paid_emps} employees for month of " . \Carbon\Carbon::createFromFormat('Y-m', $case->soc_month)->format('M y') . " may please be accorded for release of the payment through {$case->soc_payment_channel}."))) !!}
                            </p>
                        @endif
                    </div>

                    {{-- Save Draft Button (If in Draft Mode) --}}
                    @if($canEdit)
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top flex-wrap" style="font-family: Arial, sans-serif; gap: 8px;">
                                <div class="text-muted small">
                                    <i class="fas fa-info-circle mr-1 text-primary"></i> Save changes as draft without forwarding to review anytime.
                                </div>
                                <button type="submit" class="btn btn-primary font-weight-bold px-4 py-2 shadow-sm" style="border-radius: 6px; font-size: 13.5px; background: #0f172a; border-color: #0f172a;">
                                    <i class="fas fa-save mr-1.5 text-warning"></i> Save as Draft
                                </button>
                            </div>
                        </form>
                        <form id="syncOrdersForm" method="POST" action="{{ route('salary_cases.sync', $case->soc_id) }}" style="display: none;">
                            @csrf
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- RIGHT PANE: ROUTING & ACTION BOX (Purchase/Hiring Style) --}}
        {{-- ========================================================= --}}
        <div class="col-xl-4 col-lg-5 mb-4">
            {{-- Decision Action Box (Send Case / Approve / Return) --}}
            <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px; background: #ffffff;">
                <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold rajdhani text-dark d-flex align-items-center" style="font-size: 13.5px; letter-spacing: 0.5px;">
                        <span class="badge badge-dark px-1.5 py-0.5 mr-1.5" style="font-size: 11px; background: #0f172a;">= MINUTE</span>
                    </span>
                    <span class="badge badge-light border text-muted px-2 py-0.5" style="font-size: 10.5px;">
                        <i class="fas fa-paperclip mr-1"></i> ATTACHMENTS 1
                    </span>
                </div>

                <div class="card-body p-3">
                    @if($isCaseWithMe)
                        {{-- User Identity Line matching Purchase Case --}}
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom" style="border-bottom: 1px dashed #cbd5e1 !important;">
                            <div class="font-weight-bold rajdhani text-dark" style="font-size: 13.5px;">
                                <i class="fas fa-user-circle text-primary mr-1"></i> {{ \App\Services\SalaryCaseService::getOfficialDesignation(auth()->user()) }} 
                                <span class="text-muted small ml-1" style="font-weight: 600;">({{ strtoupper(auth()->user()->acc_untarea ?? 'FIN') }})</span>
                                <span class="ml-2 pl-2 border-left border-secondary font-weight-bold" style="font-size: 10px; color: var(--rd-accent, #5F7858); letter-spacing: 0.5px;">
                                    <i class="fas fa-pen-nib mr-1"></i> SCRUTINY & ACTION
                                </span>
                            </div>
                            <span class="badge badge-warning text-dark font-weight-bold" style="font-size: 10px;">ACTION PENDING</span>
                        </div>

                        {{-- Action Form --}}
                        <form id="salaryCaseActionForm" method="POST" action="{{ route('salary_cases.send', $case->soc_id) }}">
                            @csrf
                            <input type="hidden" name="action_type" id="caseActionType" value="send">

                            {{-- Remarks & Scrutiny Notes Textarea --}}
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap" style="gap: 6px;">
                                    <span class="text-dark small rajdhani font-weight-bold" style="font-size: 11.5px; letter-spacing: 0.5px;">
                                        <i class="fas fa-pen-nib mr-1 text-primary"></i> REMARKS & SCRUTINY NOTES *
                                    </span>
                                    <div class="d-flex align-items-center" style="gap: 8px;">
                                        <button type="button" class="btn btn-xs btn-outline-danger py-0 px-1.5" id="btnClearRemarks" style="font-size: 10px;">
                                            <i class="fas fa-times mr-0.5"></i> Clear
                                        </button>
                                        <span class="text-muted font-italic" style="font-size: 10px;">
                                            <i class="fas fa-arrows-alt-v mr-0.5"></i> Drag corner to resize
                                        </span>
                                    </div>
                                </div>
                                <textarea name="remarks" id="caseRemarksInput" class="form-control" placeholder="Write official minutes or scrutiny observations..." style="background: #ffffff; color: #0f172a; font-family: 'Arial', sans-serif; font-size: 13px; min-height: 110px; height: 110px; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 10px 12px; outline: none; box-shadow: inset 0 1px 2px rgba(0,0,0,0.04); resize: vertical; width: 100%;" required></textarea>

                                {{-- User Quick Remarks (Custom shortcuts 0/7) --}}
                                <div class="mt-2">
                                    @include('partials._user_quick_remarks', ['targetTextarea' => '#caseRemarksInput'])
                                </div>
                            </div>

                            {{-- Send / Forward To Destination Dropdown (Downwards with Real-time Search) --}}
                            <div class="form-group mb-3 position-relative" id="pcDestDropdownContainer">
                                <label class="font-weight-bold text-muted small mb-1 d-flex justify-content-between" style="font-size: 10px; text-transform: uppercase;">
                                    <span><i class="fas fa-paper-plane text-primary mr-1"></i> Send / Forward To Destination: <span class="text-muted font-italic font-weight-normal" style="font-size: 9px; text-transform: none;">(Only if forwarding)</span></span>
                                    <span class="text-muted font-italic" style="font-size: 9px; text-transform: none;">Search authority or desk</span>
                                </label>

                                <input type="hidden" name="target_destination" id="targetDestinationInput" value="">

                                {{-- Display Toggle Box --}}
                                <div id="pcDestDropdownToggle" class="form-control form-control-sm d-flex align-items-center justify-content-between" style="font-size: 12px; font-weight: 600; border-radius: 6px; border-color: #cbd5e1; height: 38px; cursor: pointer; background: #ffffff; user-select: none;">
                                    <span id="pcDestSelectedLabel" class="text-muted text-truncate font-weight-normal">
                                        <i class="fas fa-search mr-1.5 text-muted"></i> -- Select Destination Authority --
                                    </span>
                                    <i class="fas fa-chevron-down text-muted ml-2" id="pcDestDropdownChevron" style="font-size: 11px; transition: transform 0.2s;"></i>
                                </div>

                                {{-- Downward Dropdown Menu --}}
                                <div id="pcDestDropdownMenu" class="shadow-lg border bg-white" style="display: none; position: absolute; top: 100% !important; bottom: auto !important; left: 0; right: 0; z-index: 1050; margin-top: 3px; border-radius: 8px; border-color: #cbd5e1 !important; box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;">
                                    {{-- Sticky Search Input --}}
                                    <div class="p-2 border-bottom bg-light">
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white border-right-0" style="border-color: #cbd5e1;"><i class="fas fa-search text-muted" style="font-size: 11px;"></i></span>
                                            </div>
                                            <input type="text" id="pcDestSearchInput" class="form-control border-left-0" placeholder="Type authority or department..." style="font-size: 12px; border-color: #cbd5e1;" autocomplete="off">
                                        </div>
                                    </div>

                                    {{-- Scrollable Destination Items --}}
                                    <div id="pcDestItemsList" style="max-height: 230px; overflow-y: auto; padding: 4px 0;">
                                        @foreach($destinations as $destCode => $dest)
                                            @php
                                                $searchKeywords = strtolower($dest['name'] . ' ' . ($dest['dept'] ?? '') . ' ' . ($dest['badge'] ?? '') . ' ' . $destCode);
                                            @endphp
                                            <div class="pc-dest-option-item px-3 py-2" data-code="{{ $destCode }}" data-name="{{ $dest['name'] }}" data-search="{{ $searchKeywords }}" style="cursor: pointer; transition: background 0.15s; border-bottom: 1px solid #f1f5f9;">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-dark font-weight-bold" style="font-size: 12.5px;">{{ $dest['name'] }}</span>
                                                    <span class="badge badge-light border text-muted px-1.5 py-0.5" style="font-size: 9.5px; border-radius: 4px;">{{ $dest['badge'] ?? $destCode }}</span>
                                                </div>
                                                <div class="text-muted text-truncate" style="font-size: 11px; margin-top: 1px;">
                                                    <i class="fas fa-building text-secondary mr-1" style="font-size: 9.5px;"></i> {{ $dest['dept'] }}
                                                </div>
                                            </div>
                                        @endforeach
                                        <div id="pcDestNoResults" class="p-3 text-center text-muted small" style="display: none;">
                                            <i class="fas fa-info-circle mr-1"></i> No matching authority or officer found.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Action Buttons Row: SEND CASE (Left, prominent) + APPROVE (Green tick) + RETURN (Red cross) on SAME LINE --}}
                            <div class="d-flex align-items-center" style="gap: 8px; width: 100%;">
                                {{-- Left: Prominent Send Case Button --}}
                                <button type="submit" id="btnSendCase" class="btn-action-send flex-grow-1" style="height: 40px; font-size: 13.5px; letter-spacing: 0.6px; display: inline-flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    <span class="font-weight-bold rajdhani">SEND CASE</span>
                                </button>

                                @if($canApprove)
                                    {{-- Right: Compact Approve Button (Green Tick, expands on hover to APPROVE CASE) --}}
                                    <button type="button" id="btnApproveCase" class="btn-action-approve" title="Approve Salary Orders Case">
                                        <i class="fas fa-check"></i>
                                        <span class="btn-expand-text rajdhani font-weight-bold">APPROVE CASE</span>
                                    </button>
                                @endif

                                {{-- Right: Compact Cancel Button (Red Cross, expands on hover to CANCEL CASE) --}}
                                <button type="button" id="btnCancelCase" class="btn-action-cancel" title="Cancel Verification Case (Salary orders will remain active in Draft)">
                                    <i class="fas fa-times"></i>
                                    <span class="btn-expand-text rajdhani font-weight-bold">CANCEL CASE</span>
                                </button>
                            </div>
                        </form>
                    @elseif(in_array(strtolower($case->soc_status), ['approved', 'closed', 'cancelled']) || strtoupper(trim((string)$case->soc_current_stage)) === 'CLOSED')
                        {{-- Finalized Status Box --}}
                        <div class="p-3 bg-light border rounded text-center">
                            <span class="badge badge-{{ strtolower($case->soc_status) === 'approved' ? 'success' : 'danger' }} px-3 py-1 font-weight-bold" style="font-size: 13px; text-transform: uppercase;">
                                <i class="fas {{ strtolower($case->soc_status) === 'approved' ? 'fa-check-circle' : 'fa-ban' }} mr-1"></i> Case {{ $case->soc_status }}
                            </span>
                            <div class="text-muted small mt-2">
                                @if(strtolower($case->soc_status) === 'cancelled')
                                    This salary verification case has been cancelled and closed. Associated salary orders are active in Draft and can be processed in a separate case anytime.
                                @else
                                    This salary verification case has been finalized. No further actions are pending.
                                @endif
                            </div>
                        </div>
                    @else
                        {{-- Locked for this user --}}
                        <div class="p-3 bg-light border rounded text-center">
                            <div class="text-muted font-weight-bold" style="font-size: 13px;">
                                <i class="fas fa-lock text-secondary mr-1.5"></i>
                                Case is currently with <strong class="text-dark">{{ $case->soc_current_holder ?: ($case->soc_current_stage . ' (' . $case->soc_status . ')') }}</strong>.
                            </div>
                            <div class="text-muted mt-1 small font-italic">
                                Scrutiny and decision actions are locked for your account until the case is forwarded to your desk.
                            </div>
                        </div>
                    @endif

                    {{-- Unified Minute Remarks Trail (Directly underneath action box in same card) --}}
                    @php
                        // Calculate global running sequential numbering for conversation trail (1, 2, 3...)
                        $trailRunningNumberMap = [];
                        $currSeq = 1;
                        foreach($case->decisions->sortBy('scd_id') as $d) {
                            $trailRunningNumberMap[$d->scd_id] = $currSeq;
                            $currSeq++;
                        }
                    @endphp

                    @if($case->decisions->count() > 0)
                        <div class="border-top my-3" style="border-top: 1px dashed #cbd5e1 !important;"></div>

                        <div class="case-minutes-stream">
                            @foreach($case->decisions as $decIdx => $dec)
                                @php
                                    $seqNo = $trailRunningNumberMap[$dec->scd_id] ?? 1;
                                    $rawRemarks = trim($dec->scd_remarks ?: '');
                                    $hasNumberPrefix = preg_match('/^\d+[\.\)]\s*/', $rawRemarks);
                                    $displayRemarks = $hasNumberPrefix ? $rawRemarks : ($seqNo . '. ' . ($rawRemarks ?: 'No additional notes provided.'));
                                    $officialSender = \App\Services\SalaryCaseService::getOfficialDesignation($dec->scd_acc_id);
                                    $displaySender = $officialSender ?: ($dec->scd_user_desig ?: $dec->scd_user_name);
                                @endphp
                                <div class="pb-3 mb-3 border-bottom" style="border-bottom: 1px dashed #e2e8f0 !important;">
                                    {{-- Header matching Purchase Case: Designation | Action -> Destination  Timestamp --}}
                                    <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap" style="gap: 6px;">
                                        <div class="font-weight-bold text-dark" style="font-size: 13px;">
                                            <i class="fas fa-user-circle text-primary mr-1"></i>
                                            <strong>{{ $displaySender }}</strong> 
                                            <span class="mx-1 text-muted">|</span>
                                            <span class="text-primary font-weight-bold" style="font-size: 11.5px;">
                                                &blacktriangleright; {{ $dec->scd_action }}
                                            </span>
                                        </div>
                                        <span class="text-muted small" style="font-size: 11px;">
                                            <i class="far fa-clock mr-1"></i> {{ $dec->scd_dtg?->format('d M Y, h:i A') }}
                                        </span>
                                    </div>

                                    {{-- Dashed divider right below user header --}}
                                    <div class="mb-2" style="border-bottom: 1px dashed #f1f5f9;"></div>

                                    {{-- Remarks formatted like official military minute note with paragraph numbering --}}
                                    <div class="text-dark" style="font-size: 13px; line-height: 1.6; padding-left: 4px; white-space: pre-line;">
                                        {!! nl2br(e($displayRemarks)) !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Page & Container Scrolling Guarantees */
html, body {
    height: auto !important;
    min-height: 100vh !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
}
.wrapper {
    height: auto !important;
    min-height: 100vh !important;
    overflow: visible !important;
}
.content-wrapper {
    height: auto !important;
    min-height: 100vh !important;
    overflow: visible !important;
    padding-bottom: 120px !important;
}

/* Scrollable dropdown list with visible scrollbar */
#pcDestItemsList {
    max-height: 220px !important;
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch !important;
    overscroll-behavior: contain !important;
    scrollbar-width: thin !important;
    scrollbar-color: #94a3b8 #f1f5f9 !important;
}
#pcDestItemsList::-webkit-scrollbar {
    width: 6px !important;
    display: block !important;
}
#pcDestItemsList::-webkit-scrollbar-thumb {
    background: #94a3b8 !important;
    border-radius: 4px !important;
}

/* Send Button: wide on the left */
.btn-action-send {
    background: #2563eb !important;
    border: none;
    border-radius: 6px;
    color: #ffffff !important;
    font-family: 'Rajdhani', sans-serif;
    font-weight: 700;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(37, 99, 235, 0.25);
}
.btn-action-send:hover {
    background: #1d4ed8 !important;
    box-shadow: 0 4px 10px rgba(29, 78, 216, 0.35);
    transform: translateY(-1px);
}

/* Compact Approve Button: green icon on right, expands on hover */
.btn-action-approve {
    flex: 0 0 42px;
    width: 42px;
    height: 40px;
    background: #16a34a !important;
    border: none;
    border-radius: 6px;
    color: #ffffff !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: 'Rajdhani', sans-serif;
    font-size: 13px;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.12);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    overflow: hidden;
    cursor: pointer;
    padding: 0 12px;
}
.btn-action-approve .btn-expand-text {
    max-width: 0;
    opacity: 0;
    margin-left: 0;
    transition: max-width 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.2s ease, margin-left 0.3s ease;
    overflow: hidden;
    display: inline-block;
    white-space: nowrap;
}
.btn-action-approve:hover {
    flex: 0 0 auto !important;
    width: auto !important;
    min-width: 145px;
    padding: 0 14px !important;
    background: #15803d !important;
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.35);
    transform: translateY(-1px);
}
.btn-action-approve:hover .btn-expand-text {
    max-width: 130px;
    opacity: 1;
    margin-left: 8px;
}

/* Compact Cancel/Return Button: red icon on right, expands on hover */
.btn-action-cancel {
    flex: 0 0 42px;
    width: 42px;
    height: 40px;
    background: #dc2626 !important;
    border: none;
    border-radius: 6px;
    color: #ffffff !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: 'Rajdhani', sans-serif;
    font-size: 13px;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.12);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    overflow: hidden;
    cursor: pointer;
    padding: 0 12px;
}
.btn-action-cancel .btn-expand-text {
    max-width: 0;
    opacity: 0;
    margin-left: 0;
    transition: max-width 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.2s ease, margin-left 0.3s ease;
    overflow: hidden;
    display: inline-block;
    white-space: nowrap;
}
.btn-action-cancel:hover {
    flex: 0 0 auto !important;
    width: auto !important;
    min-width: 135px;
    padding: 0 14px !important;
    background: #b91c1c !important;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35);
    transform: translateY(-1px);
}
.btn-action-cancel:hover .btn-expand-text {
    max-width: 130px;
    opacity: 1;
    margin-left: 8px;
}

.pc-dest-option-item:hover {
    background: #f1f5f9;
}
.pc-dest-option-item.selected {
    background: #e2e8f0;
}
</style>

{{-- Scripts for Searchable Dropdown & Form Actions --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('salaryCaseActionForm');
    const btnApprove = document.getElementById('btnApproveCase');
    const btnCancel = document.getElementById('btnCancelCase');
    const btnClearRemarks = document.getElementById('btnClearRemarks');
    const remarksInput = document.getElementById('caseRemarksInput');

    // Dropdown elements
    const pcDestContainer = document.getElementById('pcDestDropdownContainer');
    const pcDestToggle = document.getElementById('pcDestDropdownToggle');
    const pcDestMenu = document.getElementById('pcDestDropdownMenu');
    const pcDestSearch = document.getElementById('pcDestSearchInput');
    const pcDestHiddenInput = document.getElementById('targetDestinationInput');
    const pcDestLabel = document.getElementById('pcDestSelectedLabel');
    const pcDestChevron = document.getElementById('pcDestDropdownChevron');
    const pcDestItems = document.querySelectorAll('.pc-dest-option-item');
    const pcDestNoResults = document.getElementById('pcDestNoResults');

    function openPcDestDropdown() {
        if (!pcDestMenu) return;
        pcDestMenu.style.display = 'block';
        if (pcDestChevron) pcDestChevron.style.transform = 'rotate(180deg)';
        if (pcDestSearch) {
            pcDestSearch.value = '';
            filterPcDestItems('');
            setTimeout(() => pcDestSearch.focus(), 50);
        }
        setTimeout(() => {
            pcDestMenu.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 80);
    }

    function closePcDestDropdown() {
        if (!pcDestMenu) return;
        pcDestMenu.style.display = 'none';
        if (pcDestChevron) pcDestChevron.style.transform = 'rotate(0deg)';
    }

    function filterPcDestItems(q) {
        let matchCount = 0;
        const query = (q || '').toLowerCase().trim();
        pcDestItems.forEach(item => {
            const searchData = item.getAttribute('data-search') || '';
            if (query === '' || searchData.includes(query)) {
                item.style.display = 'block';
                matchCount++;
            } else {
                item.style.display = 'none';
            }
        });
        if (pcDestNoResults) {
            pcDestNoResults.style.display = matchCount === 0 ? 'block' : 'none';
        }
    }

    if (pcDestToggle) {
        pcDestToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            if (pcDestMenu.style.display === 'block') {
                closePcDestDropdown();
            } else {
                openPcDestDropdown();
            }
        });
    }

    if (pcDestSearch) {
        pcDestSearch.addEventListener('input', function() {
            filterPcDestItems(this.value);
        });
        pcDestSearch.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    pcDestItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.stopPropagation();
            const code = this.getAttribute('data-code');
            const name = this.getAttribute('data-name');
            if (pcDestHiddenInput) pcDestHiddenInput.value = code;
            if (pcDestLabel) {
                pcDestLabel.innerHTML = `<span class="text-dark font-weight-bold"><i class="fas fa-check-circle text-success mr-1"></i> ${name}</span>`;
            }
            pcDestItems.forEach(i => i.classList.remove('selected'));
            this.classList.add('selected');
            closePcDestDropdown();
        });
    });

    document.addEventListener('click', function(e) {
        if (pcDestContainer && !pcDestContainer.contains(e.target)) {
            closePcDestDropdown();
        }
    });

    const nextMinuteSeq = {{ $case->decisions->count() + 1 }};
    const defaultPrefix = nextMinuteSeq + '. ';

    if (remarksInput) {
        // Auto prepopulate next running minute number if blank or prefix missing
        if (!remarksInput.value.trim() || !remarksInput.value.startsWith(defaultPrefix)) {
            remarksInput.value = defaultPrefix;
        }

        // Keydown listener for prefix protection & smart sequential paragraph numbering
        remarksInput.addEventListener('keydown', function(e) {
            const cursor = this.selectionStart;
            const end = this.selectionEnd;

            // Protection: Select All + Backspace or Delete
            if ((e.key === 'Backspace' || e.key === 'Delete') && cursor === 0 && end === this.value.length) {
                e.preventDefault();
                this.value = defaultPrefix;
                this.setSelectionRange(defaultPrefix.length, defaultPrefix.length);
                return;
            }

            // Protection: Backspace at or inside defaultPrefix
            if (e.key === 'Backspace') {
                if (cursor <= defaultPrefix.length && end <= defaultPrefix.length) {
                    e.preventDefault();
                    this.setSelectionRange(defaultPrefix.length, defaultPrefix.length);
                    return;
                }
                if (cursor < defaultPrefix.length && end > defaultPrefix.length) {
                    e.preventDefault();
                    const keepEnd = this.value.substring(end);
                    this.value = defaultPrefix + keepEnd;
                    this.setSelectionRange(defaultPrefix.length, defaultPrefix.length);
                    return;
                }
            }

            // Protection: Delete inside defaultPrefix
            if (e.key === 'Delete') {
                if (cursor < defaultPrefix.length && end <= defaultPrefix.length) {
                    e.preventDefault();
                    this.setSelectionRange(defaultPrefix.length, defaultPrefix.length);
                    return;
                }
            }

            // Smart Enter key auto-numbering inside remarks textarea
            if (e.key === 'Enter' && !e.shiftKey) {
                const textBeforeCursor = this.value.substring(0, cursor);
                const lines = textBeforeCursor.split('\n');
                const currentLine = lines[lines.length - 1];

                const match = currentLine.match(/^(\d+)\.\s*(.*)$/);
                if (match) {
                    const num = parseInt(match[1], 10);
                    const content = match[2];

                    // If user pressed enter on an empty number like "3. " with no text, remove it (exit numbering)
                    if (!content.trim() && lines.length > 1) {
                        e.preventDefault();
                        const lineStart = cursor - currentLine.length;
                        this.value = this.value.substring(0, lineStart) + this.value.substring(cursor);
                        this.setSelectionRange(lineStart, lineStart);
                        return;
                    }

                    if (!content.trim() && lines.length === 1) {
                        e.preventDefault();
                        return;
                    }

                    // Insert next sequential number on new line: "\n3. "
                    e.preventDefault();
                    const nextPrefix = '\n' + (num + 1) + '. ';
                    const afterCursor = this.value.substring(this.selectionEnd);
                    this.value = textBeforeCursor + nextPrefix + afterCursor;
                    const newPos = cursor + nextPrefix.length;
                    this.setSelectionRange(newPos, newPos);
                }
            }
        });

        // Input event safety net: if text somehow loses defaultPrefix (paste, drag-drop, etc.)
        remarksInput.addEventListener('input', function() {
            if (!this.value.startsWith(defaultPrefix)) {
                if (!this.value.trim() || this.value.length < defaultPrefix.length) {
                    this.value = defaultPrefix;
                    this.setSelectionRange(defaultPrefix.length, defaultPrefix.length);
                } else {
                    const clean = this.value.replace(/^(\d+\.\s*)?/, '');
                    this.value = defaultPrefix + clean;
                }
            }
        });

        // Cut event safety net
        remarksInput.addEventListener('cut', function() {
            setTimeout(() => {
                if (!this.value.startsWith(defaultPrefix)) {
                    this.value = defaultPrefix;
                    this.setSelectionRange(defaultPrefix.length, defaultPrefix.length);
                }
            }, 0);
        });

        // Clear button resets strictly to defaultPrefix
        if (btnClearRemarks) {
            btnClearRemarks.addEventListener('click', function() {
                remarksInput.value = defaultPrefix;
                remarksInput.focus();
                remarksInput.setSelectionRange(defaultPrefix.length, defaultPrefix.length);
            });
        }
    }

    if (btnApprove && form) {
        btnApprove.addEventListener('click', function() {
            const val = remarksInput.value.trim();
            if (!val || val === defaultPrefix.trim()) {
                alert('Please write official approval remarks before proceeding.');
                remarksInput.focus();
                return;
            }
            if (confirm('Are you sure you want to APPROVE this Salary Order Verification Case? Associated salary orders will be marked Approved.')) {
                form.action = "{{ route('salary_cases.approve', $case->soc_id) }}";
                form.submit();
            }
        });
    }

    if (btnCancel && form) {
        btnCancel.addEventListener('click', function() {
            const val = remarksInput.value.trim();
            if (!val || val === defaultPrefix.trim()) {
                alert('Please write cancellation reason in remarks before proceeding.');
                remarksInput.focus();
                return;
            }
            if (confirm('Are you sure you want to CANCEL this Verification Case?\n\nThe case will be closed as Cancelled. Associated salary orders will remain active in Draft so a separate verification case can be created anytime without losing them.')) {
                form.action = "{{ route('salary_cases.cancel', $case->soc_id) }}";
                form.submit();
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            const sendBtnClicked = document.activeElement && document.activeElement.id === 'btnSendCase';
            if (sendBtnClicked || form.action.includes('/send')) {
                if (!pcDestHiddenInput || !pcDestHiddenInput.value) {
                    e.preventDefault();
                    alert('Please select a destination authority to forward this case.');
                    openPcDestDropdown();
                    return false;
                }
                if (!remarksInput.value.trim()) {
                    e.preventDefault();
                    alert('Please write remarks or minutes before forwarding.');
                    remarksInput.focus();
                    return false;
                }
            }
        });
    }
});

let minuteRowIndex = {{ $rowIndex ?? 100 }};
function recalcMinuteTotals() {
    let totPayments = 0;
    let totAmount = 0;
    document.querySelectorAll('.minute-table-row').forEach(row => {
        const pInput = row.querySelector('.row-payments-input');
        const aInput = row.querySelector('.row-amount-input');
        if (pInput) {
            totPayments += parseInt(pInput.value) || 0;
        }
        if (aInput) {
            totAmount += parseFloat(aInput.value) || 0;
        }
    });
    const pEl = document.getElementById('tableTotalPayments');
    const aEl = document.getElementById('tableTotalAmount');
    if (pEl) pEl.innerText = totPayments;
    if (aEl) aEl.innerText = Math.round(totAmount).toLocaleString();
}

function removeMinuteTableRow(btn) {
    const row = btn.closest('tr');
    if (row) {
        row.remove();
        recalcMinuteTotals();
    }
}

function addMinuteTableRow() {
    const tbody = document.getElementById('minuteTableBody');
    if (!tbody) return;
    minuteRowIndex++;
    const idx = minuteRowIndex;
    const tr = document.createElement('tr');
    tr.className = 'minute-table-row';
    tr.innerHTML = `
        <td style="border: 1px solid #000; padding: 3px 5px;">
            <select name="table_rows[${idx}][division_group]" class="form-control form-control-sm" style="font-size: 11.5px; height: 28px; padding: 2px 4px;">
                <option value="Center">Center</option>
                <option value="Command, Control and Communication (C3)">Command, Control and Communication (C3)</option>
                <option value="AI and Immersive Technology Division">AI and Immersive Technology Division</option>
                <option value="Under Water Tech Division">Under Water Tech Division</option>
                <option value="Sensors and UAV Technology Division">Sensors and UAV Technology Division</option>
                <option value="Radar and EW Division">Radar and EW Division</option>
                <option value="Ex System Division">Ex System Division</option>
            </select>
        </td>
        <td style="border: 1px solid #000; padding: 3px 5px;">
            <input type="text" name="table_rows[${idx}][paid_from]" class="form-control form-control-sm font-weight-bold" placeholder="e.g. HFT or CSRF" style="font-size: 12px; height: 28px; padding: 2px 5px;" required>
        </td>
        <td style="border: 1px solid #000; padding: 3px 5px;">
            <input type="number" name="table_rows[${idx}][payments]" class="form-control form-control-sm text-center row-payments-input" value="1" min="0" oninput="recalcMinuteTotals()" style="font-size: 12px; height: 28px; padding: 2px 4px;" required>
        </td>
        <td style="border: 1px solid #000; padding: 3px 5px;">
            <input type="number" step="any" name="table_rows[${idx}][amount]" class="form-control form-control-sm text-right row-amount-input font-mono" value="0" oninput="recalcMinuteTotals()" style="font-size: 12px; height: 28px; padding: 2px 5px; font-family: 'Courier New', monospace;" required>
        </td>
        <td style="border: 1px solid #000; padding: 3px 5px;">
            <input type="number" step="any" name="table_rows[${idx}][remaining]" class="form-control form-control-sm text-right row-remaining-input font-mono" value="0" style="font-size: 12px; height: 28px; padding: 2px 5px; font-family: 'Courier New', monospace;">
        </td>
        <td style="border: 1px solid #000; padding: 3px 5px;">
            <input type="text" name="table_rows[${idx}][remarks]" class="form-control form-control-sm" placeholder="Remarks" style="font-size: 11px; height: 28px; padding: 2px 4px;">
        </td>
        <td style="border: 1px solid #000; padding: 3px 5px; text-align: center;">
            <button type="button" class="btn btn-xs btn-outline-danger py-0 px-1" onclick="removeMinuteTableRow(this)" title="Delete Row" style="height: 24px; line-height: 22px;">
                <i class="fas fa-trash-alt"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    recalcMinuteTotals();
}
</script>
@endpush
@endsection
