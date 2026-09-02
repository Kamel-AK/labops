<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EquipmentController extends Controller
{
    use AuthorizesRequests;

    public function index(): Response
    {
        $this->authorize('viewAny', Equipment::class);

        return Inertia::render('equipment/pages/Index');
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
