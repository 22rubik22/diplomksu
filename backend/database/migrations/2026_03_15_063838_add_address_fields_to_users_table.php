<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('name');
            $table->string('address_line')->nullable()->after('phone');
            $table->string('city')->nullable()->after('address_line');
            $table->enum('role', ['user', 'admin', 'manager'])->default('user')->after('city');
            $table->rememberToken()->after('role'); // Добавляем remember_token
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'address_line',
                'city',
                'role',
                'remember_token', // Удаляем и remember_token
            ]);
        });
    }
};