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
    ) {
    }

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
            $result = $this->equipmentService->parseCsvAndImport($request->file('file'));

            return response()->json([
                'message' => "Imported {$result['imported_count']} equipment records successfully.",
                'warnings' => $result['errors'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }
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
