<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nis')->nullable()->unique()->after('name');
            $table->string('nip')->nullable()->unique()->after('nis');
            $table->enum('gender', ['male', 'female'])->nullable()->after('nip');
            $table->string('phone')->nullable()->after('gender');
            $table->text('address')->nullable()->after('phone');
            $table->string('photo')->nullable()->after('address');
            $table->boolean('is_active')->default(true)->after('photo');
            $table->foreignId('class_id')->nullable()->after('role')->constrained('classes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
            $table->dropColumn(['nis', 'nip', 'gender', 'phone', 'address', 'photo', 'is_active', 'class_id']);
        });
    }
};
