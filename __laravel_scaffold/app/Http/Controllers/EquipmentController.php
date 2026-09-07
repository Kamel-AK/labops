<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Services\EquipmentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EquipmentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected EquipmentService $equipmentService
    ) {}

    public function index(): Response
    {
        $this->authorize('viewAny', Equipment::class);

        return Inertia::render('equipment/pages/Index');
    }

    public function import(Request $request)
    {
        $this->authorize('create', Equipment::class);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        try {
            $result = $this->equipmentService->importCsv($request->file('file'), $request->user());

            return response()->json([
                'message' => "Imported {$result['imported_count']} equipment records successfully.",
                'created_count' => $result['created_count'],
                'updated_count' => $result['updated_count'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function previewImport(Request $request)
    {
        $this->authorize('create', Equipment::class);
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:2048']]);

        return response()->json($this->equipmentService->previewCsv($request->file('file')));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Equipment::class);
    }

    public function show(Equipment $equipment)
    {
        $this->authorize('view', $equipment);
    }

    public function update(Request $request, Equipment $equipment)
    {
        $this->authorize('update', $equipment);
    }

    public function destroy(Equipment $equipment)
    {
        $this->authorize('delete', $equipment);
    }
}
