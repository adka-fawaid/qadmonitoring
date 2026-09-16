<?php

namespace App\Http\Controllers;

use App\Models\QadModule;
use App\Models\QadActivityLog;
use App\Services\ModuleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QadModuleController extends Controller
{
    private ModuleService $moduleService;

    public function __construct(ModuleService $moduleService)
    {
        $this->moduleService = $moduleService;
    }

    /**
     * Display list of all modules.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        $days = $request->input('days', 30);
        $modules = $this->moduleService->getAllModules($days);

        return view('modules.index', [
            'modules' => $modules,
            'days' => $days,
            'dataDateRange' => $this->getDataDateRange(),
        ]);
    }

    private function getDataDateRange(): array
    {
        $dates = QadActivityLog::query()
            ->join('qad_programs', 'qad_activity_logs.program_id', '=', 'qad_programs.id')
            ->whereNotNull('qad_programs.module_id')
            ->selectRaw('MIN(qad_activity_logs.activity_date) as date_from, MAX(qad_activity_logs.activity_date) as date_to')
            ->first();

        return [
            'from' => $dates?->date_from ? Carbon::parse($dates->date_from) : null,
            'to' => $dates?->date_to ? Carbon::parse($dates->date_to) : null,
        ];
    }
    /**
     * Display detailed module information.
     *
     * @param int $id Module ID
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function show(int $id, Request $request): View
    {
        $days = $request->input('days', 30);
        $moduleDetail = $this->moduleService->getModuleDetail($id, $days);

        if (!$moduleDetail) {
            abort(404, 'Module not found');
        }

        $programs = $this->moduleService->getModulePrograms($id, 20, $days);
        $usage = $this->moduleService->getModuleUsage($id, $days);
        $activityTrend = $this->moduleService->getModuleActivityTrend($id, $days);
        $implementationStatus = $this->moduleService->getImplementationStatus($id);

        return view('modules.show', [
            'module' => $moduleDetail,
            'programs' => $programs,
            'usage' => $usage,
            'activityTrend' => $activityTrend,
            'implementationStatus' => $implementationStatus,
            'days' => $days,
            'backUrl' => route('modules.index'),
            'backLabel' => 'Module List',
        ]);
    }

    /**
     * Get module overview via API (for dynamic loading).
     *
     * @param int $id Module ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function overview(int $id)
    {
        $moduleDetail = $this->moduleService->getModuleDetail($id);

        if (!$moduleDetail) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        return response()->json($moduleDetail);
    }

    /**
     * Get module usage data via API.
     *
     * @param int $id Module ID
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function usageData(int $id, Request $request)
    {
        $days = $request->input('days', 30);
        $usage = $this->moduleService->getModuleUsage($id, $days);

        return response()->json($usage);
    }

    /**
     * Get module activity trend via API.
     *
     * @param int $id Module ID
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function activityTrendData(int $id, Request $request)
    {
        $days = $request->input('days', 30);
        $trend = $this->moduleService->getModuleActivityTrend($id, $days);

        return response()->json($trend);
    }
}
