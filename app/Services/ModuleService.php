<?php

namespace App\Services;

use App\Models\QadModule;
use App\Models\QadProgram;
use App\Models\QadActivityLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ModuleService
{
    /**
     * Get all modules with basic statistics.
     *
     * @param int $days Time period for statistics
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllModules(int $days = 30)
    {
        $startDate = $this->getActivityWindowStart($days);

        return QadModule::query()
            ->with('programs:id,module_id,program_code,name,program_type')
            ->withCount('programs')
            ->addSelect([
                'activity_count' => QadActivityLog::query()
                    ->selectRaw('COUNT(*)')
                    ->join('qad_programs', 'qad_activity_logs.program_id', '=', 'qad_programs.id')
                    ->whereColumn('qad_programs.module_id', 'qad_modules.id')
                    ->where('qad_activity_logs.activity_date', '>=', $startDate),
                'unique_users' => QadActivityLog::query()
                    ->selectRaw('COUNT(DISTINCT qad_activity_logs.user_id)')
                    ->join('qad_programs', 'qad_activity_logs.program_id', '=', 'qad_programs.id')
                    ->whereColumn('qad_programs.module_id', 'qad_modules.id')
                    ->where('qad_activity_logs.activity_date', '>=', $startDate),
            ])
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Get detailed module information.
     *
     * @param int $moduleId Module ID
     * @param int $days Time period for statistics
     * @return array|null
     */
    public function getModuleDetail(int $moduleId, int $days = 30): ?array
    {
        $module = QadModule::query()
            ->where('id', $moduleId)
            ->first();

        if (!$module) {
            return null;
        }

        $startDate = Carbon::now()->subDays($days);

        return [
            'id' => $module->id,
            'name' => $module->name,
            'normalized_name' => $module->normalized_name,
            'total_programs' => $module->programs()->count(),
            'total_activity_count' => $this->getModuleActivityCount($moduleId, $startDate),
            'unique_users' => $this->getModuleUniqueUsers($moduleId, $startDate),
            'unique_programs_used' => $this->getModuleUniqueProgramsUsed($moduleId, $startDate),
            'last_activity_date' => $this->getModuleLastActivityDate($moduleId),
        ];
    }

    /**
     * Get all programs in a module.
     *
     * @param int $moduleId Module ID
     * @param int $perPage Pagination per page
     * @param int $days Time period for statistics
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getModulePrograms(int $moduleId, int $perPage = 20, int $days = 30)
    {
        $startDate = Carbon::now()->subDays($days);

        return QadProgram::query()
            ->where('module_id', $moduleId)
            ->withCount([
                'activityLogs as activity_count' => function ($query) use ($startDate) {
                    $query->where('activity_date', '>=', $startDate);
                },
                'activityLogs as unique_users' => function ($query) use ($startDate) {
                    $query->where('activity_date', '>=', $startDate)
                        ->distinct('user_id');
                }
            ])
            ->orderBy('name', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get module usage statistics.
     *
     * @param int $moduleId Module ID
     * @param int $days Time period
     * @return array
     */
    public function getModuleUsage(int $moduleId, int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);

        $query = QadActivityLog::query()
            ->where('activity_date', '>=', $startDate)
            ->whereHas('program', function ($q) use ($moduleId) {
                $q->where('module_id', $moduleId);
            });

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
            'top_programs' => (clone $query)
                ->selectRaw('qad_programs.program_code, qad_programs.name, COUNT(*) as count')
                ->join('qad_programs', 'qad_activity_logs.program_id', '=', 'qad_programs.id')
                ->groupBy('qad_programs.id', 'qad_programs.program_code', 'qad_programs.name')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'program_code' => $item->program_code,
                        'program_name' => $item->name,
                        'activity_count' => $item->count,
                    ];
                })
                ->toArray(),
            'top_users' => (clone $query)
                ->selectRaw('qad_users.user_id, qad_users.name, COUNT(*) as count')
                ->join('qad_users', 'qad_activity_logs.user_id', '=', 'qad_users.id')
                ->groupBy('qad_users.id', 'qad_users.user_id', 'qad_users.name')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'user_id' => $item->user_id,
                        'user_name' => $item->name,
                        'activity_count' => $item->count,
                    ];
                })
                ->toArray(),
        ];
    }

    /**
     * Get module activity trend.
     *
     * @param int $moduleId Module ID
     * @param int $days Time period
     * @return array
     */
    public function getModuleActivityTrend(int $moduleId, int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);

        $results = QadActivityLog::query()
            ->selectRaw('DATE(activity_date) as date, COUNT(*) as count')
            ->where('activity_date', '>=', $startDate)
            ->whereHas('program', function ($q) use ($moduleId) {
                $q->where('module_id', $moduleId);
            })
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return $results->map(function ($item) {
            return [
                'date' => $item->date,
                'count' => $item->count,
            ];
        })->toArray();
    }

    /**
     * Get implementation status of a module.
     *
     * @param int $moduleId Module ID
     * @return array
     */
    public function getImplementationStatus(int $moduleId): array
    {
        $module = QadModule::query()
            ->where('id', $moduleId)
            ->with('implementations')
            ->first();

        if (!$module) {
            return [];
        }

        return [
            'module_name' => $module->name,
            'total_programs' => $module->programs()->count(),
            'implementations' => $module->implementations->map(function ($impl) {
                return [
                    'id' => $impl->id,
                    'sub_module' => $impl->sub_module,
                    'implementation_status' => $impl->implementation_status,
                    'implementation_possible' => $impl->implementation_possible,
                    'investment' => $impl->investment,
                    'notes' => $impl->notes,
                ];
            })->toArray(),
        ];
    }

    /**
     * Search modules by name.
     *
     * @param string $search Search term
     * @param int $limit Limit results
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchModules(string $search, int $limit = 10)
    {
        return QadModule::query()
            ->where('name', 'like', "%{$search}%")
            ->orWhere('normalized_name', 'like', "%{$search}%")
            ->select('id', 'name', 'normalized_name')
            ->limit($limit)
            ->get();
    }

    // Helper methods

    private function getModuleActivityCount(int $moduleId, Carbon $startDate): int
    {
        return QadActivityLog::query()
            ->where('activity_date', '>=', $startDate)
            ->whereHas('program', function ($q) use ($moduleId) {
                $q->where('module_id', $moduleId);
            })
            ->count();
    }

    private function getModuleUniqueUsers(int $moduleId, Carbon $startDate): int
    {
        return QadActivityLog::query()
            ->where('activity_date', '>=', $startDate)
            ->whereHas('program', function ($q) use ($moduleId) {
                $q->where('module_id', $moduleId);
            })
            ->distinct('user_id')
            ->count();
    }

    private function getModuleUniqueProgramsUsed(int $moduleId, Carbon $startDate): int
    {
        return QadActivityLog::query()
            ->where('activity_date', '>=', $startDate)
            ->whereHas('program', function ($q) use ($moduleId) {
                $q->where('module_id', $moduleId);
            })
            ->distinct('program_id')
            ->count();
    }

    private function getModuleLastActivityDate(int $moduleId): ?string
    {
        $lastActivity = QadActivityLog::query()
            ->whereHas('program', function ($q) use ($moduleId) {
                $q->where('module_id', $moduleId);
            })
            ->orderBy('activity_date', 'desc')
            ->first(['activity_date']);

        return $lastActivity ? $lastActivity->activity_date->format('Y-m-d H:i') : null;
    }

    private function getActivityWindowStart(int $days): Carbon
    {
        $latestDate = QadActivityLog::query()->max('activity_date');

        return ($latestDate ? Carbon::parse($latestDate) : Carbon::now())->subDays($days);
    }
}
