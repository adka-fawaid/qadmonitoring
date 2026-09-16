<?php

namespace App\Services;

use App\Models\QadUser;
use App\Models\QadModule;
use App\Models\QadProgram;
use App\Models\QadActivityLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    /**
     * Get KPI metrics for the dashboard.
     *
     * @param array $filters Filter options: date_from, date_to, user_id, module_id, program_id
     * @return array
     */
    public function getKpis(array $filters = []): array
    {
        return [
            'total_users' => $this->getTotalUsers(),
            'active_users' => $this->getActiveUsers(),
            'total_modules' => $this->getTotalModules(),
            'entryan' => $this->getTotalProgramsByType('Entryan', $filters),
            'laporan' => $this->getTotalProgramsByType('Laporan', $filters),
            'total_programs' => $this->getTotalPrograms($filters),
            'activities' => $this->getTotalActivities($filters),
        ];
    }

    /**
     * Get activity trend data for the dashboard chart.
     *
     * @param int $days Number of days to retrieve (default 30)
     * @param array $filters Filter options
     * @return array
     */
    public function getActivityTrend(int $days = 30, array $filters = []): array
    {
        $startDate = $this->getActivityWindowStart($days, $filters);
        
        $query = QadActivityLog::query()
            ->select(
                DB::raw('DATE(activity_date) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('activity_date', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date', 'asc');

        $query = $this->applyFilters($query, $filters);

        $results = $query->get();

        return $results->map(function ($item) {
            return [
                'date' => $item->date,
                'count' => $item->count,
            ];
        })->toArray();
    }

    /**
     * Get top active users.
     *
     * @param int $limit Number of users to return
     * @param int $days Time period in days
     * @param array $filters Filter options
     * @return array
     */
    public function getTopUsers(int $limit = 10, int $days = 30, array $filters = []): array
    {
        $startDate = $this->getActivityWindowStart($days, $filters);

        $query = QadActivityLog::query()
            ->select(
                'qad_users.id',
                'qad_users.user_id',
                'qad_users.user_name',
                DB::raw('COUNT(*) as activity_count')
            )
            ->join('qad_users', 'qad_activity_logs.user_id', '=', 'qad_users.id')
            ->where('qad_activity_logs.activity_date', '>=', $startDate)
            ->groupBy('qad_users.id', 'qad_users.user_id', 'qad_users.user_name')
            ->orderBy('activity_count', 'desc')
            ->limit($limit);

        $query = $this->applyFilters($query, $filters);

        $results = $query->get();

        return $results->map(function ($item) {
            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'user_name' => $item->user_name,
                'activity_count' => $item->activity_count,
            ];
        })->toArray();
    }

    /**
     * Get module usage statistics.
     *
     * @param int $days Time period in days
     * @param array $filters Filter options
     * @return array
     */
    public function getModuleUsage(int $days = 30, array $filters = []): array
    {
        $startDate = $this->getActivityWindowStart($days, $filters);

        $query = QadModule::query()
            ->from('qad_modules')
            ->leftJoin('qad_programs', 'qad_programs.module_id', '=', 'qad_modules.id')
            ->leftJoin('qad_activity_logs', function ($join) use ($startDate) {
                $join->on('qad_activity_logs.program_id', '=', 'qad_programs.id')
                    ->where('qad_activity_logs.activity_date', '>=', $startDate);
            })
            ->select(
                'qad_modules.id',
                'qad_modules.name',
                DB::raw('COUNT(qad_activity_logs.id) as activity_count'),
                DB::raw('COUNT(DISTINCT qad_activity_logs.user_id) as unique_users'),
                DB::raw('COUNT(DISTINCT qad_activity_logs.program_id) as unique_programs')
            )
            ->groupBy('qad_modules.id', 'qad_modules.name')
            ->orderBy('activity_count', 'desc');

        $results = $query->get();

        return $results->map(function ($item) {
            return [
                'module_id' => $item->id,
                'module_name' => $item->name,
                'activity_count' => $item->activity_count,
                'unique_users' => $item->unique_users,
                'unique_programs' => $item->unique_programs,
            ];
        })->toArray();
    }

    /**
     * Get program usage statistics.
     *
     * @param int $limit Number of programs to return
     * @param int $days Time period in days
     * @param array $filters Filter options
     * @return array
     */
    public function getProgramUsage(int $limit = 15, int $days = 30, array $filters = []): array
    {
        $startDate = $this->getActivityWindowStart($days, $filters);

        $query = QadActivityLog::query()
            ->select(
                'qad_programs.id',
                'qad_programs.program_code',
                'qad_programs.name',
                'qad_programs.program_type',
                DB::raw('COUNT(*) as activity_count'),
                DB::raw('COUNT(DISTINCT qad_activity_logs.user_id) as unique_users')
            )
            ->join('qad_programs', 'qad_activity_logs.program_id', '=', 'qad_programs.id')
            ->where('qad_activity_logs.activity_date', '>=', $startDate)
            ->groupBy('qad_programs.id', 'qad_programs.program_code', 'qad_programs.name', 'qad_programs.program_type')
            ->orderBy('activity_count', 'desc')
            ->limit($limit);

        $query = $this->applyFilters($query, $filters);

        $results = $query->get();

        return $results->map(function ($item) {
            return [
                'program_id' => $item->id,
                'program_code' => $item->program_code,
                'program_name' => $item->name,
                'program_type' => $item->program_type,
                'activity_count' => $item->activity_count,
                'unique_users' => $item->unique_users,
            ];
        })->toArray();
    }

    /**
     * Get transaction type breakdown.
     *
     * @param int $days Time period in days
     * @param array $filters Filter options
     * @return array
     */
    public function getTransactionTypeBreakdown(int $days = 30, array $filters = []): array
    {
        $startDate = $this->getActivityWindowStart($days, $filters);

        $query = QadActivityLog::query()
            ->select(
                'activity_type',
                DB::raw('COUNT(*) as count')
            )
            ->where('activity_date', '>=', $startDate)
            ->where('activity_type', '!=', '')
            ->groupBy('activity_type')
            ->orderBy('count', 'desc');

        $query = $this->applyFilters($query, $filters);

        $results = $query->get();

        return $results->map(function ($item) {
            return [
                'type' => $item->activity_type,
                'count' => $item->count,
            ];
        })->toArray();
    }

    // Helper methods

    private function getTotalModules(): int
    {
        return QadModule::count();
    }

    private function getTotalPrograms(array $filters = []): int
    {
        $query = QadProgram::query();

        if (!empty($filters['module_id'])) {
            $query->where('module_id', $filters['module_id']);
        }

        return $query->count();
    }

    private function getTotalProgramsByType(string $programType, array $filters = []): int
    {
        $query = QadProgram::query()->where('program_type', $programType);

        if (!empty($filters['module_id'])) {
            $query->where('module_id', $filters['module_id']);
        }

        return $query->count();
    }

    private function getTotalUsers(): int
    {
        return QadUser::count();
    }

    private function getActiveUsers(): int
    {
        return QadUser::query()->where('active', 'yes')->count();
    }

    private function getTotalActivities(array $filters = []): int
    {
        return $this->applyFilters(QadActivityLog::query(), $filters)->count();
    }

    private function getActivityWindowStart(int $days, array $filters = []): Carbon
    {
        if (!empty($filters['date_from'])) {
            return Carbon::parse($filters['date_from'])->startOfDay();
        }

        $latestDate = !empty($filters['date_to'])
            ? Carbon::parse($filters['date_to'])
            : Carbon::parse(QadActivityLog::query()->max('activity_date') ?: now());

        return $latestDate->copy()->subDays($days)->startOfDay();
    }

    /**
     * Apply common filters to a query.
     *
     * @param $query
     * @param array $filters
     * @return mixed
     */
    private function applyFilters($query, array $filters = [])
    {
        if (!empty($filters['date_from'])) {
            $query->where('qad_activity_logs.activity_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('qad_activity_logs.activity_date', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        if (!empty($filters['user_id'])) {
            $query->where('qad_activity_logs.user_id', $filters['user_id']);
        }

        if (!empty($filters['module_id'])) {
            $query->whereHas('program', function ($q) use ($filters) {
                $q->where('module_id', $filters['module_id']);
            });
        }

        if (!empty($filters['program_id'])) {
            $query->where('qad_activity_logs.program_id', $filters['program_id']);
        }

        return $query;
    }
}
