<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AttendanceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manually_create_and_correct_attendance(): void
    {
        [$admin, , $student] = $this->data();

        $this->actingAs($admin)
            ->put(route('attendance-manage.update', $student), [
                'date' => '2026-11-16',
                'status' => 'hadir',
                'time' => '07:30',
                'check_out_time' => '14:00',
                'notes' => 'Koreksi manual',
            ])
            ->assertRedirect();

        $attendance = Attendance::query()->where('user_id', $student->id)->firstOrFail();

        $this->assertSame('hadir', $attendance->status);
        $this->assertSame(30, $attendance->late_minutes);
        $this->assertSame(60, $attendance->early_leave_minutes);
        $this->assertSame('Koreksi manual', $attendance->notes);
        $this->assertSame('2026-11-16', $attendance->date->toDateString());
    }

    public function test_admin_can_reset_attendance_record(): void
    {
        [$admin, , $student] = $this->data();

        Attendance::query()->create([
            'user_id' => $student->id,
            'class_id' => $student->class_id,
            'date' => '2026-11-16',
            'time' => '07:10:00',
            'status' => 'hadir',
        ]);

        $this->actingAs($admin)
            ->delete(route('attendance-manage.destroy', [
                'student' => $student,
                'date' => '2026-11-16',
            ]))
            ->assertRedirect();

        $this->assertDatabaseMissing('attendances', [
            'user_id' => $student->id,
        ]);
    }

    public function test_guru_can_only_manage_students_in_their_classes(): void
    {
        [$admin, $guru, $student, $otherStudent] = $this->data();

        $this->actingAs($guru)
            ->get(route('attendance-manage.index', ['date' => '2026-11-16']))
            ->assertOk()
            ->assertSee($student->name)
            ->assertDontSee($otherStudent->name);

        $this->actingAs($guru)
            ->put(route('attendance-manage.update', $otherStudent), [
                'date' => '2026-11-16',
                'status' => 'alfa',
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('attendance-manage.index', ['date' => '2026-11-16']))
            ->assertOk()
            ->assertSee($otherStudent->name);
    }

    public function test_leave_decision_sends_guardian_notification(): void
    {
        Mail::fake();
        Http::fake();
        config([
            'attendance.whatsapp.endpoint' => 'https://wa.test/send',
            'attendance.whatsapp.token' => 'secret',
        ]);

        [$admin, , $student] = $this->data();
        $wali = User::factory()->create([
            'role' => 'wali',
            'phone' => '081234567890',
        ]);
        $wali->children()->attach($student->id, ['relationship' => 'Ayah', 'is_primary' => true]);

        $leave = $student->leaveRequests()->create([
            'requested_by' => $wali->id,
            'type' => 'izin',
            'start_date' => '2026-11-16',
            'end_date' => '2026-11-16',
            'reason' => 'Acara keluarga',
        ]);

        $this->actingAs($admin)
            ->post(route('leave-requests.approve', $leave))
            ->assertRedirect();

        Http::assertSent(fn ($request) => $request->url() === 'https://wa.test/send'
            && $request['target'] === '081234567890'
            && str_contains($request['message'], 'Disetujui'));
    }

    /**
     * @return array{0: User, 1: User, 2: User, 3: User}
     */
    private function data(): array
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
        $otherClass = SchoolClass::query()->create([
            'name' => 'X IPA 2',
            'academic_year_id' => $year->id,
        ]);

        $student = User::factory()->create(['role' => 'student', 'class_id' => $class->id]);
        $otherStudent = User::factory()->create(['role' => 'student', 'class_id' => $otherClass->id]);

        return [$admin, $guru, $student, $otherStudent];
    }
}
