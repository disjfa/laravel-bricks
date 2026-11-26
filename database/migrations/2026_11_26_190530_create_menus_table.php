<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('handle')->unique();
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('type');
            $table->foreignId('page_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('blog_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('url', 2048)->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['menu_id', 'position']);
        });
    }
};
