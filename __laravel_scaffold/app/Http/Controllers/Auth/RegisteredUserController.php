<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        abort(404);
    }

    public function store(Request $request): RedirectResponse
    {
        abort(404);
    }
}
