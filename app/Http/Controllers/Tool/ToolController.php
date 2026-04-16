<?php

namespace App\Http\Controllers\Tool;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tool\StoreToolRequest;
use App\Http\Requests\Tool\UpdateToolRequest;
use App\Http\Resources\Tool\ToolResource;
use App\Models\Tool;
use App\Services\ActivityLogService;
use App\Services\ToolManagementService;
use Illuminate\Http\Request;

class ToolController extends Controller
{
    public function __construct(
        private readonly ToolManagementService $toolService
    ) {}

    public function index(Request $request)
    {
        $tools = $this->toolService->getAllTools(
            $request->get('per_page', 10),
            $request->get('search'),
            $request->get('category')
        );

        return ToolResource::collection($tools);
    }

    public function store(StoreToolRequest $request)
    {
        $photoFile = $request->file('photo');
        $tool = $this->toolService->createTool(
            $request->validated(),
            $photoFile
        );
        app(ActivityLogService::class)->log(
            'tool.created',
            'tools',
            "Membuat alat {$tool->name}.",
            ['tool_id' => $tool->id, 'item_type' => $tool->item_type]
        );

        return (new ToolResource($tool))
            ->additional([
                'message' => 'Alat berhasil dibuat.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Tool $tool)
    {
        $tool = $this->toolService->getToolById($tool->id);

        return new ToolResource($tool);
    }

    public function update(UpdateToolRequest $request, Tool $tool)
    {
        $tool = $this->toolService->updateTool(
            $tool,
            $request->validated(),
            $request->file('photo')
        );
        app(ActivityLogService::class)->log(
            'tool.updated',
            'tools',
            "Mengupdate alat {$tool->name}.",
            ['tool_id' => $tool->id, 'item_type' => $tool->item_type]
        );

        return (new ToolResource($tool))
            ->additional([
                'message' => 'Alat berhasil diupdate.',
            ]);
    }

    public function destroy(Tool $tool)
    {
        $meta = ['tool_id' => $tool->id, 'name' => $tool->name];
        $this->toolService->deleteTool($tool);
        app(ActivityLogService::class)->log(
            'tool.deleted',
            'tools',
            "Menghapus alat {$meta['name']}.",
            $meta
        );

        return response()->json([
            'message' => 'Alat berhasil dihapus.',
        ]);
    }
}
