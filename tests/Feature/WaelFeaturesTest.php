<?php

namespace Tests\Feature;

use App\Models\Cohort;
use App\Models\Engagement;
use App\Models\LabGroup;
use App\Models\Session;
use App\Models\Track;
use App\Models\User;
use App\Models\StudentTag;
use App\Models\Announcement;
use App\Models\BillingRecord;
use App\Models\AttendanceLedger;
use App\Models\AttendanceRecord;
use App\Models\Course;
use App\Models\CourseGrade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WaelFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected User $branchManager;
    protected User $trackAdmin;
    protected User $instructor;
    protected User $otherInstructor;
    protected User $student;
    protected User $otherStudent;
    protected Track $track;
    protected Cohort $cohort;
    protected LabGroup $labGroup;
    protected Course $course;
    protected Engagement $engagement;
    protected Session $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branchManager = User::factory()->create(['role' => 'branch_manager']);
        $this->trackAdmin = User::factory()->create(['role' => 'track_admin']);
        $this->instructor = User::factory()->create([
            'role' => 'instructor',
            'compensation_type' => 'external',
            'hourly_rate' => 150,
        ]);
        $this->otherInstructor = User::factory()->create(['role' => 'instructor']);
        $this->student = User::factory()->create(['role' => 'student']);
        $this->otherStudent = User::factory()->create(['role' => 'student']);

        $this->track = Track::create([
            'id' => Str::uuid(),
            'name' => 'Web Development',
            'code' => 'WD',
            'description' => 'Web Dev Track',
        ]);

        $this->cohort = Cohort::create([
            'track_id' => $this->track->id,
            'name' => 'Intake 45',
            'status' => 'active',
            'starts_at' => now()->subMonths(1)->toDateString(),
            'ends_at' => now()->addMonths(9)->toDateString(),
        ]);

        $this->cohort->trackAdmins()->attach($this->trackAdmin->id);

        $this->labGroup = LabGroup::create([
            'cohort_id' => $this->cohort->id,
            'name' => 'Lab Group A',
        ]);

        $this->labGroup->students()->attach($this->student->id);

        $this->course = Course::create([
            'cohort_id' => $this->cohort->id,
            'name' => 'PHP/Laravel',
            'credit_hours' => 3,
            'grade_weight' => 100,
        ]);

        $this->engagement = Engagement::create([
            'cohort_id' => $this->cohort->id,
            'instructor_id' => $this->instructor->id,
            'lab_group_id' => $this->labGroup->id,
            'type' => 'lab',
            'starts_at' => now()->subWeeks(2)->toDateString(),
            'ends_at' => now()->addWeeks(2)->toDateString(),
            'hours_per_session' => 4.0,
        ]);

        $this->session = Session::create([
            'engagement_id' => $this->engagement->id,
            'session_date' => now()->toDateString(),
            'is_delivered' => false,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // 1. STUDENT TAGS
    // ─────────────────────────────────────────────────────────

    public function test_instructor_can_tag_student_in_their_lab_group(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson("/api/students/{$this->student->id}/tags", [
                'cohort_id' => $this->cohort->id,
                'tag_type' => 'predefined',
                'tag_value' => 'uses AI',
                'note' => 'Used ChatGPT for grading labs.',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.tag_value', 'uses AI');

        $this->assertDatabaseHas('student_tags', [
            'student_id' => $this->student->id,
            'tagged_by' => $this->instructor->id,
            'tag_value' => 'uses AI',
        ]);
    }

    public function test_instructor_cannot_tag_student_outside_their_lab_group(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson("/api/students/{$this->otherStudent->id}/tags", [
                'cohort_id' => $this->cohort->id,
                'tag_type' => 'predefined',
                'tag_value' => 'uses AI',
            ]);

        $response->assertStatus(403);
    }

    public function test_cannot_tag_with_invalid_predefined_value(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson("/api/students/{$this->student->id}/tags", [
                'cohort_id' => $this->cohort->id,
                'tag_type' => 'predefined',
                'tag_value' => 'invalid tag here',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tag_value']);
    }

    public function test_student_cannot_tag_anyone(): void
    {
        $response = $this->actingAs($this->student)
            ->postJson("/api/students/{$this->student->id}/tags", [
                'cohort_id' => $this->cohort->id,
                'tag_type' => 'free_text',
                'tag_value' => 'some tag',
            ]);

        $response->assertStatus(403);
    }

    public function test_tag_deletion_authorization(): void
    {
        $tag = StudentTag::create([
            'student_id' => $this->student->id,
            'tagged_by' => $this->instructor->id,
            'cohort_id' => $this->cohort->id,
            'tag_type' => 'free_text',
            'tag_value' => 'lazy',
        ]);

        // Other instructor cannot delete
        $this->actingAs($this->otherInstructor)
            ->deleteJson("/api/students/{$this->student->id}/tags/{$tag->id}")
            ->assertStatus(403);

        // Track Admin can delete
        $this->actingAs($this->trackAdmin)
            ->deleteJson("/api/students/{$this->student->id}/tags/{$tag->id}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('student_tags', ['id' => $tag->id]);
    }

    // ─────────────────────────────────────────────────────────
    // 2. ANNOUNCEMENTS
    // ─────────────────────────────────────────────────────────

    public function test_track_admin_can_post_announcements(): void
    {
        $response = $this->actingAs($this->trackAdmin)
            ->postJson("/api/cohorts/{$this->cohort->id}/announcements", [
                'title' => 'Important Notice',
                'body' => 'Classes will start tomorrow.',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Important Notice');
    }

    public function test_instructor_with_active_engagement_can_post(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson("/api/cohorts/{$this->cohort->id}/announcements", [
                'title' => 'Lab Deadline',
                'body' => 'Submit labs tonight.',
            ]);

        $response->assertStatus(201);
    }

    public function test_instructor_without_active_engagement_cannot_post(): void
    {
        $response = $this->actingAs($this->otherInstructor)
            ->postJson("/api/cohorts/{$this->cohort->id}/announcements", [
                'title' => 'Hacked Announcement',
                'body' => 'Failed post.',
            ]);

        $response->assertStatus(403);
    }

    public function test_announcement_ownership_on_delete(): void
    {
        $announcement = Announcement::create([
            'cohort_id' => $this->cohort->id,
            'author_id' => $this->instructor->id,
            'title' => 'Title',
            'body' => 'Body',
        ]);

        // Other instructor cannot delete
        $this->actingAs($this->otherInstructor)
            ->deleteJson("/api/announcements/{$announcement->id}")
            ->assertStatus(403);

        // Author can delete
        $this->actingAs($this->instructor)
            ->deleteJson("/api/announcements/{$announcement->id}")
            ->assertStatus(200);
    }

    // ─────────────────────────────────────────────────────────
    // 3. BILLING
    // ─────────────────────────────────────────────────────────

    public function test_session_delivery_creates_billing_record(): void
    {
        // Deliver session via PATCH (Menna's API)
        $response = $this->actingAs($this->trackAdmin)
            ->patchJson("/api/sessions/{$this->session->id}/deliver");

        $response->assertStatus(200);

        // Verify billing record was created
        $this->assertDatabaseHas('billing_records', [
            'user_id' => $this->instructor->id,
            'session_id' => $this->session->id,
            'hours' => 4.0,
            'person_type' => 'external',
        ]);
    }

    public function test_branch_manager_can_view_billing_rollups(): void
    {
        // Seed a delivered session billing record
        BillingRecord::create([
            'user_id' => $this->instructor->id,
            'session_id' => $this->session->id,
            'hours' => 4.0,
            'person_type' => 'external',
        ]);

        $response = $this->actingAs($this->branchManager)
            ->getJson('/api/billing');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'person_type' => 'external',
                'total_hours' => 4.0,
                'total_due' => 600, // 4.0 hours * $150/hr
            ]);
    }

    // ─────────────────────────────────────────────────────────
    // 4. ANALYTICS
    // ─────────────────────────────────────────────────────────

    public function test_student_can_only_view_own_analytics(): void
    {
        AttendanceLedger::create([
            'student_id' => $this->student->id,
            'cohort_id' => $this->cohort->id,
            'balance' => 240,
        ]);

        CourseGrade::create([
            'student_id' => $this->student->id,
            'course_id' => $this->course->id,
            'computed_score' => 85.0,
        ]);

        $response = $this->actingAs($this->student)
            ->getJson('/api/analytics/student');

        $response->assertStatus(200)
            ->assertJsonPath('attendance_balance', 240)
            ->assertJsonPath('course_grades.0.computed_score', 85);
    }

    public function test_analytics_dashboard_authorizations(): void
    {
        $this->actingAs($this->instructor)
            ->getJson('/api/analytics/student')
            ->assertStatus(403); // Instructor can't see student view

        $this->actingAs($this->branchManager)
            ->getJson('/api/analytics/at-risk')
            ->assertStatus(200); // Manager can see at-risk list
    }

    // ─────────────────────────────────────────────────────────
    // 5. QR CODE ATTENDANCE
    // ─────────────────────────────────────────────────────────

    public function test_qr_attendance_lifecycle(): void
    {
        // 1. Generate payload
        $response = $this->actingAs($this->instructor)
            ->getJson("/api/qr/session/{$this->session->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['qr_payload']);

        $payload = $response->json('qr_payload');

        // 2. Student scans (Check-in)
        $scanResponse = $this->actingAs($this->student)
            ->postJson('/api/qr/scan', [
                'session_id' => $this->session->id,
                'payload' => $payload,
            ]);

        $scanResponse->assertStatus(200)
            ->assertJsonPath('status', 'checked_in');

        // 3. Student scans again (Check-out)
        $scanResponse2 = $this->actingAs($this->student)
            ->postJson('/api/qr/scan', [
                'session_id' => $this->session->id,
                'payload' => $payload,
            ]);

        $scanResponse2->assertStatus(200)
            ->assertJsonPath('status', 'checked_out');

        // 4. Student scans third time (Conflict)
        $scanResponse3 = $this->actingAs($this->student)
            ->postJson('/api/qr/scan', [
                'session_id' => $this->session->id,
                'payload' => $payload,
            ]);

        $scanResponse3->assertStatus(409);

        // 5. Scan with invalid signature
        $scanResponseInvalid = $this->actingAs($this->student)
            ->postJson('/api/qr/scan', [
                'session_id' => $this->session->id,
                'payload' => $payload . 'tampered',
            ]);

        $scanResponseInvalid->assertStatus(422);
    }
}
