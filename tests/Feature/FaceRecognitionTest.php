<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaceRecognitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_attendance_scan_page_is_accessible_without_login(): void
    {
        $this->get('/absen')
            ->assertOk()
            ->assertSee('Absensi Mandiri');
    }

    public function test_face_recognition_records_attendance_without_login(): void
    {
        $descriptor = array_fill(0, 128, 0.1);
        $user = $this->userWithFace('student', $descriptor);

        $this->postJson('/absen/recognize', [
            'mode' => 'attendance',
            'descriptors' => [$descriptor],
        ])
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('user.name', $user->name)
            ->assertJsonPath('already_absent', false);

        $this->assertGuest();
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => 'hadir',
        ]);
    }

    public function test_face_login_authenticates_user_and_records_attendance(): void
    {
        $descriptor = array_fill(0, 128, 0.2);
        $user = $this->userWithFace('guru', $descriptor);

        $this->postJson('/absen/recognize', [
            'mode' => 'login',
            'descriptors' => [$descriptor],
        ])
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('redirect', route('guru.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => 'hadir',
        ]);
    }

    public function test_unrecognized_face_does_not_record_attendance(): void
    {
        $registered = array_fill(0, 128, 0.1);
        $unknown = array_fill(0, 128, 0.9);
        $user = $this->userWithFace('student', $registered);

        $this->postJson('/absen/recognize', [
            'mode' => 'attendance',
            'descriptors' => [$unknown],
        ])
            ->assertOk()
            ->assertJsonPath('status', 'error');

        $this->assertDatabaseMissing('attendances', [
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_register_face_descriptors(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $descriptor = array_fill(0, 128, 0.3);

        $this->actingAs($user)
            ->postJson('/profile/face', [
                'descriptors' => [$descriptor, $descriptor],
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $user->refresh();

        $this->assertTrue($user->hasRegisteredFace());
        $this->assertNotNull($user->face_registered_at);
        $this->assertCount(2, $user->face_descriptor);
    }

    private function userWithFace(string $role, array $descriptor): User
    {
        $user = User::factory()->create(['role' => $role]);

        $user->forceFill([
            'face_descriptor' => [$descriptor],
            'face_registered_at' => now(),
        ])->save();

        return $user;
    }
}
