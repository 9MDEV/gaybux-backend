<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ลบคอลัมน์ name
            if (Schema::hasColumn('users', 'name')) {
                $table->dropColumn('name');
            }

            // เพิ่ม username ที่ต้องไม่ซ้ำ
            $table->string('username')->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // คืนค่าคอลัมน์ name
            $table->string('name')->after('id');

            // ลบ username
            $table->dropColumn('username');
        });
    }
};
