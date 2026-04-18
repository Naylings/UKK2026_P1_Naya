<?php

namespace App\Http\Controllers\Appeal;

use App\Services\AppealService;
use App\Http\Requests\Appeal\StoreAppealRequest;
use App\Http\Requests\Appeal\ReviewAppealRequest;
use App\Http\Resources\Appeal\AppealResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppealController extends Controller
{
    public function __construct(
        protected AppealService $appealService
    ) {}

    public function store(StoreAppealRequest $request): JsonResponse
    {
        $appeal = $this->appealService->create($request->validated());

        return response()->json([
            'message' => 'Appeal berhasil diajukan',
            'data' => new AppealResource($appeal),
        ], 201);
    }

    public function index(Request $request)
    {
        $appeals = $this->appealService->getAll($request->all());
        return AppealResource::collection($appeals);
    }

    public function myAppeals(Request $request)
    {
        $appeals = $this->appealService->getByUserId(
            $request->user()->id,
            $request->all()
        );
        return AppealResource::collection($appeals);
    }

    public function review(int $id, ReviewAppealRequest $request): JsonResponse
    {
        $appeal = $this->appealService->review(
            $id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Review appeal berhasil',
            'data' => new AppealResource($appeal),
        ]);
    }
}
?>

