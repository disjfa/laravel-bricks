<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('submit_label')->nullable();
            $table->text('success_message')->nullable();
            $table->timestamps();
        });

        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('name');
            $table->json('settings');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['form_id', 'name']);
            $table->index(['form_id', 'position']);
        });

        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->json('data');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }
};
