<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SecurityAlert;
use App\Models\User;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')
            ->when($request->event,    fn($q) => $q->where('event', $request->event))
            ->when($request->user_id,  fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->entity,   fn($q) => $q->where('auditable_type', 'like', '%' . $request->entity . '%'))
            ->when($request->ip,       fn($q) => $q->where('ip_address', $request->ip))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to,   fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($q2) use ($request) {
                    $q2->where('ip_address', 'like', '%' . $request->search . '%')
                       ->orWhere('url', 'like', '%' . $request->search . '%');
                });
            });

        $logs = $query->latest('created_at')->paginate(50)->withQueryString();

        // Stats for current filters
        $stats = [
            'total'    => AuditLog::count(),
            'today'    => AuditLog::whereDate('created_at', today())->count(),
            'week'     => AuditLog::where('created_at', '>=', now()->startOfWeek())->count(),
            'users'    => AuditLog::whereNotNull('user_id')->distinct('user_id')->count('user_id'),
        ];

        // Filter options
        $events = AuditLog::distinct('event')->orderBy('event')->pluck('event');
        $users  = User::whereIn('id', AuditLog::whereNotNull('user_id')->distinct('user_id')->pluck('user_id'))
                      ->orderBy('name')->get(['id', 'name']);

        // Security alerts (unresolved)
        $unresolvedAlerts = SecurityAlert::whereNull('resolved_at')
            ->latest('created_at')->get();

        return view('admin.audit.index', compact(
            'logs', 'stats', 'events', 'users', 'unresolvedAlerts'
        ));
    }

    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');
        return view('admin.audit.show', compact('auditLog'));
    }

    public function resolveAlert(SecurityAlert $alert)
    {
        $alert->update([
            'resolved_at'          => now(),
            'resolved_by_user_id'  => auth()->id(),
        ]);
        return back()->with('success', 'Alerta marcada como resuelta.');
    }
}
