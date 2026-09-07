<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class MemberController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Member::class);

        $canViewContact = Gate::allows('view-member-contact');

        $members = Member::query()
            ->when($request->user()->isTeamLead(), fn ($query) => $query->where('access_status', 'granted'))
            ->orderBy('full_name')
            ->get()
            ->map(fn (Member $member) => $this->memberPayload($member, $canViewContact));

        return Inertia::render('members/pages/Index', [
            'members' => $members,
            'canCreateMembers' => $request->user()->can('create', Member::class),
            'canViewContactDetails' => $canViewContact,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Member::class);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:members,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'in:coordinator,team_lead,volunteer'],
            'access_status' => ['required', 'in:pending,granted,revoked,suspended'],
            'skills' => ['nullable', 'array'],
            'certifications' => ['nullable', 'array'],
            'emergency_contact' => ['nullable', 'string', 'max:1000'],
            'join_date' => ['nullable', 'date'],
        ]);

        Member::create([
            ...collect($validated)->except(['password'])->all(),
            'password_hash' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Member account created.');
    }

    public function show(Request $request, Member $member): Response
    {
        $this->authorize('view', $member);

        $canViewContact = Gate::allows('view-member-contact', $member);

        return Inertia::render('members/pages/Index', [
            'member' => $this->memberPayload($member, $canViewContact),
            'canViewContactDetails' => $canViewContact,
        ]);
    }

    public function update(Request $request, Member $member): RedirectResponse
    {
        $this->authorize('update', $member);

        $rules = [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'skills' => ['nullable', 'array'],
            'certifications' => ['nullable', 'array'],
            'emergency_contact' => ['nullable', 'string', 'max:1000'],
        ];

        if ($request->user()->isCoordinator()) {
            $rules += [
                'role' => ['required', 'in:coordinator,team_lead,volunteer'],
                'access_status' => ['required', 'in:pending,granted,revoked,suspended'],
                'join_date' => ['nullable', 'date'],
            ];
        }

        $member->update($request->validate($rules));

        return back()->with('success', 'Member updated.');
    }

    public function destroy(Member $member): RedirectResponse
    {
        $this->authorize('delete', $member);

        $member->delete();

        return back()->with('success', 'Member removed.');
    }

    private function memberPayload(Member $member, bool $includeContact): array
    {
        $payload = [
            'id' => $member->id,
            'full_name' => $member->full_name,
            'role' => $member->role,
            'access_status' => $member->access_status,
            'skills' => $member->skills,
            'certifications' => $member->certifications,
            'join_date' => optional($member->join_date)->toDateString(),
        ];

        if ($includeContact) {
            $payload += [
                'email' => $member->email,
                'phone' => $member->phone,
                'emergency_contact' => $member->emergency_contact,
            ];
        }

        return $payload;
    }
}
