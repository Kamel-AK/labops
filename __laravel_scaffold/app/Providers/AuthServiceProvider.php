<?php

namespace App\Providers;

use App\Models\Equipment;
use App\Models\EquipmentCheckout;
use App\Models\Member;
use App\Models\Project;
use App\Models\Reservation;
use App\Models\Spot;
use App\Models\Zone;
use App\Policies\EquipmentCheckoutPolicy;
use App\Policies\EquipmentPolicy;
use App\Policies\MemberPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\ReservationPolicy;
use App\Policies\SpotPolicy;
use App\Policies\ZonePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Member::class => MemberPolicy::class,
        Project::class => ProjectPolicy::class,
        Reservation::class => ReservationPolicy::class,
        Equipment::class => EquipmentPolicy::class,
        EquipmentCheckout::class => EquipmentCheckoutPolicy::class,
        Zone::class => ZonePolicy::class,
        Spot::class => SpotPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('view-member-contact', [MemberPolicy::class, 'viewContactDetails']);
        Gate::define('create-reservation-for', [ReservationPolicy::class, 'createFor']);
        Gate::define('initiate-equipment-checkout', [EquipmentPolicy::class, 'initiateCheckout']);
        Gate::define('check-in-equipment', [EquipmentCheckoutPolicy::class, 'checkIn']);
    }
}
