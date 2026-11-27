<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Where an email goes for every submission of a form: one or more addresses, separated by commas.
 * Empty sends it to the admins.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->string('notification_email', 1000)->nullable()->after('success_message');
        });
    }
};
