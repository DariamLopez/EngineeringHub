<?php

use App\Enums\AuditTrailsActionsEnum;
use App\Enums\AuditTrailsEntityTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('entity_type', AuditTrailsEntityTypeEnum::values());
            $table->unsignedBigInteger('entity_id');
            $table->enum('action', AuditTrailsActionsEnum::values());
            $table->json('before_json')->nullable();
            $table->json('after_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
    }
};
