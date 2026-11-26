<?php

use App\Enums\PublishStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('status')->default(PublishStatus::Concept->value)->after('meta_description')->index();
            $table->timestamp('published_until')->nullable()->after('published_at');
            $table->unsignedInteger('seqnr')->default(0)->after('published_until')->index();
        });

        // Pages with a publish date were published before the status existed.
        DB::table('pages')
            ->whereNotNull('published_at')
            ->update(['status' => PublishStatus::Active->value]);

        DB::table('pages')->update(['seqnr' => DB::raw('id')]);
    }
};
