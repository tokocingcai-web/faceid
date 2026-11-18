<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $year = AcademicYear::query()->updateOrCreate(
            ['name' => '2026/2027'],
            [
                'start_date' => '2026-07-01',
                'end_date' => '2027-06-30',
                'is_active' => true,
            ]
        );

        AcademicYear::query()->whereKeyNot($year->id)->update(['is_active' => false]);

        $dev = $this->user('Developer', 'dev@example.com', 'dev', ['nip' => 'DEV001']);
        $admin = $this->user('Admin Sekolah', 'admin@example.com', 'admin', ['nip' => 'ADM001']);
        $guru = $this->user('Guru Contoh', 'guru@example.com', 'guru', ['nip' => 'GR001', 'gender' => 'female']);

        $class = SchoolClass::query()->updateOrCreate(
            ['name' => 'X IPA 1', 'academic_year_id' => $year->id],
            ['level' => 'X', 'major' => 'IPA', 'homeroom_teacher_id' => $guru->id]
        );

        $students = collect([
            $this->user('Siswa Contoh', 'siswa@example.com', 'student', [
                'nis' => '1001',
                'gender' => 'male',
                'class_id' => $class->id,
            ]),
            $this->user('Siti Aminah', 'siti@example.com', 'student', [
                'nis' => '1002',
                'gender' => 'female',
                'class_id' => $class->id,
            ]),
            $this->user('Budi Santoso', 'budi@example.com', 'student', [
                'nis' => '1003',
                'gender' => 'male',
                'class_id' => $class->id,
            ]),
        ]);

        $wali = $this->user('Wali Contoh', 'wali@example.com', 'wali', [
            'phone' => '081234567890',
            'gender' => 'male',
        ]);

        $wali->children()->syncWithoutDetaching([
            $students[0]->id => ['relationship' => 'Ayah', 'is_primary' => true],
        ]);

        $matematika = Subject::query()->updateOrCreate(['code' => 'MTK'], ['name' => 'Matematika']);
        $bahasa = Subject::query()->updateOrCreate(['code' => 'BIN'], ['name' => 'Bahasa Indonesia']);
        $ipa = Subject::query()->updateOrCreate(['code' => 'IPA'], ['name' => 'Ilmu Pengetahuan Alam']);

        foreach ([1, 2, 3, 4, 5] as $day) {
            Schedule::query()->updateOrCreate(
                [
                    'class_id' => $class->id,
                    'teacher_id' => $guru->id,
                    'day_of_week' => $day,
                    'start_time' => '07:00',
                ],
                [
                    'academic_year_id' => $year->id,
                    'subject_id' => $day % 2 === 0 ? $bahasa->id : $matematika->id,
                    'end_time' => '08:30',
                    'room' => 'R-'.$class->id,
                ]
            );

            Schedule::query()->updateOrCreate(
                [
                    'class_id' => $class->id,
                    'teacher_id' => $guru->id,
                    'day_of_week' => $day,
                    'start_time' => '08:45',
                ],
                [
                    'academic_year_id' => $year->id,
                    'subject_id' => $ipa->id,
                    'end_time' => '10:15',
                    'room' => 'Lab IPA',
                ]
            );
        }

        $this->command?->info('Akun demo: dev/admin/guru/siswa/wali@example.com dengan password "password".');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function user(string $name, string $email, string $role, array $attributes = []): User
    {
        $user = User::query()->updateOrCreate(
            ['email' => $email],
            array_merge([
                'name' => $name,
                'role' => $role,
                'password' => 'password',
                'is_active' => true,
                'email_verified_at' => now(),
            ], $attributes)
        );

        return $user;
    }
}
