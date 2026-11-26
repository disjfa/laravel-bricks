<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['pages', 'blogs'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('meta_title')->nullable()->after('slug');
                $table->string('meta_image', 2048)->nullable()->after('meta_description');
                $table->boolean('noindex')->default(false)->after('meta_image');
            });
        }
    }
};
