<?php

namespace App\Http\Controllers;

use App\Models\QadUser;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a paginated list of users.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        $perPage = $request->input('per_page', 15);
        $days = $request->input('days', 30);

        $users = $this->userService->getUserList($perPage, $days, [
            'search' => $request->input('search'),
            'description' => $request->input('description'),
            'active' => $request->input('active'),
        ]);

        return view('users.index', [
            'users' => $users,
            'filters' => [
                'per_page' => $perPage,
                'days' => $days,
                'search' => $request->input('search'),
                'description' => $request->input('description'),
                'active' => $request->input('active'),
            ],
            'dataDateRange' => $this->getDataDateRange(),
        ]);
    }

    private function getDataDateRange(): array
    {
        $dates = QadUser::query()
            ->selectRaw('MIN(last_logon) as date_from, MAX(last_logon) as date_to')
            ->first();

        return [
            'from' => $dates?->date_from ? Carbon::parse($dates->date_from) : null,
            'to' => $dates?->date_to ? Carbon::parse($dates->date_to) : null,
        ];
    }

    /**
     * Display detailed information about a specific user.
     *
     * @param int $id User database ID
     * @return \Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function show(int $id)
    {
        $user = $this->userService->getUserDetail($id);

        if (!$user) {
            abort(404, 'User not found');
        }

        $activities = $this->userService->getUserActivity($id, 20);
        $modules = $this->userService->getUserModules($id);
        $programs = $this->userService->getUserPrograms($id, null, 20);
        $summary = $this->userService->getUserActivitySummary($id);

        return view('users.show', [
            'user' => $user,
            'activities' => $activities,
            'modules' => $modules,
            'programs' => $programs,
            'summary' => $summary,
            'backUrl' => route('users.index'),
            'backLabel' => 'User List',
            'eyebrow' => 'USER DETAIL',
            'description' => 'User profile and activity summary.',
        ]);
    }

    /**
     * Search users by name or user_id.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $search = $request->input('q', '');
        $limit = $request->input('limit', 10);

        $results = $this->userService->searchUsers($search, $limit);

        return response()->json($results);
    }
}
