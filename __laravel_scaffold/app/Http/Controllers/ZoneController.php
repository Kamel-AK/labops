<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ZoneController extends Controller
{
    use AuthorizesRequests;

    public function index(): Response
    {
        $this->authorize('viewAny', Zone::class);

        return Inertia::render('zones/pages/Index');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Zone::class);
    }

    public function update(Request $request, Zone $zone)
    {
        $this->authorize('update', $zone);
    }

    public function destroy(Zone $zone)
    {
        $this->authorize('delete', $zone);
    }
}
