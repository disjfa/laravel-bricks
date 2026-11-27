<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bricks', function (Blueprint $table) {
            $table->id();

            // The page or blog the brick is part of, see App\Bricks\Brickable
            $table->string('brickable_type');
            $table->unsignedBigInteger('brickable_id');

            $table->string('type');
            $table->json('data');
            $table->json('settings')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['brickable_type', 'brickable_id', 'position']);
        });
    }
};
