<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('schedule_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->foreignId('class_id')->nullable()->after('schedule_id')->constrained('classes')->nullOnDelete();
            $table->foreignId('leave_request_id')->nullable()->after('class_id')->constrained()->nullOnDelete();
            $table->time('check_out_time')->nullable()->after('time');
            $table->unsignedInteger('late_minutes')->default(0)->after('check_out_time');
            $table->unsignedInteger('early_leave_minutes')->default(0)->after('late_minutes');
            $table->text('notes')->nullable()->after('early_leave_minutes');

            $table->index(['date', 'class_id']);
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['schedule_id']);
            $table->dropForeign(['class_id']);
            $table->dropForeign(['leave_request_id']);
            $table->dropColumn([
                'schedule_id',
                'class_id',
                'leave_request_id',
                'check_out_time',
                'late_minutes',
                'early_leave_minutes',
                'notes',
            ]);
        });
    }
};
