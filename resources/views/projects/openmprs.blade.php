@extends('welcome')

@section('content')
<div class="content-wrapper">
<section class="content">
        <div class="container-fluid">
            
            <div class="card card-primary card-outline shadow">
                <div class="card-header">
                    <h3 class="card-title text-bold">
                        <i class="fas fa-list-alt mr-2"></i> Select Project for MPR
                    </h3>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead style="background: var(--rd-surface2); color: var(--rd-text2);">
                            <tr>
                                <th>Code</th>
                                <th>Project Title</th>
                                <th>Sponsor</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projects as $project)
                            <tr>
                                <td><span class="badge badge-secondary">{{ $project->prj_code ?? $project->prj_id }}</span></td>
                                <td class="font-weight-bold">{{ $project->prj_title }}</td>
                                <td>{{ $project->prj_sponsor ?? 'N/A' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('mpr.view', $project->prj_id) }}" class="btn btn-sm btn-primary shadow-sm">
                                        <i class="fas fa-eye mr-1"></i> View / Prepare MPR
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No projects available for reporting.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>
@endsection