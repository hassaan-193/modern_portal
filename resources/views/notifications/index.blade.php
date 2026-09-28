@extends('layouts.master')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-bell text-maroon mr-2"></i>Notifications
                </h1>
                <p class="text-muted small mb-0">Stay updated on official memos, announcements, approvals, and system alerts</p>
            </div>
            <div class="col-sm-6 text-sm-right mt-2 mt-sm-0">
                @if($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.markAllRead') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm shadow-sm font-weight-bold">
                            <i class="fas fa-check-double mr-1 text-success"></i> Mark All as Read
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<section class="content pb-5">
    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Filter Tabs -->
        <div class="card card-outline card-maroon shadow-sm mb-4">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                <ul class="nav nav-pills card-header-pills small font-weight-bold">
                    <li class="nav-item">
                        <a class="nav-link {{ $filter === 'all' ? 'active bg-maroon' : 'text-dark' }}" href="{{ route('notifications.index', ['filter' => 'all']) }}">
                            All Notifications <span class="badge badge-light ml-1">{{ $totalCount }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $filter === 'unread' ? 'active bg-maroon' : 'text-dark' }}" href="{{ route('notifications.index', ['filter' => 'unread']) }}">
                            Unread <span class="badge {{ $unreadCount > 0 ? 'badge-danger' : 'badge-light' }} ml-1">{{ $unreadCount }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $filter === 'read' ? 'active bg-maroon' : 'text-dark' }}" href="{{ route('notifications.index', ['filter' => 'read']) }}">
                            Read <span class="badge badge-light ml-1">{{ max(0, $totalCount - $unreadCount) }}</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body p-0">
                @if($notifications->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($notifications as $n)
                            @php
                                $data = is_array($n->data) ? $n->data : json_decode($n->data, true) ?? [];
                                $title = $data['title'] ?? $data['message'] ?? 'Notification';
                                $note = $data['note'] ?? $data['body'] ?? '';
                                $action = route('notifications.open', $n->id);
                                $isUnread = $n->unread();
                                $isMemo = str_contains($n->type, 'Memo') || str_contains($title, 'Memo');
                            @endphp
                            <a href="{{ $action }}" class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center justify-content-between {{ $isUnread ? 'bg-light border-left-unread' : '' }}" style="transition: all 0.15s ease;">
                                <div class="d-flex align-items-start pr-3">
                                    <div class="rounded-circle mr-3 p-3 shadow-sm d-flex align-items-center justify-content-center text-white" 
                                         style="width: 44px; height: 44px; min-width: 44px; background-color: {{ $isMemo ? '#800000' : ($isUnread ? '#007bff' : '#6c757d') }};">
                                        <i class="fas {{ $isMemo ? 'fa-file-signature' : 'fa-bell' }}"></i>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center mb-1 flex-wrap">
                                            <h6 class="mb-0 font-weight-bold {{ $isUnread ? 'text-dark' : 'text-secondary' }} mr-2">
                                                {{ $title }}
                                            </h6>
                                            @if($isUnread)
                                                <span class="badge badge-danger text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">New</span>
                                            @endif
                                        </div>
                                        @if($note)
                                            <p class="text-muted small mb-1">{{ $note }}</p>
                                        @endif
                                        <div class="small text-muted">
                                            <i class="far fa-clock mr-1"></i>{{ $n->created_at->diffForHumans() }} &bull; {{ $n->created_at->format('M d, Y h:i A') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-nowrap pl-2 text-right">
                                    <span class="btn btn-sm btn-outline-primary font-weight-bold">
                                        View &rarr;
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="far fa-bell-slash fa-3x mb-3 text-secondary"></i>
                        <h5>No notifications found</h5>
                        <p class="small text-muted mb-0">
                            @if($filter === 'unread')
                                You have caught up with all your notifications!
                            @else
                                You do not have any notifications at the moment.
                            @endif
                        </p>
                    </div>
                @endif
            </div>

            @if($notifications->hasPages())
                <div class="card-footer bg-white border-top py-3 d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <div class="small text-muted mb-2 mb-md-0">
                        Showing <strong>{{ $notifications->firstItem() }}</strong> to <strong>{{ $notifications->lastItem() }}</strong> of <strong>{{ $notifications->total() }}</strong> notifications
                    </div>
                    <div>
                        {{ $notifications->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @endif
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
.text-maroon {
    color: #800000 !important;
}
.card-maroon.card-outline {
    border-top: 3px solid #800000;
}
.border-left-unread {
    border-left: 4px solid #800000 !important;
    background-color: #fdfaf8 !important;
}
.list-group-item-action:hover {
    background-color: #f8f9fa !important;
}
.pagination {
    margin-bottom: 0;
}
.page-item.active .page-link {
    background-color: #800000;
    border-color: #800000;
}
.page-link {
    color: #800000;
}
</style>
@endsection
