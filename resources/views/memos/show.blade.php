@extends('layouts.master')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <div class="d-flex align-items-center mb-1">
                    <a href="{{ route('memos.index') }}" class="btn btn-outline-secondary btn-sm mr-2" title="Back to Memos">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h1 class="m-0 text-dark font-weight-bold h4">
                        {{ $memo->title }}
                    </h1>
                </div>
                <div class="small text-muted ml-sm-4 pl-sm-2">
                    @if($memo->reference_number)
                        <span class="badge badge-light border font-mono mr-1">
                            <i class="fas fa-hashtag text-muted mr-1"></i>{{ $memo->reference_number }}
                        </span>
                    @endif
                    <span class="badge badge-light border text-capitalize mr-1">
                        <i class="fas fa-tag text-muted mr-1"></i>{{ str_replace('_', ' ', $memo->category) }}
                    </span>
                    <span class="text-muted mr-2">
                        <i class="far fa-calendar-alt mr-1"></i>{{ $memo->memo_date ? $memo->memo_date->format('F d, Y') : '-' }}
                    </span>
                    <span class="text-muted">
                        <i class="far fa-user mr-1"></i>Published by {{ $memo->uploader ? $memo->uploader->name : 'Management' }}
                    </span>
                </div>
            </div>

            <!-- Action Toolbar: WhatsApp, Copy Link, Admin Tracking -->
            <div class="col-sm-6 text-sm-right mt-3 mt-sm-0">
                <div class="btn-group shadow-sm">
                    <a href="{{ $shareUrl }}" target="_blank" class="btn btn-success btn-sm font-weight-bold" title="Share via WhatsApp">
                        <i class="fab fa-whatsapp mr-1"></i> Share via WhatsApp
                    </a>
                    <button type="button" class="btn btn-light border btn-sm font-weight-bold" onclick="copyShareLink()" title="Copy Memo URL">
                        <i class="fas fa-link mr-1"></i> Copy Link
                    </button>
                    @if($isAdmin)
                        <a href="{{ route('memos.tracking', $memo->id) }}" class="btn btn-info btn-sm font-weight-bold" title="View Acknowledgment Status">
                            <i class="fas fa-chart-pie mr-1"></i> Tracking
                        </a>
                    @endif
                </div>
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

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-info-circle mr-1"></i> {{ session('info') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Full-Width Description Banner -->
        @if($memo->description)
            <div class="row">
                <div class="col-12 mb-3">
                    <div class="card card-outline card-maroon shadow-sm mb-0">
                        <div class="card-body py-3 px-4">
                            <h6 class="font-weight-bold text-dark mb-1">
                                <i class="fas fa-info-circle text-info mr-1"></i> Summary & Instructions:
                            </h6>
                            <p class="mb-0 text-secondary" style="white-space: pre-line; font-size: 0.95rem; line-height: 1.6;">{{ $memo->description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Center-Aligned Constrained Layout for Document & Actions -->
        <div class="row justify-content-center">
            <div class="col-xl-9 col-lg-10 col-md-11 col-12" style="max-width: 900px;">
                
                <!-- Official Document Viewer Card -->
                <div class="card card-outline card-maroon shadow-sm mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title font-weight-bold mb-0 text-dark">
                            <i class="fas fa-file-contract text-maroon mr-2"></i>Official Memo Document
                        </h5>
                        <div class="small text-muted font-weight-bold">
                            {{ strtoupper($memo->file_type) }} &bull; {{ $memo->formattedFileSize() }}
                        </div>
                    </div>
                    <div class="card-body p-2 p-md-3">
                        <!-- Document Viewer Switcher based on file type -->
                        @if($memo->file_type === 'pdf')
                            <div class="document-preview-container rounded overflow-hidden shadow-sm mx-auto" 
                                 style="width: 100%; min-height: 520px; background-color: #525659; border: 1px solid #dee2e6;">
                                <iframe src="{{ route('memos.file', $memo->id) }}#view=FitH&toolbar=0&navpanes=0" 
                                        width="100%" height="520px" style="border: none; display: block;"
                                        title="PDF Preview">
                                    <div class="p-4 text-center text-white">
                                        <p>Your browser does not support inline PDF viewing.</p>
                                    </div>
                                </iframe>
                            </div>
                        @elseif(in_array($memo->file_type, ['jpg', 'jpeg', 'png']))
                            <div class="text-center p-3 bg-light rounded border mx-auto">
                                <img src="{{ route('memos.file', $memo->id) }}" 
                                     alt="{{ $memo->title }}" class="img-fluid rounded shadow-sm" style="max-height: 600px;">
                            </div>
                        @else
                            <!-- Word / Generic Document Card -->
                            <div class="card bg-light border p-4 text-center">
                                <div class="mb-3">
                                    <i class="far fa-file-word text-primary fa-4x"></i>
                                </div>
                                <h5 class="font-weight-bold text-dark">{{ $memo->file_name }}</h5>
                                <p class="text-muted small mb-0">This document is formatted as a Microsoft Word file ({{ strtoupper($memo->file_type) }}).</p>
                            </div>
                        @endif
                    </div>
                    <div class="card-footer bg-white border-top py-2 d-flex justify-content-between align-items-center small text-muted">
                        <span><i class="fas fa-file-alt mr-1"></i> Document: <code>{{ $memo->file_name }}</code></span>
                        <span class="badge badge-light border text-muted"><i class="fas fa-eye mr-1"></i> View Only</span>
                    </div>
                </div>

                <!-- Beneath Document: Acknowledgment Section -->
                <div id="acknowledgment-card" class="mb-4">
                    @if($isAcknowledged)
                        <!-- State 1: Already Acknowledged -->
                        <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); border-left: 6px solid #2e7d32 !important;">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-success text-white rounded-circle p-3 mr-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="fas fa-check fa-lg"></i>
                                    </div>
                                    <div>
                                        <h5 class="font-weight-bold text-success mb-0">Acknowledged</h5>
                                        <div class="small text-muted font-weight-bold">Compliance Status Recorded</div>
                                    </div>
                                </div>

                                <p class="text-dark small mb-3">
                                    You have officially read, understood, and acknowledged this memo.
                                </p>

                                <div class="bg-white p-3 rounded shadow-sm small border">
                                    <div class="row">
                                        <div class="col-md-6 mb-2 mb-md-0">
                                            <span class="text-muted d-block">Acknowledged At:</span>
                                            <strong class="text-dark font-mono">
                                                <i class="far fa-clock text-success mr-1"></i>{{ $acknowledgment->acknowledged_at->format('M d, Y - h:i A') }}
                                            </strong>
                                        </div>
                                        <div class="col-md-6 mb-2 mb-md-0">
                                            <span class="text-muted d-block">Acknowledged By:</span>
                                            <strong class="text-dark">
                                                <i class="far fa-user text-muted mr-1"></i>{{ auth()->user()->name }} ({{ auth()->user()->email }})
                                            </strong>
                                        </div>
                                    </div>
                                    @if($acknowledgment->ip_address)
                                        <div class="mt-2 pt-2 border-top">
                                            <span class="text-muted mr-2">Logged IP Address:</span>
                                            <code class="text-secondary">{{ $acknowledgment->ip_address }}</code>
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-3 text-center">
                                    <span class="badge badge-success px-3 py-2 text-uppercase font-weight-bold" style="letter-spacing: 0.5px;">
                                        <i class="fas fa-shield-alt mr-1"></i> Legally Binding Acknowledgment
                                    </span>
                                </div>
                            </div>
                        </div>

                    @else
                        <!-- State 2: Acknowledgment Required -->
                        <div class="card card-outline card-warning shadow-sm mb-4">
                            <div class="card-header bg-white py-3">
                                <h5 class="card-title font-weight-bold text-dark mb-0">
                                    <i class="fas fa-pen-alt text-warning mr-2"></i>Staff Acknowledgment
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="alert alert-warning mb-3 small">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> <strong>Action Required:</strong>
                                    You are officially required to review this document and record your acknowledgment below.
                                </div>

                                <form method="POST" action="{{ route('memos.acknowledge', $memo->id) }}" id="acknowledgmentForm">
                                    @csrf

                                    <!-- Mandatory Confirmation Checkbox -->
                                    <div class="custom-control custom-checkbox mb-4 p-3 bg-light rounded border">
                                        <input type="checkbox" class="custom-control-input" id="confirm_understood" name="confirm_understood" value="1" required onchange="toggleAckButton(this)">
                                        <label class="custom-control-label font-weight-bold text-dark user-select-none" for="confirm_understood" style="cursor: pointer; line-height: 1.4;">
                                            I confirm that I have read and carefully understood this memo/letter.
                                        </label>
                                        <small class="text-muted d-block mt-2">
                                            By checking this box and clicking the button, your confirmation, timestamp, and identity will be securely logged in company records.
                                        </small>
                                    </div>

                                    <!-- Acknowledge Button (Disabled by default) -->
                                    <button type="submit" id="ackBtn" class="btn btn-success btn-block btn-lg font-weight-bold shadow-sm" disabled>
                                        <i class="fas fa-check-circle mr-1"></i> Acknowledge Memo
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Beneath Acknowledgment: WhatsApp Share & Admin Widget in 2 Columns -->
                <div class="row">
                    <div class="{{ $isAdmin && $stats ? 'col-md-7' : 'col-12' }} mb-3">
                        <!-- Share Link Details Box -->
                        <div class="card bg-light border shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-dark mb-2">
                                    <i class="fab fa-whatsapp text-success mr-1"></i> Shareable Memo Link
                                </h6>
                                <p class="small text-muted mb-2">
                                    Share this link directly with colleagues. If they are not logged in, they will be securely guided to the login screen and automatically redirected back to this exact memo.
                                </p>
                                <div class="input-group input-group-sm mb-3">
                                    <input type="text" id="shareUrlInput" class="form-control bg-white font-mono small" value="{{ route('memos.show', $memo->id) }}" readonly>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="copyLinkBtn" onclick="copyShareLink()">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                                <a href="{{ $shareUrl }}" target="_blank" class="btn btn-success btn-block btn-sm font-weight-bold">
                                    <i class="fab fa-whatsapp mr-1"></i> Send via WhatsApp
                                </a>
                            </div>
                        </div>
                    </div>

                    @if($isAdmin && $stats)
                        <div class="col-md-5 mb-3">
                            <!-- Admin Progress Widget -->
                            <div class="card card-outline card-info shadow-sm h-100">
                                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                                    <h6 class="font-weight-bold text-dark mb-0">
                                        <i class="fas fa-users-cog text-info mr-1"></i>Recipient Response
                                    </h6>
                                    <a href="{{ route('memos.tracking', $memo->id) }}" class="small text-info font-weight-bold">
                                        Details &rarr;
                                    </a>
                                </div>
                                <div class="card-body py-3">
                                    <div class="row text-center mb-2">
                                        <div class="col-4">
                                            <div class="h5 font-weight-bold mb-0 text-dark">{{ $stats['total'] }}</div>
                                            <small class="text-muted">Target</small>
                                        </div>
                                        <div class="col-4">
                                            <div class="h5 font-weight-bold mb-0 text-success">{{ $stats['acknowledged'] }}</div>
                                            <small class="text-muted">Ack'd</small>
                                        </div>
                                        <div class="col-4">
                                            <div class="h5 font-weight-bold mb-0 text-danger">{{ $stats['pending'] }}</div>
                                            <small class="text-muted">Pending</small>
                                        </div>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $stats['percentage'] }}%;"></div>
                                    </div>
                                    <div class="text-center small text-muted font-weight-bold mt-2">
                                        {{ $stats['percentage'] }}% Compliance
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

            </div>
        </div>

    </div>
</section>
@endsection

@section('scripts')
<script>
function toggleAckButton(checkbox) {
    const btn = document.getElementById('ackBtn');
    if (btn) {
        btn.disabled = !checkbox.checked;
    }
}

const ackForm = document.getElementById('acknowledgmentForm');
if (ackForm) {
    ackForm.addEventListener('submit', function() {
        const btn = document.getElementById('ackBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Recording Acknowledgment...';
    });
}

function copyShareLink() {
    const input = document.getElementById('shareUrlInput');
    if (!input) return;
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(function() {
        const copyBtn = document.getElementById('copyLinkBtn');
        if (copyBtn) {
            const originalText = copyBtn.innerHTML;
            copyBtn.innerHTML = '<i class="fas fa-check text-success"></i>';
            setTimeout(function() {
                copyBtn.innerHTML = originalText;
            }, 2000);
        }
        if (window.toast) {
            window.toast.fire({
                icon: 'success',
                title: 'Memo link copied to clipboard!'
            });
        }
    });
}
</script>
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
</style>
@endsection
