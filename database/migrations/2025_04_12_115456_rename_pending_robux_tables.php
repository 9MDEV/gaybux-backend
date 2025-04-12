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
        Schema::rename('pending_robux_tables', 'pending_robuxes');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pending_robux_tables', function (Blueprint $table) {
            //
        });
    }
};
