@extends('welcome')

@section('content')
<div class="content-wrapper">
    <style>
        .card-history {
            border-top: 3px solid #007bff; /* AdminLTE Primary Blue */
        }
        .table thead th {
            background-color: #f4f6f9;
            color: #495057;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            border-bottom-width: 2px;
        }
        .badge-month {
            font-size: 0.85rem;
            padding: 5px 10px;
            font-weight: 500;
        }
        .text-finalize { color: #28a745; font-weight: 600; }
        .text-forward { color: #17a2b8; font-weight: 600; }
        .text-edit { color: #ffc107; font-weight: 600; }
        
        /* Row Hover Effect */
        .table-hover tbody tr:hover {
            background-color: rgba(0,123,255,.05);
        }
    </style>

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><i class="fas fa-history mr-2"></i>Project History</h1>
                </div>
                
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-history shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title">Action Logs & Progress Tracking</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="maximize">
                                    <i class="fas fa-expand"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary ml-2" onclick="window.print()">
                                    <i class="fas fa-print mr-1"></i> Print
                                </button>
                            </div>
                        </div>
                        
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped m-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 20%">Date & Time</th>
                                            <th style="width: 15%">Month</th>
                                            <th style="width: 15%">Action</th>
                                            <th style="width: 15%">By</th>
                                            <th style="width: 15%">To</th>
                                            <th class="text-center" style="width: 20%">Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $historyData = [
                                                ['31 Dec 21 10:11:11', 'Nov 21', 'Finalize', 'DR&D', '-', true],
                                                ['08 Dec 21 12:23:23', 'Nov 21', 'Forward', 'DCom', 'DR&D', false],
                                                ['28 Oct 21 11:17:17', 'Sep 21', 'Finalize', 'DR&D', '-', false],
                                                ['28 Sep 21 9:10:10', 'Sep 21', 'Forward', 'DCom', 'DR&D', false],
                                                ['24 Sep 21 11:31:31', 'Aug 21', 'Finalize', 'DR&D', '-', false],
                                                ['15 Jun 21 14:54:54', 'May 21', 'Edit', 'DR&D', '-', false],
                                                ['05 May 21 9:30:30', 'Apr 21', 'Finalize', 'DR&D', '-', false]
                                            ];
                                        @endphp

                                        @foreach($historyData as $row)
                                        <tr>
                                            <td class="text-muted font-weight-light">{{ $row[0] }}</td>
                                            <td><span class="badge badge-secondary badge-month">{{ $row[1] }}</span></td>
                                            <td>
                                                @if($row[2] == 'Finalize')
                                                    <span class="text-finalize"><i class="fas fa-check-circle mr-1"></i>{{ $row[2] }}</span>
                                                @elseif($row[2] == 'Forward')
                                                    <span class="text-forward"><i class="fas fa-paper-plane mr-1"></i>{{ $row[2] }}</span>
                                                @else
                                                    <span class="text-edit"><i class="fas fa-pen-alt mr-1"></i>{{ $row[2] }}</span>
                                                @endif
                                            </td>
                                            <td><span class="text-dark font-weight-bold">{{ $row[3] }}</span></td>
                                            <td>{{ $row[4] }}</td>
                                            <td class="text-center">
                                                <div class="icheck-primary d-inline">
                                                    <input type="checkbox" id="check{{$loop->index}}" {{ $row[5] ? 'checked' : '' }} disabled>
                                                    <label for="check{{$loop->index}}"></label>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="card-footer clearfix">
                            <span class="text-muted small">Showing last 15 actions</span>
                            <ul class="pagination pagination-sm m-0 float-right">
                                <li class="page-item"><a class="page-link" href="#">&laquo;</a></li>
                                <li class="page-item"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">&raquo;</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@endsection