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

            // The link type (App\MenuLinks) and what it links to, like the page id or the url
            $table->string('type');
            $table->json('data');

            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['menu_id', 'position']);
        });
    }
};
