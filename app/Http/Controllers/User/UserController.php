<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserCreditRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\UserManagementService;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function __construct(
        private readonly UserManagementService $userService
    ) {}

    public function index(Request $request)
    {
        $users = $this->userService->getAllUsers(
            $request->get('per_page', 10),
            $request->get('search'),
            $request->get('role')
        );

        return UserResource::collection($users);
    }
    
    public function store(StoreUserRequest $request)
    {
        $user = $this->userService->createUser($request->validated());
        app(ActivityLogService::class)->log(
            'user.created',
            'users',
            "Membuat user {$user->email}.",
            ['user_id' => $user->id, 'role' => $user->role]
        );

        return (new UserResource($user))
            ->additional([
                'message' => 'User berhasil dibuat. Credit awal: 100.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function show(User $user)
    {
        $user = $this->userService->getUserById($user->id);

        return new UserResource($user);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user = $this->userService->updateUser($user, $request->validated());
        app(ActivityLogService::class)->log(
            'user.updated',
            'users',
            "Mengupdate user {$user->email}.",
            ['user_id' => $user->id, 'role' => $user->role]
        );

        return (new UserResource($user))
            ->additional([
                'message' => 'User berhasil diupdate.',
            ]);
    }

    public function destroy(User $user)
    {
        $meta = ['user_id' => $user->id, 'email' => $user->email];
        $this->userService->deleteUser($user);
        app(ActivityLogService::class)->log(
            'user.deleted',
            'users',
            "Menghapus user {$meta['email']}.",
            $meta
        );

        return response()->json([
            'message' => 'User berhasil dihapus.',
        ]);
    }

    public function updateCredit(UpdateUserCreditRequest $request, User $user)
    {
        $user = $this->userService->updateUserCredit(
            $user,
            $request->validated()['credit']
        );
        app(ActivityLogService::class)->log(
            'user.credit_updated',
            'users',
            "Mengupdate credit user {$user->email}.",
            ['user_id' => $user->id, 'credit_score' => $user->credit_score]
        );

        return (new UserResource($user))
            ->additional([
                'message' => 'Credit berhasil diupdate.',
            ]);
    }
}
