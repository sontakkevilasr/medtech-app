<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // ── Full notifications page ───────────────────────────────────────────────

    public function index(Request $request)
    {
        $user   = auth()->user();
        $filter = $request->get('filter', 'all'); // all | unread

        $query = Notification::forUser($user->id)
            ->when($filter === 'unread', fn($q) => $q->unread())
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $unreadCount = Notification::forUser($user->id)->unread()->count();

        // Group by type for icon mapping
        return view('notifications.index', compact('query', 'filter', 'unreadCount'));
    }

    // ── Mark single notification as read ─────────────────────────────────────

    public function markRead(Request $request, int $id)
    {
        $notif = Notification::forUser(auth()->id())->findOrFail($id);
        $notif->markAsRead();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        // Redirect to the relevant page if data has a URL hint
        $data = $notif->data ?? [];
        return redirect($this->resolveRedirect($notif->type, $data, auth()->user()->role));
    }

    // ── Mark all as read ──────────────────────────────────────────────────────

    public function markAllRead(Request $request)
    {
        Notification::forUser(auth()->id())
            ->unread()
            ->update(['is_read' => true, 'read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    // ── Delete a notification ─────────────────────────────────────────────────

    public function destroy(Request $request, int $id)
    {
        Notification::forUser(auth()->id())->findOrFail($id)->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }

    // ── AJAX: unread count (for bell badge) ───────────────────────────────────

    public function unreadCount()
    {
        $count = Notification::forUser(auth()->id())->unread()->count();

        return response()->json(['count' => $count]);
    }

    // ── AJAX: latest 5 for dropdown ───────────────────────────────────────────

    public function latest()
    {
        $notifications = Notification::forUser(auth()->id())
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($n) => [
                'id'       => $n->id,
                'title'    => $n->title,
                'body'     => $n->body,
                'type'     => $n->type,
                'is_read'  => $n->is_read,
                'ago'      => $n->created_at->diffForHumans(),
                'icon'     => $this->iconFor($n->type),
                'read_url' => route('notifications.read', $n->id),
            ]);

        $unread = Notification::forUser(auth()->id())->unread()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread'        => $unread,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function iconFor(string $type): string
    {
        return match(true) {
            str_starts_with($type, 'appointment') => '📅',
            str_starts_with($type, 'prescription') => '💊',
            str_starts_with($type, 'access')       => '🔒',
            str_starts_with($type, 'payment')      => '💳',
            str_starts_with($type, 'timeline')     => '📋',
            str_starts_with($type, 'doctor')       => '✅',
            default                                => '🔔',
        };
    }

    private function resolveRedirect(string $type, array $data, string $role): string
    {
        try {
            return match(true) {
                str_starts_with($type, 'appointment') && isset($data['appointment_id'])
                    => $role === 'doctor'
                        ? route('doctor.appointments.index')
                        : route('patient.appointments.index'),

                str_starts_with($type, 'prescription') && isset($data['prescription_id'])
                    => route('patient.history.index'),

                str_starts_with($type, 'access')
                    => $role === 'patient'
                        ? route('patient.access.index')
                        : route('doctor.patients.index'),

                str_starts_with($type, 'payment') && isset($data['payment_id'])
                    => route('patient.payments.index'),

                str_starts_with($type, 'timeline') && isset($data['timeline_id'])
                    => route('patient.timelines.index'),

                str_starts_with($type, 'doctor')
                    => route('doctor.setup'),

                default => $role === 'doctor'
                    ? route('doctor.dashboard')
                    : route('patient.dashboard'),
            };
        } catch (\Throwable) {
            return $role === 'doctor' ? route('doctor.dashboard') : route('patient.dashboard');
        }
    }
}
