<?php

namespace App\Http\Controllers;

use App\Models\QadActivityLog;
use App\Services\ActivityService;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private DashboardService $dashboardService;
    private ActivityService $activityService;

    public function __construct(DashboardService $dashboardService, ActivityService $activityService)
    {
        $this->dashboardService = $dashboardService;
        $this->activityService = $activityService;
    }

    /**
     * Display the dashboard with KPIs and analytics.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        // Get filters from request
        $filters = $this->getFiltersFromRequest();
        $days = request()->input('days', 30);

        // Get KPI data
        $kpis = $this->dashboardService->getKpis($filters);

        // Get trend data (30 days by default)
        $activityTrend = $this->dashboardService->getActivityTrend($days, $filters);

        // Get module usage
        $moduleUsage = $this->dashboardService->getModuleUsage($days, $filters);

        // Get program usage (top 15)
        $programUsage = $this->dashboardService->getProgramUsage(10, $days, $filters);

        $recentActivities = $this->activityService->getRecentActivities(10);

        return view('dashboard.index', [
            'kpis' => $kpis,
            'activityTrend' => $activityTrend,
            'moduleUsage' => $moduleUsage,
            'programUsage' => $programUsage,
            'recentActivities' => $recentActivities,
            'filters' => $filters,
            'days' => $days,
            'dateRangeLabel' => $this->getDateRangeLabel($filters),
            'dataDateRange' => $this->getDataDateRange(),
        ]);
    }

    /**
     * Extract filters from request.
     *
     * @return array
     */
    private function getFiltersFromRequest(): array
    {
        return [
            'date_from' => request()->input('date_from'),
            'date_to' => request()->input('date_to'),
            'user_id' => request()->input('user_id'),
            'module_id' => request()->input('module_id'),
            'program_id' => request()->input('program_id'),
        ];
    }

    private function getDateRangeLabel(array $filters): ?string
    {
        if (empty($filters['date_from']) && empty($filters['date_to'])) {
            return null;
        }

        $from = !empty($filters['date_from']) ? Carbon::parse($filters['date_from'])->format('d M Y') : 'awal data';
        $to = !empty($filters['date_to']) ? Carbon::parse($filters['date_to'])->format('d M Y') : 'akhir data';

        return "Hasil pencarian dari {$from} sampai {$to}";
    }

    private function getDataDateRange(): array
    {
        $dates = QadActivityLog::query()
            ->selectRaw('MIN(activity_date) as date_from, MAX(activity_date) as date_to')
            ->first();

        return [
            'from' => $dates?->date_from ? Carbon::parse($dates->date_from) : null,
            'to' => $dates?->date_to ? Carbon::parse($dates->date_to) : null,
        ];
    }
}