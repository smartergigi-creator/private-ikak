<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE ebook MODIFY access_role ENUM('public','member','bc','operator') NOT NULL DEFAULT 'public'");
    }

    public function down(): void
    {
        DB::table('ebook')
            ->where('access_role', 'operator')
            ->update(['access_role' => 'bc']);

        DB::statement("ALTER TABLE ebook MODIFY access_role ENUM('public','member','bc') NOT NULL DEFAULT 'public'");
    }
};
