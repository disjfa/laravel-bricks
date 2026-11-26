<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bricks belonged to pages only, now they belong to any model that is built from bricks (pages and blogs).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bricks', function (Blueprint $table) {
            $table->nullableMorphs('brickable');
        });

        DB::table('bricks')->update([
            'brickable_type' => Page::class,
            'brickable_id' => DB::raw('page_id'),
        ]);

        Schema::table('bricks', function (Blueprint $table) {
            $table->dropForeign(['page_id']);
            $table->dropIndex(['page_id', 'position']);
            $table->dropColumn('page_id');
            $table->index(['brickable_type', 'brickable_id', 'position']);
        });
    }
};
