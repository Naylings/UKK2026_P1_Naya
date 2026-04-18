<?php

namespace App\Services;

use App\Exceptions\CategoryException;
use App\Models\Category;

class CategoryManagementService
{
    
    public function getAllCategories(int $perPage = 10, ?string $search = null)
    {
        $query = Category::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        $result = $query->paginate($perPage);

        
        
        

        return $result;
    }



    
    public function createCategory(array $data): Category
    {

        try {
            
            $category = Category::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            return $category;
        } catch (\Exception $e) {
            throw CategoryException::createFailed($e->getMessage());
        }
    }

    
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

    
    public function deleteCategory(Category $category): void
    {
        
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
