<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('url')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('menus')->onDelete('cascade');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();
        });

        $now = now();

        DB::table('menus')->insert([
            [
                'name' => 'Home',
                'slug' => 'home',
                'url' => '/home',
                'parent_id' => null,
                'sort_order' => 10,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Website',
                'slug' => 'website',
                'url' => '/websites',
                'parent_id' => null,
                'sort_order' => 20,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'eBooks',
                'slug' => 'ebooks',
                'url' => '/home#ebooksSection',
                'parent_id' => null,
                'sort_order' => 30,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
