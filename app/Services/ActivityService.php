<?php

namespace App\Services;

use App\Models\QadActivityLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ActivityService
{
    /**
     * Get paginated activity log.
     *
     * @param int $perPage Items per page
     * @param array $filters Filter options
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getActivityLog(int $perPage = 25, array $filters = [])
    {
        $query = QadActivityLog::query()
            ->with([
                'user:id,user_id,user_name,description,active',
                'program:id,program_code,name,program_type,module_id',
                'program.module:id,name'
            ])
            ->orderBy('activity_date', 'desc')
            ->orderBy('id', 'desc');

        $query = $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    /**
     * Get detail of a specific activity.
     *
     * @param int $activityId Activity ID
     * @return \App\Models\QadActivityLog|null
     */
    public function getActivityDetail(int $activityId): ?QadActivityLog
    {
        return QadActivityLog::query()
            ->where('id', $activityId)
            ->with([
                'user:id,user_id,user_name,description,active',
                'program:id,program_code,kode,name,address,program_type,module_id',
                'program.module:id,name'
            ])
            ->first();
    }

    /**
     * Get activity statistics for a date range.
     *
     * @param string|null $dateFrom Start date (Y-m-d format)
     * @param string|null $dateTo End date (Y-m-d format)
     * @param array $filters Additional filters
     * @return array
     */
    public function getActivityStats(?string $dateFrom = null, ?string $dateTo = null, array $filters = []): array
    {
        $query = QadActivityLog::query();

        $query->where('activity_date', '>=', $dateFrom ?: $this->getDefaultStartDate(30));

        if ($dateTo) {
            $query->where('activity_date', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        $query = $this->applyFilters($query, $filters);

        return [
            'total_activities' => (clone $query)->count(),
            'unique_users' => (clone $query)->distinct('user_id')->count(),
            'unique_programs' => (clone $query)->distinct('program_id')->count(),
            'activity_by_date' => (clone $query)
                ->selectRaw('DATE(activity_date) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get()
                ->pluck('count', 'date')
                ->toArray(),
            'activity_by_module' => (clone $query)
                ->selectRaw('qad_modules.name, COUNT(*) as count')
                ->join('qad_programs', 'qad_activity_logs.program_id', '=', 'qad_programs.id')
                ->join('qad_modules', 'qad_programs.module_id', '=', 'qad_modules.id')
                ->groupBy('qad_modules.name')
                ->orderBy('count', 'desc')
                ->get()
                ->pluck('count', 'name')
                ->toArray(),
            'activity_by_type' => (clone $query)
                ->selectRaw('activity_type, COUNT(*) as count')
                ->where('activity_type', '!=', '')
                ->groupBy('activity_type')
                ->orderBy('count', 'desc')
                ->get()
                ->pluck('count', 'activity_type')
                ->toArray(),
        ];
    }

    /**
     * Get activity by time of day (hour breakdown).
     *
     * @param string|null $dateFrom Start date
     * @param string|null $dateTo End date
     * @param array $filters Additional filters
     * @return array
     */
    public function getActivityByHour(?string $dateFrom = null, ?string $dateTo = null, array $filters = []): array
    {
        $query = QadActivityLog::query();

        $query->where('activity_date', '>=', $dateFrom ?: $this->getDefaultStartDate(7));

        if ($dateTo) {
            $query->where('activity_date', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        $query = $this->applyFilters($query, $filters);

        // Using HOUR function - adjust for database support
        $results = $query
            ->selectRaw('HOUR(activity_date) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour', 'asc')
            ->get();

        // Convert to array with hour labels
        $hourly = [];
        for ($i = 0; $i < 24; $i++) {
            $hourly[$i] = 0;
        }

        foreach ($results as $item) {
            $hourly[$item->hour] = $item->count;
        }

        return $hourly;
    }

    /**
     * Get recent activities with full details.
     *
     * @param int $limit Number of records
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentActivities(int $limit = 50)
    {
        return QadActivityLog::query()
            ->with([
                'user:id,user_id,user_name,description,active',
                'program:id,program_code,name,module_id',
                'program.module:id,name'
            ])
            ->orderBy('activity_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Export activity log to array (for CSV/Excel).
     *
     * @param array $filters Filter options
     * @param int|null $limit Max records
     * @return array
     */
    public function exportActivityLog(array $filters = [], ?int $limit = null)
    {
        $query = QadActivityLog::query()
            ->with([
                'user:id,user_id,user_name,description,active',
                'program:id,program_code,name,module_id',
                'program.module:id,name'
            ])
            ->select([
                'id',
                'user_id',
                'program_id',
                'transaction_code',
                'activity_date',
                'activity_time',
                'cost_center',
                'end_user',
                'activity_type',
                'status'
            ]);

        $query = $this->applyFilters($query, $filters);

        if ($limit) {
            $query->limit($limit);
        }

        $query->orderBy('activity_date', 'desc');

        return $query->get()->map(function ($log) {
            return [
                'ID' => $log->id,
                'Date' => optional($log->activity_date)->format('Y-m-d') ?: '',
                'Time' => $log->activity_time,
                'User ID' => optional($log->user)->user_id ?? '',
                'User Name' => optional($log->user)->user_name ?? '',
                'Description' => optional($log->user)->description ?? '',
                'Program Code' => optional($log->program)->program_code ?? '',
                'Program Name' => optional($log->program)->name ?? '',
                'Module' => optional(optional($log->program)->module)->name ?? '',
                'Transaction Code' => $log->transaction_code,
                'Activity Type' => $log->activity_type,
                'Status' => $log->status,
                'Cost Center' => $log->cost_center,
                'End User' => $log->end_user,
            ];
        })->toArray();
    }

    /**
     * Apply filters to activity query.
     *
     * @param $query
     * @param array $filters
     * @return mixed
     */
    private function applyFilters($query, array $filters = [])
    {
        if (!empty($filters['date_from'])) {
            $query->where('activity_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('activity_date', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['module_id'])) {
            $query->whereHas('program', function ($q) use ($filters) {
                $q->where('module_id', $filters['module_id']);
            });
        }

        if (!empty($filters['program_id'])) {
            $query->where('program_id', $filters['program_id']);
        }

        if (!empty($filters['activity_type'])) {
            $query->where('activity_type', $filters['activity_type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['transaction_code'])) {
            $query->where('transaction_code', 'like', "%{$filters['transaction_code']}%");
        }

        return $query;
    }

    private function getDefaultStartDate(int $days): Carbon
    {
        $latestDate = QadActivityLog::query()->max('activity_date');

        return Carbon::parse($latestDate ?: now())->subDays($days);
    }
}
