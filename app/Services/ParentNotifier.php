<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ParentNotifier
{
    public function __construct(private WhatsAppService $whatsApp) {}

    public function notifyLate(Attendance $attendance): void
    {
        $student = $attendance->user;
        $message = "Pemberitahuan: {$student->name} tercatat terlambat pada "
            .$attendance->date->format('d/m/Y').' pukul '.substr($attendance->time, 0, 5)
            ." ({$attendance->late_minutes} menit).";

        $this->notifyGuardians($student, 'Pemberitahuan Keterlambatan', $message);
    }

    public function notifyAbsent(Attendance $attendance): void
    {
        $student = $attendance->user;
        $message = "Pemberitahuan: {$student->name} tidak tercatat hadir pada "
            .$attendance->date->format('d/m/Y').'. Mohon konfirmasi ke pihak sekolah.';

        $this->notifyGuardians($student, 'Pemberitahuan Ketidakhadiran', $message);
    }

    public function notifyLeaveDecision(LeaveRequest $leaveRequest): void
    {
        $student = $leaveRequest->student;
        $message = "Pengajuan {$leaveRequest->typeLabel()} untuk {$student->name} "
            ."({$leaveRequest->start_date->format('d/m/Y')} - {$leaveRequest->end_date->format('d/m/Y')}) "
            ."telah {$leaveRequest->statusLabel()}.";

        if ($leaveRequest->review_note) {
            $message .= " Catatan: {$leaveRequest->review_note}";
        }

        $this->notifyGuardians($student, 'Keputusan Pengajuan Izin/Sakit', $message);
    }

    private function notifyGuardians(User $student, string $subject, string $message): void
    {
        $guardians = $student->guardians()->get();

        foreach ($guardians as $guardian) {
            if ($guardian->email) {
                try {
                    Mail::raw($message, function ($mail) use ($guardian, $subject) {
                        $mail->to($guardian->email)->subject($subject);
                    });
                } catch (\Throwable $exception) {
                    Log::error('Failed to send guardian email notification.', [
                        'guardian_id' => $guardian->id,
                        'error' => $exception->getMessage(),
                    ]);
                }
            }

            if ($guardian->phone) {
                $this->whatsApp->send($guardian->phone, $message);
            }
        }
    }
}
