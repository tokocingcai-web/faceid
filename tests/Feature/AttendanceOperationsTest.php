<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AttendanceOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_check_in_records_on_time_attendance(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        Carbon::setTestNow(Carbon::parse('2026-11-16 07:10:00', config('face.timezone')));

        $result = app(AttendanceService::class)->checkIn($student);

        $this->assertSame('success', $result['status']);
        $this->assertSame(0, $result['attendance']->late_minutes);
        $this->assertDatabaseHas('attendances', [
            'user_id' => $student->id,
            'status' => 'hadir',
            'late_minutes' => 0,
        ]);
    }

    public function test_check_in_calculates_late_minutes(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        Carbon::setTestNow(Carbon::parse('2026-11-16 07:30:00', config('face.timezone')));

        $result = app(AttendanceService::class)->checkIn($student);

        $this->assertSame('success', $result['status']);
        $this->assertSame(30, $result['attendance']->late_minutes);
        $this->assertSame('Terlambat', $result['attendance']->displayStatus());
    }

    public function test_check_out_records_time_and_early_leave_minutes(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        Carbon::setTestNow(Carbon::parse('2026-11-16 07:00:00', config('face.timezone')));
        app(AttendanceService::class)->checkIn($student);

        Carbon::setTestNow(Carbon::parse('2026-11-16 14:00:00', config('face.timezone')));
        $result = app(AttendanceService::class)->checkOut($student);

        $this->assertSame('success', $result['status']);
        $this->assertNotNull($result['attendance']->check_out_time);
        $this->assertSame(60, $result['attendance']->early_leave_minutes);
    }

    public function test_approved_leave_creates_or_updates_attendance(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $leave = $student->leaveRequests()->create([
            'requested_by' => $student->id,
            'type' => 'sakit',
            'start_date' => '2026-11-16',
            'end_date' => '2026-11-17',
            'reason' => 'Demam',
        ]);

        $this->actingAs($admin)
            ->post(route('leave-requests.approve', $leave))
            ->assertRedirect();

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => 'approved',
        ]);
        $this->assertDatabaseHas('attendances', [
            'user_id' => $student->id,
            'status' => 'sakit',
            'date' => '2026-11-16 00:00:00',
        ]);
        $this->assertDatabaseHas('attendances', [
            'user_id' => $student->id,
            'status' => 'sakit',
            'date' => '2026-11-17 00:00:00',
        ]);
    }

    public function test_mark_absent_command_creates_alfa_for_students_without_attendance(): void
    {
        $year = AcademicYear::query()->create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);
        $class = SchoolClass::query()->create([
            'name' => 'X IPA 1',
            'academic_year_id' => $year->id,
        ]);
        $student = User::factory()->create(['role' => 'student', 'class_id' => $class->id]);

        $this->artisan('attendance:mark-absent', ['date' => '2026-11-16'])->assertSuccessful();

        $this->assertDatabaseHas('attendances', [
            'user_id' => $student->id,
            'status' => 'alfa',
            'date' => '2026-11-16 00:00:00',
        ]);
    }

    public function test_admin_can_view_daily_report_and_export_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('reports.daily', ['date' => '2026-11-16']))
            ->assertOk()
            ->assertSee('Laporan Harian');

        $response = $this->actingAs($admin)
            ->get(route('reports.daily.export', ['date' => '2026-11-16']));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
    }

    public function test_admin_can_import_users_from_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $year = AcademicYear::query()->create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);
        SchoolClass::query()->create([
            'name' => 'X IPA 1',
            'academic_year_id' => $year->id,
        ]);

        $csv = "name,email,role,nis,nip,class,gender,phone,password\n"
            ."Siswa Import,import@example.com,student,2001,,X IPA 1,male,08123,password\n";

        $file = UploadedFile::fake()->createWithContent('users.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.users.import.store'), ['file' => $file])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'import@example.com',
            'role' => 'student',
            'nis' => '2001',
        ]);
    }

    public function test_student_cannot_access_admin_area(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }
}
