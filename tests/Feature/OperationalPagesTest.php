<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_operational_pages_render_for_each_role(): void
    {
        $year = AcademicYear::query()->create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $guru = User::factory()->create(['role' => 'guru']);
        $class = SchoolClass::query()->create([
            'name' => 'X IPA 1',
            'academic_year_id' => $year->id,
            'homeroom_teacher_id' => $guru->id,
        ]);
        $subject = Subject::query()->create(['code' => 'MTK', 'name' => 'Matematika']);
        $schedule = Schedule::query()->create([
            'academic_year_id' => $year->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $guru->id,
            'day_of_week' => 1,
            'start_time' => '07:00',
            'end_time' => '08:30',
        ]);

        $student = User::factory()->create(['role' => 'student', 'class_id' => $class->id]);
        $wali = User::factory()->create(['role' => 'wali']);
        $wali->children()->attach($student->id, ['relationship' => 'Ayah', 'is_primary' => true]);
        $leave = $student->leaveRequests()->create([
            'requested_by' => $wali->id,
            'type' => 'izin',
            'start_date' => '2026-11-16',
            'end_date' => '2026-11-16',
            'reason' => 'Acara keluarga',
        ]);

        $adminRoutes = [
            route('admin.dashboard'),
            route('admin.users.index'),
            route('admin.users.create'),
            route('admin.users.import'),
            route('admin.academic-years.index'),
            route('admin.academic-years.create'),
            route('admin.academic-years.edit', $year),
            route('admin.subjects.index'),
            route('admin.subjects.create'),
            route('admin.subjects.edit', $subject),
            route('admin.classes.index'),
            route('admin.classes.create'),
            route('admin.classes.edit', $class),
            route('admin.schedules.index'),
            route('admin.schedules.create'),
            route('admin.schedules.edit', $schedule),
            route('admin.parent-links.index'),
            route('leave-requests.index'),
            route('reports.daily'),
            route('reports.monthly'),
        ];

        foreach ($adminRoutes as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }

        $this->actingAs($guru)->get(route('guru.dashboard'))->assertOk();
        $this->actingAs($student)->get(route('student.dashboard'))->assertOk();
        $this->actingAs($student)->get(route('leave-requests.index'))->assertOk();
        $this->actingAs($student)->get(route('leave-requests.create'))->assertOk();
        $this->actingAs($wali)->get(route('wali.dashboard'))->assertOk();
        $this->actingAs($wali)->get(route('leave-requests.create'))->assertOk();
        $this->actingAs($admin)->get(route('leave-requests.index'))->assertOk();
        $this->actingAs($admin)->get(route('leave-requests.index'))->assertSee($leave->reason);
    }
}
