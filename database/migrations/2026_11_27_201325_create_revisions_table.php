<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The revisions of pages, blogs and forms (App\Revisions\Revisionable): a full snapshot after every save.
 * Revisions stay when their model is deleted, so a deleted item keeps its history.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revisions', function (Blueprint $table) {
            $table->id();
            $table->morphs('revisionable');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->foreignId('reverted_from_id')->nullable()->constrained('revisions')->nullOnDelete();
            $table->json('snapshot');
            $table->timestamps();
        });
    }
};
