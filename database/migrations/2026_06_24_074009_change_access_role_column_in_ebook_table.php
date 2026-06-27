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
       Schema::table('ebook', function (Blueprint $table) {
        $table->string('access_role', 255)->nullable()->change();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ebook', function (Blueprint $table) {
        $table->enum('access_role', ['public', 'member', 'bc', 'operator'])->nullable()->change();
    });
    }
};
