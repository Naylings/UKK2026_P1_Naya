<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Support\Facades\Hash;

class UserManagementService
{
    /**
     * Get all users dengan pagination, search, dan role filter
     * 
     * @param int $perPage
     * @param string|null $search
     * @param string|null $role
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllUsers(int $perPage = 10, ?string $search = null, ?string $role = null)
    {
        $query = User::with('detail');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhereHas('detail', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%");
                  });
            });
        }

        if ($role) {
            $query->where('role', $role);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get user by ID dengan detail
     * 
     * @param int $userId
     * @return User
     * @throws UserException
     */
    public function getUserById(int $userId): User
    {
        $user = User::with('detail')->find($userId);
        
        if (!$user) {
            throw UserException::notFound();
        }
        
        return $user;
    }

    /**
     * Create user baru dengan credit default 100 dan detail
     * 
     * @param array{
     *   email: string, 
     *   password: string, 
     *   role?: string,
     *   nik: string,
     *   name: string,
     *   no_hp: string,
     *   address: string,
     *   birth_date: string
     * } $data
     * @return User
     * @throws UserException
     */
    public function createUser(array $data): User
    {
        // Cek email sudah ada
        if (User::where('email', $data['email'])->exists()) {
            throw UserException::emailAlreadyExists();
        }

        // Cek NIK sudah ada
        if (UserDetail::where('nik', $data['nik'])->exists()) {
            throw new UserException('NIK sudah terdaftar.');
        }

        try {
            // Create user
            $user = User::create([
                'email'         => $data['email'],
                'password'      => Hash::make($data['password']),
                'role'          => $data['role'] ?? 'User',
                'credit_score'  => 100,
                'is_restricted' => false,
            ]);

            // Create user detail
            UserDetail::create([
                'nik'        => $data['nik'],
                'user_id'    => $user->id,
                'name'       => $data['name'],
                'no_hp'      => $data['no_hp'],
                'address'    => $data['address'],
                'birth_date' => $data['birth_date'],
            ]);

            return $user->load('detail');
        } catch (UserException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new UserException('Gagal membuat user: ' . $e->getMessage());
        }
    }

    /**
     * Update user dan detail
     * 
     * @param User $user
     * @param array{
     *   email?: string, 
     *   role?: string,
     *   nik?: string,
     *   name?: string,
     *   no_hp?: string,
     *   address?: string,
     *   birth_date?: string
     * } $data
     * @return User
     * @throws UserException
     */
    public function updateUser(User $user, array $data): User
    {
        // Jika email ada di data, cek unique
        if (isset($data['email']) && $data['email'] !== $user->email) {
            if (User::where('email', $data['email'])->exists()) {
                throw UserException::emailAlreadyExists();
            }
        }

        try {
            // Update user fields (hanya email dan role)
            $userUpdate = array_filter([
                'email' => $data['email'] ?? null,
                'role'  => $data['role'] ?? null,
            ], fn($v) => $v !== null);

            if (!empty($userUpdate)) {
                $user->update($userUpdate);
            }

            // Update user detail fields
            $detailUpdate = array_filter([
                'nik'        => $data['nik'] ?? null,
                'name'       => $data['name'] ?? null,
                'no_hp'      => $data['no_hp'] ?? null,
                'address'    => $data['address'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
            ], fn($v) => $v !== null);

            if (!empty($detailUpdate)) {
                if ($user->detail) {
                    $user->detail->update($detailUpdate);
                } else {
                    // Jika detail belum ada, buat baru
                    $detailUpdate['user_id'] = $user->id;
                    UserDetail::create($detailUpdate);
                }
            }

            return $user->load('detail');
        } catch (UserException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new UserException('Gagal update user: ' . $e->getMessage());
        }
    }

    /**
     * Update credit user saja
     * 
     * @param User $user
     * @param int $creditScore
     * @return User
     * @throws UserException
     */
    public function updateUserCredit(User $user, int $creditScore): User
    {
        if ($creditScore < 0) {
            throw new UserException('Credit score tidak boleh negatif.');
        }

        try {
            $user->update(['credit_score' => $creditScore]);
            return $user->load('detail');
        } catch (\Exception $e) {
            throw new UserException('Gagal update credit: ' . $e->getMessage());
        }
    }

    /**
     * Delete user dengan validasi relasi
     * 
     * Flow:
     * 1. Check if user has relations (except detail)
     * 2. If yes, throw exception
     * 3. If no, delete detail first
     * 4. Then delete user
     * 
     * @param User $user
     * @return void
     * @throws UserException
     */
    public function deleteUser(User $user): void
    {
        // Check for relations except detail
        $hasLoans = $user->loans()->exists();
        $hasHandledLoans = $user->handledLoans()->exists();
        $hasReturns = $user->returns()->exists();
        $hasViolations = $user->violations()->exists();
        $hasSettlements = $user->settlements()->exists();
        $hasAppeals = $user->appeals()->exists();
        $hasReviewedAppeals = $user->reviewedAppeals()->exists();
        $hasActivityLogs = $user->activityLogs()->exists();

        // If any relation exists (except detail), prevent deletion
        if ($hasLoans || $hasHandledLoans || $hasReturns || $hasViolations || 
            $hasSettlements || $hasAppeals || $hasReviewedAppeals || $hasActivityLogs) {
            throw new UserException('User tidak dapat dihapus karena masih memiliki data terkait.');
        }

        try {
            // Delete detail first (cascade constraint)
            if ($user->detail) {
                $user->detail->delete();
            }

            // Then delete user
            $user->delete();
        } catch (\Exception $e) {
            throw new UserException('Gagal menghapus user: ' . $e->getMessage());
        }
    }
}

