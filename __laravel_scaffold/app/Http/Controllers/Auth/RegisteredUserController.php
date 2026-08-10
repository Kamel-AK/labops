<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
    'full_name' => 'required|string|max:255',
    'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
    'password' => ['required', 'confirmed', Rules\Password::defaults()],
    'phone' => ['nullable', 'string', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10'], 
]);

        $user = User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'access_status' => 'pending',
            'role' => 'volunteer',
        ]);

        event(new Registered($user));

        return redirect(route('login'))->with('message', 'تم إنشاء حسابك، بانتظار موافقة المنسق.');
    }
}
