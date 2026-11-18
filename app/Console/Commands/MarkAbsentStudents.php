<?php

namespace App\Console\Commands;

use App\Services\AttendanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class MarkAbsentStudents extends Command
{
    protected $signature = 'attendance:mark-absent {date?}';

    protected $description = 'Mark active students without attendance as absent and notify their guardians';

    public function handle(AttendanceService $attendanceService): int
    {
        $date = $this->argument('date')
            ? Carbon::parse($this->argument('date'), config('face.timezone'))
            : now()->timezone(config('face.timezone'));

        $count = $attendanceService->markAbsent($date);

        $this->info("{$count} siswa ditandai alfa untuk tanggal {$date->format('Y-m-d')}.");

        return self::SUCCESS;
    }
}
