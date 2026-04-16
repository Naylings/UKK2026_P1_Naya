<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\Violation;
use App\Models\UnitCondition;
use App\Exceptions\ReturnException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class ReturnService
{
    public function createByUser(int $loanId, array $data): Loan
    {
        return DB::transaction(function () use ($loanId, $data) {

            $user = Auth::user();

            $loan = Loan::lockForUpdate()->find($loanId);

            if (!$loan) {
                throw ReturnException::notFound();
            }

            if ($loan->user_id !== $user->id) {
                throw ReturnException::returnNotAllowed();
            }

            if ($loan->status !== 'approved') {
                throw ReturnException::notApproved();
            }

            if ($loan->toolReturn) {
                throw ReturnException::alreadyReturned();
            }

            if (now()->lt($loan->loan_date)) {
                throw ReturnException::returnTooEarly();
            }

            $return = $loan->toolReturn()->create([
                'employee_id'  => null,
                'return_date'  => now(),
                'proof'        => $this->storeProof($data['proof'] ?? null),
            ]);

            $loan->update([
                'status' => 'returned'
            ]);

            return $loan->fresh(['toolReturn', 'tool', 'unit']);
        });
    }

    public function confirmByEmployee(int $loanId, int $employeeId, array $data): Loan
    {
        return DB::transaction(function () use ($loanId, $employeeId, $data) {

            $loan = Loan::with([
                'tool',
                'unit',
                'user.detail',
                'employee.detail',
                'toolReturn.employee.detail',
                'toolReturn.conditions',
                'toolReturn.violation'
            ])->lockForUpdate()->find($loanId);

            if (!$loan) {
                throw ReturnException::notFound();
            }

            if ($loan->status !== 'returned') {
                throw ReturnException::notApproved();
            }


            if (!$loan->toolReturn) {
                throw ReturnException::invalidReturn();
            }

            if ($loan->toolReturn->employee_id !== null) {
                throw ReturnException::alreadyReturned();
            }

            $return = $loan->toolReturn;

            // =========================
            // 1. UNIT CONDITION (First Step)
            // =========================
            UnitCondition::create([
                'id'          => (string) Str::uuid(),
                'unit_code'   => $loan->unit_code,
                'return_id'   => $return->id,
                'conditions'  => $data['condition'],
                'notes'       => $data['condition_notes'] ?? '-',
                'recorded_at' => now(),
            ]);

            // =========================
            // 2. UPDATE RETURN
            // =========================
            $return->update([
                'employee_id'  => $employeeId,
            ]);

            // =========================
            // 3. VIOLATION HANDLING (Receive raw values from FE)
            // =========================
            $hasViolation = !empty($data['violation_type']);

            if ($hasViolation) {
                Violation::create([
                    'loan_id'     => $loan->id,
                    'user_id'     => $loan->user_id,
                    'return_id'   => $return->id,
                    'type'        => $data['violation_type'],
                    'total_score' => $data['total_score'] ?? 0,
                    'fine'        => $data['fine'] ?? 0,
                    'description' => $data['condition_notes'] ?? "Pelanggaran dicatat oleh petugas.",
                    'status'      => 'active',
                    'created_at'  => now(),
                ]);

                // Kurangi credit score user
                $loan->user->update([
                    'credit_score' => max(0, $loan->user->credit_score - ($data['total_score'] ?? 0))
                ]);
            }

            // =========================
            // 4. UNIT STATUS UPDATE
            // =========================
            // Unit kembali available HANYA jika kondisi 'good' dan tidak hilang
            $violationType = $data['violation_type'] ?? null;

            $newUnitStatus = ($data['condition'] === 'good' && $violationType !== 'lost')
                ? 'available'
                : 'nonactive';

            $loan->unit->update([
                'status' => $newUnitStatus
            ]);

            // =========================
            // 5. FINALIZE LOAN & USER
            // =========================

            // Angkat restriction HANYA jika tidak ada pelanggaran aktif
            if (!$hasViolation) {
                $loan->user()->update([
                    'is_restricted' => 0
                ]);
            }

            return $loan->fresh([
                'toolReturn.conditions',
                'toolReturn.violation',
                'toolReturn.employee.detail',
                'user.detail',
                'tool',
                'unit',
                'violation'
            ]);
        });
    }
    private function storeProof(?UploadedFile $file): ?string
    {
        if (!$file) {
            return null;
        }

        if (!$file->isValid()) {
            throw ReturnException::invalidProofUpload(
                $this->mapUploadError($file->getError())
            );
        }

        return $file->store('returns', 'public');
    }

    private function mapUploadError(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'ukuran file terlalu besar',
            UPLOAD_ERR_PARTIAL => 'file hanya terupload sebagian',
            UPLOAD_ERR_NO_FILE => 'file tidak ditemukan',
            UPLOAD_ERR_NO_TMP_DIR => 'temporary folder tidak ada',
            UPLOAD_ERR_CANT_WRITE => 'server gagal menulis file',
            default => 'upload tidak valid',
        };
    }

    public function getAll(array $filters = [])
    {
        return \App\Models\ToolReturn::query()
            ->with([
                'loan.tool.bundleComponents.tool',
                'loan.unit',
                'loan.user.detail',
                'loan.employee.detail',
                'employee.detail',
                'conditions',
                'violation'
            ])

            // 🔍 SEARCH (tool name + user name)
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($query) use ($search) {

                    // cari nama alat
                    $query->whereHas('loan.tool', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });

                    // ATAU cari nama user
                    $query->orWhereHas('loan.user.detail', function ($q3) use ($search) {
                        $q3->where('name', 'like', "%{$search}%");
                    });

                    // kalau nama langsung di tabel users (tanpa detail), pakai ini:
                    // $query->orWhereHas('loan.user', function ($q3) use ($search) {
                    //     $q3->where('name', 'like', "%{$search}%");
                    // });
                });
            })

            // 📌 FILTER REVIEW STATUS
            ->when(isset($filters['reviewed']), function ($q) use ($filters) {
                if ($filters['reviewed'] === true || $filters['reviewed'] === 'true') {
                    $q->whereNotNull('employee_id'); // sudah direview
                } else {
                    $q->whereNull('employee_id'); // belum direview
                }
            })

            ->latest()
            ->paginate($filters['per_page'] ?? 10);
    }

    public function getById(int $id)
    {
        $return = \App\Models\ToolReturn::with([
            'loan.tool',
            'loan.unit',
            'employee.detail',
            'conditions',
            'violation'
        ])->find($id);

        if (!$return) {
            throw \App\Exceptions\ReturnException::notFound();
        }

        return $return;
    }
}
