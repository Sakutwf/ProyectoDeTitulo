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
        Schema::table('hojas_anuales', function (Blueprint $table) {
            $table->string('lista')->nullable()->after('cargo');
            $table->text('labor_efectuada')->nullable()->after('lista');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hojas_anuales', function (Blueprint $table) {
            $table->dropColumn(['lista', 'labor_efectuada']);
        });
    }
};
