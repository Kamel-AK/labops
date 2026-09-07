<?php

use App\Enums\EquipmentStatus;
use App\Enums\EquipmentType;
use App\Models\ActivityLog;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\Member;
use App\Models\Project;
use App\Models\Spot;
use App\Models\Zone;
use App\Services\ConsumableUsageService;
use App\Services\EquipmentCheckoutService;
use App\Services\EquipmentService;
use App\Services\ProjectWorkflowService;
use App\Services\ReservationService;
use Database\Seeders\ZoneAndSpotSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

test('sprint one seeds an idempotent physical topology of five zones and thirty spots', function () {
    $this->seed(ZoneAndSpotSeeder::class);
    $this->seed(ZoneAndSpotSeeder::class);

    expect(Zone::count())->toBe(5)
        ->and(Spot::count())->toBe(30)
        ->and(Schema::hasColumn('spots', 'equipment_list'))->toBeFalse();
});

test('project workflow enforces legal transitions, writes audit records, and retains equipment needs', function () {
    $requester = Member::factory()->volunteer()->create();
    $lead = Member::factory()->teamLead()->create();
    $coordinator = Member::factory()->coordinator()->create();
    $equipment = Equipment::factory()->create();
    $project = Project::factory()->forLead($lead)->create(['requested_by' => $requester->id, 'status' => 'proposed', 'approved_by' => null, 'approved_at' => null]);

    $workflow = app(ProjectWorkflowService::class);
    $workflow->transition($project, 'submit', $requester);
    $workflow->transition($project, 'approve', $coordinator);
    $project->refresh();
    $project->equipmentNeeds()->sync([$equipment->id => ['quantity_needed' => 2, 'notes' => 'Needed for prototype']]);

    expect($project->status)->toBe('active')
        ->and($project->approved_by)->toBe($coordinator->id)
        ->and($project->equipmentNeeds()->first()->pivot->quantity_needed)->toBe(2)
        ->and(ActivityLog::query()->where('entity_id', $project->id)->where('action', 'project.approve')->exists())->toBeTrue();

    expect(fn () => $workflow->transition($project, 'submit', $requester))->toThrow(ValidationException::class);
});

test('csv import previews create and update operations and rejects all invalid rows without partial writes', function () {
    $service = app(EquipmentService::class);
    $good = UploadedFile::fake()->createWithContent('equipment.csv', "name,asset_tag,type,category,quantity_total,quantity_available\nSoldering Iron,LAB-100,durable,Electronics,1,1\nSolder Wire,LAB-101,consumable,Materials,5,5\n");

    $preview = $service->previewCsv($good);
    expect($preview['valid'])->toBeTrue()->and(collect($preview['rows'])->pluck('action')->all())->toBe(['create', 'create']);
    $result = $service->importCsv($good);
    expect($result['created_count'])->toBe(2)->and(Equipment::count())->toBe(2)->and(EquipmentCategory::count())->toBe(2);

    $update = UploadedFile::fake()->createWithContent('equipment.csv', "name,asset_tag,type,category,quantity_total,quantity_available\nUpdated Iron,LAB-100,durable,Electronics,1,1\n");
    expect($service->previewCsv($update)['rows'][0]['action'])->toBe('update');
    expect($service->importCsv($update)['updated_count'])->toBe(1)->and(Equipment::where('asset_tag', 'LAB-100')->value('name'))->toBe('Updated Iron');

    $invalid = UploadedFile::fake()->createWithContent('equipment.csv', "name,asset_tag,type,category,quantity_total,quantity_available\nValid Item,LAB-102,durable,Tools,1,1\nBroken Item,LAB-103,invalid,Tools,1,1\n");
    expect(fn () => $service->importCsv($invalid))->toThrow(ValidationException::class)
        ->and(Equipment::where('asset_tag', 'LAB-102')->exists())->toBeFalse();
});

test('reservation service serializes spot checks and prevents overlaps and a second active booking', function () {
    $member = Member::factory()->create();
    $otherMember = Member::factory()->create();
    $zone = Zone::create(['name' => 'Test Zone', 'status' => 'open']);
    $spot = Spot::create(['zone_id' => $zone->id, 'name' => 'Test Bench', 'type' => 'bench', 'status' => 'active']);
    $service = app(ReservationService::class);
    $start = now()->addDay()->startOfHour();
    $first = $service->create($member, ['member_id' => $member->id, 'zone_id' => $zone->id, 'spot_id' => $spot->id, 'start_time' => $start, 'end_time' => $start->copy()->addHour(), 'purpose' => 'Build']);

    expect($first->status)->toBe('confirmed');
    expect(fn () => $service->create($otherMember, ['member_id' => $otherMember->id, 'zone_id' => $zone->id, 'spot_id' => $spot->id, 'start_time' => $start->copy()->addMinutes(30), 'end_time' => $start->copy()->addHours(2), 'purpose' => 'Conflict']))->toThrow(ValidationException::class);
    expect(fn () => $service->create($member, ['member_id' => $member->id, 'zone_id' => $zone->id, 'spot_id' => $spot->id, 'start_time' => $start->copy()->addHours(2), 'end_time' => $start->copy()->addHours(3), 'purpose' => 'Second']))->toThrow(ValidationException::class);
});

test('durable checkout and consumable usage boundaries update inventory atomically', function () {
    $actor = Member::factory()->coordinator()->create();
    $recipient = Member::factory()->create();
    $durable = Equipment::factory()->create(['type' => EquipmentType::DURABLE->value, 'status' => EquipmentStatus::AVAILABLE->value]);
    $consumable = Equipment::factory()->create(['type' => EquipmentType::CONSUMABLE->value, 'quantity_total' => 10, 'quantity_available' => 10]);

    $checkout = app(EquipmentCheckoutService::class)->checkout($actor, $durable, $recipient, ['checkout_type' => 'in_lab', 'expected_return_at' => now()->addDay()]);
    expect($durable->refresh()->status)->toBe(EquipmentStatus::IN_USE)->and($durable->current_custodian_id)->toBe($recipient->id);
    app(EquipmentCheckoutService::class)->checkIn($recipient, $checkout);
    expect($durable->refresh()->status)->toBe(EquipmentStatus::AVAILABLE)->and($durable->current_custodian_id)->toBeNull();

    $usage = app(ConsumableUsageService::class)->record($actor, $consumable, $recipient, 3);
    expect($usage->quantity_remaining_after)->toBe('7.00')->and($consumable->refresh()->quantity_available)->toBe(7);
    expect(fn () => app(ConsumableUsageService::class)->record($actor, $consumable, $recipient, 8))->toThrow(ValidationException::class);
});

test('schema and scheduler foundations match the Sprint 1 operational requirements', function () {
    expect(Schema::hasTable('project_equipment_needs'))->toBeTrue()
        ->and(Schema::hasColumns('project_equipment_needs', ['project_id', 'equipment_id', 'quantity_needed', 'notes']))->toBeTrue()
        ->and(Config::get('queue.connections.database.after_commit'))->toBeTrue();

    Artisan::call('schedule:list');
    $schedule = Artisan::output();
    expect($schedule)->toContain('labops:no-show-detection')
        ->toContain('labops:reservation-reminders')
        ->toContain('labops:overdue-checks')
        ->toContain('labops:low-stock-reconciliation')
        ->toContain('labops:coordinator-summary');
});
