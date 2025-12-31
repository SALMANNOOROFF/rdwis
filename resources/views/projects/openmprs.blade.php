@extends('welcome')

@section('content')
<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">MPR Overview - Nov 25</h3>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th style="width: 25%;">Department</th>
                                    <th style="width: 20%;">Project</th>
                                    <th style="width: 15%;">Status</th>
                                    <th style="width: 25%;">Progress Status</th>
                                    <th style="width: 15%; text-align: center;">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    // 4th value add ki hai: 'awaited' ya 'forwarded'
                                    $projects = [
                                        ['Communication Division', 'CDS', 'MPR-I', 'awaited'],
                                        ['Communication Division', 'SSMLP', 'MPR-I', 'forwarded'],
                                        ['Communication Division', 'NDB', 'MPR-I', 'awaited'],
                                        ['Communication Division', 'ELINT', 'MPR-I', 'forwarded'],
                                        ['Communication Division', 'SSSS', 'MPR-I', 'awaited'],
                                        ['Communication Division', 'TLI', 'MPR-I', 'forwarded'],
                                        ['Communication Division', '22', 'MPR-II', 'awaited'],
                                        ['Communication Division', 'ELINT-II', 'MPR-II', 'forwarded'],
                                        ['Communication Division', 'MWDC', 'MPR-II', 'awaited'],
                                        ['Communication Division', '123', 'MPR-II', 'forwarded']
                                    ];
                                @endphp

                                @foreach($projects as $p)
                                <tr>
                                    <td>{{ $p[0] }}</td>
                                    <td><strong>{{ $p[1] }}</strong></td>
                                    <td>
                                        <span class="badge badge-primary" style="font-size: 0.9rem;">
                                            {{ $p[2] }}
                                        </span>
                                    </td>
                                    <td>
                                        {{-- Logic to check status --}}
                                        @if($p[3] == 'forwarded')
                                            <span class="text-success font-weight-bold">
                                                <i class="fas fa-check-circle mr-1"></i> Forwarded
                                            </span>
                                        @else
                                            <span class="text-danger font-weight-bold">
                                                <i class="fas fa-exclamation-circle mr-1"></i> Action Awaited
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{route('viewmpr')}}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> View MPR
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-center align-items-center" style="gap: 10px;">
                        <select class="form-control" style="width: auto; display: inline-block;">
                            <option>MPR Part I</option>
                            <option>MPR Part II</option>
                        </select>
                        <button class="btn btn-default">
                            <i class="fas fa-search mr-1"></i> Preview
                        </button>
                        <button class="btn btn-success">
                            <i class="fas fa-file-word mr-1"></i> Export to Word
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>
@endsection