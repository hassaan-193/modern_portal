<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a paginated list of all notifications for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $filter = $request->get('filter', 'all');

        $query = $user->notifications();

        if ($filter === 'unread') {
            $query = $user->unreadNotifications();
        } elseif ($filter === 'read') {
            $query = $user->readNotifications();
        }

        $notifications = $query->paginate(15)->appends(['filter' => $filter]);
        $unreadCount = $user->unreadNotifications()->count();
        $totalCount = $user->notifications()->count();

        return view('notifications.index', compact('notifications', 'filter', 'unreadCount', 'totalCount'));
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Open a specific notification, mark it as read, and redirect to its action URL.
     */
    public function open($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        if ($notification->unread()) {
            $notification->markAsRead();
        }

        $action = $notification->data['action'] ?? $notification->data['url'] ?? route('notifications.index');
        return redirect($action);
    }

    /**
     * AJAX endpoint to mark a specific notification as read.
     */
    public function MarkAsRead(Request $request)
    {
        $notification = auth()->user()->notifications()->find($request->notif_id);
        if ($notification) {
            $notification->markAsRead();
            return response()->json([
                'success' => true
            ]);
        }

        return response()->json(false);
    }
}
