<?php

namespace App\Services;

use App\Exceptions\CategoryException;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;

class CategoryManagementService
{
    /**
     * Get all categories dengan pagination, search
     * 
     * @param int $perPage
     * @param string|null $search
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllCategories(int $perPage = 10, ?string $search = null)
    {
        $query = Category::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        $reults = $query->paginate($perPage);

        if (!$reults->count()) {
            throw CategoryException::notFound();
        }

        return $reults;
    }



    /**
     * Create category baru
     * 
     * @param array{
     *   name: string,
     *   description?: string
     * } $data
     * @return Category
     * @throws CategoryException
     */
    public function createCategory(array $data): Category
    {

        try {
            // Create category
            $category = Category::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            return $category;
        } catch (\Exception $e) {
            throw CategoryException::createFailed($e->getMessage());
        }
    }

    /**
     * Update category
     * 
     * @param Category $category
     * @param array{
     *   name?: string,
     *   description?: string
     * } $data
     * @return Category
     * @throws CategoryException
     */
    public function updateCategory(Category $category, array $data): Category
    {
        try {
            $category->update(array_filter([
                'name' => $data['name'] ?? null,
                'description' => $data['description'] ?? null,
            ], fn($v) => $v !== null));

            return $category;
        } catch (CategoryException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw CategoryException::updateFailed($e->getMessage());
        }
    }

    /**
     * Delete category
     * 
     * @param Category $category
     * @return void
     * @throws CategoryException
     */
    public function deleteCategory(Category $category): void
    {
        // check relations
        if ($category->tools()->exists()) {
            throw CategoryException::hasRelations();
        }


        try {
            $category->delete();
        } catch (\Exception $e) {
            throw CategoryException::deleteFailed($e->getMessage());
        }
    }
}
