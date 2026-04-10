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

        $results = $query->paginate($perPage);

        if (!$results->count()) {
            throw UserException::notFound();
        }
        return $results;
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
            throw  UserException::createFailed($e->getMessage());
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


        try {
            $userUpdate = array_filter([
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
            throw UserException::updateFailed($e->getMessage());
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
            throw UserException::creditNegative();
        }

        try {
            $user->update(['credit_score' => $creditScore]);
            return $user->load('detail');
        } catch (\Exception $e) {
            throw UserException::updateCreditFailed($e->getMessage());
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
        $hasRelation = $user->loans()->exists() ||
            $user->handledLoans()->exists() ||
            $user->returns()->exists() ||
            $user->violations()->exists() ||
            $user->settlements()->exists() ||
            $user->appeals()->exists() ||
            $user->reviewedAppeals()->exists() ||
            $user->activityLogs()->exists();

        // If any relation exists (except detail), prevent deletion
        if ($hasRelation) {
            throw UserException::hasRelations();
        }

        try {
            // Delete detail first (cascade constraint)
            if ($user->detail) {
                $user->detail->delete();
            }

            // Then delete user
            $user->delete();
        } catch (\Exception $e) {
            throw UserException::deleteFailed($e->getMessage());
        }
    }
}
