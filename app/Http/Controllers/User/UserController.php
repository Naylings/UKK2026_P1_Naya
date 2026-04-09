<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StoreUserRequest;
use App\Http\Requests\Auth\UpdateUserCreditRequest;
use App\Http\Requests\Auth\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
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

        return (new UserResource($user))
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

        return new UserResource($user);
    }

    public function destroy(User $user)
    {
        $this->userService->deleteUser($user);

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    public function updateCredit(UpdateUserCreditRequest $request, User $user)
    {
        $user = $this->userService->updateUserCredit(
            $user,
            $request->validated()['credit']
        );

        return new UserResource($user);
    }
}
