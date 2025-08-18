<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audits', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('role'); // Snapshot of user's role (e.g., admin, user)
    $table->string('action'); // e.g., "Create", "Update", "Delete"
    $table->string('target')->nullable(); // optional: what was acted on
    $table->string('ip_address')->nullable();
    $table->text('description')->nullable();
    $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};
