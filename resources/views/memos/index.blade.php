@extends('layouts.master')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-file-signature text-maroon mr-2"></i>Memos & Letters
                </h1>
                <p class="text-muted small mb-0">Official company circulars, policy letters, safety notices, and staff acknowledgments</p>
            </div>
            <div class="col-sm-6 text-sm-right mt-2 mt-sm-0">
                @if($isAdmin || auth()->user()->can('create_memos'))
                    <a href="{{ route('memos.create') }}" class="btn btn-maroon btn-sm shadow-sm font-weight-bold">
                        <i class="fas fa-upload mr-1"></i> Upload New
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle mr-1"></i> {{ session('info') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Filter & Search Toolbar -->
        <div class="card card-outline card-maroon shadow-sm mb-4">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('memos.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-4 col-sm-12 mb-2 mb-md-0">
                        <label class="small text-muted mb-1 font-weight-bold">Search</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control" placeholder="Search by title, ref no, description..." value="{{ $filters['search'] ?? '' }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label class="small text-muted mb-1 font-weight-bold">Category</label>
                        <select name="category" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <option value="general" {{ ($filters['category'] ?? '') === 'general' ? 'selected' : '' }}>General Circular</option>
                            <option value="policy" {{ ($filters['category'] ?? '') === 'policy' ? 'selected' : '' }}>Company Policy</option>
                            <option value="safety" {{ ($filters['category'] ?? '') === 'safety' ? 'selected' : '' }}>Health & Safety</option>
                            <option value="hr" {{ ($filters['category'] ?? '') === 'hr' ? 'selected' : '' }}>HR & Administration</option>
                            <option value="operations" {{ ($filters['category'] ?? '') === 'operations' ? 'selected' : '' }}>Operations & Technical</option>
                            <option value="urgent" {{ ($filters['category'] ?? '') === 'urgent' ? 'selected' : '' }}>Urgent Notice</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label class="small text-muted mb-1 font-weight-bold">Status</label>
                        <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="published" {{ ($filters['status'] ?? '') === 'published' ? 'selected' : '' }}>Published (Active)</option>
                            <option value="archived" {{ ($filters['status'] ?? '') === 'archived' ? 'selected' : '' }}>Archived</option>
                            <option value="all" {{ ($filters['status'] ?? '') === 'all' ? 'selected' : '' }}>All Memos</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-12 text-md-right">
                        @if(!empty(array_filter($filters ?? [])))
                            <a href="{{ route('memos.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="fas fa-undo mr-1"></i> Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Memos Table List -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 35%;">Memo / Subject</th>
                                <th style="width: 15%;">Document</th>
                                <th style="width: 12%;">Date</th>
                                <th style="width: 15%;">Your Status</th>
                                @if($isAdmin)
                                    <th style="width: 13%;">Acknowledgment</th>
                                @endif
                                <th style="width: 10%;" class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($memos as $memo)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-start">
                                            <div class="mr-3 mt-1">
                                                @if($memo->category === 'urgent')
                                                    <span class="badge badge-danger p-2"><i class="fas fa-exclamation-triangle"></i></span>
                                                @elseif($memo->category === 'safety')
                                                    <span class="badge badge-warning p-2 text-dark"><i class="fas fa-hard-hat"></i></span>
                                                @elseif($memo->category === 'policy')
                                                    <span class="badge badge-info p-2"><i class="fas fa-balance-scale"></i></span>
                                                @else
                                                    <span class="badge badge-secondary p-2"><i class="fas fa-file-alt"></i></span>
                                                @endif
                                            </div>
                                            <div>
                                                <a href="{{ route('memos.show', $memo->id) }}" class="font-weight-bold text-dark h6 mb-1 d-block">
                                                    {{ $memo->title }}
                                                </a>
                                                <div class="small text-muted">
                                                    @if($memo->reference_number)
                                                        <span class="badge badge-light border mr-1 font-mono">
                                                            <i class="fas fa-hashtag text-muted mr-1"></i>{{ $memo->reference_number }}
                                                        </span>
                                                    @endif
                                                    <span class="badge badge-light border text-capitalize mr-1">
                                                        {{ str_replace('_', ' ', $memo->category) }}
                                                    </span>
                                                    @if($memo->isExpired())
                                                        <span class="badge badge-danger">Expired</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($memo->file_type === 'pdf')
                                                <i class="far fa-file-pdf text-danger fa-2x mr-2"></i>
                                            @elseif(in_array($memo->file_type, ['doc', 'docx']))
                                                <i class="far fa-file-word text-primary fa-2x mr-2"></i>
                                            @elseif(in_array($memo->file_type, ['jpg', 'jpeg', 'png']))
                                                <i class="far fa-file-image text-success fa-2x mr-2"></i>
                                            @else
                                                <i class="far fa-file-alt text-secondary fa-2x mr-2"></i>
                                            @endif
                                            <div class="small">
                                                <a href="{{ route('memos.show', $memo->id) }}" class="text-truncate d-block text-muted font-weight-bold" style="max-width: 140px;" title="View {{ $memo->file_name }}">
                                                    {{ $memo->file_name }}
                                                </a>
                                                <span class="text-muted small">{{ $memo->formattedFileSize() }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <span class="font-weight-bold text-dark">{{ $memo->memo_date ? $memo->memo_date->format('M d, Y') : '-' }}</span>
                                            <div class="text-muted small">By: {{ $memo->uploader ? $memo->uploader->name : 'System' }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($memo->has_acknowledged)
                                            <span class="badge badge-success px-2 py-1 font-weight-normal">
                                                <i class="fas fa-check-circle mr-1"></i> Acknowledged
                                            </span>
                                        @else
                                            <a href="{{ route('memos.show', $memo->id) }}#acknowledgment-card" class="badge badge-warning text-dark px-2 py-1 font-weight-bold">
                                                <i class="fas fa-clock mr-1"></i> Pending Action
                                            </a>
                                        @endif
                                    </td>
                                    @if($isAdmin)
                                        <td>
                                            @php $st = $memo->stats; @endphp
                                            <div class="small">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span><strong>{{ $st['acknowledged'] }}</strong> / {{ $st['total'] }}</span>
                                                    <span class="text-muted font-weight-bold">{{ $st['percentage'] }}%</span>
                                                </div>
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $st['percentage'] }}%;"></div>
                                                </div>
                                            </div>
                                        </td>
                                    @endif
                                    <td class="text-right">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('memos.show', $memo->id) }}" class="btn btn-outline-primary" title="View Memo">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ $memo->whatsAppShareUrl() }}" target="_blank" class="btn btn-outline-success" title="Share via WhatsApp">
                                                <i class="fab fa-whatsapp"></i>
                                            </a>
                                            @if($isAdmin)
                                                <a href="{{ route('memos.tracking', $memo->id) }}" class="btn btn-outline-info" title="View Acknowledgment Tracking">
                                                    <i class="fas fa-chart-pie"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isAdmin ? 6 : 5 }}" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-folder-open fa-3x mb-3 text-secondary"></i>
                                            <h5>No memos or letters found</h5>
                                            <p class="small text-muted mb-3">There are currently no circulars or notices matching your criteria.</p>
                                            @if($isAdmin || auth()->user()->can('create_memos'))
                                                <a href="{{ route('memos.create') }}" class="btn btn-maroon btn-sm">
                                                    <i class="fas fa-plus mr-1"></i> Upload the First Memo
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($memos->hasPages())
                <div class="card-footer bg-white border-top py-3 d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <div class="small text-muted mb-2 mb-md-0">
                        Showing <strong>{{ $memos->firstItem() }}</strong> to <strong>{{ $memos->lastItem() }}</strong> of <strong>{{ $memos->total() }}</strong> memos
                    </div>
                    <div>
                        {{ $memos->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection

@section('css')
<style>
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
.pagination {
    margin-bottom: 0;
}
.page-item.active .page-link {
    background-color: #800000;
    border-color: #800000;
    color: #ffffff;
}
.page-link {
    color: #800000;
}
.page-link:hover {
    color: #660000;
}
.pagination svg {
    max-width: 1rem;
    max-height: 1rem;
}
</style>
@endsection
