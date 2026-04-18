<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Support\Facades\Hash;

class UserManagementService
{

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

        
        
        
        return $results;
    }

    
    public function getUserById(int $userId): User
    {
        $user = User::with('detail')->find($userId);

        if (!$user) {
            throw UserException::notFound();
        }

        return $user;
    }


    public function createUser(array $data): User
    {

        try {
            
            $user = User::create([
                'email'         => $data['email'],
                'password'      => Hash::make($data['password']),
                'role'          => $data['role'] ?? 'User',
                'credit_score'  => 100,
                'is_restricted' => false,
            ]);

            
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


    public function updateUser(User $user, array $data): User
    {


        try {
            $userUpdate = array_filter([
                'role'  => $data['role'] ?? null,
            ], fn($v) => $v !== null);

            if (!empty($userUpdate)) {
                $user->update($userUpdate);
            }

            
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

    
    public function deleteUser(User $user): void
    {
        
        $hasRelation = $user->loans()->exists() ||
            $user->handledLoans()->exists() ||
            $user->returns()->exists() ||
            $user->violations()->exists() ||
            $user->settlements()->exists() ||
            $user->appeals()->exists() ||
            $user->reviewedAppeals()->exists() ||
            $user->activityLogs()->exists();

        
        if ($hasRelation) {
            throw UserException::hasRelations();
        }

        try {
            
            if ($user->detail) {
                $user->detail->delete();
            }

            
            $user->delete();
        } catch (\Exception $e) {
            throw UserException::deleteFailed($e->getMessage());
        }
    }
}
