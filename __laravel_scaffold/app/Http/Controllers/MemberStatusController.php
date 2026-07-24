<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;

class MemberStatusController extends Controller
{
    use AuthorizesRequests;

    public function grantAccess(User $member)
    {

        $this->authorize('update', $member);

        $member->update([
            'access_status' => 'granted'
        ]);

        return back()->with('success', 'تم تفعيل حساب العضو بنجاح.');
    }


    public function suspendAccess(User $member)
    {
        $this->authorize('update', $member);

        $member->update([
            'access_status' => 'suspended'
        ]);

        return back()->with('success', 'تم إيقاف حساب العضو.');
    }

    
    public function updateRole(Request $request, User $member)
    {
        $this->authorize('update', $member);

        $validated = $request->validate([
            'role' => 'required|in:coordinator,team_lead,volunteer',
        ]);

        $member->update([
            'role' => $validated['role']
        ]);

        return back()->with('success', 'تم تحديث دور العضو بنجاح.');
    }
}
