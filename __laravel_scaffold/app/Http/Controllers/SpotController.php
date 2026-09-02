<?php

namespace App\Http\Controllers;

use App\Models\Spot;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SpotController extends Controller
{
    use AuthorizesRequests;

    public function index(): Response
    {
        $this->authorize('viewAny', Spot::class);

        return Inertia::render('spots/pages/Index');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Spot::class);
    }

    public function update(Request $request, Spot $spot)
    {
        $this->authorize('update', $spot);
    }

    public function destroy(Spot $spot)
    {
        $this->authorize('delete', $spot);
    }
}
