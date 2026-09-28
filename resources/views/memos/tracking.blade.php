@extends('layouts.master')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <div class="d-flex align-items-center mb-1">
                    <a href="{{ route('memos.show', $memo->id) }}" class="btn btn-outline-secondary btn-sm mr-2" title="Back to Memo">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h1 class="m-0 text-dark font-weight-bold h4">
                        Acknowledgment Audit & Tracking
                    </h1>
                </div>
                <div class="small text-muted ml-sm-4 pl-sm-2">
                    Memo: <strong>{{ $memo->title }}</strong>
                    @if($memo->reference_number)
                        <span class="badge badge-light border font-mono ml-1">{{ $memo->reference_number }}</span>
                    @endif
                </div>
            </div>
            <div class="col-sm-6 text-sm-right mt-3 mt-sm-0">
                <div class="btn-group shadow-sm">
                    <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-outline-success btn-sm font-weight-bold">
                        <i class="fas fa-file-excel mr-1"></i> Export to CSV
                    </a>
                    <button type="button" class="btn btn-outline-secondary btn-sm font-weight-bold" onclick="window.print()">
                        <i class="fas fa-print mr-1"></i> Print Report
                    </button>
                    <a href="{{ route('memos.show', $memo->id) }}" class="btn btn-maroon btn-sm font-weight-bold">
                        <i class="fas fa-eye mr-1"></i> View Memo Document
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content pb-5">
    <div class="container-fluid">

        <!-- Stat Cards Row -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card shadow-sm border-0 bg-white">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="bg-primary text-white rounded p-3 mr-3 shadow-sm">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                        <div>
                            <div class="text-muted small text-uppercase font-weight-bold">Target Recipients</div>
                            <h3 class="font-weight-bold mb-0 text-dark">{{ $stats['total'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card shadow-sm border-0 bg-white">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="bg-success text-white rounded p-3 mr-3 shadow-sm">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                        <div>
                            <div class="text-muted small text-uppercase font-weight-bold">Acknowledged</div>
                            <h3 class="font-weight-bold mb-0 text-success">{{ $stats['acknowledged'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card shadow-sm border-0 bg-white">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="bg-danger text-white rounded p-3 mr-3 shadow-sm">
                            <i class="fas fa-hourglass-half fa-2x"></i>
                        </div>
                        <div>
                            <div class="text-muted small text-uppercase font-weight-bold">Pending Compliance</div>
                            <h3 class="font-weight-bold mb-0 text-danger">{{ $stats['pending'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card shadow-sm border-0 bg-white">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="bg-info text-white rounded p-3 mr-3 shadow-sm">
                            <i class="fas fa-percentage fa-2x"></i>
                        </div>
                        <div>
                            <div class="text-muted small text-uppercase font-weight-bold">Compliance Rate</div>
                            <h3 class="font-weight-bold mb-0 text-info">{{ $stats['percentage'] }}%</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compliance Progress Bar -->
        <div class="card card-outline card-maroon shadow-sm mb-4">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center mb-1 small font-weight-bold">
                    <span>Overall Organization Compliance: <strong>{{ $stats['acknowledged'] }}</strong> of <strong>{{ $stats['total'] }}</strong> acknowledged</span>
                    <span class="text-success">{{ $stats['percentage'] }}% Complete</span>
                </div>
                <div class="progress" style="height: 12px; border-radius: 6px;">
                    <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" 
                         style="width: {{ $stats['percentage'] }}%;" aria-valuenow="{{ $stats['percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>

        <!-- Filter Tabs & User List Table -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center flex-wrap">
                <ul class="nav nav-pills card-header-pills">
                    <li class="nav-item">
                        <a class="nav-link {{ $filterStatus === 'all' ? 'active bg-maroon' : 'text-dark' }}" 
                           href="{{ route('memos.tracking', ['id' => $memo->id, 'status' => 'all']) }}">
                            All Recipients ({{ $stats['total'] }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $filterStatus === 'acknowledged' ? 'active bg-success' : 'text-dark' }}" 
                           href="{{ route('memos.tracking', ['id' => $memo->id, 'status' => 'acknowledged']) }}">
                            Acknowledged ({{ $stats['acknowledged'] }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $filterStatus === 'pending' ? 'active bg-danger' : 'text-dark' }}" 
                           href="{{ route('memos.tracking', ['id' => $memo->id, 'status' => 'pending']) }}">
                            Pending Action ({{ $stats['pending'] }})
                        </a>
                    </li>
                </ul>
                <div class="small text-muted">
                    Showing <strong>{{ $recipientsData->count() }}</strong> users
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 30%;">Staff Member</th>
                                <th style="width: 20%;">Role / Department</th>
                                <th style="width: 20%;">Compliance Status</th>
                                <th style="width: 15%;">Acknowledged At</th>
                                <th style="width: 15%;">Audit IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recipientsData as $userRow)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-light border p-2 mr-2 text-center" style="width: 36px; height: 36px;">
                                                <i class="fas fa-user text-secondary"></i>
                                            </div>
                                            <div>
                                                <strong class="text-dark d-block">{{ $userRow['name'] }}</strong>
                                                <span class="text-muted small">{{ $userRow['email'] }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-light border text-secondary font-weight-normal">
                                            {{ $userRow['roles'] ?: 'User' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($userRow['status'] === 'Acknowledged')
                                            <span class="badge badge-success px-2 py-1 font-weight-bold">
                                                <i class="fas fa-check-circle mr-1"></i> Acknowledged
                                            </span>
                                        @else
                                            <span class="badge badge-warning text-dark px-2 py-1 font-weight-bold">
                                                <i class="fas fa-clock mr-1"></i> Pending Action
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($userRow['acknowledged_at'])
                                            <span class="small font-mono text-dark">{{ $userRow['acknowledged_at'] }}</span>
                                        @else
                                            <span class="text-muted small">&mdash;</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($userRow['ip_address'])
                                            <code class="small text-secondary">{{ $userRow['ip_address'] }}</code>
                                        @else
                                            <span class="text-muted small">&mdash;</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fas fa-user-check fa-3x mb-2 text-secondary"></i>
                                        <p class="mb-0">No recipient records match the selected filter.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection

@section('css')
<style>
.bg-maroon {
    background-color: #800000 !important;
    color: #ffffff !important;
}
.btn-maroon {
    background-color: #800000;
    border-color: #800000;
    color: #ffffff;
}
.btn-maroon:hover {
    background-color: #660000;
    border-color: #660000;
    color: #ffffff;
}
.text-maroon {
    color: #800000 !important;
}
.card-maroon.card-outline {
    border-top: 3px solid #800000;
}
.font-mono {
    font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}
@media print {
    .btn, .navbar, .main-header, .main-footer, .card-header-pills {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>
@endsection
