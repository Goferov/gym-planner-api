<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\ExerciseLog;
use App\Models\Plan;
use App\Models\PlanUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use AuthorizesRequests;

    public function metrics()
    {
        $trainer = auth()->user();

        // 1) Total clients
        $totalClients = User::where('trainer_id', $trainer->id)
            ->where('role', 'user')
            ->count();

        // 2) Active plans
        $activePlans = PlanUser::whereHas('plan', fn ($q) => $q->where('trainer_id', $trainer->id))
            ->where('active', true)
            ->distinct('plan_id')
            ->count('plan_id');

        // 3) Completed sessions (ćwiczeń)
        $completedSessions = ExerciseLog::whereHas('planUser', fn ($q) =>
        $q->whereHas('plan', fn ($p) =>
        $p->where('trainer_id', $trainer->id)))
            ->where('completed', true)
            ->count();

        return response()->json([
            'total_clients'      => $totalClients,
            'active_plans'       => $activePlans,
            'completed_sessions' => $completedSessions,
            'upcoming_sessions'  => 0, // $upcomingSessions
        ]);
    }

    public function performance(Request $request)
    {
        $days     = (int) $request->input('days', 30);
        $trainer  = auth()->user();
        $startDay = Carbon::today()->subDays($days - 1);

        $data = ExerciseLog::selectRaw('DATE(date) as day, COUNT(*) as total')
            ->whereHas('planUser.plan', fn ($q) => $q->where('trainer_id', $trainer->id))
            ->where('completed', true)
            ->whereDate('date', '>=', $startDay)
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        return response()->json($data);   // [{ day: '2025‑04‑15', total: 12 }, ...]
    }

    public function recentClients(Request $request)
    {
        $limit   = (int) $request->input('limit', 5);
        $trainer = auth()->user();

        $clients = User::where('trainer_id', $trainer->id)
            ->where('role', 'user')
            ->whereNotNull('last_login_at')
            ->orderByDesc('last_login_at')
            ->limit($limit)
            ->get([
                'id',
                'name',
                'email',
                'last_login_at',
            ]);

        return ClientResource::collection($clients);
    }

    public function activity(Request $request)
    {
        $limit   = (int) $request->input('limit', 20);
        $trainer = auth()->user();

        $activities = ExerciseLog::with('planDayExercise.exercise')
            ->whereHas('planUser.plan', fn ($q) => $q->where('trainer_id', $trainer->id))
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json($activities);
    }


    public function userMetrics(Request $request, User $user)
    {
        $trainer = auth()->user();
        // uprawnienie: czy $user jest klientem trenera?
        $this->authorize('view', $user);   // policy UserPolicy@view

        [$from,$to] = $this->parseDateRange($request);

        // Completed ćwiczenia klienta
        $completed = ExerciseLog::where('plan_user_id', $user->id)   // plan_user_id = klient
        ->when($from, fn($q)=>$q->whereDate('date','>=',$from))
            ->when($to,   fn($q)=>$q->whereDate('date','<=',$to))
            ->where('completed', true)
            ->count();

        // Aktywne plany klienta
        $activePlans = PlanUser::where('user_id',$user->id)->where('active',true)->count();

        // Ostatni trening
        $lastSession = ExerciseLog::where('plan_user_id',$user->id)
            ->where('completed',true)
            ->latest('date')
            ->value('date');

        return response()->json([
            'client_id'        => $user->id,
            'completed_sessions'=> $completed,
            'active_plans'     => $activePlans,
            'last_session_at'  => $lastSession,
        ]);
    }

    public function userPerformance(Request $request, User $user)
    {
        $this->authorize('view', $user);

        $days  = (int) $request->input('days', 30);
        [$from] = $this->parseDateRange($request, $days);

        $data = ExerciseLog::selectRaw('DATE(date) day, COUNT(*) total')
            ->where('plan_user_id', $user->id)
            ->where('completed', true)
            ->whereDate('date','>=',$from)
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        return response()->json($data);
    }


    private function parseDateRange(Request $request, int $daysBack = 30): array
    {
        $from = $request->input('from');
        $to   = $request->input('to');

        if (!$from && !$to) {
            $from = Carbon::today()->subDays($daysBack-1)->toDateString();
            $to   = Carbon::today()->toDateString();
        }
        return [$from,$to];
    }

}
