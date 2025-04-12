<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roblox_users', function (Blueprint $table) {
            $table->unsignedBigInteger('roblox_user_id'); // เพิ่มคอลัมน์ก่อน
            $table->primary('roblox_user_id');
            $table->string('username');
            $table->text('cookie');
            $table->text('secret_key');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roblox_users');
    }
};
