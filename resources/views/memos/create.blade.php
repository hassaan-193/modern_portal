@extends('layouts.master')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-upload text-maroon mr-2"></i>Upload Memo / Official Letter
                </h1>
                <p class="text-muted small mb-0">Publish an official circular, safety guideline, or company notice to staff</p>
            </div>
            <div class="col-sm-6 text-sm-right mt-2 mt-sm-0">
                <a href="{{ route('memos.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Back to All Memos
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="font-weight-bold mb-1"><i class="fas fa-exclamation-circle mr-1"></i> Please correct the following errors:</div>
                <ul class="mb-0 pl-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="card card-outline card-maroon shadow-sm">
            <div class="card-header bg-white py-3">
                <h3 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-file-alt text-maroon mr-2"></i>Memo Details & Document Attachment
                </h3>
            </div>
            <form method="POST" action="{{ route('memos.store') }}" enctype="multipart/form-data" id="memoUploadForm">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <!-- Left Column: Primary Details -->
                        <div class="col-md-7 col-sm-12">
                            <!-- Title -->
                            <div class="form-group">
                                <label for="title" class="font-weight-bold">
                                    Memo / Letter Title <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="title" id="title" class="form-control form-control-lg @error('title') is-invalid @enderror" 
                                       placeholder="e.g. Health & Safety Protocol Update - Q4 2026" value="{{ old('title') }}" required>
                                <small class="text-muted">A clear, descriptive title that appears in user notifications and headers.</small>
                            </div>

                            <div class="row">
                                <!-- Reference Number -->
                                <div class="col-md-6 form-group">
                                    <label for="reference_number" class="font-weight-bold">Reference Number</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-hashtag text-muted"></i></span>
                                        </div>
                                        <input type="text" name="reference_number" id="reference_number" 
                                               class="form-control @error('reference_number') is-invalid @enderror" 
                                               placeholder="e.g. FTS-MEMO-2026-004" value="{{ old('reference_number') }}">
                                    </div>
                                    <small class="text-muted">Optional unique company reference or circular ID.</small>
                                </div>

                                <!-- Category -->
                                <div class="col-md-6 form-group">
                                    <label for="category" class="font-weight-bold">Category <span class="text-danger">*</span></label>
                                    <select name="category" id="category" class="form-control" required>
                                        <option value="general" {{ old('category') == 'general' ? 'selected' : '' }}>General Circular</option>
                                        <option value="policy" {{ old('category') == 'policy' ? 'selected' : '' }}>Company Policy</option>
                                        <option value="safety" {{ old('category', 'safety') == 'safety' ? 'selected' : '' }}>Health & Safety</option>
                                        <option value="hr" {{ old('category') == 'hr' ? 'selected' : '' }}>HR & Administration</option>
                                        <option value="operations" {{ old('category') == 'operations' ? 'selected' : '' }}>Operations & Technical</option>
                                        <option value="urgent" {{ old('category') == 'urgent' ? 'selected' : '' }}>Urgent Notice</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Memo Date -->
                                <div class="col-md-6 form-group">
                                    <label for="memo_date" class="font-weight-bold">Memo Date <span class="text-danger">*</span></label>
                                    <input type="date" name="memo_date" id="memo_date" class="form-control" value="{{ old('memo_date', date('Y-m-d')) }}" required>
                                </div>

                                <!-- Expiry Date (Optional) -->
                                <div class="col-md-6 form-group">
                                    <label for="expires_at" class="font-weight-bold">Expiry Date (Optional)</label>
                                    <input type="date" name="expires_at" id="expires_at" class="form-control" value="{{ old('expires_at') }}">
                                    <small class="text-muted">Leave empty if the memo has permanent validity.</small>
                                </div>
                            </div>

                            <!-- Description / Summary -->
                            <div class="form-group">
                                <label for="description" class="font-weight-bold">Summary / Description</label>
                                <textarea name="description" id="description" rows="4" class="form-control" 
                                          placeholder="Provide a brief context or instructions for this memo/letter...">{{ old('description') }}</textarea>
                            </div>
                        </div>

                        <!-- Right Column: Attachment & Target Audience -->
                        <div class="col-md-5 col-sm-12">
                            <!-- Document File Upload Box -->
                            <div class="card bg-light border mb-4">
                                <div class="card-body">
                                    <label class="font-weight-bold d-block mb-2">
                                        <i class="fas fa-paperclip text-maroon mr-1"></i> Attach Official Document <span class="text-danger">*</span>
                                    </label>
                                    
                                    <div class="custom-file mb-2">
                                        <input type="file" name="file" id="file" class="custom-file-input" 
                                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required onchange="updateFileName(this)">
                                        <label class="custom-file-label text-truncate" for="file" id="fileLabel">Choose document file...</label>
                                    </div>

                                    <div class="small text-muted mt-2">
                                        <div><i class="fas fa-check text-success mr-1"></i> Supported: <strong>PDF, DOC, DOCX, JPG, PNG</strong></div>
                                        <div><i class="fas fa-shield-alt text-info mr-1"></i> Max File Size: <strong>20 MB</strong></div>
                                        <div><i class="fas fa-ban text-danger mr-1"></i> Executables (.exe, .bat, .sh) are prohibited.</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Target Audience / Recipients -->
                            <div class="card bg-light border">
                                <div class="card-body">
                                    <label class="font-weight-bold d-block mb-2">
                                        <i class="fas fa-users text-maroon mr-1"></i> Target Audience & Recipients
                                    </label>
                                    <p class="small text-muted mb-3">Choose who should be required to read, acknowledge, and receive notifications for this memo.</p>

                                    <div class="custom-control custom-radio mb-2">
                                        <input type="radio" id="recip_all" name="recipient_type" value="all" class="custom-control-input" 
                                               {{ old('recipient_type', 'all') == 'all' ? 'checked' : '' }} onchange="toggleRecipientOptions()">
                                        <label class="custom-control-label font-weight-bold" for="recip_all">
                                            All Company Users & Staff
                                        </label>
                                        <div class="small text-muted pl-4">Distribute and notify all authenticated portal users.</div>
                                    </div>

                                    <div class="custom-control custom-radio mb-2">
                                        <input type="radio" id="recip_roles" name="recipient_type" value="roles" class="custom-control-input" 
                                               {{ old('recipient_type') == 'roles' ? 'checked' : '' }} onchange="toggleRecipientOptions()">
                                        <label class="custom-control-label font-weight-bold" for="recip_roles">
                                            Specific Roles Only
                                        </label>
                                        <div class="small text-muted pl-4">Target specific organizational roles (e.g. Staff, Engineers, Management).</div>
                                    </div>

                                    <!-- Roles Selection Box (hidden by default unless roles selected) -->
                                    <div id="rolesBox" class="mt-2 pl-4 mb-3" style="display: {{ old('recipient_type') == 'roles' ? 'block' : 'none' }};">
                                        <div class="card card-body p-2 border bg-white" style="max-height: 150px; overflow-y: auto;">
                                            @foreach($roles as $role)
                                                <div class="custom-control custom-checkbox small">
                                                    <input type="checkbox" name="recipient_roles[]" value="{{ $role }}" id="role_{{ $loop->index }}" 
                                                           class="custom-control-input" {{ is_array(old('recipient_roles')) && in_array($role, old('recipient_roles')) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="role_{{ $loop->index }}">{{ $role }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="recip_users" name="recipient_type" value="users" class="custom-control-input" 
                                               {{ old('recipient_type') == 'users' ? 'checked' : '' }} onchange="toggleRecipientOptions()">
                                        <label class="custom-control-label font-weight-bold" for="recip_users">
                                            Select Specific Individuals
                                        </label>
                                        <div class="small text-muted pl-4">Directly select specific employees from the user directory.</div>
                                    </div>

                                    <!-- Users Selection Box (hidden by default unless users selected) -->
                                    <div id="usersBox" class="mt-2 pl-4" style="display: {{ old('recipient_type') == 'users' ? 'block' : 'none' }};">
                                        <div class="card card-body p-2 border bg-white" style="max-height: 160px; overflow-y: auto;">
                                            @foreach($users as $u)
                                                <div class="custom-control custom-checkbox small mb-1">
                                                    <input type="checkbox" name="recipient_users[]" value="{{ $u->id }}" id="user_{{ $u->id }}" 
                                                           class="custom-control-input" {{ is_array(old('recipient_users')) && in_array($u->id, old('recipient_users')) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="user_{{ $u->id }}">
                                                        <strong>{{ $u->name }}</strong> <span class="text-muted">({{ $u->email }})</span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light border-top d-flex justify-content-between align-items-center py-3">
                    <a href="{{ route('memos.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-maroon px-4 font-weight-bold" id="submitBtn">
                        <i class="fas fa-paper-plane mr-1"></i> Publish & Notify Recipients
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
function updateFileName(input) {
    if (input.files && input.files[0]) {
        document.getElementById('fileLabel').innerText = input.files[0].name;
    }
}

function toggleRecipientOptions() {
    const isRoles = document.getElementById('recip_roles').checked;
    const isUsers = document.getElementById('recip_users').checked;

    document.getElementById('rolesBox').style.display = isRoles ? 'block' : 'none';
    document.getElementById('usersBox').style.display = isUsers ? 'block' : 'none';
}

document.getElementById('memoUploadForm').addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Uploading & Publishing...';
});
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
</style>
@endsection
