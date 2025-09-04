<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('audits', function (Blueprint $table) {
        $table->string('ip_address')->nullable()->after('target'); // Add IP column
    });
}

public function down()
{
    Schema::table('audits', function (Blueprint $table) {
        $table->dropColumn('ip_address');
    });
}

};
