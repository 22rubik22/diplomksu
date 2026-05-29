<?php
// database/migrations/2024_01_15_000000_add_oauth_fields_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('yandex_id')->nullable()->unique()->after('id');
            $table->string('avatar')->nullable()->after('email');
            $table->timestamp('email_verified_at')->nullable()->after('email');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['yandex_id', 'avatar', 'email_verified_at']);
        });
    }
};