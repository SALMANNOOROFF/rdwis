@extends('welcome')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-danger font-weight-bold"><i class="fas fa-radiation-alt mr-2"></i> God Mode Panel</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card card-danger card-outline">
            <div class="card-header">
                <h3 class="card-title">Absolute User Control List</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Name</th>
                            <th>Rank</th>
                            <th>Designation</th>
                            <th>Unit/Area</th>
                            <th>Status</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $u)
                        <tr>
                            <td>{{ $u->acc_id }}</td>
                            <td class="font-weight-bold">{{ $u->acc_username }}</td>
                            <td>{{ $u->acc_name }}</td>
                            <td>{{ $u->acc_rank }}</td>
                            <td>{{ $u->acc_desig }}</td>
                            <td><span class="badge badge-secondary">{{ strtoupper($u->acc_untarea) }}</span></td>
                            <td>
                                @if($u->acc_status === 'Active')
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">{{ $u->acc_status }}</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @if($u->acc_username !== 'superadminrdw')
                                <a href="/godmode/takeover/{{ $u->acc_id }}" class="btn btn-sm btn-danger" onclick="return confirm('Take control of this user account?');"><i class="fas fa-user-secret"></i> Take Control</a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
