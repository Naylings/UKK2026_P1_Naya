<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\StoreUserRequest;
use App\Http\Requests\Auth\UpdateUserRequest;
use App\Http\Requests\Auth\UpdateUserCreditRequest;
use App\Http\Resources\Auth\AuthResource;
use App\Http\Resources\User\UserResource;
use App\Services\AuthService;
use App\Services\UserManagementService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly UserManagementService $userService,
    ) {}

    /**
     * POST /api/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return (new AuthResource($result))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(LoginRequest $request): JsonResponse
    {
        $this->authService->logout();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * POST /api/auth/refresh
     */
    public function refresh(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->refresh();

        return (new AuthResource($result))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * GET /api/auth/me
     */
    public function me(): JsonResponse
    {
        $result = $this->authService->me();

        return (new AuthResource($result))
            ->response()
            ->setStatusCode(200);
    }

    // ─────────────────────────────────────────────────────────────────────
    // User CRUD
    // ─────────────────────────────────────────────────────────────────────

    /**
     * GET /api/users
     * List semua users (paginasi, search, filter role)
     */
    public function indexUsers(\Illuminate\Http\Request $request): AnonymousResourceCollection
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');
        $role = $request->get('role');

        $users = $this->userService->getAllUsers($perPage, $search, $role);
        return UserResource::collection($users);
    }

    /**
     * POST /api/users
     * Create user baru (credit default: 100)
     */
    public function storeUser(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request->validated());

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/users/{id}
     * Get detail user
     */
    public function showUser(User $user): JsonResponse
    {
        $user = $this->userService->getUserById($user->id);

        return (new UserResource($user))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * PUT /api/users/{id}
     * Update user
     */
    public function updateUser(User $user, UpdateUserRequest $request): JsonResponse
    {
        $updatedUser = $this->userService->updateUser($user, $request->validated());

        return (new UserResource($updatedUser))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * DELETE /api/users/{id}
     * Delete user
     */
    public function destroyUser(User $user): JsonResponse
    {
        $this->userService->deleteUser($user);

        return response()->json([
            'message' => 'User berhasil dihapus.',
        ]);
    }

    /**
     * POST /api/users/{id}/credit
     * Update credit user saja
     */
    public function updateUserCredit(User $user, UpdateUserCreditRequest $request): JsonResponse
    {
        $updatedUser = $this->userService->updateUserCredit($user, $request->validated()['credit']);

        return (new UserResource($updatedUser))
            ->response()
            ->setStatusCode(200);
    }
}


