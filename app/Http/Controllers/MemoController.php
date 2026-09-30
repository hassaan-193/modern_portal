<?php

namespace App\Http\Controllers;

use App\Models\Memo;
use App\Models\MemoAcknowledgment;
use App\Notifications\NewMemoPublishedNotification;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class MemoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of memos and letters.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $this->isUserAdmin($user);

        $query = Memo::with(['uploader', 'acknowledgments'])
            ->latest('memo_date');

        // Non-admin users only see memos they are targeted for
        if (!$isAdmin) {
            $query->where(function ($q) use ($user) {
                // Memos targeted to all
                $q->where('recipient_type', 'all')
                  // Or uploaded by the user themselves
                  ->orWhere('uploaded_by', $user->id);

                // Or targeted by role
                if (method_exists($user, 'roles')) {
                    $userRoles = $user->roles->pluck('name')->toArray();
                    foreach ($userRoles as $roleName) {
                        $q->orWhere(function ($sub) use ($roleName) {
                            $sub->where('recipient_type', 'roles')
                                ->whereJsonContains('recipient_ids', $roleName);
                        });
                    }
                }

                // Or targeted directly by user ID
                $q->orWhere(function ($sub) use ($user) {
                    $sub->where('recipient_type', 'users')
                        ->whereJsonContains('recipient_ids', (int) $user->id)
                        ->orWhereJsonContains('recipient_ids', (string) $user->id);
                });
            });
        }

        // Filter: Category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter: Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'published');
        }

        // Search: Keyword
        if ($request->filled('search')) {
            $term = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                  ->orWhere('reference_number', 'like', $term)
                  ->orWhere('description', 'like', $term);
            });
        }

        $memos = $query->paginate(15)->withQueryString();

        // Attach acknowledgment stats and status
        foreach ($memos as $memo) {
            $memo->has_acknowledged = $memo->isAcknowledgedBy($user);
            if ($isAdmin) {
                $memo->stats = $memo->acknowledgmentStats();
            }
        }

        return view('memos.index', [
            'memos'   => $memos,
            'isAdmin' => $isAdmin,
            'filters' => $request->only(['category', 'status', 'search']),
        ]);
    }

    /**
     * Show the memo creation form.
     */
    public function create()
    {
        $user = Auth::user();
        if (!$this->isUserAdmin($user) && !$user->can('create_memos')) {
            abort(403, 'You are not authorized to upload new memos or letters.');
        }

        $roles = Role::orderBy('name')->pluck('name');
        $users = User::orderBy('name')->select('id', 'name', 'email')->get();

        return view('memos.create', [
            'roles' => $roles,
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created memo in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$this->isUserAdmin($user) && !$user->can('create_memos')) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'reference_number' => 'nullable|string|max:100|unique:memos,reference_number',
            'description'      => 'nullable|string',
            'category'         => 'required|string|max:50',
            'memo_date'        => 'required|date',
            'expires_at'       => 'nullable|date|after_or_equal:memo_date',
            'recipient_type'   => 'required|in:all,roles,users',
            'recipient_roles'  => 'nullable|array|required_if:recipient_type,roles',
            'recipient_users'  => 'nullable|array|required_if:recipient_type,users',
            'file'             => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:20480',
        ], [
            'file.mimes'       => 'The file must be a PDF, Word document (DOC, DOCX), or Image (JPG, PNG). Executable files are not allowed.',
            'file.max'         => 'The file size must not exceed 20MB.',
            'reference_number.unique' => 'This memo reference number has already been used.',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getClientMimeType();
        $fileSize = $file->getSize();

        // Generate safe unique storage name
        $safeFileName = 'memo_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
        $storedPath = $file->storeAs('memos', $safeFileName, 'public');

        // Determine recipient IDs
        $recipientIds = null;
        if ($validated['recipient_type'] === 'roles') {
            $recipientIds = $request->input('recipient_roles', []);
        } elseif ($validated['recipient_type'] === 'users') {
            $recipientIds = array_map('intval', $request->input('recipient_users', []));
        }

        DB::beginTransaction();
        try {
            $memo = Memo::create([
                'title'            => $validated['title'],
                'reference_number' => $validated['reference_number'] ?: null,
                'description'      => $validated['description'] ?: null,
                'category'         => $validated['category'],
                'file_path'        => $storedPath,
                'file_name'        => $originalName,
                'file_type'        => $extension,
                'file_size'        => $fileSize,
                'uploaded_by'      => $user->id,
                'recipient_type'   => $validated['recipient_type'],
                'recipient_ids'    => $recipientIds,
                'memo_date'        => $validated['memo_date'],
                'expires_at'       => $validated['expires_at'] ?? null,
                'status'           => 'published',
                'published_at'     => now(),
            ]);

            // Dispatch database notifications to target recipients
            $recipients = $memo->targetRecipients();
            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new NewMemoPublishedNotification($memo));
            }

            // Log activity audit if activity_log table exists
            if (function_exists('activity') && \Illuminate\Support\Facades\Schema::hasTable('activity_log')) {
                activity('memo')
                    ->performedOn($memo)
                    ->causedBy($user)
                    ->withProperties([
                        'title'          => $memo->title,
                        'reference'      => $memo->reference_number,
                        'recipient_type' => $memo->recipient_type,
                    ])
                    ->log('Memo published');
            }

            DB::commit();

            return redirect()->route('memos.show', $memo->id)
                ->with('success', 'Memo published successfully. Notifications sent to all targeted recipients.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to publish memo: ' . $e->getMessage());

            if (Storage::disk('public')->exists($storedPath)) {
                Storage::disk('public')->delete($storedPath);
            }

            return back()->withInput()->with('error', 'An error occurred while uploading the memo: ' . $e->getMessage());
        }
    }

    /**
     * Display a specific memo, its preview, WhatsApp share button, and acknowledgment form.
     */
    public function show($id)
    {
        $memo = Memo::with(['uploader', 'acknowledgments.user'])->findOrFail($id);
        $user = Auth::user();

        // Authorization check
        if (!$memo->canBeAccessedBy($user)) {
            abort(403, 'Access Denied: You are not authorized to view this memo/letter.');
        }

        $acknowledgment = $memo->getAcknowledgmentFor($user);
        $isAcknowledged = $acknowledgment !== null;
        $isAdmin = $this->isUserAdmin($user);
        $stats = $isAdmin ? $memo->acknowledgmentStats() : null;
        $shareUrl = $memo->whatsAppShareUrl();

        return view('memos.show', [
            'memo'           => $memo,
            'acknowledgment' => $acknowledgment,
            'isAcknowledged' => $isAcknowledged,
            'isAdmin'        => $isAdmin,
            'stats'          => $stats,
            'shareUrl'       => $shareUrl,
        ]);
    }

    /**
     * Record an acknowledgment for the memo.
     */
    public function acknowledge(Request $request, $id)
    {
        $memo = Memo::findOrFail($id);
        $user = Auth::user();

        // 1. Authorization check
        if (!$memo->canBeAccessedBy($user)) {
            abort(403, 'Unauthorized.');
        }

        // 2. Prevent duplicate acknowledgment
        if ($memo->isAcknowledgedBy($user)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already acknowledged this memo.',
                ], 422);
            }
            return back()->with('info', 'You have already acknowledged this memo.');
        }

        // 3. Mandatory confirmation checkbox validation
        $request->validate([
            'confirm_understood' => 'required|accepted',
        ], [
            'confirm_understood.accepted' => 'You must check the confirmation box indicating you have read and understood the memo.',
        ]);

        DB::beginTransaction();
        try {
            $acknowledgment = MemoAcknowledgment::create([
                'memo_id'                => $memo->id,
                'user_id'                => $user->id,
                'acknowledged_at'        => now(),
                'ip_address'             => $request->ip(),
                'user_agent'             => substr((string) $request->userAgent(), 0, 500),
                'confirmation_statement' => 'I confirm that I have read and carefully understood this memo.',
            ]);

            // Audit log if activity_log table exists
            if (function_exists('activity') && \Illuminate\Support\Facades\Schema::hasTable('activity_log')) {
                activity('memo')
                    ->performedOn($memo)
                    ->causedBy($user)
                    ->withProperties([
                        'ip_address' => $request->ip(),
                        'time'       => now()->toDateTimeString(),
                    ])
                    ->log('Memo acknowledged');
            }

            DB::commit();

            // Real-Time broadcast via Reverb WebSockets
            try {
                event(new \App\Events\MemoAcknowledged($memo->id, $memo->title, $user->id, $user->name));
            } catch (\Throwable $re) {
                \Log::warning('Reverb broadcast warning: ' . $re->getMessage());
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Thank you! Your acknowledgment has been officially recorded.',
                    'acknowledged_at' => $acknowledgment->acknowledged_at->format('M d, Y h:i A'),
                ]);
            }

            return redirect()->route('memos.show', $memo->id)
                ->with('success', 'Thank you! Your acknowledgment has been officially recorded.');

        } catch (QueryException $qe) {
            DB::rollBack();
            // Duplicate key constraint caught safely
            return redirect()->route('memos.show', $memo->id)
                ->with('info', 'This memo has already been acknowledged.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Acknowledgment error: ' . $e->getMessage());

            return redirect()->route('memos.show', $memo->id)
                ->with('error', 'Failed to record acknowledgment. Please try again.');
        }
    }

    /**
     * Stream the memo document inline for secure preview in iframe/img.
     */
    public function file($id)
    {
        $memo = Memo::findOrFail($id);
        $user = Auth::user();

        if (!$memo->canBeAccessedBy($user)) {
            abort(403, 'Unauthorized.');
        }

        if (!Storage::disk('public')->exists($memo->file_path)) {
            abort(404, 'The requested document file could not be found on the server.');
        }

        return Storage::disk('public')->response($memo->file_path, $memo->file_name, [
            'Content-Disposition' => 'inline; filename="' . $memo->file_name . '"',
        ]);
    }

    /**
     * Download route (restricted per company policy; redirects to online viewer).
     */
    public function download($id)
    {
        return redirect()->route('memos.show', $id)
            ->with('info', 'Direct document download is restricted. Please view the document directly online.');
    }

    /**
     * View detailed acknowledgment tracking for administrators.
     */
    public function tracking(Request $request, $id)
    {
        $memo = Memo::with(['uploader', 'acknowledgments.user'])->findOrFail($id);
        $user = Auth::user();

        if (!$this->isUserAdmin($user) && !$user->can('manage_memos')) {
            abort(403, 'Unauthorized to view acknowledgment tracking.');
        }

        $stats = $memo->acknowledgmentStats();
        $targetRecipients = $memo->targetRecipients();

        // Build list with acknowledgment status per user
        $acknowledgmentsByUser = $memo->acknowledgments->keyBy('user_id');

        $recipientsData = $targetRecipients->map(function ($u) use ($acknowledgmentsByUser) {
            $ack = $acknowledgmentsByUser->get($u->id);
            return [
                'user_id'         => $u->id,
                'name'            => $u->name,
                'email'           => $u->email,
                'roles'           => method_exists($u, 'roles') ? $u->roles->pluck('name')->implode(', ') : 'User',
                'status'          => $ack ? 'Acknowledged' : 'Pending',
                'acknowledged_at' => $ack ? $ack->acknowledged_at->format('Y-m-d H:i') : null,
                'ip_address'      => $ack ? $ack->ip_address : null,
            ];
        });

        // Filter by status if requested
        if ($request->status === 'acknowledged') {
            $recipientsData = $recipientsData->where('status', 'Acknowledged');
        } elseif ($request->status === 'pending') {
            $recipientsData = $recipientsData->where('status', 'Pending');
        }

        // Export to CSV if requested
        if ($request->get('export') === 'csv') {
            return $this->exportTrackingCsv($memo, $recipientsData);
        }

        return view('memos.tracking', [
            'memo'           => $memo,
            'stats'          => $stats,
            'recipientsData' => $recipientsData,
            'filterStatus'   => $request->status ?? 'all',
        ]);
    }

    /**
     * Helper to export tracking status to CSV.
     */
    protected function exportTrackingCsv(Memo $memo, $data)
    {
        $fileName = 'memo_' . $memo->id . '_acknowledgments_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($memo, $data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Memo Title', $memo->title]);
            fputcsv($file, ['Reference', $memo->reference_number ?: 'N/A']);
            fputcsv($file, ['Date', $memo->memo_date ? $memo->memo_date->format('Y-m-d') : '']);
            fputcsv($file, []);
            fputcsv($file, ['Staff ID', 'Name', 'Email', 'Role', 'Status', 'Acknowledged At', 'IP Address']);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row['user_id'],
                    $row['name'],
                    $row['email'],
                    $row['roles'],
                    $row['status'],
                    $row['acknowledged_at'] ?: 'N/A',
                    $row['ip_address'] ?: 'N/A',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Check if user has administrative privileges.
     */
    protected function isUserAdmin($user): bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasAnyRole')) {
            if ($user->hasAnyRole(['Super-User', 'Admin', 'Super Admin'])) {
                return true;
            }
        }

        if (method_exists($user, 'can') && $user->can('manage_memos')) {
            return true;
        }

        return false;
    }
}
