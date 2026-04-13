<?php

namespace App\Services;

use App\Exceptions\ToolException;
use App\Models\Tool;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ToolManagementService
{
    private function storePhotoOrFail(?UploadedFile $photoFile): ?string
    {
        if (!$photoFile) {
            return null;
        }

        if (!$photoFile->isValid()) {
            throw ToolException::invalidPhotoUpload($this->mapUploadError($photoFile->getError()));
        }

        if (!$photoFile->getRealPath()) {
            throw ToolException::invalidPhotoUpload('file temporary upload tidak ditemukan');
        }

        return $photoFile->store('tools', 'public');
    }

    private function mapUploadError(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'ukuran file melebihi batas upload server',
            UPLOAD_ERR_PARTIAL => 'file hanya terupload sebagian',
            UPLOAD_ERR_NO_FILE => 'file tidak ditemukan pada request',
            UPLOAD_ERR_NO_TMP_DIR => 'folder temporary upload di server tidak tersedia',
            UPLOAD_ERR_CANT_WRITE => 'server gagal menulis file upload',
            UPLOAD_ERR_EXTENSION => 'upload dihentikan oleh extension PHP',
            default => 'file upload tidak valid',
        };
    }
    // ─────────────────────────────────────────────────────────────────────────
    // Code Slug Prefix Rules
    //
    //  single      → no prefix,  e.g.  GERINDA
    //  bundle      → SET-,       e.g.  SET-GERINDA
    //  bundle_tool → SUB-,       e.g.  SUB-GERINDA-1
    //
    // Prefix SUB- pada bundle_tool mencegah collision dengan single
    // yang kebetulan punya nama sama dengan komponen bundle-nya.
    // ─────────────────────────────────────────────────────────────────────────

    private function applyCodeSlugPrefix(string $slug, string $itemType): string
    {
        // Strip prefix lama jika ada, lalu uppercase
        $slug = Str::upper($slug);
        $slug = preg_replace('/^(SET-|SUB-)/', '', $slug);

        return match ($itemType) {
            'bundle'      => 'SET-' . $slug,
            'bundle_tool' => 'SUB-' . $slug,
            default       => $slug, // single: no prefix
        };
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────────────────────────

    public function getAllTools(int $perPage = 10, ?string $search = null, ?string $category = null)
    {
        $query = Tool::with(['category', 'bundleComponents.tool'])
            ->where('item_type', '!=', 'bundle_tool'); // Exclude bundle_tool dari list utama

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

        return $query->paginate($perPage);
    }

    public function getToolById(int $toolId): Tool
    {
        $tool = Tool::with(['category', 'bundleComponents.tool'])->find($toolId);

        if (!$tool) {
            throw ToolException::notFound();
        }

        return $tool;
    }

    public function createTool(array $data, ?UploadedFile $photoFile = null): Tool
    {
        try {
            $tool = DB::transaction(function () use ($data, $photoFile) {
                // Handle foto upload
                $photoPath = $this->storePhotoOrFail($photoFile);
                $photoPath = $photoPath ?? ($data['photo_path'] ?? 'tools/placeholder-tool.png');

                // Terapkan prefix sesuai item_type
                $codeSlug = $this->applyCodeSlugPrefix(
                    $data['code_slug'],
                    $data['item_type']
                );

                $tool = Tool::create([
                    'category_id'      => $data['category_id'],
                    'name'             => $data['name'],
                    'item_type'        => $data['item_type'],
                    'price'            => $data['price'],
                    'min_credit_score' => $data['min_credit_score'],
                    'description'      => $data['description'] ?? '-',
                    'code_slug'        => $codeSlug,
                    'photo_path'       => $photoPath,
                    'created_at'       => now(),
                ]);

                $this->syncBundleComponents($tool, $data['bundle_components'] ?? null);

                return $tool;
            });

            return $tool->load(['category', 'bundleComponents.tool']);
        } catch (ToolException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw ToolException::createFailed($e->getMessage());
        }
    }

    public function updateTool(Tool $tool, array $data, ?UploadedFile $photoFile = null): Tool
    {
        try {
            DB::transaction(function () use ($tool, $data, $photoFile) {
                $finalItemType = $data['item_type'] ?? $tool->item_type;
                $photoPath = $tool->photo_path;

                if ($photoFile) {
                    $photoPath = $this->storePhotoOrFail($photoFile);
                }

                $codeSlug = $this->applyCodeSlugPrefix(
                    $data['code_slug'] ?? $tool->code_slug,
                    $finalItemType
                );

                $tool->update(array_filter([
                    'category_id'      => $data['category_id'] ?? null,
                    'name'             => $data['name'] ?? null,
                    'item_type'        => $data['item_type'] ?? null,
                    'price'            => $data['price'] ?? null,
                    'min_credit_score' => $data['min_credit_score'] ?? null,
                    'description'      => $data['description'] ?? null,
                    'code_slug'        => $codeSlug,
                    'photo_path'       => $photoPath,
                ], fn($v) => $v !== null));

                $shouldSyncComponents =
                    array_key_exists('bundle_components', $data)
                    || $tool->item_type !== 'bundle';

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

    // ─────────────────────────────────────────────────────────────────────────
    // Bundle component helpers
    // ─────────────────────────────────────────────────────────────────────────

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

        $createdComponents = collect($components)
            ->map(function (array $component, int $index) use ($tool) {
                $childTool = Tool::create([
                    'category_id'      => $tool->category_id,
                    'name'             => $component['name'],
                    'item_type'        => 'bundle_tool',
                    'price'            => $component['price'],
                    // min_credit_score bundle_tool selalu ikut parent
                    'min_credit_score' => $tool->min_credit_score,
                    'description'      => $component['description'] ?? $tool->description,
                    'code_slug'        => $this->generateComponentCodeSlug(
                        $tool->code_slug,
                        $component['name'],
                        $index + 1
                    ),
                    'photo_path'       => $component['photo_path'] ?? 'tools/placeholder-tool.png',
                    'created_at'       => now(),
                ]);

                return [
                    'tool_id' => $childTool->id,
                    'qty'     => $component['qty'],
                ];
            })
            ->all();

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

            if (!$childTool) continue;

            $isSafeToDelete = $childTool->isBundleTool()
                && !$childTool->loans()->exists()
                && !$childTool->units()->exists()
                && !$childTool->bundles()->exists();

            if ($isSafeToDelete) {
                $childTool->delete();
            }
        }
    }

    /**
     * Generate code slug untuk bundle_tool.
     *
     * Format: SUB-{baseParent}-{index}
     * Contoh: parent = SET-GERINDA → SUB-GERINDA-1, SUB-GERINDA-2
     *
     * base diambil dari parent slug setelah prefix SET-/SUB- dicopot.
     * Jika collision, counter naik sampai slug unik.
     */
    private function generateComponentCodeSlug(string $parentSlug, string $componentName, int $index): string
    {
        // Ambil base dari parent: SET-GERINDA → GERINDA
        $base = preg_replace('/^(SET-|SUB-)/', '', Str::upper($parentSlug));

        // Fallback ke nama komponen jika base kosong
        if ($base === '') {
            $base = Str::upper(
                Str::of($componentName)->replaceMatches('/[^A-Za-z0-9]/', '')->substr(0, 10)
            );
        }

        // Max total panjang slug = 20 char
        // SUB- (4) + base + - (1) + suffix
        $suffix    = (string) $index;
        $maxBase   = max(1, 20 - 4 - 1 - strlen($suffix));
        $trimBase  = Str::substr($base, 0, $maxBase);
        $candidate = "SUB-{$trimBase}-{$suffix}";

        // Loop sampai slug benar-benar unik
        $counter = $index;
        while (Tool::query()->where('code_slug', $candidate)->exists()) {
            $counter++;
            $suffix    = (string) $counter;
            $maxBase   = max(1, 20 - 4 - 1 - strlen($suffix));
            $trimBase  = Str::substr($base, 0, $maxBase);
            $candidate = "SUB-{$trimBase}-{$suffix}";
        }

        return $candidate;
    }
}
