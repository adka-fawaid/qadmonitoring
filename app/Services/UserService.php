<?php

namespace App\Services;

use App\Models\QadUser;
use App\Models\QadActivityLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class UserService
{
    /**
     * Get paginated list of users.
     *
     * @param int $perPage
     * @param int $days
     * @return mixed
     */
    public function getUserList(int $perPage = 15, int $days = 30, array $filters = [])
    {
        $query = QadUser::query()
            ->select([
                'qad_users.id',
                'qad_users.user_id',
                'qad_users.user_name',
                'qad_users.description',
                'qad_users.active',
                'qad_users.last_logon',
            ])

            // Total activity dalam periode
            ->withCount([
                'activityLogs as activity_count' => function ($query) {
                    return $query;
                },
            ])

            // Unique programs
            ->addSelect([
                'unique_programs' => QadActivityLog::query()
                    ->selectRaw('COUNT(DISTINCT program_id)')
                    ->whereColumn(
                        'qad_activity_logs.user_id',
                        'qad_users.id'
                    ),
            ])

            // Unique modules
            ->addSelect([
                'unique_modules' => QadActivityLog::query()
                    ->join(
                        'qad_programs',
                        'qad_activity_logs.program_id',
                        '=',
                        'qad_programs.id'
                    )
                    ->selectRaw(
                        'COUNT(DISTINCT qad_programs.module_id)'
                    )
                    ->whereColumn(
                        'qad_activity_logs.user_id',
                        'qad_users.id'
                    ),
            ])

            ->when(!empty($filters['search']), function ($query) use ($filters) {
                $term = '%' . $filters['search'] . '%';
                $query->where(function ($query) use ($term) {
                    $query->where('qad_users.user_id', 'like', $term)
                        ->orWhere('qad_users.user_name', 'like', $term);
                });
            })
            ->when(!empty($filters['description']), function ($query) use ($filters) {
                $query->where('qad_users.description', $filters['description']);
            })
            ->when(!empty($filters['active']), function ($query) use ($filters) {
                $query->where('qad_users.active', $filters['active']);
            })
            ->orderBy('qad_users.user_name', 'asc');

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get detailed information about a user.
     *
     * @param int $userId
     * @return QadUser|null
     */
    public function getUserDetail(int $userId): ?QadUser
    {
        return QadUser::query()
            ->where('id', $userId)
            ->with([
                'activityLogs' => function ($query) {
                    $query
                        ->orderBy('activity_date', 'desc')
                        ->orderBy('id', 'desc')
                        ->limit(100);
                },
            ])
            ->first();
    }

    /**
     * Get paginated activity for a user.
     *
     * @param int $userId
     * @param int $perPage
     * @param array $filters
     * @return mixed
     */
    public function getUserActivity(
        int $userId,
        int $perPage = 20,
        array $filters = []
    ) {
        $query = QadActivityLog::query()
            ->where('user_id', $userId)
            ->orderBy('activity_date', 'desc')
            ->orderBy('id', 'desc');

        if (!empty($filters['date_from'])) {
            $query->where(
                'activity_date',
                '>=',
                $filters['date_from']
            );
        }

        if (!empty($filters['date_to'])) {
            $query->where(
                'activity_date',
                '<=',
                $filters['date_to']
            );
        }

        if (!empty($filters['module_id'])) {
            $query->whereHas('program', function ($q) use ($filters) {
                $q->where(
                    'module_id',
                    $filters['module_id']
                );
            });
        }

        if (!empty($filters['program_id'])) {
            $query->where(
                'program_id',
                $filters['program_id']
            );
        }

        if (!empty($filters['activity_type'])) {
            $query->where(
                'activity_type',
                $filters['activity_type']
            );
        }

        return $query->paginate($perPage);
    }

    /**
     * Get unique modules used by a user.
     *
     * @param int $userId
     * @param int $days
     * @return Collection
     */
    public function getUserModules(
        int $userId,
        ?int $days = null
    ): Collection {
        return QadActivityLog::query()
            ->where('qad_activity_logs.user_id', $userId)
            ->when($days !== null, function ($query) use ($days) {
                $query->where('qad_activity_logs.activity_date', '>=', Carbon::now()->subDays($days)->toDateString());
            })
            ->join(
                'qad_programs',
                'qad_activity_logs.program_id',
                '=',
                'qad_programs.id'
            )
            ->join(
                'qad_modules',
                'qad_programs.module_id',
                '=',
                'qad_modules.id'
            )
            ->select([
                'qad_modules.id',
                'qad_modules.name',
                'qad_modules.normalized_name',
            ])
            ->distinct()
            ->orderBy('qad_modules.name')
            ->get();
    }

    /**
     * Get unique programs used by a user.
     *
     * @param int $userId
     * @param int $days
     * @param int $limit
     * @return Collection
     */
    public function getUserPrograms(
        int $userId,
        ?int $days = null,
        int $limit = 20
    ): Collection {
        return QadActivityLog::query()
            ->where('qad_activity_logs.user_id', $userId)
            ->when($days !== null, function ($query) use ($days) {
                $query->where('qad_activity_logs.activity_date', '>=', Carbon::now()->subDays($days)->toDateString());
            })
            ->join(
                'qad_programs',
                'qad_activity_logs.program_id',
                '=',
                'qad_programs.id'
            )
            ->select([
                'qad_programs.id',
                'qad_programs.program_code',
                'qad_programs.kode',
                'qad_programs.address',
                'qad_programs.name',
                'qad_programs.program_type',
            ])
            ->selectRaw(
                'COUNT(qad_activity_logs.id) as activity_count'
            )
            ->groupBy([
                'qad_programs.id',
                'qad_programs.program_code',
                'qad_programs.kode',
                'qad_programs.address',
                'qad_programs.name',
                'qad_programs.program_type',
            ])
            ->orderBy(
                'activity_count',
                'desc'
            )
            ->limit($limit)
            ->get();
    }

    /**
     * Get user activity summary.
     *
     * @param int $userId
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @return array
     */
    public function getUserActivitySummary(
        int $userId,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array {
        $query = QadActivityLog::query()
            ->where('user_id', $userId);

        if ($dateFrom) {
            $query->where(
                'activity_date',
                '>=',
                $dateFrom
            );
        }

        if ($dateTo) {
            $query->where(
                'activity_date',
                '<=',
                $dateTo
            );
        }

        return [
            'total_activities' => (clone $query)->count(),

            'activity_by_date' => (clone $query)
                ->selectRaw(
                    'activity_date as date, COUNT(*) as count'
                )
                ->groupBy('activity_date')
                ->orderBy('activity_date', 'asc')
                ->get()
                ->pluck('count', 'date')
                ->toArray(),

            'activity_by_type' => (clone $query)
                ->selectRaw(
                    'activity_type, COUNT(*) as count'
                )
                ->whereNotNull('activity_type')
                ->where(
                    'activity_type',
                    '!=',
                    ''
                )
                ->groupBy('activity_type')
                ->orderBy('count', 'desc')
                ->get()
                ->pluck('count', 'activity_type')
                ->toArray(),

            'activity_by_module' => (clone $query)
                ->join(
                    'qad_programs',
                    'qad_activity_logs.program_id',
                    '=',
                    'qad_programs.id'
                )
                ->join(
                    'qad_modules',
                    'qad_programs.module_id',
                    '=',
                    'qad_modules.id'
                )
                ->selectRaw(
                    'qad_modules.name, COUNT(*) as count'
                )
                ->groupBy('qad_modules.name')
                ->orderBy('count', 'desc')
                ->get()
                ->pluck('count', 'name')
                ->toArray(),
        ];
    }

    /**
     * Search users.
     *
     * @param string $search
     * @param int $limit
     * @return Collection
     */
    public function searchUsers(
        string $search,
        int $limit = 10
    ): Collection {
        return QadUser::query()
            ->where(function ($query) use ($search) {
                $query
                    ->where(
                        'user_name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'user_id',
                        'like',
                        "%{$search}%"
                    );
            })
            ->select([
                'id',
                'user_id',
                'user_name',
                'description',
                'active',
                'last_logon',
            ])
            ->orderBy(
                'user_name',
                'asc'
            )
            ->limit($limit)
            ->get();
    }
}