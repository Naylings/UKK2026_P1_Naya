<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\Category\CategoryResource;
use App\Models\Category;
use App\Services\CategoryManagementService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    public function __construct(
        private readonly CategoryManagementService $catService
    ) {}

    public function index(Request $request)
    {
        $users = $this->catService->getAllCategories(
            $request->get('per_page', 10),
            $request->get('search'),
        );

        return CategoryResource::collection($users);
    }
    
    public function store(StoreCategoryRequest $request)
    {
        $cat = $this->catService->createCategory($request->validated());

        return (new CategoryResource($cat))
            ->additional([
                'message' => 'Category berhasil dibuat.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category = $this->catService->updateCategory($category, $request->validated());

        return (new CategoryResource($category))
            ->additional([
                'message' => 'Category berhasil diupdate.',
            ]);
    }

    public function destroy(Category $category)
    {
        $this->catService->deleteCategory($category);

        return response()->json([
            'message' => 'Category berhasil dihapus.',
        ]);
    }
}
