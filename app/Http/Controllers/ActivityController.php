<?php

namespace App\Http\Controllers;

use App\Services\ActivityService;
use App\Models\QadActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    private ActivityService $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * Display paginated activity log.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        $perPage = $request->input('per_page', 25);
        $filters = $this->getFiltersFromRequest($request);

        $activityLog = $this->activityService->getActivityLog($perPage, $filters);
        $stats = $this->activityService->getActivityStats(
            $filters['date_from'] ?? null,
            $filters['date_to'] ?? null,
            $filters
        );

        return view('activity.index', [
            'activities' => $activityLog,
            'stats' => $stats,
            'filters' => $filters,
            'dateRangeLabel' => $this->getDateRangeLabel($filters),
            'dataDateRange' => $this->getDataDateRange(),
        ]);
    }

    /**
     * Display detail of a specific activity.
     *
     * @param int $id Activity ID
     * @return \Illuminate\View\View
     */
    public function show(int $id): View
    {
        $activity = $this->activityService->getActivityDetail($id);

        if (!$activity) {
            abort(404, 'Activity not found');
        }

        return view('activity.show', [
            'activity' => $activity,
            'backUrl' => route('activity.index'),
            'backLabel' => 'Activity Log',
            'eyebrow' => 'ACTIVITY DETAIL',
        ]);
    }

    /**
     * Get activity statistics page.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function statistics(Request $request): View
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $filters = $this->getFiltersFromRequest($request);

        $stats = $this->activityService->getActivityStats($dateFrom, $dateTo, $filters);
        $hourlyData = $this->activityService->getActivityByHour($dateFrom, $dateTo, $filters);
        $recentActivities = $this->activityService->getRecentActivities(50);

        return view('activity.statistics', [
            'stats' => $stats,
            'hourlyData' => $hourlyData,
            'recentActivities' => $recentActivities,
            'filters' => $filters,
            'dateRangeLabel' => $this->getDateRangeLabel($filters),
            'dataDateRange' => $this->getDataDateRange(),
        ]);
    }

    /**
     * Export activity log to CSV.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        $filters = $this->getFiltersFromRequest($request);
        $limit = $request->input('limit', 10000);

        $data = $this->activityService->exportActivityLog($filters, $limit);

        $filename = 'activity_log_' . now()->format('Y-m-d_His') . '.csv';

        return response()
            ->streamDownload(function () use ($data) {
                echo $this->arrayToCsv($data);
            }, $filename, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
    }

    /**
     * Extract filters from request.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    private function getFiltersFromRequest(Request $request): array
    {
        return [
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'user_id' => $request->input('user_id'),
            'module_id' => $request->input('module_id'),
            'program_id' => $request->input('program_id'),
            'activity_type' => $request->input('activity_type'),
            'status' => $request->input('status'),
            'transaction_code' => $request->input('transaction_code'),
        ];
    }

    private function getDateRangeLabel(array $filters): ?string
    {
        if (empty($filters['date_from']) && empty($filters['date_to'])) {
            return null;
        }

        $latestDate = QadActivityLog::query()->max('activity_date');
        $from = !empty($filters['date_from'])
            ? Carbon::parse($filters['date_from'])->format('d M Y')
            : 'awal data';
        $to = !empty($filters['date_to'])
            ? Carbon::parse($filters['date_to'])->format('d M Y')
            : ($latestDate ? Carbon::parse($latestDate)->format('d M Y') : 'akhir data');

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

    /**
     * Convert array to CSV string.
     *
     * @param array $data
     * @return string
     */
    private function arrayToCsv(array $data): string
    {
        if (empty($data)) {
            return '';
        }

        $output = fopen('php://temp', 'r+');
        fputcsv($output, array_keys($data[0]));
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        return stream_get_contents($output);
    }
}
