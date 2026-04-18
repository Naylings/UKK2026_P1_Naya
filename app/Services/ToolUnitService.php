<?php

namespace App\Services;

use App\Exceptions\ToolUnitException;
use App\Models\Tool;
use App\Models\ToolUnit;
use App\Models\UnitCondition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ToolUnitService
{
    private function generateUnitCode(Tool $tool, int $index): string
    {
        $base = $tool->code_slug;
        $code = "{$base}-" . str_pad($index, 3, '0', STR_PAD_LEFT);

        $counter = $index;
        while (ToolUnit::where('code', $code)->exists()) {
            $counter++;
            $code = "{$base}-" . str_pad($counter, 3, '0', STR_PAD_LEFT);
        }

        return $code;
    }

    private function getStatusFromCondition(string $condition): string
    {
        return match ($condition) {
            'good' => 'available',
            'broken' => 'nonactive',
            'maintenance' => 'nonactive',
            default => 'available',
        };
    }

    private function getDefaultNotes(string $condition): string
    {
        return match ($condition) {
            'good' => 'Unit dalam kondisi baik, siap digunakan',
            'broken' => 'Unit mengalami kerusakan dan tidak dapat digunakan',
            'maintenance' => 'Unit sedang dalam pemeliharaan/perbaikan',
            default => '',
        };
    }

    public function getAllUnits(
        int $perPage = 10,
        ?int $toolId = null,
        ?string $status = null,
        ?string $search = null,
    ): object {
        $query = ToolUnit::with(['tool', 'latestCondition']);

        if ($toolId) {
            $query->where('tool_id', $toolId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    public function getUnitByCode(string $code): ToolUnit
    {
        $unit = ToolUnit::with(['tool', 'latestCondition', 'conditions'])->find($code);

        if (!$unit) {
            throw ToolUnitException::notFound();
        }

        return $unit;
    }

    public function createUnit(
        int $toolId,
        int $quantity = 1,
        string $notes = '',
        string $initialCondition = 'good',
    ): array {
        try {
            return DB::transaction(function () use ($toolId, $quantity, $notes, $initialCondition) {
                $tool = Tool::find($toolId);
                if (!$tool) {
                    throw ToolUnitException::invalidToolId();
                }

                if ($quantity < 1 || $quantity > 999) {
                    throw new \Exception('Quantity harus antara 1-999');
                }

                $units = [];

                $baseCount = ToolUnit::where('tool_id', $toolId)->count();

                for ($i = 0; $i < $quantity; $i++) {
                    $lastIndex = $baseCount + 1 + $i;

                    $code = $this->generateUnitCode($tool, $lastIndex);

                    if (ToolUnit::where('code', $code)->exists()) {
                        throw ToolUnitException::codeAlreadyExists($code);
                    }

                    $unit = ToolUnit::create([
                        'code'       => $code,
                        'tool_id'    => $toolId,
                        'status'     => $this->getStatusFromCondition($initialCondition),
                        'notes'      => $notes,
                        'created_at' => now(),
                    ]);

                    $this->recordCondition(
                        $code,
                        $initialCondition,
                        $notes,
                    );

                    $unit = ToolUnit::with(['tool', 'latestCondition'])->find($code);
                    $units[] = $unit;
                }

                return $units;
            });
        } catch (ToolUnitException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw ToolUnitException::createFailed($e->getMessage());
        }
    }

    public function updateUnitNotes(string $code, string $notes): ToolUnit
    {
        try {
            $unit = $this->getUnitByCode($code);
            $unit->update(['notes' => $notes]);

            return $unit->load(['tool', 'latestCondition']);
        } catch (ToolUnitException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw ToolUnitException::updateFailed($e->getMessage());
        }
    }

    public function updateStatus(string $code, string $newStatus): ToolUnit
    {
        try {
            if (!in_array($newStatus, ['available', 'lent', 'nonactive'])) {
                throw ToolUnitException::invalidStatus($newStatus);
            }

            $unit = $this->getUnitByCode($code);

            if ($newStatus === 'lent' && $unit->status === 'lent') {
                return $unit;
            }

            $unit->update(['status' => $newStatus]);

            return $unit->load(['tool', 'latestCondition']);
        } catch (ToolUnitException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw ToolUnitException::updateFailed($e->getMessage());
        }
    }

    public function recordCondition(
        string $unitCode,
        string $condition,
        string $notes = '',
        ?int $returnId = null,
    ): UnitCondition {
        try {
            $unit = ToolUnit::find($unitCode);
            if (!$unit) {
                throw ToolUnitException::notFound();
            }

            if (!in_array($condition, ['good', 'broken', 'maintenance'])) {
                throw new \Exception('Kondisi tidak valid. Gunakan: good, broken, maintenance.');
            }

            $finalNotes = !empty(trim($notes)) ? $notes : $this->getDefaultNotes($condition);

            $conditionRecord = UnitCondition::create([
                'id'           => Str::uuid()->toString(),
                'unit_code'    => $unitCode,
                'return_id'    => $returnId,
                'conditions'   => $condition,
                'notes'        => $finalNotes,
                'recorded_at'  => now(),
            ]);

            $unit->update([
                'notes' => $finalNotes,
            ]);

            $newStatus = $this->getStatusFromCondition($condition);
            $unit->update([
                'status' => $newStatus,
            ]);

            $unit = ToolUnit::with(['tool', 'latestCondition'])->find($unitCode);

            return $conditionRecord;
        } catch (ToolUnitException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw ToolUnitException::recordConditionFailed($e->getMessage());
        }
    }

    public function deleteUnit(string $code): void
    {
        try {
            $unit = $this->getUnitByCode($code);

            if ($unit->loans()->exists()) {
                throw new \Exception('Unit tidak bisa dihapus karena masih memiliki history peminjaman');
            }

            if ($unit->isLent()) {
                throw ToolUnitException::unitIsLent();
            }

            $unit->conditions()->delete();

            $unit->delete();
        } catch (ToolUnitException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw ToolUnitException::deleteFailed($e->getMessage());
        }
    }

    public function createBulkUnits(int $toolId, int $quantity, string $notes = '', string $initialCondition = 'good'): array
    {
        return $this->createUnit($toolId, $quantity, $notes, $initialCondition);
    }

    public function getConditionsHistory(string $unitCode): array
    {
        $unit = $this->getUnitByCode($unitCode);
        return $unit->conditions()
            ->orderBy('recorded_at', 'desc')
            ->get()
            ->toArray();
    }

    public function getUnitLoans(string $unitCode): array
    {
        $unit = $this->getUnitByCode($unitCode);
        return $unit->loans()
            ->orderBy('loan_date', 'desc')
            ->get()
            ->toArray();
    }

    public function getAvailableUnits(int $toolId, string $loanDateStr, string $dueDateStr)
    {
        try {
            $loanDate = \Carbon\Carbon::parse($loanDateStr);
            $dueDate  = \Carbon\Carbon::parse($dueDateStr);

            $units = ToolUnit::with(['latestCondition', 'tool'])
                ->where('tool_id', $toolId)
                ->whereIn('status', ['available', 'lent'])
                ->whereDoesntHave('loans', function ($query) use ($loanDate, $dueDate) {
                    $query->whereIn('status', ['active', 'pending'])
                        ->where(function ($q) use ($loanDate, $dueDate) {
                            $q->where('loan_date', '<=', $dueDate)
                                ->where('due_date', '>=', $loanDate);
                        });
                })
                ->get()
                ->map(function ($unit) use ($loanDate, $dueDate) {
                    $unit->availability_reason =
                        'Tersedia untuk periode ' .
                        $loanDate->format('d/m') . ' - ' .
                        $dueDate->format('d/m');
                    $unit->is_available_for_period = true;

                    return $unit;
                });

            return $units;
        } catch (\Exception $e) {
            throw ToolUnitException::fetchAvailableFailed($e->getMessage());
        }
    }
}
