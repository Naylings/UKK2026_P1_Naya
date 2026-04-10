<?php

namespace App\Services;

use App\Exceptions\ToolException;
use App\Models\Tool;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ToolManagementService
{
    /**
     * Get all tools dengan pagination, search
     * 
     * @param int $perPage
     * @param string|null $search
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllTools(int $perPage = 10, ?string $search = null, ?string $category = null)
    {
        $query = Tool::with(['category', 'bundleComponents.tool']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('code_slug', 'like', "%{$search}%");
            });
        }

        if ($category) {
            $query->where('category_id', $category);
        }

        $result = $query->paginate($perPage);

        if (!$result->count()) {
            throw ToolException::notFound();
        }

        return $result;
    }

    /**
     * Get tool by ID.
     *
     * @throws ToolException
     */
    public function getToolById(int $toolId): Tool
    {
        $tool = Tool::with(['category', 'bundleComponents.tool'])->find($toolId);

        if (!$tool) {
            throw ToolException::notFound();
        }

        return $tool;
    }



    /**
     * Create tool baru
     * 
     * @param array{
     * category_id: int,
     * name: string,
     * item_type: string,
     * price: float,
     * min_credit_score: int,
     * description?: string,
     * code_slug: string,
     * photo_path?: string
     * } $data
     * @return Tool
     * @throws ToolException
     */
    public function createTool(array $data): Tool
    {
        try {
            $tool = DB::transaction(function () use ($data) {
                $tool = Tool::create([
                    'category_id' => $data['category_id'],
                    'name' => $data['name'],
                    'item_type' => $data['item_type'],
                    'price' => $data['price'],
                    'min_credit_score' => $data['min_credit_score'],
                    'description' => $data['description'] ?? null,
                    'code_slug' => $data['code_slug'],
                    'photo_path' => $data['photo_path'] ?? null,
                ]);

                $this->syncBundleComponents($tool, $data['bundle_components'] ?? null);

                return $tool;
            });

            return $tool->load(['category', 'bundleComponents.tool']);
        } catch (\Exception $e) {
            throw ToolException::createFailed($e->getMessage());
        }
    }

    /**
     * Update tool
     * 
     * @param Tool $tool
     * @param array{
     * category_id?: int,
     * name?: string,
     * item_type?: string,
     * price?: float,
     * min_credit_score?: int,
     * description?: string,
     * code_slug?: string,
     * photo_path?: string
     * } $data
     * @return Tool
     * @throws ToolException
     */
    public function updateTool(Tool $tool, array $data): Tool
    {
        try {
            DB::transaction(function () use ($tool, $data) {
                $tool->update(array_filter([
                    'category_id' => $data['category_id'] ?? null,
                    'name' => $data['name'] ?? null,
                    'item_type' => $data['item_type'] ?? null,
                    'price' => $data['price'] ?? null,
                    'min_credit_score' => $data['min_credit_score'] ?? null,
                    'description' => $data['description'] ?? null,
                    'code_slug' => $data['code_slug'] ?? null,
                    'photo_path' => $data['photo_path'] ?? null,
                ], fn($v) => $v !== null));

                $shouldSyncComponents = array_key_exists('bundle_components', $data) || $tool->item_type !== 'bundle';
                if ($shouldSyncComponents) {
                    $this->syncBundleComponents($tool, $data['bundle_components'] ?? null);
                }
            });

            return $tool->load(['category', 'bundleComponents.tool']);
        } catch (ToolException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw ToolException::updateFailed($e->getMessage());
        }
    }

    /**
     * Delete tool
     * 
     * @param Tool $tool
     * @return void
     * @throws ToolException
     */
    public function deleteTool(Tool $tool): void
    {
        $hasRelation = $tool->loans()->exists()
            || $tool->units()->exists()
            || $tool->bundles()->exists();

        if ($hasRelation) {
            throw ToolException::hasRelations();
        }


        try {
            $this->clearBundleComponents($tool);
            $tool->delete();
        } catch (\Exception $e) {
            throw ToolException::deleteFailed($e->getMessage());
        }
    }

    /**
     * Sync komponen untuk tool bertipe bundle.
     *
     * @param array<int, array{name:string, price:numeric, qty:int, description?:?string, photo_path?:?string, category_id?:?int, min_credit_score?:?int, code_slug?:?string}>|null $components
     *
     * @throws ToolException
     */
    private function syncBundleComponents(Tool $tool, ?array $components): void
    {
        if (!$tool->isBundle()) {
            $this->clearBundleComponents($tool);
            return;
        }

        if (empty($components)) {
            throw ToolException::bundleComponentsRequired();
        }

        $this->clearBundleComponents($tool);

        $createdComponents = collect($components)->map(function (array $component, int $index) use ($tool) {
            $childTool = Tool::create([
                'category_id' => $component['category_id'] ?? $tool->category_id,
                'name' => $component['name'],
                'item_type' => 'bundle_tool',
                'price' => $component['price'],
                'min_credit_score' => $component['min_credit_score'] ?? $tool->min_credit_score,
                'description' => $component['description'] ?? $tool->description,
                'code_slug' => $component['code_slug'] ?? $this->generateComponentCodeSlug($tool->code_slug, $component['name'], $index + 1),
                'photo_path' => $component['photo_path'] ?? null,
            ]);

            return [
                'tool_id' => $childTool->id,
                'qty' => $component['qty'],
            ];
        })->all();

        if (empty($createdComponents)) {
            throw ToolException::invalidBundleComponent();
        }

        $tool->bundleComponents()->createMany($createdComponents);
    }

    private function clearBundleComponents(Tool $tool): void
    {
        $existingComponents = $tool->bundleComponents()->with('tool')->get();

        $tool->bundleComponents()->delete();

        foreach ($existingComponents as $component) {
            $childTool = $component->tool;

            if (!$childTool) {
                continue;
            }

            $isSafeToDelete = $childTool->isBundleTool()
                && !$childTool->loans()->exists()
                && !$childTool->units()->exists()
                && !$childTool->bundles()->exists();

            if ($isSafeToDelete) {
                $childTool->delete();
            }
        }
    }

    private function generateComponentCodeSlug(string $parentCode, string $name, int $index): string
    {
        $seed = Str::upper(Str::of($parentCode)->replaceMatches('/[^A-Za-z0-9]/', '')->substr(0, 8));
        if ($seed === '') {
            $seed = Str::upper(Str::of($name)->replaceMatches('/[^A-Za-z0-9]/', '')->substr(0, 8));
        }

        $suffix = (string) $index;
        $base = Str::substr($seed, 0, max(1, 15 - strlen($suffix) - 1));
        $candidate = "{$base}-{$suffix}";

        $counter = $index;
        while (Tool::query()->where('code_slug', $candidate)->exists()) {
            $counter++;
            $suffix = (string) $counter;
            $base = Str::substr($seed, 0, max(1, 15 - strlen($suffix) - 1));
            $candidate = "{$base}-{$suffix}";
        }

        return $candidate;
    }
}
