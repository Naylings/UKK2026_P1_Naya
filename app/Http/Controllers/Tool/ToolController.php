<?php

namespace App\Http\Controllers\Tool;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tool\StoreToolRequest;
use App\Http\Requests\Tool\UpdateToolRequest;
use App\Http\Resources\Tool\ToolResource;
use App\Models\Tool;
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
        $tool = $this->toolService->updateTool($tool, $request->validated());

        return (new ToolResource($tool))
            ->additional([
                'message' => 'Alat berhasil diupdate.',
            ]);
    }

    public function destroy(Tool $tool)
    {
        $this->toolService->deleteTool($tool);

        return response()->json([
            'message' => 'Alat berhasil dihapus.',
        ]);
    }
}
