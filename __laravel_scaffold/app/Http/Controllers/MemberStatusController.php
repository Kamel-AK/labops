<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MemberStatusController extends Controller
{
    use AuthorizesRequests;

    public function grantAccess(Member $member): RedirectResponse
    {
        $this->authorize('manageLifecycle', $member);

        $member->update(['access_status' => 'granted']);

        return back()->with('success', 'Member access granted.');
    }

    public function suspendAccess(Member $member): RedirectResponse
    {
        $this->authorize('manageLifecycle', $member);

        $member->update(['access_status' => 'suspended']);

        return back()->with('success', 'Member access suspended.');
    }

    public function revokeAccess(Member $member): RedirectResponse
    {
        $this->authorize('manageLifecycle', $member);

        $member->update(['access_status' => 'revoked']);

        return back()->with('success', 'Member access revoked.');
    }

    public function updateRole(Request $request, Member $member): RedirectResponse
    {
        $this->authorize('manageLifecycle', $member);

        $validated = $request->validate([
            'role' => ['required', 'in:coordinator,team_lead,volunteer'],
        ]);

        $member->update(['role' => $validated['role']]);

        return back()->with('success', 'Member role updated.');
    }
}
