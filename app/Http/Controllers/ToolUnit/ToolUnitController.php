<?php

namespace App\Http\Controllers\ToolUnit;

use App\Exceptions\ToolUnitException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ToolUnit\AvailableUnitRequest;
use App\Http\Requests\ToolUnit\RecordConditionRequest;
use App\Http\Requests\ToolUnit\StoreToolUnitRequest;
use App\Http\Requests\ToolUnit\UpdateToolUnitRequest;
use App\Http\Resources\ToolUnit\AvailableUnitResource;
use App\Http\Resources\ToolUnit\ToolUnitResource;
use App\Http\Resources\ToolUnit\UnitConditionResource;
use App\Services\ActivityLogService;
use App\Services\ToolUnitService;
use Illuminate\Http\Request;

class ToolUnitController extends Controller
{
    public function __construct(
        private readonly ToolUnitService $unitService
    ) {}

    public function index(Request $request)
    {
        try {
            $units = $this->unitService->getAllUnits(
                $request->get('per_page', 10),
                $request->get('tool_id'),
                $request->get('status'),
                $request->get('search'),
            );

            return ToolUnitResource::collection($units);
        } catch (\Exception $e) {
            return response()->json(
                ['message' => 'Gagal mengambil data unit: ' . $e->getMessage()],
                500
            );
        }
    }

    public function show(string $code)
    {
        try {
            $unit = $this->unitService->getUnitByCode($code);
            return new ToolUnitResource($unit);
        } catch (ToolUnitException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                $e->getCode() ?: 404
            );
        } catch (\Exception $e) {
            return response()->json(
                ['message' => 'Error: ' . $e->getMessage()],
                500
            );
        }
    }

    public function store(StoreToolUnitRequest $request)
    {
        try {
            $data = $request->validated();
            $quantity = $data['quantity'] ?? 1;

            if ($quantity === 1) {
                $units = $this->unitService->createUnit(
                    $data['tool_id'],
                    $quantity,
                    $data['notes'] ?? '',
                    $data['condition'] ?? 'good',
                );
                $unit = $units[0];
                app(ActivityLogService::class)->log(
                    'tool_unit.created',
                    'tool_units',
                    "Membuat unit {$unit->code}.",
                    ['unit_code' => $unit->code, 'tool_id' => $unit->tool_id]
                );

                return (new ToolUnitResource($unit))
                    ->additional(['message' => 'Unit berhasil dibuat.'])
                    ->response()
                    ->setStatusCode(201);
            }

            $units = [];

            for ($i = 0; $i < $quantity; $i++) {
                $created = $this->unitService->createUnit(
                    $data['tool_id'],
                    1,
                    $data['notes'] ?? '',
                    $data['condition'] ?? 'good',
                );

                $units[] = $created[0];
            }

            app(ActivityLogService::class)->log(
                'tool_unit.bulk_created',
                'tool_units',
                "Membuat {$quantity} unit baru.",
                ['quantity' => $quantity, 'tool_id' => $data['tool_id']]
            );

            return response()->json([
                'data'    => ToolUnitResource::collection($units),
                'message' => "{$quantity} unit berhasil dibuat.",
            ], 201);
        } catch (ToolUnitException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                $e->getCode() ?: 422
            );
        } catch (\Exception $e) {
            return response()->json(
                ['message' => 'Error: ' . $e->getMessage()],
                500
            );
        }
    }

    public function update(UpdateToolUnitRequest $request, string $code)
    {
        try {
            $data = $request->validated();
            $unit = $this->unitService->updateStatus($code, $data['status']);

            if (isset($data['notes'])) {
                $unit->update(['notes' => $data['notes']]);
            }
            app(ActivityLogService::class)->log(
                'tool_unit.updated',
                'tool_units',
                "Mengupdate unit {$unit->code}.",
                ['unit_code' => $unit->code, 'status' => $unit->status]
            );

            return (new ToolUnitResource($unit))
                ->additional(['message' => 'Unit berhasil diupdate.']);
        } catch (ToolUnitException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                $e->getCode() ?: 422
            );
        } catch (\Exception $e) {
            return response()->json(
                ['message' => 'Error: ' . $e->getMessage()],
                500
            );
        }
    }

    public function destroy(string $code)
    {
        try {
            $meta = ['unit_code' => $code];
            $this->unitService->deleteUnit($code);
            app(ActivityLogService::class)->log(
                'tool_unit.deleted',
                'tool_units',
                "Menghapus unit {$code}.",
                $meta
            );

            return response()->json([
                'message' => 'Unit berhasil dihapus.',
            ]);
        } catch (ToolUnitException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                $e->getCode() ?: 422
            );
        } catch (\Exception $e) {
            return response()->json(
                ['message' => 'Error: ' . $e->getMessage()],
                500
            );
        }
    }

    public function recordCondition(RecordConditionRequest $request, string $code)
    {
        try {
            $data = $request->validated();
            $condition = $this->unitService->recordCondition(
                $code,
                $data['condition'],
                $data['notes'] ?? '',
                $data['return_id'] ?? null,
            );
            app(ActivityLogService::class)->log(
                'tool_unit.condition_recorded',
                'tool_units',
                "Mencatat kondisi unit {$code}.",
                [
                    'unit_code' => $code,
                    'condition' => $data['condition'],
                    'return_id' => $data['return_id'] ?? null,
                ]
            );

            return (new UnitConditionResource($condition))
                ->additional(['message' => 'Kondisi unit berhasil dicatat.'])
                ->response()
                ->setStatusCode(201);
        } catch (ToolUnitException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                $e->getCode() ?: 422
            );
        } catch (\Exception $e) {
            return response()->json(
                ['message' => 'Error: ' . $e->getMessage()],
                500
            );
        }
    }

    public function conditionHistory(string $code)
    {
        try {
            $unit = $this->unitService->getUnitByCode($code);
            $history = $unit->conditions()
                ->orderBy('recorded_at', 'desc')
                ->get();

            return response()->json([
                'data' => UnitConditionResource::collection($history),
            ]);
        } catch (ToolUnitException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                $e->getCode() ?: 404
            );
        } catch (\Exception $e) {
            return response()->json(
                ['message' => 'Error: ' . $e->getMessage()],
                500
            );
        }
    }


    public function availableUnits(AvailableUnitRequest $request)
    {
        try {
            $data = $request->validated();

            $units = $this->unitService->getAvailableUnits(
                $data['tool_id'],
                $data['loan_date'],
                $data['due_date']
            );

            return AvailableUnitResource::collection($units)
                ->additional(['message' => 'Berhasil mengambil unit tersedia']);
        } catch (ToolUnitException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                $e->getCode() ?: 422
            );
        } catch (\Exception $e) {
            return response()->json(
                ['message' => 'Error: ' . $e->getMessage()],
                500
            );
        }
    }
}
