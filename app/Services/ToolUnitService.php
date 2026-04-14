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
    /**
     * Generate kode unit unik berdasarkan tool.
     * Format: {code_slug}-{index}
     * Contoh: LPT-001, SET-PK-001
     */
    private function generateUnitCode(Tool $tool, int $index): string
    {
        $base = $tool->code_slug;
        $code = "{$base}-" . str_pad($index, 3, '0', STR_PAD_LEFT);

        // Loop sampai kode benar-benar unik
        $counter = $index;
        while (ToolUnit::where('code', $code)->exists()) {
            $counter++;
            $code = "{$base}-" . str_pad($counter, 3, '0', STR_PAD_LEFT);
        }

        return $code;
    }

    /**
     * Map kondisi ke status unit
     * good -> available
     * broken -> nonactive
     * maintenance -> nonactive
     */
    private function getStatusFromCondition(string $condition): string
    {
        return match ($condition) {
            'good' => 'available',
            'broken' => 'nonactive',
            'maintenance' => 'nonactive',
            default => 'available',
        };
    }

    /**
     * Generate default notes berdasarkan condition
     */
    private function getDefaultNotes(string $condition): string
    {
        return match ($condition) {
            'good' => 'Unit dalam kondisi baik, siap digunakan',
            'broken' => 'Unit mengalami kerusakan dan tidak dapat digunakan',
            'maintenance' => 'Unit sedang dalam pemeliharaan/perbaikan',
            default => '',
        };
    }

    /**
     * Ambil semua unit dengan pagination dan filter
     */
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

    /**
     * Ambil unit berdasarkan code
     */
    public function getUnitByCode(string $code): ToolUnit
    {
        $unit = ToolUnit::with(['tool', 'latestCondition', 'conditions'])->find($code);

        if (!$unit) {
            throw ToolUnitException::notFound();
        }

        return $unit;
    }

    /**
     * Buat unit baru untuk tool
     * Support single atau multiple units (bulk)
     */
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

                // Validasi quantity
                if ($quantity < 1 || $quantity > 999) {
                    throw new \Exception('Quantity harus antara 1-999');
                }

                $units = [];

                // Query count ONCE sebelum loop untuk menghindari kalkulasi yang salah
                $baseCount = ToolUnit::where('tool_id', $toolId)->count();

                for ($i = 0; $i < $quantity; $i++) {
                    // Hitung unit terbaru untuk tool ini
                    $lastIndex = $baseCount + 1 + $i;

                    $code = $this->generateUnitCode($tool, $lastIndex);

                    // Cek jika code sudah ada (precaution)
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

                    // Catat kondisi awal
                    // Pass user's notes ke recordCondition, let it handle default logic
                    $this->recordCondition(
                        $code,
                        $initialCondition,
                        $notes,
                    );

                    // Reload unit dari database untuk memastikan status terupdate setelah recordCondition
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

    /**
     * Update notes unit saja (tidak mengubah status)
     */
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

    /**
     * Update status unit
     */
    public function updateStatus(string $code, string $newStatus): ToolUnit
    {
        try {
            if (!in_array($newStatus, ['available', 'lent', 'nonactive'])) {
                throw ToolUnitException::invalidStatus($newStatus);
            }

            $unit = $this->getUnitByCode($code);

            if ($newStatus === 'lent' && $unit->status === 'lent') {
                // Sudah dalam status lent, tidak perlu update
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

    /**
     * Catat kondisi unit
     * Auto-update: status berdasarkan condition, notes dengan latest condition
     */
    public function recordCondition(
        string $unitCode,
        string $condition,
        string $notes = '',
        ?int $returnId = null,
    ): UnitCondition {
        try {
            // Validasi unit ada
            $unit = ToolUnit::find($unitCode);
            if (!$unit) {
                throw ToolUnitException::notFound();
            }

            // Validasi condition value
            if (!in_array($condition, ['good', 'broken', 'maintenance'])) {
                throw new \Exception('Kondisi tidak valid. Gunakan: good, broken, maintenance.');
            }

            // Gunakan provided notes atau default notes
            $finalNotes = !empty(trim($notes)) ? $notes : $this->getDefaultNotes($condition);

            // Buat condition record
            $conditionRecord = UnitCondition::create([
                'id'           => Str::uuid()->toString(),
                'unit_code'    => $unitCode,
                'return_id'    => $returnId,
                'conditions'   => $condition,
                'notes'        => $finalNotes,
                'recorded_at'  => now(),
            ]);

            // Update unit notes dengan latest condition notes
            $unit->update([
                'notes' => $finalNotes,
            ]);

            // Update unit status berdasarkan condition
            $newStatus = $this->getStatusFromCondition($condition);
            $unit->update([
                'status' => $newStatus,
            ]);

            // Reload unit dari database untuk memastikan relasi terbaru (latestCondition, dll)
            $unit = ToolUnit::with(['tool', 'latestCondition'])->find($unitCode);

            return $conditionRecord;
        } catch (ToolUnitException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw ToolUnitException::recordConditionFailed($e->getMessage());
        }
    }

    /**
     * Hapus unit
     */
    public function deleteUnit(string $code): void
    {
        try {
            $unit = $this->getUnitByCode($code);

            // Check apakah unit memiliki loans
            if ($unit->loans()->exists()) {
                throw new \Exception('Unit tidak bisa dihapus karena masih memiliki history peminjaman');
            }

            if ($unit->isLent()) {
                throw ToolUnitException::unitIsLent();
            }

            // Hapus conditions terlebih dahulu
            $unit->conditions()->delete();

            // Hapus unit
            $unit->delete();
        } catch (ToolUnitException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw ToolUnitException::deleteFailed($e->getMessage());
        }
    }

    /**
     * Bulk create units untuk tool
     * @deprecated Gunakan createUnit dengan quantity parameter
     */
    public function createBulkUnits(int $toolId, int $quantity, string $notes = '', string $initialCondition = 'good'): array
    {
        return $this->createUnit($toolId, $quantity, $notes, $initialCondition);
    }

    /**
     * Get conditions history untuk unit
     */
    public function getConditionsHistory(string $unitCode): array
    {
        $unit = $this->getUnitByCode($unitCode);
        return $unit->conditions()
            ->orderBy('recorded_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get loans untuk unit (untuk timeline peminjaman)
     */
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
