<?php

namespace Tests\Feature;

use App\Models\Cohort;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CohortControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $branchManager;
    protected User $trackAdmin;
    protected User $otherTrackAdmin;
    protected User $student;
    protected Track $track;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branchManager = User::factory()->create(['role' => 'branch_manager']);
        $this->trackAdmin = User::factory()->create(['role' => 'track_admin']);
        $this->otherTrackAdmin = User::factory()->create(['role' => 'track_admin']);
        $this->student = User::factory()->create(['role' => 'student']);
        
        $this->track = Track::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'name' => 'Web Development',
            'description' => 'Full-stack web track',
        ]);
    }

    public function test_branch_manager_can_list_all_cohorts(): void
    {
        $cohort1 = Cohort::create([
            'track_id' => $this->track->id,
            'name' => 'Cohort 1',
            'status' => 'active',
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonths(3)->toDateString(),
        ]);

        $cohort2 = Cohort::create([
            'track_id' => $this->track->id,
            'name' => 'Cohort 2',
            'status' => 'open',
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonths(3)->toDateString(),
        ]);

        $response = $this->actingAs($this->branchManager)
            ->getJson('/api/cohorts');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['name' => 'Cohort 1'])
            ->assertJsonFragment(['name' => 'Cohort 2']);
    }

    public function test_track_admin_can_only_list_their_own_cohorts(): void
    {
        $cohort1 = Cohort::create([
            'track_id' => $this->track->id,
            'name' => 'My Cohort',
            'status' => 'active',
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonths(3)->toDateString(),
        ]);
        $cohort1->trackAdmins()->attach($this->trackAdmin->id);

        $cohort2 = Cohort::create([
            'track_id' => $this->track->id,
            'name' => 'Other Cohort',
            'status' => 'open',
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonths(3)->toDateString(),
        ]);
        $cohort2->trackAdmins()->attach($this->otherTrackAdmin->id);

        $response = $this->actingAs($this->trackAdmin)
            ->getJson('/api/cohorts');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['name' => 'My Cohort'])
            ->assertJsonMissing(['name' => 'Other Cohort']);
    }

    public function test_track_admin_can_view_own_cohort(): void
    {
        $cohort = Cohort::create([
            'track_id' => $this->track->id,
            'name' => 'My Cohort',
            'status' => 'active',
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonths(3)->toDateString(),
        ]);
        $cohort->trackAdmins()->attach($this->trackAdmin->id);

        $response = $this->actingAs($this->trackAdmin)
            ->getJson("/api/cohorts/{$cohort->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'My Cohort')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'track_id',
                    'name',
                    'status',
                    'starts_at',
                    'ends_at',
                    'track',
                    'track_admins',
                ]
            ]);
    }

    public function test_track_admin_cannot_view_other_cohort(): void
    {
        $cohort = Cohort::create([
            'track_id' => $this->track->id,
            'name' => 'Other Cohort',
            'status' => 'active',
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonths(3)->toDateString(),
        ]);
        $cohort->trackAdmins()->attach($this->otherTrackAdmin->id);

        $response = $this->actingAs($this->trackAdmin)
            ->getJson("/api/cohorts/{$cohort->id}");

        $response->assertStatus(403);
    }

    public function test_branch_manager_can_create_cohort_with_valid_track_admins(): void
    {
        $response = $this->actingAs($this->branchManager)
            ->postJson('/api/cohorts', [
                'track_id' => $this->track->id,
                'name' => 'New Cohort',
                'starts_at' => now()->toDateString(),
                'ends_at' => now()->addMonths(3)->toDateString(),
                'track_admin_ids' => [$this->trackAdmin->id],
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('name', 'New Cohort');

        $cohort = Cohort::firstWhere('name', 'New Cohort');
        $this->assertNotNull($cohort);
        $this->assertTrue($cohort->trackAdmins->contains($this->trackAdmin));
    }

    public function test_create_cohort_fails_with_invalid_track_admin_role(): void
    {
        $response = $this->actingAs($this->branchManager)
            ->postJson('/api/cohorts', [
                'track_id' => $this->track->id,
                'name' => 'New Cohort',
                'starts_at' => now()->toDateString(),
                'ends_at' => now()->addMonths(3)->toDateString(),
                'track_admin_ids' => [$this->student->id],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['track_admin_ids.0']);
    }

    public function test_create_cohort_fails_if_track_already_has_active_cohort(): void
    {
        Cohort::create([
            'track_id' => $this->track->id,
            'name' => 'Active Cohort',
            'status' => 'active',
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonths(3)->toDateString(),
        ]);

        $response = $this->actingAs($this->branchManager)
            ->postJson('/api/cohorts', [
                'track_id' => $this->track->id,
                'name' => 'Another Active Cohort',
                'status' => 'active',
                'starts_at' => now()->toDateString(),
                'ends_at' => now()->addMonths(3)->toDateString(),
            ]);

        $response->assertStatus(409)
            ->assertJsonFragment(['message' => 'This track already has an active cohort.']);
    }

    public function test_branch_manager_can_update_cohort(): void
    {
        $cohort = Cohort::create([
            'track_id' => $this->track->id,
            'name' => 'Old Cohort',
            'status' => 'open',
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonths(3)->toDateString(),
        ]);
        $cohort->trackAdmins()->attach($this->trackAdmin->id);

        $response = $this->actingAs($this->branchManager)
            ->patchJson("/api/cohorts/{$cohort->id}", [
                'name' => 'Updated Cohort',
                'track_admin_ids' => [$this->otherTrackAdmin->id],
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('name', 'Updated Cohort');

        $cohort->refresh();
        $this->assertTrue($cohort->trackAdmins->contains($this->otherTrackAdmin));
        $this->assertFalse($cohort->trackAdmins->contains($this->trackAdmin));
    }

    public function test_update_cohort_fails_if_status_updated_to_active_and_track_has_another_active(): void
    {
        Cohort::create([
            'track_id' => $this->track->id,
            'name' => 'Already Active',
            'status' => 'active',
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonths(3)->toDateString(),
        ]);

        $cohort = Cohort::create([
            'track_id' => $this->track->id,
            'name' => 'Open Cohort',
            'status' => 'open',
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonths(3)->toDateString(),
        ]);

        $response = $this->actingAs($this->branchManager)
            ->patchJson("/api/cohorts/{$cohort->id}", [
                'status' => 'active',
            ]);

        $response->assertStatus(409)
            ->assertJsonFragment(['message' => 'This track already has an active cohort.']);
    }

    public function test_student_cannot_create_or_update_cohort(): void
    {
        $responseCreate = $this->actingAs($this->student)
            ->postJson('/api/cohorts', [
                'track_id' => $this->track->id,
                'name' => 'Student Cohort',
                'starts_at' => now()->toDateString(),
                'ends_at' => now()->addMonths(3)->toDateString(),
            ]);
        $responseCreate->assertStatus(403);

        $cohort = Cohort::create([
            'track_id' => $this->track->id,
            'name' => 'Some Cohort',
            'status' => 'open',
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonths(3)->toDateString(),
        ]);

        $responseUpdate = $this->actingAs($this->student)
            ->patchJson("/api/cohorts/{$cohort->id}", [
                'name' => 'Hacked Cohort',
            ]);
        $responseUpdate->assertStatus(403);
    }
}
