<?php

namespace App\Http\Controllers\Report;

use App\Exports\ReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Report\ReportPreviewRequest;
use App\Http\Resources\Report\ReportPreviewResource;
use App\Services\Report\ActivityLogReportService;
use App\Services\Report\AppealReportService;
use App\Services\Report\InventoryReportService;
use App\Services\Report\LoanReportService;
use App\Services\Report\ReturnReportService;
use App\Services\Report\SettlementReportService;
use App\Services\Report\UnitConditionReportService;
use App\Services\Report\UserReportService;
use App\Services\Report\ViolationReportService;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;


class ReportController extends Controller
{
    public function __construct(
        private readonly LoanReportService $loanService,
        private readonly ViolationReportService $violationService,
        private readonly SettlementReportService $settlementService,
        private readonly ReturnReportService $returnService,
        private readonly InventoryReportService $inventoryService,
        private readonly UnitConditionReportService $conditionService,
        private readonly UserReportService $userReportService,
        private readonly ActivityLogReportService $logService,
        private readonly AppealReportService $appealService,
    ) {}
    
    private function resolve(string $type, array $filters, string $mode)
    {
        return match ($type) {
            'inventory' => $mode === 'preview'
                ? $this->inventoryService->preview($filters)
                : $this->inventoryService->export($filters),
            
            'conditions' => $mode === 'preview'
                ? $this->conditionService->preview($filters)
                : $this->conditionService->export($filters),
            
            'users' => $mode === 'preview'
                ? $this->userReportService->preview($filters)
                : $this->userReportService->export($filters),
            
            'activity_logs' => $mode === 'preview'
                ? $this->logService->preview($filters)
                : $this->logService->export($filters),
            'appeals' => $mode === 'preview'
                ? $this->appealService->preview($filters)
                : $this->appealService->export($filters),
            'loans' => $mode === 'preview'
                ? $this->loanService->preview($filters)
                : $this->loanService->export($filters),
    
            'violations' => $mode === 'preview'
                ? $this->violationService->preview($filters)
                : $this->violationService->export($filters),
    
            'settlements' => $mode === 'preview'
                ? $this->settlementService->preview($filters)
                : $this->settlementService->export($filters),
    
            'returns' => $mode === 'preview'
                ? $this->returnService->preview($filters)
                : $this->returnService->export($filters),
    
            default => throw \App\Exceptions\ReportException::invalidType(),
        };
    }


    public function preview(string $type, ReportPreviewRequest $request)
    {
        $data = $this->resolve($type, $request->validated(), 'preview');

        app(ActivityLogService::class)->log(
            'report.preview',
            'reports',
            "Preview report {$type}.",
            ['filters' => $request->validated()]
        );
    
        return response()->json([
            'data' => $data,
            'message' => "Report {$type} preview loaded successfully",
        ]);
    }
    
    public function export(string $type, ReportPreviewRequest $request)
    {
        $data = $this->resolve($type, $request->validated(), 'export');

        app(ActivityLogService::class)->log(
            'report.export',
            'reports',
            "Export report {$type}.",
            ['filters' => $request->validated()]
        );
    
        if ($data instanceof \Illuminate\Support\Collection) {
            $data = $data->values()->toArray();
        } elseif (is_object($data) && method_exists($data, 'toArray')) {
            $data = $data->toArray();
        }

        return Excel::download(
            new ReportExport($data, $type),
            "{$type}-report.xlsx"
        );
    }
}
