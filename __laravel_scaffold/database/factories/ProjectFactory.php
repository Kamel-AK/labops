<?php

namespace Database\Factories;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $lead = Member::factory()->teamLead();

        return [
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'status' => ProjectStatus::ACTIVE->value,
            'start_date' => now()->toDateString(),
            'target_end_date' => now()->addMonth()->toDateString(),
            'lead_id' => $lead,
            'requested_by' => $lead,
            'approved_by' => null,
            'priority' => ProjectPriority::MEDIUM->value,
            'approved_at' => now(),
        ];
    }

    public function forLead(Member $member): static
    {
        return $this->state(fn () => [
            'lead_id' => $member->id,
            'requested_by' => $member->id,
        ]);
    }
}
