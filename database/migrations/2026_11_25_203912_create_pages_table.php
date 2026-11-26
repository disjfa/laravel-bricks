<?php

use App\Enums\PublishStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();

            // SEO, see App\Concerns\HasSeo
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_image', 2048)->nullable();
            $table->boolean('noindex')->default(false);

            // Publishing, see App\Concerns\HasPublishing
            $table->string('status')->default(PublishStatus::Concept->value)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('published_until')->nullable();
            $table->unsignedInteger('seqnr')->default(0)->index();

            $table->timestamps();
        });
    }
};
